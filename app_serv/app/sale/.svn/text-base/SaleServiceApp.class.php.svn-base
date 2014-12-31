<?php

/**
 * @package              v3
 * @subpackage           
 * @author               $Author:   weihongfeng$
 * @file                 $HeadURL$
 * @version              $Rev$
 * @lastChangeBy         $LastChangedBy$
 * @lastmodified         $LastChangedDate$
 * @copyright            Copyright (c) 2012, www.273.cn
 */
require_once API_PATH . '/interface/CarSaleInterface.class.php';
require_once API_PATH . '/interface/AccountInterface.class.php';
require_once API_PATH . '/interface/CreditInterface.class.php';
require_once API_PATH . '/interface/LocationInterface.class.php';
require_once CONF_PATH . '/payment/PaymentConfig.class.php';
require_once API_PATH . '/interface/MbsDeptInterface.class.php';
require_once API_PATH . '/interface/MbsUserInterface.class.php';
require_once API_PATH . '/interface/MobileToCityInterface.class.php';
require_once FRAMEWORK_PATH . '/util/cache/CacheNamespace.class.php';
require_once CONF_PATH . '/cache/MemcacheConfig.class.php';
require_once  dirname(__FILE__) . '/include/config.php';
require_once  dirname(__FILE__) . '/include/function.php';
require_once  dirname(__FILE__) . '/include/Db.class.php';
require_once  dirname(__FILE__) . '/include/Model.class.php';
require_once  dirname(__FILE__) . '/include/Cheyou.class.php';
require_once  dirname(__FILE__) . '/include/Request.class.php';
require_once API_PATH . '/interface/MbsRoleInterface.class.php';
require_once API_PATH . '/interface/VehicleV2Interface.class.php';
require_once API_PATH . '/interface/MbsCheckCarNumberInterface.class.php';
require_once API_PATH . '/interface/MbsCheckCarPhotoInterface.class.php';
require_once APP_SERV . '/config/AppServConfVars.class.php';
class SaleServiceApp {

    private $messageType = array(
        1 => 'checkMessage',
        2 => 'visitMessage',
        3 => 'message',
    );
    
    public function getInfoByExtPhone($params) {
        return CarSaleInterface::getInfoByExtPhone($params);
    }

    public function saleList($params) {
        if(empty($params['follow_user'])) {
            $params['follow_user'] = $params['salesman'];
        }
        $saleModel = D('Sale');
         $user = 0;
        if ($params['query_type'] == 'my') {
            $user = AppServAuth::$userInfo['user']['username'];
        }
        //query_type值为store时为查询店内的卖车列表
        if($params['query_type'] == 'store') {
            $user = AppServAuth::$userInfo['user']['dept_id'];
        }
        //搜索查询时，若关键字为品牌或车系，将关键字转换为品牌或车系的ID进行查询，列表多返回品牌车系数据
        if (!empty($params['keyword'])) {
            if (is_numeric($params['keyword']) || preg_match('/^[\x81-\xfe][\x40-\xfe]{2,4}[A-Z][A-Z0-9]{4}[A-Z0-9]$/', $params['keyword'])) {
        
            } else {
                $brandInfo = $this->_parseKeywords($params['keyword']);
                if ($brandInfo) {
                    $params['keyword'] = '';
                    if ($brandInfo['series_id']) {
                        $params['series_id'] = $brandInfo['series_id'];
                    } else {
                        $params['brand_id'] = $brandInfo['brand_id'];
                    }
                    $brandInfo['series_id'] = $brandInfo['series_id'] ? $brandInfo['series_id'] : '';
                    $brandInfo['series_name'] = $brandInfo['series_name'] ? $brandInfo['series_name'] : '';
                    $brandInfo['series_path'] = $brandInfo['series_path'] ? $brandInfo['series_path'] : '';
                    $data['ext'] = $brandInfo;
                }
            }
        }
        //获取车源总数
        $saleListNumsArr = $saleModel->saleList(
                                $user,
                                intval($params['start']),
                                intval($params['end']) ? intval($params['end']):20,
                                intval($params['status']),
                                trim($params['keyword']),
                                trim($params['insert_time']),
                                trim($params['price']),
                                trim($params['time']),
                                trim($params['make_code']),
                                trim($params['family_code']),
                                trim($params['vehicle_type']),
                                intval($params['sale_status']),
                                intval($params['published']),
                                intval($params['originate']),
                                intval($params['follow_user']),
                                intval($params['province']),
                                intval($params['city']),
                                intval($params['color']),
                                intval($params['gear_type']),
                                intval($params['brand_id']),
                                intval($params['series_id']),
                                intval($params['type_id']),
                                true
                            );
        $data['total'] = $saleListNumsArr[0]['count(id)'];
        //获取车源列表
        $saleList = $saleModel->saleList(
                        $user,
                        intval($params['start']),
                        intval($params['end']) ? intval($params['end']):20,
                        intval($params['status']),
                        trim($params['keyword']),
                        trim($params['insert_time']),
                        trim($params['price']),
                        trim($params['time']),
                        trim($params['make_code']),
                        trim($params['family_code']),
                        trim($params['vehicle_type']),
                        intval($params['sale_status']),
                        intval($params['published']),
                        intval($params['originate']),
                        intval($params['follow_user']),
                        intval($params['province']),
                        intval($params['city']),
                        intval($params['color']),
                        intval($params['gear_type']),
                        intval($params['brand_id']),
                        intval($params['series_id']),
                        intval($params['type_id'])
                        );
        if (empty($saleList)) {
            return array('total' => $data['total'], 'ext' => '', 'list' => array());
        }
        $saleList = $this->_format($saleList, 'sale', $params['query_type']);
        $data['list'] = $saleList;
        return $data;
    }

    public function phoneList($params) {
        $saleModel = D('Sale');
        $phoneList = $saleModel->phoneList($params);
        if(empty($phoneList)) {
            return array();
        }
        return $this->_format($phoneList, 'phone', $params['query_type']);
    }

