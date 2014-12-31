<?php

/**
 * @package              v3
 * @subpackage           
 * @author               $Author:   zenghuanjia$
 * @file                 $HeadURL$
 * @version              $Rev$
 * @lastChangeBy         $LastChangedBy$
 * @lastmodified         $LastChangedDate$
 * @copyright            Copyright (c) 2012, www.273.cn
 */
require_once API_PATH . '/interface/CarSaleForOracleInterface.class.php';
require_once API_PATH . '/interface/MbsDeptInterface.class.php';
require_once API_PATH . '/interface/CarCompetitionInterface.class.php';
require_once(FRAMEWORK_PATH . '/util/gearman/LoggerGearman.class.php');

class SpidersServiceApp {

    /**
     * 个人车源入库
     * @param $params
     * @return mix
     */
    public function insertPersonalCar($params) {
        $infoList = empty($params['info_list']) ? '[]' : $params['info_list'];
        $infoList = str_replace('\\', '', $infoList);
        $infoList = json_decode($infoList, true);
        if (empty($infoList)) {
            return false;
        }
        $comId = 1;
        foreach ($infoList as $info) {
            if (!empty($info['from_url'])) {
                $params = array(array('from_url', '=', $info['from_url']));
                $num = CarCompetitionInterface::getCount($params);
                if ($num > 0) {
                    continue;
                } 
            }
            $comInfo = array(
                'from_type' => $info['from_type'],
                'telephone' => $info['telephone'],
                'province'  => $info['province'],
                'city'  => $info['city'],
                'seller_name'  => $info['seller'],
                'from_url'  => $info['from_url'],
                'title'  => $info['title'],
                'price'  => $info['price'],
                'card_time'  => empty($info['card_time']) ? (!empty($info['card_time_str']) ? strtotime($info['card_time_str']) : 0) : $info['card_time'],
                'description'   => $info['description'],
                'brand_id'  => $info['brand_id'],
                'series_id' => $info['series_id'],
                'kilometer' => $info['kilometer'],
                'create_time'   => time(),
            );
            if (!empty($info['imgs']) && is_array($info['imgs'])) {
                $imgs = $info['imgs'];
                sort($imgs);
                $comInfo['image_json'] = json_encode($imgs);
            }
            $comId = CarCompetitionInterface::add($comInfo);
            if ($comId > 0) {
                $comExtInfo = array(
                    'car_competition_id' => $comId,
                    'safe_force_time' => isset($info['safe_force']) ? $info['safe_force'] : 0,
                    'year_check_time' => isset($info['year_check']) ? $info['year_check'] : 0,
                    'type_id' => isset($info['type_id']) ? $info['type_id'] : 0,
                    'maker_id' => isset($info['maker_id']) ? $info['maker_id'] : 0,
                    'gearbox_type' => isset($info['gearbox_type']) ? $info['gearbox_type'] : 0,
                );
                if (isset($info['is4s'])) {
                    if ($info['is4s'] == '0') {
                        $comExtInfo['maintain_address'] = 2;
                    } elseif ($info['is4s'] == '1') {
                        $comExtInfo['maintain_address'] = 1;
                    }
                }
                include_once API_PATH . '/interface/CarCompetitionExtInterface.class.php';
                CarCompetitionExtInterface::add($comExtInfo);
            }
        }
        return $comId;
    }

    public function add($params) {
        $result = array();
        $params['data'] = json_decode($this->unCompressLongStr($params['data']),true);
        if (empty($params['data']) || !is_array($params['data'])) {
            return $result;
        }
        $allCity = MbsDeptInterface::getAllDeptCity();
        $allPhone = MbsUserInterface::getAllUserPhone();
        foreach ($params['data'] as $d) {
        	if (isset($allPhone[$d['telephone']])) {
                //LoggerGearman::logInfo(array('data'=>'爬虫出现重复' . date('Y-m-d H:i:s'), 'identity'=>'spider'));
        		continue;
        	}
            $d['title'] = $this->charsetIconv($d['title']);
            $d['contact_user'] = $this->charsetIconv($d['contact_user']);
            $d['brind_name'] = $this->charsetIconv($d['brind_name']);
            $d['note'] = $this->charsetIconv($d['note']);
            $d['brand_caption'] = $this->charsetIconv($d['brand_caption']);
            $isDept = 0 ;
            if (isset($allCity[$d['city']])) {
            	$d['order_source'] = 40;
                $isDept = 1;
            }
            $carId = CarSaleForOracleInterface::getNextId();
            $d['id'] = $carId;
            $sourceId = $d['source_id'];
            $photoId = $d['photo_id'];
            $photoTime = $d['photo_time'];
         	unset($d['photo_id']);
            unset($d['source_id']);
            unset($d['photo_time']);
            $ret = CarSaleForOracleInterface::add($d);
            if ($ret) {
            	$data = array(
                    'source_id' => $sourceId,
                    'car_id' => $carId,
                	'photo_id' => $photoId,
                	'photo_time'=>$photoTime,
                    'is_dept' => $isDept
                );
            	//写入缓存
            	//CacheNamespace::writeLocalCache('spiders_'.date('Yn',$photoTime).'_'.$carId,$data);
                $result[] = $data;
            } else {
                LoggerGearman::logFatal(array('data'=>$sourceId.':爬虫入库失败，数据库错误' . date('Y-m-d H:i:s'), 'identity'=>'spider.to.car.sale'));
            }
        }
        return $result;
    }

