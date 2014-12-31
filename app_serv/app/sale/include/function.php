<?php

/**
 * D函数用于实例化Model
 */
function D($name='') {
    if(empty($name))return new Model;

    static $_model = array();
    if(isset($_model[$name]))
        return $_model[$name];

    $class = $name.'Model';
    $modelPath = dirname(__FILE__) . '/Model/'.$name.'Model.class.php';
    if(file_exists($modelPath)){
        require $modelPath;
        if(class_exists($class)) {
            $model = new $class($name);
        }
    }else {
        $model  = new Model($name);
    }
    $_model[$name]  =  $model;
    return $model;
}

/**
 * 数据接口
 * @staticvar null $_db
 * @return type
 */
function db(){
    static $_db = null;
    if($_db === null) {
        $_db = Db::getInstance(Cheyou::$dbConfig);
    }
    return $_db;
}

/**
 * 时间格式转化为 Y-m-d H:i:s 格式
 * @param type $time
 * @return string
 */
function timeToFormat($time){
    if(preg_match('/^([0-9]{4})-([0-9]{1,2})$/', $time))return 'YYYY-MM';
    elseif(preg_match('/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})$/', $time))return 'YYYY-MM-DD';
    else return 'YYYY-MM-DD hh24:mi:ss';
}

/**
 * 生成n位随机字符串
 * @param type $n
 * @return type 生成n位随机字符串
 */
function randomstr($n=15){
    return substr(str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, $n);
}


/**
 * 创建多级目录
 * @param type $dir
 * @return type
 */
function mkdirs($dir){
	return is_dir($dir) or (mkdirs(dirname($dir)) and mkdir($dir, 0777));
}

// 快速文件数据读取和保存 针对简单类型数据 字符串、数组
function F($name, $value='', $path=DATA_PATH) {
    static $_cache = array();
    $filename = $path . $name . '.php';
    if (isset($_cache[$name]))
        return $_cache[$name];
    // 获取缓存数据
    if (is_file($filename)) {
        $value = include $filename;
        $_cache[$name] = $value;
    } else {
        $value = false;
    }
    return $value;
}


/**
 * 根据车源品牌信息生成标题，买车和卖车都需要
 * @param type $type
 * @param type $make
 * @param type $family
 * @param type $vehicle
 */
function makeCaption($type='',$make='',$family='',$vehicle=''){
    if(empty($make))return;

    $caption='';
    $makes=F('make', '', DATA_COMMON_PATH);
    $caption.=$makes[$make];

    if(!empty($family)){
        $familys=F($make, '', DATA_FAMILY_PATH);
        $famName=!empty($type)?$familys[$type.'_'.$family]:$familys[$family];  //客车、货车缓存名字前加vehicle_type
        $caption .= $famName;
        if(!empty($vehicle)){
            $vehicles=F($family, '', DATA_VEHICLE_PATH.$make.'/');
            $caption .= $vehicles[$vehicle];
        }
    }
    return $caption;
}

/**
 * 分词
 * @param type $keywords
 * @return type
 */
function fenci($keywords){
    $keywords=preg_replace('/\s+/',' ',  strtolower($keywords)); //去掉收尾空格并把多个空格合并为一个空格
    if(empty($keywords))return false;

    $keyList=array();
    $so = scws_new();
    $so->set_charset('utf8');
    $so->set_dict(DATA_PATH.'etc/dict.utf8.xdb');
    $so->set_rule(DATA_PATH.'etc/rules.utf8.ini');
    $so->send_text($keywords);
    while ($tmp = $so->get_result()){
        foreach($tmp as $w)$keyList[]=$w['word'];
    }
    $so->close();

    return $keyList;

}

/**
 * 电话号码加密
 * @param type $mobile
 */
function enPhone($mobile){
    if(empty($mobile) or strlen($mobile)<9)return '';
    $times=strval(time());
    $min=$times[8];
    $max=$times[9];
    $ins=$times[7];
    $rnd=$times[6];
    if($min==$max)$max=9-$min;
    $_t=$mobile[$max];
    $mobile[$max]=$mobile[$min];
    $mobile[$min]=$_t;
    $mobile.=$min.$max;
    $mobile=substr($mobile,0,$ins).$rnd.substr($mobile,$ins).$ins;

    return $mobile;
}

/**
 * 电话号码解密
 * @param type $mobile
 */
function dePhone($mobile){
    if(empty($mobile))return '';
    $len=strlen($mobile);
    $min=$mobile[$len-3];
    $max=$mobile[$len-2];
    $ins=$mobile[$len-1];
    $_mobile=substr($mobile,0,$len-3);
    $_mobile=substr($_mobile,0,$ins).substr($_mobile,$ins+1);
    $_t=$_mobile[$max];
    $_mobile[$max]=$_mobile[$min];
    $_mobile[$min]=$_t;

    return $_mobile;
}

/**
 * 生成info_id
 * 1卖车 2买车 3客户
 */
function getInfoid($infoType){
    switch($infoType){
        case '1':
            $type = 'S';
            break;
        case '2':
            $type = 'B';
            break;
        case '3':
            $type = 'C';
            break;
        case '4':
            $type = 'T';
            break;
        case '5':
            $type = 'L';
            break;
        case '6'://意向协议
            $type = 'Y';
            break;
        case '7'://结算协议
            $type = 'P';
            break;
        case '8'://过户管理
            $type = 'G';
            break;
        case '16'://投诉编号
            $type = 'TS';
            break;
        default:
            $type = 'L';
            break;
    }


    $name = 'MBS_INFOID_'.$infoType.'_'.date('Ymd');

    $row=D()->query("SELECT ".$name.".nextval as id from sys.dual");   //获取客户最新id

    $seq = $row[0]['id'];
    $nextInfoId = date('Ymd').sprintf("%04d",$seq);

    return $type.$nextInfoId;
}