    public function buyList($params) {
        $buyModel = D('Buy');
        $user = 0;
        if ($params['query_type'] == 'my') {
            $user = AppServAuth::$userInfo['user']['username'];
        }
        //query_type值为store时为查询店内的买车列表
        if($params['query_type'] == 'store') {
            $user = AppServAuth::$userInfo['user']['dept_id'];
        }
        //搜索查询时，若关键字为品牌或车系，将关键字转换为品牌或车系的ID进行查询，列表多返回品牌车系数据
        if (!empty($params['keyword'])) {
            if (is_numeric($params['keyword'])) {
        
            } else {
                $brandInfo = $this->_parseKeywords($params['keyword']);
                if ($brandInfo) {
                    $params['keyword'] = '';
                    if ($brandInfo['series_id']) {
                        $params['series_id'] = $brandInfo['series_id'];
                    } else {
                        $params['brand_id'] = $brandInfo['brand_id'];
                    }
                    $brandInfo['series_id'] = $brandInfo['series_id'] ? $brandInfo['series_id'] : '';
                    $brandInfo['series_name'] = $brandInfo['series_name'] ? $brandInfo['series_name'] : '';
                    $brandInfo['series_path'] = $brandInfo['series_path'] ? $brandInfo['series_path'] : '';
                    $data['ext'] = $brandInfo;
                }
            }
        }
        //获取车源总数
        $buyListNumsArr = $buyModel->buyList(
                            $user,
                            intval($params['start']),
                            intval($params['end']) ? intval($params['end']):20,
                            intval($params['status']),
                            trim($params['keyword']),
                            trim($params['insert_time']),
                            trim($params['price']),
                            trim($params['time']),
                            intval($params['brand_id']),
                            intval($params['series_id']),
                            intval($params['type_id']),
                            true
                        );
        $data['total'] = $buyListNumsArr[0]['count(id)'];
        $buyList = $buyModel->buyList(
                        $user,
                        intval($params['start']),
                        intval($params['end']) ? intval($params['end']):20,
                        intval($params['status']),
                        trim($params['keyword']),
                        trim($params['insert_time']),
                        trim($params['price']),
                        trim($params['time']),
                        trim($params['make_code']),
                        trim($params['family_code']),
                        trim($params['vehicle_type']),
                        intval($params['brand_id']),
                        intval($params['series_id']),
                        intval($params['type_id'])
                        );
        if (empty($buyList)) {
            return array('total' => $data['total'], 'ext' => '', 'list' => array());
        }
        $buyList = $this->_format($buyList, 'buy', $params['query_type']);
        $data['list'] = $buyList;
        return $data;
    }

    public function newSale($params) {
        $balance = $this->_getBalance();
        if ($balance < -50) {
            throw new AppServException(AppServErrorVars::CUSTOM, '门店欠费超过50，发布车源功能被关闭');
        }
        if ($this->_isPunished()) {
            throw new AppServException(AppServErrorVars::CUSTOM, '您已经被限制发布卖车,3天内不允许发布与刷新车源');
        }
        //福州地区发布车源时必须同时填写车牌号和车源图片，否则不允许发布        
        $is_force = AppServConfVars::getConfigCity(AppServAuth::$userInfo['user']['city']);
        if (!empty($is_force)) { 
            foreach ($params['image'] as $key => $photo) {
                if (isset($photo['is_plate']) && $photo['is_plate']) {
                    $imagePlate = $params['image'][$key];
                    break;
                }
            }
            if (empty($params['car_number']) || empty($imagePlate['content'])) {
                throw new AppServException(AppServErrorVars::CUSTOM, '发布失败！请填写车牌号并上传车源图片！');
            }
        }
        
        //去除重复车源
        if (isset($params['car_number']) && $params['car_number']) {
            $this->_checkDuplicateEntry(trim($params['car_number']));
        }
        $photoes=$params['image'];
        unset($params['image']);
        $saleModel = D('Sale');
        $saleId = $saleModel->newSale($params);
        if($saleId){
            //图片处理
            if (!empty($photoes)) {
                $attachModel=D('Attach');
                $attachModel->savePhotoes($photoes, $saleId, intval($params['dept_id']));
                //处理sale的缩略图
                $cover=$attachModel->getCover();
                $saleModel->saleCover($cover, $saleId);
            }

            //客户信息处理
            $customerInfo=array(
                'province'=>intval($params['province']),
                'city'=>intval($params['city']),
                'real_name'=>trim($params['contact_user']),
                'mobile'=>trim($params['telephone'])
            );
            if ($params['telephone2']) {
                $customerInfo['telephone'] = trim($params['telephone2']);
            }
            if ($params['idcard']) {
                $customerInfo['idcard'] = trim($params['idcard']);
            }
            $customerModel=D("Customer");
            $cid = $customerModel->newCustomer($customerInfo);
            $saleModel->saleCustomer($cid, $saleId);
            
            //插入车牌信息
            $carData = $saleModel->saleInfo($saleId);
            $CheckCarNumberInfo = array(
                    'car_id' => $saleId,
                    'car_number' => trim($params['car_number']),
                    'create_time'=> time(),
                    'brand' => $carData['brand_caption'],
                    'contact_user' => $params['contact_user'],
//                     'follow_user'  => AppServAuth::$userInfo['user']['username'],
                    'follow_user'  => $carData['follow_user'],
                    'dept_id'   => AppServAuth::$userInfo['user']['dept_id'],
                    'car_telephone' => trim($params['telephone']),
            );
            if (!empty($photoes)) {
                $attachModel=D('Attach');
                $CheckCarNumberInfo['photo'] = $attachModel->getPathOne($saleId);
                //若有上传车牌图片，将车牌图片信息插入mbs_check_car_photo
                if ($CheckCarNumberInfo['photo'] != 'http://img.273.com.cn/') {
                    $CheckCarPhotoInfo['car_id'] = $saleId;
                    $CheckCarPhotoInfo['photo'] = $CheckCarNumberInfo['photo'];
                    $CheckCarPhotoInfo['photo_type'] = 1;
                    $CheckCarPhotoInfo['follow_user'] = $carData['follow_user'];
                    MbsCheckCarPhotoInterface::add($CheckCarPhotoInfo);
                }
            }
            if (isset($params['car_number']) && $params['car_number']) {
                MbsCheckCarNumberInterface::add($CheckCarNumberInfo);
            }
            
            return $saleId;
        }
        throw new AppServException(AppServErrorVars::CUSTOM, '保存车源信息失败');
    }