    public function addToCarSpiderData($params) {
        $result = array();
        $params['data'] = json_decode($this->unCompressLongStr($params['data']),true);
        if (empty($params['data']) || !is_array($params['data'])) {
            return $result;
        }
        $allCity = MbsDeptInterface::getAllDeptCity();
        $allPhone = MbsUserInterface::getAllUserPhone();
        require_once FRAMEWORK_PATH . '/util/db/DbFactory.class.php';
        require_once CONF_PATH . '/db/OracleConfig.class.php';
        $db = DbFactory::create('oracle', OracleConfig::$DB_MASTER, OracleConfig::$DB_SLAVE);
        $tableName = 'car_spider_data';
        foreach ($params['data'] as $d) {
            if (isset($allPhone[$d['telephone']])) {
                continue;
            }
            $d['title'] = $this->charsetIconv($d['title']);
            $d['contact_user'] = $this->charsetIconv($d['contact_user']);
            $d['brind_name'] = $this->charsetIconv($d['brind_name']);
            $d['note'] = $this->charsetIconv($d['note']);
            $d['brand_caption'] = $this->charsetIconv($d['brand_caption']);
            $isDept = 0 ;
            if (isset($allCity[$d['city']])) {
                $d['order_source'] = 40;
                $isDept = 1;
            }
            $carId = CarSaleForOracleInterface::getNextId();
            $d['id'] = $carId;
            $sourceId = $d['source_id'];
            $photoId = $d['photo_id'];
            $photoTime = $d['photo_time'];
            unset($d['photo_id']);
            unset($d['source_id']);
            unset($d['photo_time']);
            $ret = $ret = $db->insert($tableName, $d);
            if ($ret) {
                $data = array(
                    'source_id' => $sourceId,
                    'car_id' => $carId,
                    'photo_id' => $photoId,
                    'photo_time'=>$photoTime,
                    'is_dept' => $isDept
                );
                //写入缓存
                //CacheNamespace::writeLocalCache('spiders_'.date('Yn',$photoTime).'_'.$carId,$data);
                $result[] = $data;
            }
            else {
                LoggerGearman::logFatal(array('data'=>$sourceId.':爬虫入库失败，数据库错误' . date('Y-m-d H:i:s'), 'identity'=>'spider.to.car.sale'));
            }
        }
        return $result;
    }

    //爬虫入库到mysql
    public function addMysql($params) {
        $result = array();
        $params['data'] = json_decode($this->unCompressLongStr($params['data']),true);
        if (empty($params['data']) || !is_array($params['data'])) {
            return $result;
        }
        require_once API_PATH .'/model/CarSaleModel.class.php';
        require_once API_PATH .'/model/CarSaleExtModel.class.php';
        require_once API_PATH .'/interface/CarCompetitionInterface.class.php';
        $carSaleModel = new CarSaleModel();
        $carSaleExtModel = new CarSaleExtModel();

        $allCity = MbsDeptInterface::getAllDeptCity();
        $allPhone = MbsUserInterface::getAllUserPhone();

        foreach ($params['data'] as $d) {
            if (isset($allPhone[$d['telephone']])) {
                continue;
            }
            if (isset($allCity[$d['city']])) {
                $d['order_source'] = 40;
            }
            $carSaleInfo = $carSaleExtInfo = array();
            $carSaleInfo = $this->_carSaleFiles($d);
            $carSaleExtInfo = $this->_carSaleExtFiles($d);

            $carId = $carSaleModel->insert($carSaleInfo);
            $carSaleExtInfo['car_id'] = $carId;
            $carSaleExtModel->insert($carSaleExtInfo);
            $sourceId = $d['source_id'];
            $photoId = $d['photo_id'];
            $photoTime = $d['photo_time'];
            if ($carId) {
                $data = array(
                    'source_id' => $sourceId,
                    'car_id' => $carId,
                    'photo_id' => $photoId,
                    'photo_time'=>$photoTime
                );
                $result[] = $data;

                //插入car_competition表
                /* $competitionInfo = array(
                        'car_sale_id' => $carId,
                        'telephone' => $carSaleExtInfo['telephone'],
                        'create_time' => time()
                    );
                CarCompetitionInterface::insertCompetition($competitionInfo); */
            }
            else {
                LoggerGearman::logFatal(array('data'=>$sourceId.':爬虫入库失败，数据库错误' . date('Y-m-d H:i:s'), 'identity'=>'spider.to.car.sale'));
            }
        }
        return $result;
    }

    function updateOrderSource($params){
        if(empty($params)){
            return false;
        }
        $params['data'] = json_decode($this->unCompressLongStr($params['data']),true);
        $info = array(
            'update' => array('order_source' => 40),
            'cond'  => array('id' => $params['data'])
        );
        $ret = CarSaleForOracleInterface::edit($info);
        if($ret){
            return $params['data'];
        }
        return false;
    }

