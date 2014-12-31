<?php

class AttachModel extends Model{
    protected $pk = 'id';
    protected $seqName = 'CAR_SEQ_ATTACHS_ID';
    protected $tableName = 'attachs';

    //处理图片的对象id
    private $object_id = 0;

    //水印图片地址
    private $waterPng = '';
    //Image类是否加载标志
    private $imgObj = false;

    //不同大小缩略图的高宽
    const IMG_BIG_WIDTH = 800;
    const IMG_BIG_HEIGHT = 600;
    const IMG_MEDIUM_WIDTH = 320;
    const IMG_MEDIUM_HEIGHT = 240;
    const IMG_SMALL_WIDTH = 120;
    const IMG_SMALL_HEIGHT = 90;
    //图片后缀
    const IMG_TYPE = '.jpg';
    //中等图片文件名
    const IMG_MEDIUM_NAME = '_266x200';
    //小图文件名
    const IMG_SMALL_NAME = '_min';

    //图片模型常量 1车源,6店铺水印logo,99车牌
    const IMG_MODULE_SALE = 1;
    const IMG_MODULE_WATER = 6;
    const IMG_MODULE_PLATE = 99;

    //车源图片封面
    protected $cover = '';  //图片地址
    protected $coverId = 0; //图片id

    //车源图片小图数组
    protected $smallPics = array();


    protected $fields = array(
        'object_id'=>'number(1,10)',              //图片所属模型(车源,文章,评估,店铺)ID
        'object_type'=>'number(1,2)',            //图片所属模型
        'file_type'=>'string(20)',          //图片类型格式 image/pjpeg
        'file_size'=>'number',              //图片大小
        'file_path'=>'string(10,150)',         //原始图片保存路径
        'mini_file_path'=>'string(10,150)',     //小图保存路径
        'insert_time'=>'date(yYYY-MM-DD hh24:mi:ss)',              //插入时间
        'status'=>'boolen',                 //是否审核通过
        'sort_order'=>'number',             //排序
        'is_cover'=>'boolen',               //是否封面
        'insert_user_id'=>'number',         //插入图片的用户ID
        'note'=>'string(500)',              //图片说明
        'hits'=>'number',                   //图片点击次数
    );

    /**
     * 获取部门的水印图片，图片格式是png，做缓存，10天更新一次
     * @param type $deptId
     */
    private function getDeptWater(){
        return false;
        $imgName = DATA_WATER_PATH.Cheyou::$userInfo['dept_id'].'.png';
        if(file_exists($imgName)){
            if(time() - filemtime($imgName) < 10*24*3600){
                $this->waterPng=$imgName;
                return true;
            }
        }
        $this->setOption('where', 'object_type='.self::IMG_MODULE_WATER.' and object_id=\''.Cheyou::$userInfo['dept_id'].'\'');
        $this->setOption('field', 'file_path');
        $water = $this->select();
        $imgFile = $water[0]['file_path'];
        if(!empty($imgFile)){
            $_con = file_get_contents($imgFile);
            if(!empty($_con))file_put_contents($imgName, $_con);
        }
        $this->waterPng=$imgName;
        return true;
    }