    public function updateSale($params) {
        $photoes=$params['image'];
        unset($params['image']);
        
        //处理sale
        $saleModel=D("Sale");
        $carData = $saleModel->saleInfo($params['id']);
        $saleId = $saleModel->saveSale($params);
        $attachModel=D('Attach');

        if($saleId){
            //图片处理
            if (!empty($photoes)) {                             
                $attachModel->savePhotoes($photoes, $saleId, intval($params['dept_id']));
                //处理sale的缩略图
                $cover=$attachModel->getCover();
                $saleModel->saleCover($cover, $saleId);
            }

            //更新客户信息
            D("Customer")->saveCustomer(array(
                'id'=>intval($params['customer_id']),
                'real_name'=>$params['contact_user'],
            ));
            
            //若有修改车牌,更新mbs_check_car_number数据
            if ($params['car_number'] && ($params['car_number'] != $carData['car_number'])){
                $CheckCarNumberInfo = array(
                        'car_number' => trim($params['car_number']),
                        'create_time'=> time(),
                        'status' => 1,
                        'access_time' => time(),
                        'check_from' => 1,
                        'follow_user'  => AppServAuth::$userInfo['user']['username'],
                        'dept_id'   => AppServAuth::$userInfo['user']['dept_id'],
                );
                if ($params['brind_name']){
                    $CheckCarNumberInfo['brand'] = trim($params['brind_name']);
                }
                if ($params['contact_user']){
                    $CheckCarNumberInfo['contact_user'] = trim($params['contact_user']);
                }
                if ($params['telephone']){
                    $CheckCarNumberInfo['car_telephone'] = trim($params['telephone']);
                }
                if (!empty($photoes)) {
                    $CheckCarNumberInfo['photo'] = $attachModel->getPathOne($saleId);
                }
                if (!empty($carData['car_number'])) {
                    MbsCheckCarNumberInterface::update($CheckCarNumberInfo, array('car_id' => $saleId));
                } else {
                    $CheckCarNumberInfo['car_id'] = $saleId;
                    $CheckCarNumberInfo['brand'] = $carData['brand_caption'];
                    MbsCheckCarNumberInterface::add($CheckCarNumberInfo);
                }
            }
            
            //若有修改车牌图片，则同时更新mbs_check_car_photo
            if (!empty($photoes)) {
                $carPhotoInfo = MbsCheckCarPhotoInterface::getCheckInfoByCarId(array('car_id' => $saleId));
                $plate = $attachModel->getPathOne($saleId);
                $CheckCarPhotoInfo['photo'] = $plate;
                //当$plate等于http://img.273.com.cn/代表发布卖车时未上传车牌图片
                if (empty($carPhotoInfo) && ($plate != 'http://img.273.com.cn/')) {
                    $CheckCarPhotoInfo['car_id'] = $saleId;
                    $CheckCarPhotoInfo['photo_type'] = 1;
                    $CheckCarPhotoInfo['follow_user'] = $carData['follow_user'];
                    MbsCheckCarPhotoInterface::add($CheckCarPhotoInfo);
                } else {
                    MbsCheckCarPhotoInterface::update($CheckCarPhotoInfo, array('car_id' => $saleId));
                }
            }
            return $saleId;
        }
        throw new AppServException(AppServErrorVars::CUSTOM, '更新车源信息失败');
    }

    public function newBuy($params) {
         //处理buy
        $buyModel=D("Buy");
        $buyId = $buyModel->newBuy($params);

        //处理需求
        if($buyId){
            //客户信息处理
            $customerInfo=array(
                'province'=>intval($params['province']),
                'city'=>intval($params['city']),
                'real_name'=>trim($params['contact_user']),
                'mobile'=>trim($params['telephone'])
            );
            if ($params['telephone2']) {
                $customerInfo['telephone'] = trim($params['telephone2']);
            }
            if ($params['idcard']) {
                $customerInfo['idcard'] = trim($params['idcard']);
            }
            $customerModel=D("Customer");
            $cid=$customerModel->newCustomer($customerInfo);
            $buyModel->buyCustomer($cid, $buyId);
            return $buyId;
        }
        throw new AppServException(AppServErrorVars::CUSTOM, '保存买车信息失败');
    }

    public function updateBuy($params) {
        //处理buy
        $buyModel=D("Buy");
        $buyId = $buyModel->saveBuy($params);

        //处理需求
        if($buyId){

            //更新客户信息
            D("Customer")->saveCustomer(array(
                'id'=>intval($params['customer_id']),
                'real_name'=>$params['contact_user'],
            ));
            return $buyId;
        }
        throw new AppServException(AppServErrorVars::CUSTOM, '更新买车信息失败');
    }

    public function refreshRank($params) {
        $accountInfo = AccountInterface::get(array('fields'=>'*', 'cond'=>array('from_app'=>'backend','dept_id' => AppServAuth::$userInfo['user']['dept_id'])));
        $creditInfo = CreditInterface::get(array('fields'=>'*', 'cond'=>array('account_id'=>$accountInfo['id'],'source_id' => PaymentConfig::$app['backend']['phone']['source_id'])));
        $balance = sprintf("%.2f", ($accountInfo['available_balance'] + $creditInfo['available_balance'])/100);
        if (!$balance) {
           $balance = 0;
        }
        if ($balance < -50) {
            throw new AppServException(AppServErrorVars::CUSTOM, '您所在的门店已欠费，无法使用刷新功能');
        }
        if ($this->_isPunished()) {
            throw new AppServException(AppServErrorVars::CUSTOM, '您已经被限制发布卖车,3天内不允许发布与刷新车源');
        }
        $info_id = $params['info_id'];
        $saleModel = D('Sale');
        $carData = $saleModel->saleInfo(0, $info_id);
        if($carData['status'] != 1){
            throw new AppServException(AppServErrorVars::CUSTOM, '非审核通过车源不可刷新');
        }
        $inserTimestamp = strtotime($carData['insert_time']);
        $sixMonthsAgo = strtotime('6 months ago');
        if($inserTimestamp <= $sixMonthsAgo){
            throw new AppServException(AppServErrorVars::CUSTOM, '本车源发布时间已超过6个月，不允许刷新。');
        }
        if(AppServAuth::$userInfo['user']['username'] != $carData['follow_user']) {
            throw new AppServException(AppServErrorVars::CUSTOM, '该车源不是你所发布');
        }
        $today = strtotime('today');
        $refreshStat = D('Refresh');
        $refreshData = $refreshStat->getDetailByUsername(AppServAuth::$userInfo['user']['username']);
        $username = AppServAuth::$userInfo['user']['username'];
        if($refreshData){
            $refreshData['detail'] = unserialize($refreshData['detail']);
            if(!isset($refreshData['detail'][$info_id])){
                $refreshData['detail'][$info_id] = 2;
            }
            
        } else {
            $refreshData = array(
                'username' => $username,
                'insert_time' => $today,
                'last_refresh_time' => $today,
                'total_number' => 50,
                'mobile_total_number' => 50,
                'detail' => serialize(array($info_id => 2))
            );
            $refreshStat->addStat($refreshData);
            $refreshData['detail'] = unserialize($refreshData['detail']);
            if(!isset($refreshData['detail'][$info_id])){
                $refreshData['detail'][$info_id] = 2;
            }
        }
        $prompt = '';
        if (isset($params['operation']) && $params['operation'] == 'getNums') {
            $totalNum = $refreshData['total_number'] + $refreshData['mobile_total_number'];
            if ($totalNum <= 0) {
                $prompt = '<html><head><body><font color=\'#696969\' size=\'15px\'>您今日的刷新次数已全部用完，<br>'
                        .'请明日再进行刷新操作</font></body></head></html>';
            } elseif ($refreshData['detail'][$info_id] <= 0) {
                $prompt = '<html><head><body><font color=\'#cc0000\' size=\'15px\'>您本条车源今日刷新次数已用完<br></font>'
                        .'<font color=\'#696969\' size=\'15px\'>刷新次数还剩：'.$totalNum.'次</font></body></head></html>';
            } else {
                $prompt = '<html><head><body><font color=\'#000\' size=\'15px\'>本车源剩余刷新次数：'
                        .$refreshData['detail'][$info_id].'次<br>刷新次数还剩：'.$totalNum.'次</font></body></head></html>';
            }
            return $prompt;
            //return array('total_number' => $refreshData['total_number'] + $refreshData['mobile_total_number'], 'car_number' => $refreshData['detail'][$info_id]); 
        }
        if($refreshData['mobile_total_number'] <= 0 && $refreshData['total_number'] <= 0){
            throw new AppServException(AppServErrorVars::CUSTOM, '今日刷新次数已用完，请明日再试。');
        }
        if(isset($refreshData['detail'][$info_id]) && $refreshData['detail'][$info_id] <= 0){
            throw new AppServException(AppServErrorVars::CUSTOM, '该车源刷新次数已用完， 请明日再试。');
        }
        
        if($refreshStat->updateStat($info_id, $username, $refreshData)){
            $saleModel->updateTime($carData['id'],$info_id);
            if ($refreshData['mobile_total_number'] > 0) {
                $refreshData['mobile_total_number']--;
            } else {
                $refreshData['total_number']--;
            }
            $refreshData['detail'][$info_id]--;
            $totalNum = $refreshData['total_number'] + $refreshData['mobile_total_number'];
            $prompt = '<html><head><body><font color=\'#6D9E30\' size=\'15px\'>刷新成功，5分钟后生效<br></font>'
                    .'<font color=\'#696969\' size=\'15px\'>本车源剩余刷新次数：'.$refreshData['detail'][$info_id]
                    .'次<br>刷新次数还剩：'.$totalNum.'次</font></body></head></html>';
            return $prompt;
            //return array('total_number' => $refreshData['total_number'] + $refreshData['mobile_total_number'], 'car_number' => $refreshData['detail'][$info_id]);
        }
        throw new AppServException(AppServErrorVars::CUSTOM, '刷新错误，请稍候再试。');
    }
    