    private function _carSaleFiles($info){
        if(empty($info)){
            return false;
        }
        $time = time();
        $carSaleInfo = array(
            'create_time' => strtotime($info['insert_time']),
            'update_time' => $time,
            'type_id' => $info['car_type'],
            'brand_id' => $info['brand_id'],
            'maker_id' => $info['maker_id'],
            'series_id' => $info['series_id'],
            'deal_province_id' => $info['province'],
            'deal_city_id' => $info['city'],
            'title' =>  $info['brand_caption'],
            'price' =>  $info['price'],
            'kilometer' =>  $info['kilometer'],
            'description' =>  $info['note'],
            'card_time' =>  strtotime($info['card_time']),
            'safe_force_time' => strtotime($info['safe_time']),
            'year_check_time' =>  strtotime($info['year_check_time']),
            'safe_business_time' =>  strtotime($info['busi_insur_time']),
            'maintain_address' =>  $info['maintain_address'],
            'status' =>  $info['status'],
            'order_status' => $info['order_status'],
            'index_update_time' => $time,
            'source_type' =>  $info['info_source'],
            );
        return $carSaleInfo;
    }
    private function _carSaleExtFiles($info){
        if(empty($info)){
            return false;
        }
        $carSaleExtInfo = array(
            'car_id' => $info['car_id'], // 车源id
            'plate_province_id' => $info['plate_province'], // 上牌省份
            'plate_city_id' => $info['plate_city'], // 上牌城市
            'seller_name' => $info['contact_user'], // 卖主姓名
            'telephone' => $info['telephone'], // 车主联系方式
            'source_type' => $info['info_source'], // 信息来源1网站,2个人,3店秘录入,5来电,6其他,7联盟站,8呼叫中心,9抓取,10营销人员,11外聘营销人员,12评估,13手机客户端,14赶集
            'order_source'      => $info['order_source'], //来源渠道
            );
        return $carSaleExtInfo;
    }
    function compressLongStr($str) {
        return bin2hex(gzcompress($str));
    }
    
    function unCompressLongStr($hexStr) {
        return gzuncompress(pack('H*', $hexStr));
    }
    function charsetIconv($vars, $from = 'UTF-8', $to = 'GBK//IGNORE') {
        if (is_array($vars)) {
            $result = array();
            foreach ($vars as $key => $value) {
                $result[$key] = $this->charsetIconv($value, $from, $to);
            }
        } else {
            $result = iconv($from, $to, $vars);
        }
        return $result;
    }
    
    /**
     * @desc 删除链接已无效的搜车个人车源
     * @param string $url 车源的来源网址
     * @return boolean
     */
    public function delPersonalCarByUrl($params) {
        if (empty($params['url'])) {
            return false;
        }
        $condParams = array(
            'field' => 'create_time, from_type, telephone',
            'cond' => array(
                array('from_url', '=', $params['url']),
            ),
        );
        $row = CarCompetitionInterface::get($condParams);
        if (!empty($row)) {
            $filters = array(
                array('from_url', '=', $params['url']),
            );
            $result = CarCompetitionInterface::delete($filters);
            if ($result > 0) {
                include_once API_PATH . '/interface/CarCompetitionDelUrlLogInterface.class.php';
                $insertArr = array(
                    'from_url' => $params['url'],
                    'from_type' => $row['from_type'],
                    'telephone' => $row['telephone'],
                    'create_time' => $row['create_time'],
                );
                CarCompetitionDelUrlLogInterface::add($insertArr);
                return true;
            }
        }
        return false;
    }
    
    /**
     * @brief 全网淘车未读信息个数
     * @author 林宇
     * @param : 参数
     * @return json格式数据
     * 返回值名称   | 返回值类型   | 返回值补充描述
     * ------------|----------|------------------------------------------------
     * number | int   | 信息个数
     * errorMessage| string   | 如果发送异常的话，保存异常说明信息
     */
    public function getNewAllNetCount($params) {
        $userInfo = AppServAuth::$userInfo['user'];
        $number = (int) CarCompetitionInterface::getNewCount($userInfo);
        return array('number' => $number);
    }
    
    /**
     * @brief 全网淘车抢车源更新拨打状态
     * @author 林宇
     * @param : 参数
     * @return json格式数据
     * 返回值名称   | 返回值类型   | 返回值补充描述
     * ------------|----------|------------------------------------------------
     * result | int   | 操作记录id
     * errorMessage| string   | 如果发送异常的话，保存异常说明信息
     */
    public function sendCallEventToTao($params) {
        include_once API_PATH . '/interface/CarCompetitionGrabLogInterface.class.php';
        if (empty($params['source_id']) || empty($params['source_type'])) {
            return false;
        }
        $userInfo = AppServAuth::$userInfo['user'];
        $result = CarCompetitionGrabLogInterface::logCallAction($params['source_id'], $params['source_type'], $userInfo['username'], 'competition');
        return array('result' => $result);
    }
}