    /**
     * 保存图片
     */
    public function savePhotoes($photoes, $objId=0, $deptId = 0){
        if($objId)$this->setObject($objId);
        if(!$this->object_id)return false;
        $this->cover = '';
        $this->coverId=0;
        $this->smallPics = array();

        //if(empty($this->waterPng))$this->getDeptWater();
        if(!$this->imgObj){
            require_once  dirname(__FILE__).'/../Image.class.php';
            $this->imgObj = true;
        }

        //图片存储
        $savePath=date("Ym/");
        $saveDir=IMG_PATH.$savePath;
        $savePath="saleimg/" . $savePath;
        @mkdir($saveDir, 0755, true);
        //$key代表图片属于第几张;$i代表次序0,1,2递增
        $i=0;
        foreach($photoes as $key => $photo){
            if(empty($photo['content']))continue;
            $photoInfo=array();

            //不同大小图片命名
            $_saveName=randomstr().($deptId && !$photo['is_plate'] ? $deptId : '');
            $saveName = $_saveName.self::IMG_TYPE;
            $mediumName = $_saveName.self::IMG_MEDIUM_NAME.self::IMG_TYPE;
            $smallName = $_saveName.self::IMG_SMALL_NAME.self::IMG_TYPE;

            //保存原图
            $fullPath = $saveDir.$saveName;
            Image::saveBase64Img($photo['content'], $fullPath);

            //生成缩略图
            //Image::thumb($fullPath, $saveDir.$smallName, self::IMG_SMALL_WIDTH, self::IMG_SMALL_HEIGHT);
            //Image::thumb($fullPath, $saveDir.$mediumName, self::IMG_MEDIUM_WIDTH, self::IMG_MEDIUM_HEIGHT);

            //给大图加水印
            //Image::water($fullPath, $this->waterPng);

            //保存数据
            $photoInfo['file_path']=$savePath.$saveName;
            //$photoInfo['mini_file_path']=IMG_DOMAIN.$savePath.$smallName;
            $photoInfo['mini_file_path']='';
            $photoInfo['is_cover']=$photo['cover']?1:0;
            $photoInfo['sort_order']=$key;

            if($photo['id']){
                $photoInfo['id']=$photo['id'];
                $this->removePic($photo['id']);
                $this->save($photoInfo);
            }else{
                if (isset($photo['is_plate']) && $photo['is_plate']) {
                    $photoInfo['object_type'] = self::IMG_MODULE_PLATE;
                } else {
                    $photoInfo['object_type']=self::IMG_MODULE_SALE;
                }
                $photoInfo['object_id']=$this->object_id;
                $photoInfo['insert_time']='to_date(\''.date("Y-m-d H:i:s").'\',\'YYYY-MM-DD hh24:mi:ss\')';
                $photoInfo['status']=1;
                $photoInfo['insert_user_id']=AppServAuth::$userInfo['user']['username'];
                $photoInfo['hits']=0;
                $this->add($photoInfo);
            }

            $this->smallPics[] = $this->buildPhoto($photoInfo['file_path']);
            if($photo['cover']){
                $this->cover = $photoInfo['file_path'];
                $this->coverId = $photoInfo['id'];
            }
        }

        //取消其它封面
        if($this->coverId){
            $this->query('update car_attachs set is_cover=0 where object_type='.self::IMG_MODULE_SALE.'
                and object_id='.$this->object_id.' and id !='.$this->coverId);
        }

        return $returnArr;
    }

    private function removePic($id){
        $sql="select file_path from car_attachs where id='".$id."'";
        $result=$this->getOne($sql);
        $file_path=$result['file_path'];
        $ext=substr($file_path, strrpos($file_path, '.'));
        $filePath=str_replace(IMG_DOMAIN,'',$file_path);
        $maxFile=IMG_PATH.$filePath;
        unlink($maxFile);
        //$miniFile=IMG_PATH.str_replace($ext, self::IMG_SMALL_NAME.$ext, $filePath);
        //$mediumFile=IMG_PATH.str_replace($ext, self::IMG_MEDIUM_NAME.$ext, $filePath);
        //unlink($miniFile);
        //unlink($mediumFile);
        //echo $maxFile.'<br />'.$miniFile.'<br />'.$mediumFile.'<br />';
        return false;
    }

    public function getCover(){
        return $this->cover;
    }

    public function getPics(){
        return $this->smallPics;
    }

    public function setObject($id){
        $this->object_id = $id;
    }

    public function getPhotoes($id){
//         $sql="select id,file_path,sort_order,is_cover,object_type from car_attachs where (object_type='".self::IMG_MODULE_SALE."' or object_type='".self::IMG_MODULE_PLATE."') and object_id='".$id."' and status=1 order by sort_order asc";
        $sql="select id,file_path,sort_order,is_cover,object_type from car_attachs where object_type in (1,99,98,97) and object_id='".$id."' and status=1 order by sort_order asc";
        $_result=$this->query($sql);

        $result=array();
        foreach($_result as $r){
            $r['file_path'] = $this->buildPhoto($r['file_path']);
            $r['cover']=$r['is_cover'];
            $r['index']=$r['sort_order'];
            //如果为车牌图片则增加数组元素作标识
//             if ($r['object_type'] == self::IMG_MODULE_PLATE) {
            if (in_array($r['object_type'], array(99,98,97))) {
                $r['is_plate'] = 1;
            }
            unset($r['object_type']);
            unset($r['sort_order']);
            unset($r['is_cover']);
            unset($r['mini_file_path']);
            $result[]=$r;
        }
        return $result;
    }

    //生成不同尺寸的图片，$path 原图url
    public function buildPhoto($path, $width = 120, $height = 90) {
        $path = str_replace('273.cn', '273.com.cn', $path);
        $imgDomain = IMG_DOMAIN;
        if (strpos($path,'http://')===0) {
            $imgDomain = '';
        }

        $path = str_replace('_min.jpg', '.jpg', $path);
        return $imgDomain . str_replace('.jpg', '_' .$width . '-' . $height . '_6_0_0.jpg', $path);
    }
    
    //取车牌图片路径
    public function getPathOne($objectId) {
        $sql="select mini_file_path,file_path from car_attachs where object_type='".self::IMG_MODULE_PLATE."' and object_id='".intval($objectId)."' and status=1 order by sort_order asc";
        $attachsInfo = $this->query($sql);
        return !empty($attachsInfo[0]['mini_file_path'])?$this->buildPhoto($attachsInfo[0]['mini_file_path']):$this->buildPhoto($attachsInfo[0]['file_path']);
    }
}

?>