    /**
     * 通迅录
     */
    public function addressBook($params) {
        $deptId = AppServAuth::$userInfo['user']['dept_id'];
        if(isset($params['dept_id'])) {
            if(intval($params['dept_id']) == AppServAuth::$userInfo['user']['dept_id']) {
                
            }elseif(!empty($params['dept_id'])) {
                $deptId = intval($params['dept_id']);
            }
        }
        $data = array();
        //获取当前店的相关信息
        $deptInfo = MbsDeptInterface::getDeptInfoByDeptId(array('id'=>$deptId));
        $data['dept_info']['dept_id']   = $deptInfo['id'];
        $data['dept_info']['dept_name'] = $deptInfo['dept_name'];
        $data['dept_info']['telephone'] = empty($deptInfo['telephone'])?'':$deptInfo['telephone'];
        $data['dept_info']['address']   = empty($deptInfo['address'])?'':$deptInfo['address'];
        //获取当前店内所有员工的角色ID
        $userInfoArr = MbsUserInterface::getUsersByDept(array('dept_id'=>$deptId));
        foreach ($userInfoArr as $key => $value) {
            $roleIdArr[] = $value['role_id'];  
        }
        //去重
        $roleIds = array_unique($roleIdArr);
        $roleInfo = MbsRoleInterface::getRoleInfoByRoleIds(array('role_ids'=>$roleIds));
        foreach ($roleInfo as $roleInfoValue) {
            $roleIdAndName[$roleInfoValue['role_id']] = $roleInfoValue['role_name'];
        }
        //获取当前店内所有员工通迅录信息
        foreach ($userInfoArr as $k => $v) {
            $data['dept_info']['user_info'][$k]['username'] = $v['username'];
            $data['dept_info']['user_info'][$k]['real_name'] = $v['real_name'];
            $data['dept_info']['user_info'][$k]['role_id'] = $v['role_id'];
            $data['dept_info']['user_info'][$k]['role_name'] = $roleIdAndName[$v['role_id']];
            $data['dept_info']['user_info'][$k]['mobile'] = empty($v['mobile'])?'':$v['mobile'];
            $data['dept_info']['user_info'][$k]['telephone'] = empty($v['telephone'])?'':$v['telephone'];
            $data['dept_info']['user_info'][$k]['address'] = empty($v['address'])?'':$v['address'];
            $data['dept_info']['user_info'][$k]['email'] = empty($v['email'])?'':$v['email'];
        }
        return $data;
    }
    
    /**
     * 获取同市的所有门店
     */
    public function getOtherDepts() {
        $city = AppServAuth::$userInfo['user']['city'];
        $dept = AppServAuth::$userInfo['user']['dept_id'];
        $depts = MbsDeptInterface::getDeptsByCity(array('id' => $city));
        $data = array();
        $myDept = array();
        foreach ($depts as $k => $v) {
            $data[$k]['dept_id'] = $v['id'];
            $data[$k]['dept_name'] = $v['dept_name'];
            $data[$k]['telephone'] = ($v['telephone'] == 'NULL')?'':$v['telephone'];
            $data[$k]['address'] = ($v['address'] == 'NULL')?'':$v['address'];
            //获取当前门店面
            if ($v['id'] == $dept) {
                $myDept = $data[$k];
            }
        }
        $nums = count($data);
        //过滤掉测试门店
        if($city == 1) {
            for ($i=0;$i<$nums;++$i) {
                if($data[$i]['dept_id'] == 1354) {
                    array_splice($data, $i,1);
                    break;
                }
            }
        }
        //将当前门店放至数组头部
        for ($i=0;$i<$nums;++$i) {
            if($data[$i]['dept_id'] == $dept) {
                array_splice($data, $i,1);
                break;
            }
        }
        array_unshift($data, $myDept);
        //过滤空数组
        foreach ($data as $key => $value) {
            if(!empty($value)) {
                $address[] = $value;
            }
        }
        return $address;
    }
    
    private function _getBalance() {
        $accountInfo = AccountInterface::get(array('fields'=>'*', 'cond'=>array('from_app'=>'backend','dept_id' => AppServAuth::$userInfo['user']['dept_id'])));
        $creditInfo = CreditInterface::get(array('fields'=>'*', 'cond'=>array('account_id'=>$accountInfo['id'],'source_id' => PaymentConfig::$app['backend']['phone']['source_id'])));
        $balance = sprintf("%.2f", ($accountInfo['available_balance'] + $creditInfo['available_balance'])/100);
        if (!$balance) {
            $balance = 0;
        }
        return $balance;
    }

    /**
     * 判断当前业管是否需要接受惩罚
     */
    private function  _isPunished() {
        //$userInfo = MbsUserInterface::getInfoByUser(array('username'=>AppServAuth::$userInfo['user']['username']));
        $userModel = D('User');
        $userInfo = $userModel->getInfoByUser(AppServAuth::$userInfo['user']['username']);
        $punishType = $userInfo['punish_type'];
        $lastPunishedTime = $userInfo['punish_time'];
        //3 天惩罚时间
        $inThreeDays = time() - $lastPunishedTime < 259200 ? true : false;
        if ($punishType == 1 or $punishType == 2) {
            if ($inThreeDays) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * 根据车牌号判断是否重复录入
     */
    private function _checkDuplicateEntry($carNumber) {
        $saleModel=D("Sale");
        $role_id = AppServAuth::$userInfo['user']['role_id'];
        $carId = $saleModel->saleValid($carNumber,$role_id);
        if ($carId) {
            if ($role_id == 27 || $role_id == 28 || $role_id == 165) {  //店长或交易顾问或业务主任录入
                throw new AppServException(AppServErrorVars::CUSTOM, '您已录入该车牌号的有效车源');
            }
            if ($role_id == 26) {  //店秘录入          
                throw new AppServException(AppServErrorVars::CUSTOM, '门店中已录入该车牌号的有效车源');
            }
        }
        return true;
    }
    
    private function _format($data, $type = '', $query_type = '') {
        if(empty($data)) {
            return array();
        }
        $balance = 0;
        if ($type == 'phone') { 
            $balance = $this->_getBalance();
        }
        $attachModel=D("Attach");
        $customerModel=D("Customer");
        $saleModel = D("Sale");
        $cacheHandle = CacheNamespace::createCache(CacheNamespace::MODE_MEMCACHE, MemcacheConfig::$GROUP_DEFAULT);
        foreach ($data as $k=>$v) {
            if ($type == 'buy') {
                $data[$k]['min_price'] = number_format($v['min_price'] / 10000, 2);
                $data[$k]['max_price'] = number_format($v['max_price'] / 10000, 2);
                $data[$k]['brand_caption'] = $saleModel->getVehicleFullTitle($data[$k]['vehicle_key']);
            } else {
                $data[$k]['price'] = number_format($v['price'] / 10000, 2);
                $data[$k]['kilometer'] = number_format($v['kilometer'] / 10000, 1);
                $data[$k]['photo'] = $attachModel->buildPhoto($data[$k]['photo']);
                if (strpos($data[$k]['photo'], '120-90_6_0_0_120-90_6_0_0')) {
                    $data[$k]['photo'] = str_replace('120-90_6_0_0_120-90_6_0_0', '120-90_6_0_0', $data[$k]['photo']);
                }
            }
            if ($data[$k]['status'] == 0) {
                $data[$k]['status_show'] = '未审核';
            } elseif ($data[$k]['status'] == 1) {
                $data[$k]['status_show'] = '已审核';
            } elseif ($data[$k]['status'] == 2) {
                $data[$k]['status_show'] = '冻结';
            } else {
                $data[$k]['status_show'] = '终止';
            }
            if ($data[$k]['sale_status'] == 0) {
                $data[$k]['sale_status_show'] = '未售出';
            }elseif ($data[$k]['status'] == 1) {
                $data[$k]['sale_status_show'] = '已售出';
            }else {
                $data[$k]['sale_status_show'] = '';
            }
            if(isset($data[$k]['insert_user_id'])){
                if($data[$k]['insert_user_id'] == $data[$k]['follow_user']) {
                    $data[$k]['originate'] = '个人录入';
                }else {
                    $data[$k]['originate'] = '系统分配';
                }
            }
            $data[$k]['telephone'] = '';
            $data[$k]['follow_user_name'] = '';
            if (isset($data[$k]['file_path'])) {
                if($data[$k]['file_path'] == '-') {
                    $data[$k]['phone_access'] = '未接通';
                }else {
                    $data[$k]['phone_access'] = '';
                }
                $file = str_replace('d:\recwav', '', $data[$k]['file_path']);
                $file = str_replace('e:\recwav', '', $file);
                $file = str_replace('\\', '/', $file);
                $file = "http://fzct.273.cn:90" . $file;
                if ($balance < -50) {
                    $file = '';
                    $data[$k]['call_phone'] = substr($data[$k]['call_phone'],0,strlen($data[$k]['call_phone'])-4) . '****';
                    $data[$k]['call_lasting'] = 0;
                }
                $data[$k]['audio_URL'] = $file;
                if ($balance >= -50 && strlen($data[$k]['call_phone']) == 11 && substr($data[$k]['call_phone'],0,1) != '0') {
                    $memcacheKey = 'phone_to_city_v6_'.$data[$k]['call_phone'];
                    $addr = $cacheHandle->read($memcacheKey);
                    if ($addr === false || $addr === null) {
                        $phoneInfo = MobileToCityInterface::mtc(array('tel'=>$data[$k]['call_phone']));
                        if (in_array($phoneInfo[1], array('北京','上海','天津','重庆'))) {
                            $data[$k]['call_phoneAddr'] = $phoneInfo[1];
                        } else {
                            $data[$k]['call_phoneAddr'] = $phoneInfo[1] . $phoneInfo[4];
                        }
                        $cacheHandle->write($memcacheKey, $data[$k]['call_phoneAddr'], 360000);
                    } else {
                        $data[$k]['call_phoneAddr'] = $addr;
                    }
                } elseif ($balance >= -50 && is_numeric($data[$k]['call_phone']) && substr($data[$k]['call_phone'],0,1) != '0') {
                    $data[$k]['call_phoneAddr'] = '福建福州';
                } elseif ($balance >= -50 && is_numeric($data[$k]['call_phone']) && substr($data[$k]['call_phone'],0,1) == '0') {
                    $params['domain'] = substr($data[$k]['call_phone'], 0, 4);
                    $city = LocationInterface::getLocationByDomain($params);
                    if(!$city) {
                        $params['domain'] = substr($data[$k]['call_phone'], 0, 3);
                        $city = LocationInterface::getLocationByDomain($params);
                    }
                    if ($city) {
                        $params['province_id'] = $city['parent_id'];
                        $province = LocationInterface::getProvinceById($params);
                        $data[$k]['call_phoneAddr'] = $province['name'].$city['name'];
                    } else {
                        $data[$k]['call_phoneAddr'] = '';
                    }
                } else {
                    $data[$k]['call_phoneAddr'] = '';
                }
                unset($data[$k]['file_path']);
            }
            //不是自己的车源，需要取得业务员信息和门店信息
            $user = MbsUserInterface::getInfoByUser(array('username' => $v['follow_user']));
            $data[$k]['follow_user_name'] = empty($user['real_name'])?'':$user['real_name'];
            $data[$k]['telephone'] = $user['mobile'] ? $user['mobile'] : '';
            $dept = MbsDeptInterface::getDeptInfoByDeptId(array('id' => $user['dept_id']));
            $data[$k]['dept_name'] = $dept['dept_name'] ? $dept['dept_name'] : '';
            if ($query_type == 'my' || $query_type == 'user') {
                $data[$k]['contact_user'] = '';
                $data[$k]['contact_telephone'] = '';
                $data[$k]['contact_telephone2'] = '';
                $data[$k]['contact_telephone_addr'] = '';
                $data[$k]['idcard'] = '';
                $customer=$customerModel->getOne("select real_name,mobile,idcard,telephone from mbs_customer where id=".intval($v['customer_id']));
                $data[$k]['contact_user'] = $customer['real_name'] ? $customer['real_name'] : '';
                $data[$k]['contact_telephone'] = $customer['mobile'] ? $customer['mobile'] : '';
                $data[$k]['contact_telephone2'] = $customer['telephone'] ? $customer['telephone'] : '';
                $data[$k]['idcard'] = $customer['idcard'] ? $customer['idcard'] : '';
                if (strlen($customer['mobile']) == 11 && substr($customer['mobile'], 0, 1) != '0') {
                    $memcacheKey = 'phone_to_city_v6_' . $customer['mobile'];
                    $addr = $cacheHandle->read($memcacheKey);
                    if ($addr === false || $addr === null) {
                        $phoneInfo = MobileToCityInterface::mtc(array('tel'=>$data[$k]['contact_telephone']));
                        if (in_array($phoneInfo[1], array('北京','上海','天津','重庆'))) {
                            $data[$k]['contact_telephone_addr'] = $phoneInfo[1];
                        } else {
                            $data[$k]['contact_telephone_addr'] = $phoneInfo[1] . $phoneInfo[4];
                        }
                        $cacheHandle->write($memcacheKey, $data[$k]['contact_telephone_addr'], 360000);
                    } else {
                        $data[$k]['contact_telephone_addr'] = $addr;
                    }
                } else {
                    $data[$k]['contact_telephone_addr'] = '';
                }
            }
            if ($type != 'buy') {
                //$data[$k]['image']=$attachModel->getPhotoes($v['id']);
                $data[$k]['image'] = array();
                $data[$k]['image_plate'] = array();
                $imageList = $attachModel->getPhotoes($v['id']);
                if (!empty($imageList)) {
                    foreach ($imageList as $key => $images) {
                        if (!isset($images['is_plate'])) {
                            $data[$k]['image'][] = $imageList[$key]; 
                        }else {
                            unset($imageList[$key]['is_plate']);
                            $data[$k]['image_plate'][] = $imageList[$key];
                        }
                    }
                }
            }
            if ($type == 'sale') {
                //if ($v['follow_user'] == AppServAuth::$userInfo['user']['username']) {
                if ($query_type == 'my') {
                    $data[$k]['check_status'] = '';
                    $data[$k]['check_status_show'] = '';
                    $data[$k]['check_remark'] = '';
                    $data[$k]['check_time'] = '';
                    $checkInfo = MbsCheckCarNumberInterface::getCheckInfoByCarId(array('car_id'=>$v['id']));
                    if (!empty($checkInfo)) {
                        $data[$k]['check_status'] = $checkInfo['status'];
                        switch ($checkInfo['status']) {
                            case 2: $data[$k]['check_status_show'] = '通过'; break;
                            case 3: $data[$k]['check_status_show'] = '不通过，下架车源'; break;
                            case 4: $data[$k]['check_status_show'] = '不通过，下架车源，3天内不允许发布与刷新车源'; break;
                            case 6: $data[$k]['check_status_show'] = '不通过'; break;
                            default: $data[$k]['check_status_show'] = ''; break;
                        }
                        $data[$k]['check_remark'] = $checkInfo['remark'] ? $checkInfo['remark'] : '';
                        $data[$k]['check_time'] = $checkInfo['check_time'] ? $checkInfo['check_time'] : '';
                    }
                }
            }
        }
        return $data;
    }
    
    //终止车源
    public function stop($params) {
        $ret = array();
        if(substr($params['info_id'],0,1) == 'S'){
            $params['model'] = 'sale';
        } else {
            $params['model'] = 'buy';
        }
        $url = 'http://mbs.corp.273.cn/index.php?model=' . $params['model'] . '&action=SubmitStopInfo';
        $params['username'] = AppServAuth::$userInfo['user']['username'];
        $params['mobile_use'] = md5('273_mobile_mbs');
        $ret = Request::RequestUrl($url, $params);
        if($ret == 1){
            return 1;
        }else {
            return 0;
        }
        
    }
    
    //取消终止车源
    public function cancleStop($params) {
        if(substr($params['info_id'],0,1) == 'S'){
            $params['model'] = 'sale';
        } else {
            $params['model'] = 'buy';
        } 
        $url = 'http://mbs.corp.273.cn/index.php?model=' . $params['model']
        		    						. '&action=CancelStop&info_id=' . $params['info_id'] 
        		    						. '&customer_id=' . $params['customer_id']
        		    					    . '&web_id=&id=' . $params['id'];
        $data['username'] = AppServAuth::$userInfo['user']['username'];
        $data['mobile_use'] = md5('273_mobile_mbs');
        $ret = Request::RequestUrl($url, $data);
        if($ret == 1) {
            return 1;
        } else {
            return 0;
        }
        
    }
    
    //消息中心-审核消息
    public function checkMessage($params) {
        $ret = array();
        $url = 'http://mbs.corp.273.cn/index.php?model=message&message_type=message';
        $data['username'] = AppServAuth::$userInfo['user']['username']; 
        $data['mobile_use'] = md5('273_mobile_mbs');
        $data['start'] = $params['start'] ? $params['start'] : 1;
        $data['end'] = $params['page_num'] ? $params['page_num'] : 20;
        $data['is_read'] = $params['is_read'];
        $data['status'] = $params['status'] ? $params['status'] : 0;
        $data['reason'] = '1, 3, 4, 5, 7, 8, 98, 129, 500';
        if(!empty($params['start_time'])) {
            $data['start_time'] = $params['start_time'];
        }
        
        $ret = Request::RequestUrl($url, $data);
        $ret = json_decode($ret, true);
        
        return $ret;
      
    }
     //消息拉取
    public function getMessageBytime($params) {
        $ret = array();
        $url = 'http://mbs.corp.273.cn/index.php?model=message&message_type=visitMessage';
        $data['username'] = AppServAuth::$userInfo['user']['username'];
     //   $data['time'] = $params['time'];
//        $data['is_read'] = $params['is_read'];
        $data['mobile_use'] = md5('273_mobile_mbs');
        $data['reason'] = '1, 3, 36, 37';
        $ret1 = Request::RequestUrl($url, $data);
        $ret1 = json_decode($ret1, true);
        $ret1['time'] = time();
        return $ret1;
    }

    //事务审核
    public function checkList($params) {
        $ret = array();
        $url = 'http://mbs.corp.273.cn/index.php?model=message&message_type=checkMessage';
        $data['username'] = AppServAuth::$userInfo['user']['username']; 
        $data['mobile_use'] = md5('273_mobile_mbs');
        $data['start'] = $params['start'] ? $params['start'] : 1;
        $data['end'] = $params['page_num'] ? $params['page_num'] : 20;
        $data['status'] = $params['status'];
        $data['reason'] = '26, 56, 4, 3, 5, 1';
        if(!empty($params['start_time'])) {
            $data['start_time'] = $params['start_time'];
        }
        $data['is_read'] = $params['is_read'];
        $ret = Request::RequestUrl($url, $data);
        $ret = json_decode($ret, true);
        return $ret;
      
    }
    
    //消息中心-回访消息
    public function visitMessage($params) {
        $ret = array();
        $url = 'http://mbs.corp.273.cn/index.php?model=message&message_type=visitMessage';
        $data['username'] = AppServAuth::$userInfo['user']['username'];
        $data['is_read'] = $params['is_read'];
        $data['mobile_use'] = md5('273_mobile_mbs');
        $data['reason'] = '1, 3, 36, 37';
        $ret1 = Request::RequestUrl($url, $data);
        $ret1 = json_decode($ret1, true);

        $url = 'http://mbs.corp.273.cn/index.php?model=message&message_type=message';
        $data['mobile_use'] = md5('273_mobile_mbs');
        $data['reason'] = '500';
        $ret3 = Request::RequestUrl($url, $data);
        $ret3 = json_decode($ret3, true);
        $ret['count'] = $ret1['count'] +$ret3['count'];
        $ret['info'] = array_merge($ret1['info'], $ret3['info']);
        
        return $ret;
    }
    
    //审核通过
    public function checkPass($params) {
        $ret = array();
        $url = 'http://mbs.corp.273.cn/index.php?model=message&action=CheckPass';
        $params['username'] = AppServAuth::$userInfo['user']['username'];
        $params['mobile_use'] = md5('273_mobile_mbs');
        $ret = Request::RequestUrl($url, $params);
        if(empty($ret)) {
            return 1;
        } else {
            return 0;
        }
    }
    
    //审核驳回
    public function noCheckPass($params) {
        $ret = array();
        $url = 'http://mbs.corp.273.cn/index.php?model=message&action=NoCheckPass';
        $params['username'] = AppServAuth::$userInfo['user']['username'];
        $params['mobile_use'] = md5('273_mobile_mbs');
        
        $ret = Request::RequestUrl($url, $params);
        if(empty($ret)) {
            return 1;
        } else {
            return 0;
        }
    }
    
   
    
    public function messageCount($params) {
        $params['username'] = AppServAuth::$userInfo['user']['username'];
        $messageModel = D('Message');
        $params['reason'] = '1, 3, 4, 5, 7, 8, 98, 129';
        $where = $this->_getWhere($params);
        $tableName = 'mbs_message';
        $checkMessage  = $messageModel->getMessageCount($tableName, $where);
        $ret['check_message'] = (int)$checkMessage;
        
        $params['reason'] = '500';
        $where = $this->_getWhere($params);
        $tableName = 'mbs_message';
        $visitMessage = $messageModel->getMessageCount($tableName, $where);
        $ret['visit_message'] = (int)$visitMessage;
        
        $params['reason'] = '1, 3, 36, 37';
        $where = $this->_getWhere($params);
        $tableName = 'mbs_visit_message';
        $count= $messageModel->getMessageCount($tableName, $where);
        $ret['visit_message'] += $count;
        
        $params['reason'] = '26, 56, 4, 5, 1';
        $where = $this->_getWhere($params);
        $tableName = 'mbs_check_message';
        $ret['check_list'] = (int)$messageModel->getMessageCount($tableName, $where);
        $ret['total'] = $ret['check_message'] + $ret['visit_message'];
        return $ret;
    }
    
    private function _getWhere($info) {
        $wheres = array();
    
        $wheres[] = 'accept_id = '.$info['username'];

        if($info['status']){
            $wheres[] = 'status = '.intval($info['status']);
        } else {
            $wheres[] = 'status = 0';
        }
        if($info['reason']){
            $wheres[] = 'reason in ('.$info['reason'].')';
        }
        if(isset($info['is_read'])){
            $wheres[] = 'is_read = '.intval($info['is_read']);
        }else {
            $info['is_read'] = 0;
            $wheres[] = 'is_read = '.intval($info['is_read']);
        }
        if($wheres){
            $where = ' where '.implode(' and ', $wheres);
        }
        
        if ($info['is_read'] == 1) {
            $where .= " and to_char(insert_time,'yyyymmdd') >= to_char(SYSDATE-20,'yyyymmdd')";
        } elseif ($info['is_read'] == 0) {
            $where .= " and to_char(insert_time,'yyyymmdd') >= to_char(SYSDATE-60,'yyyymmdd')";
        }
        return $where;

    }
    //更新已读未读或逻辑删除
    public function updateRead($params) {
        $ret = array();
        if(empty($params['message_type'])) {
            return 0;
        }
        if(strlen($params['message_type']) == 1){
            $messageType = $this->messageType[$params['message_type']];
            $url = 'http://mbs.corp.273.cn/index.php?model=message&action=updateRead&message_type=' . $messageType;
            $data['username'] = AppServAuth::$userInfo['user']['username'];
            $data['mobile_use'] = md5('273_mobile_mbs');
            $data['is_read'] = $params['is_read'] ? (int) $params['is_read'] : 1;
            $data['id'] = '('. $params['id'] . ')';
            $ret = Request::RequestUrl($url, $data);
            $ret = empty($ret) ? 1 : 0;
        } else {
            $id = explode(',', $params['id']);
            $type = explode(',', $params['message_type']);
            foreach ($type as $key => $value){
                if($value == 1){
                    $checkId[] = $id[$key];
                }
                if($value == 2) {
                    $visitId[] = $id[$key]; 
                }
                if($value == 3) {
                    $messageId[] = $id[$key]; 
                }
            }
            
            if($visitId){
                $params['visit_id'] = implode(',', $visitId);
            }
            if($messageId){
                $params['message_id'] = implode(',', $messageId);
            }
            $url = 'http://mbs.corp.273.cn/index.php?model=message&action=updateRead&message_type=visitMessage';
            $data['username'] = AppServAuth::$userInfo['user']['username'];
            $data['mobile_use'] = md5('273_mobile_mbs');
            $data['is_read'] = $params['is_read'] ? (int) $params['is_read'] : 1;
           
            if($visitId){
                $params['visit_id'] = implode(',', $visitId);
                $data['id'] = '('. $params['visit_id'] . ')';
                $ret1 = Request::RequestUrl($url, $data);
            }
            
            $url = 'http://mbs.corp.273.cn/index.php?model=message&action=updateRead&message_type=checkMessage';
            if($checkId){
                $params['check_id'] = implode(',', $checkId);
                $data['id'] = '('. $params['check_id'] . ')';
                $ret2 = Request::RequestUrl($url, $data);
            }
            
            
            
            $url = 'http://mbs.corp.273.cn/index.php?model=message&action=updateRead&message_type=message';
            
            if($messageId){
                $params['message_id'] = implode(',', $messageId);
                $data['id'] = '('. $params['message_id'] . ')';
                $ret3 = Request::RequestUrl($url, $data);
            }
            
            $ret = (empty($ret1) &&empty($ret2) && empty($ret3)) ? 1 :0;
            
        }
        return $ret;
    }
    
    //根据车源编号获取卖车车源详情
    public function getSaleDetailByCarId($params) {
        $saleModel = D('Sale');
        if (isset($params['car_id']) && $params['car_id']) {
            $saleDetail[] = $saleModel->saleViewInfo(intval($params['car_id']));
        } elseif (isset($params['info_id']) && $params['info_id']) {
            $saleDetail[] = $saleModel->saleViewInfo(0,$params['info_id']);
        }
        if (empty($saleDetail[0])) {
            throw new AppServException(AppServErrorVars::CUSTOM, '该车源不存在');
        }
        $data = $this->_format($saleDetail,'sale', 'my');
        $this->updateRead(array('id' => $params['id'], 'message_type' => $params['message_type']));
        return array_shift($data);
    }
    
    //根据车源编号获取买车车源详情
    public function getBuyDetailByCarId($params) {
        $buyModel = D('Buy');
        if (isset($params['car_id']) && $params['car_id']) {
            $buyDetail[] = $buyModel->buyViewInfo(intval($params['car_id']));
        } elseif (isset($params['info_id']) && $params['info_id']) {
            $buyDetail[] = $buyModel->buyViewInfo(0,$params['info_id']);
        }
        if (empty($buyDetail[0])) {
            throw new AppServException(AppServErrorVars::CUSTOM, '该车源不存在');
        }
        $data = $this->_format($buyDetail, 'buy', 'my');
        $this->updateRead(array('id' => $params['id'], 'message_type' => $params['message_type']));
        return array_shift($data);
    }
    //根据品牌车系关键词获取对应品牌车系数据
    private function _parseKeywords($kw) {
        $kw = trim($kw);
        $kw = preg_replace('/\s+/', " ", $kw);
        $kwOrigin = $kw;
        $kwSeparate = explode(' ', $kwOrigin, 2);
        $kw = str_replace(' ', '', $kw);
        if (empty($kw)) {
            return;
        }
        $searchRet = array();
        $searchParam = array (
                'type'  => 'all',
                'area_id' => 0,
                'limit' => 3,
                'fuzzy' => false
        );
        $data = array();
        do{
            //以用户原始输入为关键字查找
            $searchParam['keyword'] = $kw;
            $searchRet = VehicleV2Interface::getVehicleFromWord($searchParam);
            if (count($searchRet) >= 2) {
                //匹配结果为2个且属于同一个品牌时，跳转第一个匹配结果
                if (count($searchRet) == 2 && $searchRet[0]['brand_id'] == $searchRet[1]['brand_id'] ) {
                    unset($searchRet[0]['import_id']);
                    unset($searchRet[0]['count']);
                    $data = $searchRet[0];
                    break;
                } else {
                    //当有多个匹配结果时，把输入串以关键词直接到检索系统搜索
                    break;
                }
            } else if (!empty($searchRet[0])) {
                unset($searchRet[0]['import_id']);
                unset($searchRet[0]['count']);
                $data = $searchRet[0];
                break;
            }
            //拆分出两个关键字
            if (count($kwSeparate) == 2) {
                $brandName  = $kwSeparate[0];
                $seriesName = $kwSeparate[1];
            } else {
                break;
            }
            
            //以用户输入的第二个参数查找车系
            if (!empty($seriesName)) {
                $searchRet = array();
                $searchParam['keyword'] = $seriesName;
                $searchParam['type'] = 'series';
                $searchParam['limit'] = 10;
            
                $searchRet = VehicleV2Interface::getVehicleFromWord($searchParam);
                if (!empty($searchRet)) {
                    foreach ($searchRet as $item) {
                        if ($item['brand_name'] == $brandName) {
                            //搜索结果中的品牌与用户输入的品牌相同时，跳转
                            unset($item['import_id']);
                            unset($item['count']);
                            $data = $item;
                            break;
                        }
                    }
                    if (!empty($data)) {
                        break;
                    }
                }
            
            }
            //以用户输入的第二个参数查找品牌
            if (!empty($brandName)) {
                $searchRet = array();
                $searchParam['keyword'] = $brandName;
                $searchParam['type'] = 'brand';
                $searchRet = VehicleV2Interface::getVehicleFromWord($searchParam);
                if ($searchRet[0]) {
                    unset($searchRet[0]['import_id']);
                    unset($searchRet[0]['count']);
                    $data = $searchRet[0];
                    break;
                }
            }
            break;
        } while(true);
        return $data;
    }
}
