<?php
/**
 * @author guoch$
 * @brief  手机业管客户端使用的接口集合第三版，
 * @change miaosq@273.cn
 */

require_once API_PATH . '/interface/MbsDeptInterface.class.php';
require_once API_PATH . '/interface/MbsUserInterface.class.php';
require_once API_PATH . '/interface/MbsRoleInterface.class.php';
require_once API_PATH . '/interface/AccountInterface.class.php';
require_once API_PATH . '/interface/CreditInterface.class.php';
require_once CONF_PATH . '/payment/PaymentConfig.class.php';
require_once API_PATH . '/interface/mbs/MbsCarSaleInterface.class.php';
require_once API_PATH . '/interface/mbs/MbsCarSaleTmpInterface.class.php';
require_once API_PATH . '/interface/VehicleV2Interface.class.php';
require_once API_PATH . '/interface/mbs/MbsCustomerInterface.class.php';
require_once FRAMEWORK_PATH . '/util/cache/CacheNamespace.class.php';
require_once FRAMEWORK_PATH . '/util/common/Util.class.php';
require_once CONF_PATH . '/cache/MemcacheConfig.class.php';
require_once API_PATH . '/interface/mbs/MbsAttachInterface.class.php';
require_once API_PATH . '/interface/MobileToCityInterface.class.php';
require_once API_PATH . '/interface/CarAttachInterface.class.php';
require_once  APP_SERV . '/app/sale/include/config.php';
require_once  APP_SERV . '/app/sale/include/PhoneList.class.php';
require_once APP_SERV . '/app/sale/include/CarSale.class.php';
require_once APP_SERV . '/app/sale/include/CarSaleList.class.php';
require_once APP_SERV . '/app/sale/include/CarBuy.class.php';
require_once APP_SERV . '/app/sale/include/CarBuyList.class.php';
require_once APP_SERV . '/app/sale/include/CarUtil.class.php';
require_once APP_SERV . '/app/sale/include/BcPost.class.php';
require_once APP_SERV . '/app/sale/include/CarStop.class.php';
require_once API_PATH . '/interface/mbs/MbsRefreshStatInterface.class.php';
require_once  APP_SERV . '/app/sale/include/CarPostVars.class.php';
require_once API_PATH . '/interface/mbs/CarBuyBakInterface.class.php';
require_once API_PATH . '/interface/mbs/MbsUserInterface2.class.php';
require_once API_PATH . '/interface/CarSaleBakInterface.class.php';
require_once API_PATH . '/interface/mbs/MbsVisitMessageInterface.class.php';
require_once API_PATH . '/interface/mbs/MbsUpdateOperationInterface.class.php';
require_once API_PATH . '/interface/mbs/MbsMessageInterface.class.php';
require_once API_PATH . '/interface/mbs/MbsCheckMessageInterface.class.php';
require_once API_PATH . '/interface/MbsCheckCarNumberInterface.class.php';
require_once API_PATH . '/interface/MbsCheckCarPhotoInterface.class.php';
require_once API_PATH . '/interface/mbs/CarSaleExtBakInterface.class.php';
require_once API_PATH . '/interface/mbs/CarSaleExtInterface.class.php';
require_once API_PATH . '/interface/mbs/MbsEvaluateInterface.class.php';
require_once API_PATH . '/interface/mbs/MbsDisevaluateInterface.class.php';
require_once API_PATH . '/interface/mbs/CarBuyInterface.class.php';
require_once API_PATH . '/interface/mbs/MbsStopOperationInterface.class.php';
require_once API_PATH . '/interface/mbs/RoleInterface.class.php';
require_once API_PATH . '/interface/CarCompetitionInterface.class.php';
require_once CONF_PATH . '/../common/quality/ForceQualityCityVars.php';
require_once API_PATH . '/interface/sync2site/SyncDeptInfoInterface.class.php';
require_once API_PATH . '/interface/sync2site/SyncInfoInterface.class.php';
require_once API_PATH . '/interface/sync2site/SyncMapInterface.class.php';
require_once API_PATH . '/interface/CarSaleInterface.class.php';
require_once API_PATH . '/interface/mbs/MbsSmsEmailInterface.class.php';
require_once API_PATH . '/interface/mbs/SaleOperationInterface.class.php';
require_once API_PATH . '/interface/mbs/log/MbsContactLogInterface.class.php';
require_once COM_PATH . '/car/CarVars.class.php';
require_once COM_PATH . '/car/CarMbsVars.class.php';

class SaleServiceApp extends BcPost{
    
    public function getInfoByExtPhone($params) {
        //return 1;
        return CarSaleInterface::getInfoByExtPhone($params);
    }
    
    /**
     * @brief 车源列表
     * @params 新旧参数变化请参照apiworkbook 
     * @return 将数据格式化后兼容旧版本
     */
    public function saleList($params) {

        $ext = $this->getInfoByKeyword($params);
        if(!empty($ext)) {
            if ($params['_app_source'] == 3) {
                $data['exts'] = $ext;
            } else {
                $data['ext'] = $ext;
            }
        }
        
        //格式化搜索参数
        $searchParams = CarSaleList::getSearchParams($params);
        $searchInExt = false;   //为true时表明查电话号码或车牌号或个人录入或系统录入
        $searchId = false;      //为true时表明查车源ID
        $myOrMystore = false;     //为true时表明查我的卖车或店内卖车
        foreach ($searchParams['filters'] as $key => $filter) {
            if ($filter[0] == 'car_status') {
                $carStatus = $filter[2];
                if ($carStatus == 4030) {
                    $searchParams['filters'][$key] = array('status', '=', 3);
                    $searchParams['filters'][] = array('sale_status', '!=', 1);
                }
            } else if ($filter[0] == 'sale_status') {
                $saleStatus = $filter[2];
            } else if ($filter[0] == 'customer_id') {
                $searchInExt = true;
            } else if ($filter[0] == 'car_number') {
                $searchInExt = true;
            } else if ($filter[0] == 'create_user_id') {
                $searchInExt = true;
            } else if ($filter[0] == 'id') {
                $searchId = true;
            }
            
            //如果搜索转接号，就不走检索，目前检索暂时不支持转接号查询
            if ($filter[0] == 'ext_phone') {
                $myOrMystore = true;
            }
        }
        if ($params['query_type'] == 'my' || $params['query_type'] == 'store') {
            $myOrMystore = true;
        }

        //是否查询bak表
        $isSearchBak = $this->isSearchBak($carStatus);
        if ($searchInExt) {     //查电话号码或车牌号或个人录入或系统录入
            $searchParams['is_mobile'] = 1;
            
            if (!$isSearchBak) {
                $postList = CarSaleInterface::getPostFullFieldsByFilters($searchParams);
                $postList['total'] = CarSaleInterface::getFullFieldsPostCount($searchParams);
            } else {
                $postList = CarSaleBakInterface::getPostFullFieldsByFilters($searchParams);
                $postList['total'] = CarSaleBakInterface::getFullFieldsPostCount($searchParams);
            }
        } else if (!$isSearchBak && !$searchId && !$myOrMystore) {   //搜索查询,走检索
            $searchParams = CarSaleList::adjustParams($searchParams);
            $postList = CarSaleInterface::search($searchParams);
            if (!$postList['total']) {
                $postList = array();
            }
        } else {    //在car_sale_bak表里查或查车源ID
            $postList = MbsCarSaleTmpInterface::getCarSaleList($searchParams);
        }
        //格式化车源列表
        $data['total'] = $postList['total'] ? $postList['total'] : 0;
        unset($postList['total']);
        $postList = CarSaleList::_formatSaleList($postList['info'] ? $postList['info'] : $postList, $params['query_type']);
        $data['list'] = $postList;
        return $data;
    }
    
    /*
     * @brief 根据车源id获取车源详情
     * 
     * @params info_id 原有info_id删除 现在由S(B)car_id组成，目的不让客户端端修改，就可以直接使用
     *         id      事务审核id
     *         car_id  车源id
     *         message_type 消息类型
     *         
     * @return 参照saleList
     */
    public function getSaleDetailByCarId($params) {
        if($params['info_id']) {
            $carId = substr($params['info_id'], 1);
        } else {
            $carId = $params['car_id'];
        }
        $searchParams['isExt'] = true;
        $searchParams['filters'][] = array('car_id', '=', $carId);
        $postList = MbsCarSaleInterface::getCarSaleList($searchParams);
        if(empty($postList)) {
            $postInfo = CarSaleBakInterface::getCarDetail(array('id' => $carId));
            $postList[] = $postInfo;
        }
        if(empty($postList)) {
            throw new AppServException(AppServErrorVars::CUSTOM, '车源不存在！');
        }
        
        //构造$this->_checksPermission(检查同步到58，爱卡权限)的参数
        $syncParams = array();
        $syncParams['cover_photo'] = $postList[$carId]['cover_photo'];
        $syncParams['id'] = $params['id'];
        $syncParams['car_id'] = $carId;
        
        
        $data['total'] = $postList['total'];
        unset($postList['total']);
        $postList = CarSaleList::_formatSaleList($postList,'my');
        
        $syncParams['dept_id'] = $postList[0]['dept_id'];
        $this->updateRead(array('id' => $params['id'], 'message_type' => $params['message_type'], 'is_read' => 1));
        $ret = $postList[0];
        
        //消息类型为审核信息时查看权限
        if($params['message_type'] ==1) {
            $ret['sync_info'] = $this->_getSyncInfo($syncParams);
        }
        
        return $ret;
    }
    
    /**
     * @author guoch
     * @desc 通过门店id获取
     * @params array('dept_id' =>门店id
     *               'cover_photo' =>封面图片
     *               'car_id' =>车源id
     *               'id' => 事物审核id
     *              )
     * @return array(
     *          published_count=>已发布条数
     *          publish_able => 本季度还可发布条数
     *          sync_source => 客户端展示
     *          is_empty => 本季度是否有同步条数，没有同步条数时为1，有同步条数为0
     * )
     *
     */
    private function _getSyncInfo($params) {
        //检查参数是否为空
        if(empty($params['dept_id']) || empty($params['id']) || empty($params['car_id'])) {
            return false;
        }
        
        //检查权限
        $permission = $this->_checkSyncPermission($params);
        if($permission != 1) {
            $ret['visible'] = 1;
            $ret['errorMessage'] = $permission;
            $ret['sites'] = array();
            return $ret;
        } else {
            $ret['errorMessage'] = '';
        }
        $ret['visible'] = 1;
        //构造查询参数
        $searchParams['dept_id'] = $params['dept_id'];
        $searchParams['sync_site_id'] = 1;
    
        //查询可同步到58同城的参数
        $deptInfo = SyncDeptInfoInterface::getDeptInfoById($searchParams);

        //58同城
        $published = $deptInfo['published_count'] ? $deptInfo['published_count'] : 0;
        $total = $deptInfo['publish_count_total'] ? $deptInfo['publish_count_total'] : 0;
        $publishAble = $total - $published;


        
        //门店没有相关权限
        if(empty($deptInfo['id'])) {
            $ret['visible'] = 1;
            $ret['sites'] = $rs;
            $ret['errorMessage'] = '';
            return $ret;
        }
        
        //VIP过期
        if($deptInfo['publish_life'] < time()) {
            $rs[0]['state'] = '门店58同城对应VIP账户已过期，请续费。';
            $publishAble = 0;
        } else {
            $rs[0]['state'] = '本季度还可以发布'.$publishAble . '条，已发布' . $published . '条';
        }
        
        $rs[0]['name'] = '58同城';
        $rs[0]['type'] = '58';
        $rs[0]['disabled'] = ($publishAble > 0) ? 0 : 1;

        //爱卡汽车
        $rs[1]['name'] = '爱卡汽车';
        $rs[1]['type'] = 'xcar';
        $rs[1]['state'] = '无发布数量限制';
        $rs[1]['disabled'] = 0;

        $ret['sites'] = $rs;
        return $ret;
    }
    
    /**
     * @brief 查询车源是否有同步到58和爱卡的权限 (审核过，同步并编辑过，门店没有权限，没有封面图没有同步权限)
     * @params array('dept_id' =>门店id
     *               'cover_photo' =>封面图片
     *               'car_id' =>车源id
     *               'id' => 事物审核id
     *              )
     * @return 1,有发同步到58和爱卡的权限；
     *          否则返回无法同步的原因
     */
    private function _checkSyncPermission($params) {
        //检查是否已经审核
        $message = MbsCheckMessageInterface::getInfoById(array('id' => $params['id']));
        if($message['finish_time']) {
            return '已审核，无法同步！';
        }
        
        //已经同步并编辑过的
        $is_sync_info = SyncInfoInterface::getSyncSaleInfoList(array('ids'=>array($params['car_id'])));
        if( $is_sync_info ) {
            foreach( $is_sync_info as $k=>$v ) {
                $tmp[$v['sync_site_id']] = $v;
            }
            $is_sync_info = $tmp;
        }
        if($message['finish_time']) {
            return '已经同步并编辑过!';
        }
        
        //检查时候有封面图片
        if(empty($params['cover_photo'])) {
            return '车源没有封面图片，无法同步！';
        }
        return 1;
    }
    /**
     * @brief 我的（店内）买车列表
     *
     * @params 新旧参数变化请参照apiworkbook
     *
     * @return 将数据格式化后兼容旧版本
     */
    public function buyList($params) {
        //搜索查询时，若关键字为品牌或车系，将关键字转换为品牌或车系的ID进行查询，列表多返回品牌车系数据
        $ext = self::getInfoByKeyword($params);
        if(!empty($ext)) {
            $ret['ext'] = $ext;
        }
        
        //格式化搜索参数
        $searchParams = CarBuyList::getSearchParams($params);
        if (!empty($params['status']) && ($params['status'] == 5 || $params['status'] == 6)) {
            //去car_buy_bak取数据
            $searchParams['bak'] = 1;
        }
        $postList = CarBuyInterface::getCarBuyList($searchParams);
        
        //格式化输出结果
        $ret['total'] = $postList['total'];
        unset($postList['total']);
        $postList = CarBuyList::formatBuyList($postList);
        $ret['list'] = $postList;
        return $ret;
    }
    
    /*
     * @ brief 根据车源id获取车源详情
     *
     * @ params info_id 原有info_id删除 现在由S(B)car_id组成，目的不让客户端端修改，就可以直接使用
     *         id      事务审核id
     *         car_id  车源id
     *         message_type //消息类型
     *
     * @ return 参照buylist
     */
    public  function getBuyDetailByCarId($params) {
        if($params['info_id']) {
            $carId = substr($params['info_id'], 1);
        } else {
            $carId = $params['car_id'];
        }
        $postList = CarBuyInterface::getCarInfoById(array('id' => $carId));
        if(empty($postList)) {
            throw new AppServException(AppServErrorVars::CUSTOM, '车源不存在！');
        }
        $postList = CarBuyList::formatBuyList(array($postList), 'my');
        self::updateRead(array('id' => $params['id'], 'message_type' => $params['message_type'], 'is_read' => 1));
        return $postList[0];
    }
    
    
    /*
     * @brief 新增买车信息
    *
    * @params 兼容旧版本的参数（格式化参数）
    *
    * @return 没有返回，成功与否都会抛出语句。（后期修改？）
    */
    public function newBuy($params) {
        $ret = CarBuy::submmitBuy($params);
        return $ret;
    }
    
    /*
     * @brief 更新买车信息
     * 
     * @params 兼容旧版本的参数（格式化参数）
     * 
     * @return 没有返回，成功与否都会抛出语句。（后期修改？）
     */
    public function updateBuy($params) {
        $ret = CarBuy::submmitBuy($params);
        return $ret;
    }
    
    /*
     * @brief 新增卖车信息
    *
    * @params 兼容旧版本的参数（格式化参数）
    *
    * @return 没有返回，成功与否都会抛出语句。（后期修改？）
    */
    public function newSale($params) {
        $ret = CarSale::submmitSale($params);
        return $ret;
    }

    /***
     * @brief 新增卖车信息，供合作站使用，发布车源成功后，车源将出现在“全网搜车”后台
     * **/
    public function newSaleSouche( $params ) {
        
        //$str = '{"_api_version":"3","_api_key":"5dd24a00c096d9a92bbf4d9e07ed764f","from_site":"xcar","is_nologin":"1","car_type":"1","brand_id":"","series_id":"","model_id":"3138","province":"18","city":"11","district":"0","plate_province":"18","plate_city":"11","brind_name":null,"car_color":"2","price":"10.4","kilometer":"5","car_number":null,"note":null,"title":"","card_time":"2010-01-01","safe_time":"2015-001-01","year_check_time":"2014-001-01","busi_insur_time":"0000-00-00","transfer_num":"1","maintain_address":"1","use_quality":"1","sale_quality":"1","contact_user":null,"telephone":"18650361296","telephone2":"","idcard":"","dept_id":"","_api_app":"bc","_api_passport":"2hG29zhw%2FBUyVe04Vqlvc2qK1V%2FF1qjQqF%2BtxgGB4wsKu2aW0vptfvlWrgbKUTwm","image":[{"cover":"1","is_plate":"1","file_path":"eqsdfs01\/M01\/9A\/EF\/CgDJBFNUz5SAK_yxAACnVshBTc4304.jpg"},{"cover":"0","is_plate":"1","file_path":"eqsdfs01\/M01\/98\/60\/CgDJBVNUz5SAF__rAACa7VCr1Mw238.jpg"},{"cover":"0","is_plate":"1","file_path":"eqsdfs01\/M02\/9A\/EF\/CgDJBFNUz5SARKhfAACFyfGBAdE340.jpg"},{"cover":"0","is_plate":"1","file_path":"eqsdfs01\/M02\/98\/60\/CgDJBVNUz5SAEEYWAAB6UfJ_bQE886.jpg"},{"cover":"0","is_plate":"1","file_path":"eqsdfs01\/M03\/9A\/EF\/CgDJBFNUz5SAWriFAACB7GnG94A469.jpg"},{"cover":"0","is_plate":"1","file_path":"eqsdfs01\/M03\/98\/60\/CgDJBVNUz5SAIPNfAACgnVFB--M591.jpg"}],"php_input":"","car_sale_id_own":"10395889"}';
        //$params = json_decode($str,true);
        //print_r($params); exit;
        $params['image'] = self::getCarSaleImg($params['image']);
        $ret = CarSale::submmitSale($params);
        if( $ret && $ret != 3 ) {
            $nowCarId = $ret;
            if (is_array($ret)) {
                $nowCarId = $ret['car_id'];
            }
            $params['car_sale_id_own'] = $nowCarId;
            
            // 爱卡车源默认类型为1
            $fromType = 1;
            
            // 检查此车源是否是有效的个人车源
            include_once API_PATH . '/interface/CarPhoneLibInterface.class.php';
            $params['brand_id'] = isset($params['brand_id']) ? $params['brand_id'] : 0;
            $params['series_id'] = isset($params['series_id']) ? $params['series_id'] : 0;
            $phoneLibParams = array(
                    'phone' => $params['telephone'],
                    'site' => 'xcar',
                    'url' => 'http://www.xcar.com.cn/'.$params['brand_id'].'/'.$params['series_id'].'/'.$params['telephone'],
                    'brand_id' => $params['brand_id'],
                    'series_id' => $params['series_id'],
                    'from_source' => 1,
                    'publish_time' => time(),
                    'province' => $params['province'],
                    'city' => $params['city'],
            );
            $isvalid = CarPhoneLibInterface::insertThirdPartyInfo($phoneLibParams);
            if ($isvalid) {
                //若是有效个人车源，将数据插入全网搜车表
                $array = array(
                        'car_sale_id'=>$nowCarId,
                        'telephone'=>$params['telephone'],
                        'create_time'=>time(),
                        'from_type'=> $fromType,
                        'from_data_info'=>json_encode($params),
                        'source_id' => $params['cid'],
                        'province' => $params['province'],
                        'city' => $params['city'],
                );
                CarCompetitionInterface::insertCompetition($array);
            }
        }
        return $nowCarId;
    }
    
    /*
    * @brief 处理车源图片，上传至服务器中，并且返回路径参数
    *
    * @params $imageArray 要处理的图片数据
    *
    * @return $params['image'] 处理完后的图片数据
    */
    public function getCarSaleImg($imageArray) {
        if (is_array($imageArray)) {
            $imgKeyStr = '';
            foreach ($imageArray as $key => $value) {
                $postResult = self::getCurlPostResult($value);
                $imageArray[$key]['file_path'] = $postResult['url'];
                unset($imageArray[$key]['content']);
            }
            return $imageArray;
        }
        else {
            return $imageArray;
        }
    }
    
    /*
     * @brief curl提交
    *
    * @params $params 提交的相关参数
    *
    * @return $postResult 提交后的返回结果
    */
    public function getCurlPostResult($params) {
        $url = 'http://upload.273.com.cn/upload2.php?category=outside';
        
        $postData = $params['content'];
        $postData = base64_decode($postData);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        
        $output = curl_exec($ch);
        curl_close($ch);
        
        $output = json_decode($output, true);
        
        return $output;
    }
    
    /**
    * @brief 卖车更新接口
    * @params 兼容旧版本的参数（格式化参数）
    * @return 没有返回，成功与否都会抛出语句。（后期修改？）
    */
    public function updateSale($params) {
        
        $ret = CarSale::submmitSale($params);
        
        if($ret == 3) {
            return array('result' => 1);
        }
        throw new AppServException(AppServErrorVars::CUSTOM, '更新卖车失败！');
    }
    

    /**
     * @brief 获取我的来电列表和店内来电列表
     */
    public function phoneList($params) {
        if(empty($params)) {
            return array();
        }
        $phoneList = PhoneList::getPhoneList($params);
        if(empty($phoneList)) {
            return array();
        }
        return PhoneList::_formatPhoneList($phoneList);
    }
    

    /**
     * @brief 通迅录
     * 
     */
    public function addressBook($params) {
        $deptId = AppServAuth::$userInfo['user']['dept_id'];
        $deptId = (!empty($params['dept_id']) && $params['dept_id'] != $deptId) ? $params['dept_id'] : $deptId;
        $deptInfo = MbsDeptInterface::getDeptInfoByDeptId(array('id'=>$deptId));
        
        //记录切换别的门店时的日志
        if ($deptId != AppServAuth::$userInfo['user']['dept_id'] && !empty($params['dept_id'])) {
            $info = array(
                'username'    => AppServAuth::$userInfo['user']['username'],
                'dept_id'     => $deptId,
                'dept_name'   => $deptInfo['dept_name'],
                'client_type' => 2,
            );
            MbsContactLogInterface::insertInfo(array('info' => $info));
        }
        $data = array();
        //获取当前店的相关信息
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
    /*
     * @brief 车源刷新功能
     * 
     * @param car_id 车源id
     * 
     * @return 如果刷新成功返回状态1，刷新失败返回失败原因。
     */
    public function refreshRank($params) {
        if($params['info_id']) {
            $carId = substr($params['info_id'], 1);
        } else {
            $carId = $params['car_id'];
        }
        
        $refreshparams['loginUserInfo'] = AppServAuth::$userInfo['user'];
        $refreshparams['carId'] = $carId;
        $refreshparams['is_mobile'] = 1;
        $ret = MbsCarSaleInterface::refreshCarRank($refreshparams);
        
        if (!empty($ret) && $ret['refreshStatus'] == 9) {
            $refreshInfo = MbsRefreshStatInterface::getDetailByUsername(array('username' => AppServAuth::$userInfo['user']['account']));
            if (!empty($refreshInfo)) {
                $webNumber = isset($refreshInfo['total_number']) ? $refreshInfo['total_number'] : 50;
                $mobileNumber = isset($refreshInfo['mobile_total_number']) ? $refreshInfo['mobile_total_number'] : 50;
                $total = $webNumber + $mobileNumber;
                $detail = !empty($refreshInfo) ? unserialize($refreshInfo['detail']) : array();
                $thisCar = isset($detail[$carId]) ? $detail[$carId] : 2;
            }
            if ($detail[$carId] <= 0) {
                $prompt = '<html><head><body><font color=\'#cc0000\' size=\'15px\'>您本条车源今日刷新次数已用完<br></font>'
                        .'<font color=\'#696969\' size=\'15px\'>刷新次数还剩：'.$total.'次</font></body></head></html>';
            } else {
                $prompt = '<html><head><body><font color=\'#000000\' size=\'15px\'>本车源剩余刷新次数：'
                        .$thisCar.'次<br>刷新次数还剩：'.$total.'次</font></body></head></html>';
            }
            return $prompt;
            throw new AppServException(AppServErrorVars::CUSTOM, '刷新成功！');
        } else {
            //车源刷新操作接口的返回状态值
            $errorMsg = isset(CarPostVars::$REFRESH_STATUS[$ret['refreshStatus']]) ? CarPostVars::$REFRESH_STATUS[$ret['refreshStatus']] : "刷新车源失败";
            throw new AppServException(AppServErrorVars::CUSTOM, $errorMsg);
        }
    }
    
    /*
     * @brief 终止车源功能
     * 
     * @param  type      要操作的车源的类型(sale:卖车,buy：买车)
     *         month     暂不卖得时候冻结的月数
     *         day       暂不卖的时候冻结的天数
     *         stoptype  终止类型：（5暂不卖，6虚假，7重复，8不卖，9已卖，10其他）
     *
     * @return 如果终止车源成功，返回状态码1，终止失败，则返回终止失败原因
     * 
     */
    public function stop($params) {
        if($params['info_id']) {
            //$params['car_id'] = substr($params['info_id'], 1);
            $params['car_id'] = $params['info_id'];
            //$params['type'] = (substr($params['info_id'], 0, 1) == 'S') ? 'sale' : 'buy';
        } else {
            $params['car_id'] = $params['car_id'];
        }
        //检查参数完整性
        if(empty($params['type']) || empty($params['stop_type']) || empty($params['car_id'])) {
            throw new AppServException(AppServErrorVars::CUSTOM, '参数不完整');
        }
        $ret = CarStop::stop($params);
        return $ret;
    }
    
    /*
     * @brief       取消终止车源操作
     * 
     * @params type     需要取消终止车源的类型
     * @params car_id   终止车源id
     * 
     * @return 返回1取消终止成功，否则返回取消终止失败的原因
     * 
     */
    public function cancleStop($params) {
        if($params['info_id']) {
            $carId = substr($params['info_id'], 1);
        } else {
            $carId = $params['car_id'];
        }
        $msg = '';
        if ($params['type'] == 'buy') {
            //买车取消终止
            $carBuyBakInfo = CarBuyBakInterface::getInfoById(array('id' => $carId));
            if (empty($carBuyBakInfo)) {
                throw new AppServException(AppServErrorVars::CUSTOM, '无法获取该买车信息，无法取消');
            }
            if ($carBuyBakInfo['status'] != 3 && $carBuyBakInfo['status'] != 2) {
                throw new AppServException(AppServErrorVars::CUSTOM, '该车源目前不是终止或冻结状态，无法取消');
            }
            $updateInfo['status'] = 1;
            $updateInfo['update_user'] = AppServAuth::$userInfo['user'];
            CarBuyBakInterface::updateInfo(array('info' => $updateInfo, 'filters' => array(array('id', '=', $carId))));
            if (!empty($carBuyBakInfo['customer_id'])) {
                //把客户信息回复到正常
                MbsCustomerInterface::customerUpdate(array('info' => array('status' => 1), 'filters' => array(array('id', '=', $carBuyBakInfo['customer_id']))));
            }
            throw new AppServException(AppServErrorVars::CUSTOM, '取消成功!');
        } else {
            //卖车取消终止
            $interfaceParams = array(
                'car_id'     => $carId,
                'user_info'  => AppServAuth::$userInfo['user'],
                'ext_params' => array(
                ),
            );
            $result = SaleOperationInterface::sponsorCancelStopForMobile($interfaceParams);
            if ($result['errorCode']) {
                $msg = !empty($result['msg']) ? $result['msg'] : '提交取消终止失败';
                throw new AppServException(AppServErrorVars::CUSTOM, $msg);
            }
        }
        throw new AppServException(AppServErrorVars::CUSTOM, '取消成功,已发送店长审核!');
    }
    

    
    /*
     * @brief   审核消息列表--消息中心
    *
    * @params 
    *
    */
    public function checkList($params) {
        $status = $params['status'] ? $params['status'] : 0;
        $Searchparams['filters'][] = array('status', '=', $status);
        
        //按照时间来分页
        if(!empty($params['start_time'])) {
            $insert_time = $params['start_time'];
            $Searchparams['filters'][] = array('insert_time', '<', $params['start_time']);
        }
        if (AppServAuth::$userInfo['user']['role_id'] == 27) {//店长
            $Searchparams['filters'][] = array('reason', 'in', array( 26, 56, 5));
        } else {
            //店秘
            $Searchparams['filters'][] = array('reason', 'in', array(4, 1));
        }
        //$Searchparams['filters'][] = array('reason', 'in', array( 26, 56, 4, 3, 5, 1));
        //$Searchparams['filters'][] = array('accept_id', '=', AppServAuth::$userInfo['user']['username']);
        $Searchparams['filters'][] = array('dept_id', '=', AppServAuth::$userInfo['user']['dept_id']);
        $Searchparams['limit'] = $params['page_num'] ? $params['page_num'] : 20;
        $Searchparams['offset'] = 0;
        $Searchparams['orderBy'] = array('insert_time' => 'desc');
        if($params['is_read'] == 2) {
            $Searchparams['filters'][] = array('is_read', 'in', array(0,1));
        }
        $postList = MbsCheckMessageInterface::getList($Searchparams);
        $rs['count'] = $postList['total'];
        unset($postList['total']);
        foreach($postList as $key => $value) {
            $ret['id'] = $value['id'] ? $value['id'] : '';
            $ret['create_time'] = $value['insert_time'] ? $value['insert_time'] : '';
            $ret['title'] = $value['title'] ? $value['title'] : '';
            $ret['is_read'] = $value['is_read'] ? $value['is_read'] : 0;
            $ret['type'] = $value['reason'] ? $value['reason'] : 0;
            $ret['message_type'] = 1;
            
            //修改信息
            if (!empty($value['object_id'])) {
                //去获取原始值等 更新信息
                $updateInfo = MbsUpdateOperationInterface::getInfoById(array('id' => $value['object_id'], 'field' => 'old_value,update_value'));
                if ($value['reason'] == 1) {
                    //修改联系方式：mobile telephone telephone2
                    $updateInfo['update_value'] = unserialize($updateInfo['update_value']);
                    $updateInfo['old_value'] = unserialize($updateInfo['old_value']);
                    $oldValue = $updateInfo['old_value']['mobile'];
                    $updateValue = $updateInfo['update_value']['mobile'];
                } else {
                    $oldValue = $updateInfo['old_value'];
                    $updateValue = $updateInfo['update_value'];
                }
            }
            if(!empty($value['car_id'])) {
                if($value['type'] == 0) {
                    $carInfo = CarSaleInterface::getCarInfoById(array('id' => $value['car_id']));
                    if(empty($carInfo)) {
                        $carInfo = CarSaleBakInterface::getCarInfoById(array('id' => $value['car_id']));
                    }
                    $tag = 'S';
                } else {
                    $carInfo = CarBuyInterface::getCarInfoById(array('id' => $value['car_id']));
                    if(empty($carInfo)) {
                        $carInfo = CarBuyBakInterface::getInfoById(array('id' => $value['car_id']));
                    }
                    $tag = 'B';
                }
            }
            $ret['status'] = $value['status'];
            $ret['old_value'] = $oldValue ? $oldValue : '';
            $ret['update_value'] = $updateValue ? $updateValue : '';
            $ret['car_id'] = $value['car_id'] ? $value['car_id'] : 0;
            $ret['brand'] = $carInfo['title'] ? $carInfo['title'] : '';
            $ret['price'] = $value['appraised_price'] ? $value['appraised_price'] : 0;
            $ret['kilometer'] = $carInfo['kilometer'] ? $carInfo['kilometer'] : '';
            $ret['info_id'] = $tag.$value['car_id'];
            $ret['is_self'] = AppServAuth::$userInfo['user']['username'] == $carInfo['follow_user_id'] ? 1 : 0;
            $rs['info'][] = $ret;
        }
        return $rs;
    }
    
    /*
     * @brief   审核消息列表--消息中心
    *
    * @params
    *
    */
    public function checkMessage($params) {
        $rs = array();
        $filters = array();
        $countFilters = array(
            array('reason', 'in', array(1, 3, 4, 5, 7, 8, 98, 129, 500, 87, 132)),
            array('accept_id', '=', AppServAuth::$userInfo['user']['username']),
            array('status', '=', 0),
        );
        
        // 暂时用is_read表示逻辑删除
        $filters[] = array('is_read', '!=', 2);

        //按照时间来分页
        if(!empty($params['start_time'])) {
            $filters[] = array('insert_time', '<', $params['start_time']);
        }

        // is_read其实已经废弃了用status来替代，兼容客户端的请求
        if(in_array($params['is_read'], array(0, 1))) {
            $filters[] = array('status', '=', (int) $params['is_read']);
        }

        $filters[] = array('reason', 'in', array(1, 3, 4, 5, 7, 8, 98, 129, 500, 87, 132));
        $filters[] = array('accept_id', '=', AppServAuth::$userInfo['user']['username']);
        $limit  = $params['page_num'] ? $params['page_num'] : 20;
        $offset = 0;
        $order  = array('insert_time' => 'desc');
        
        $postList = MbsMessageInterface::getList(array(
            'filters'     => $filters,
            'limit'       => $limit,
            'offset'      => $offset,
            'orderBy'     => $order,
        ));
        
        $rs['info'] = array();
        $rs['count'] = MbsMessageInterface::getCountByFilter(array('filters' => $countFilters));
        unset($postList['total']);
        foreach($postList as $key => $value) {
            $ret['id'] = $value['id'];
            $ret['create_time'] = $value['insert_time'];
            $ret['title'] = $value['title'];
            $ret['is_read'] = $value['status'];
            $ret['type'] = $value['type'];
            $ret['status'] = $value['status'];
            $ret['message_type'] = 3;
            if(!empty($value['car_id'])) {
                if($value['type'] == 0) {
                    $carInfo = CarSaleInterface::getCarInfoById(array('id' => $value['car_id']));
                    if(empty($carInfo)) {
                        $carInfo = CarSaleBakInterface::getCarInfoById(array('id' => $value['car_id']));
                    }
                    $tag = 'S';
                } else {
                    $carInfo = CarBuyInterface::getCarInfoById(array('id' => $value['car_id']));
                    if(empty($carInfo)) {
                        $carInfo = CarBuyBakInterface::getInfoById(array('id' => $value['car_id']));
                    }
                    $tag = 'B';
                }
            }
            
            $ret['reason'] = !empty($value['reason']) && !empty($value['car_id']) ? $this->_getMessageReason($value['reason'], $value['car_id']) : '';
            $ret['car_id'] = $value['car_id'] ? $value['car_id'] : 0;
            
            $ret['brand'] = $carInfo['title'] ? $carInfo['title'] : '';
            $ret['price'] = $carInfo['price'] ? $carInfo['price'] : 0;
            $ret['kilometer'] = $carInfo['kilometer'] ? $carInfo['kilometer'] : 0;
            $ret['info_id'] = $tag.$value['car_id'];
            $ret['is_self'] = $carInfo['follow_user_id'] == AppServAuth::$userInfo['user']['username'] ? 1 : 0;
            $rs['info'][] = $ret;
        }
        return $rs;
    }
    
    private function _getMessageReason($reason, $carId) {
        if (empty($reason)) {
            return '';
        }
        switch ($reason) {
            /*case 129 : //卖车审核通过的提醒
                $evaluateInfo = MbsEvaluateInterface::getEvaluateInfoByCarId(array('car_id' => $carId));
                return !empty($evaluateInfo['evaluate_content']) ? $evaluateInfo['evaluate_content'] : '';
                break;*/
            case 98 : //卖车审核驳回
                $evaluateInfo = MbsDisevaluateInterface::getInfoByCarId(array('car_id' => $carId));
                return !empty($evaluateInfo['disevaluate_content']) ? $evaluateInfo['disevaluate_content'] : '';
                break;
            default :
                break;
        }
        
        return '';
    }
    
    /**
     * @brief   审核消息列表--消息中心
     * @params
     *
     */
    public function visitMessage($params) {
        //构造参数
        $countFilters = array(
            array('accept_id', '=', AppServAuth::$userInfo['user']['username']),
            array('status', '=', 0),
            array('is_read', '!=', 2),
        );
        $count = MbsVisitMessageInterface::getMessageCount(array('filters' => $countFilters));
        
        $Searchparams = array(
            'filters'    => array(),
        );
        // 暂时用is_read表示逻辑删除
        $Searchparams['filters'][] = array('is_read', '!=', 2);
        //按照时间来分页
        if(!empty($params['start_time'])) {
            $insert_time = $params['start_time'];
            $Searchparams['filters'][] = array('insert_time', '<', $params['start_time']);
        }
        
        if($params['is_read'] == 2) {
            $Searchparams['filters'][] = array('is_read', 'in', array(0,1));
        }
        if(in_array($params['is_read'], array(0, 1))) {
            $Searchparams['filters'][] = array('status', '=', (int) $params['is_read']);
        }
        //$Searchparams['filters'][] = array('reason', 'in', array(1, 3, 36, 37));
        $Searchparams['filters'][] = array('accept_id', '=', AppServAuth::$userInfo['user']['username']);
        $Searchparams['limit'] = $params['page_num'] ? $params['page_num'] : 20;
        $Searchparams['offset'] = 0;
        $params['order'] = array('insert_time' => 'desc');
        
        $postList = MbsVisitMessageInterface::getList($Searchparams);
        unset($postList['total']);
        $rs = array(
            'info'   => array(),
            'count' => $count,
        );
        foreach($postList as $key => $value) {
            $ret['id'] = $value['id'];
            $ret['create_time'] = $value['insert_time'];
            $ret['title'] = $value['title'];
            $ret['is_read'] = $value['status'];
            $ret['type'] = $value['type'];
            $ret['status'] = $value['status'];
            $ret['message_type'] = 2;
            if(!empty($value['car_id'])) {
                if($value['type'] == 0) {
                    $carInfo = CarSaleInterface::getCarInfoById(array('id' => $value['car_id']));
                    if(empty($carInfo)) {
                        $carInfo = CarSaleBakInterface::getCarInfoById(array('id' => $value['car_id']));
                    }
                    $tag = 'S';
                } else {
                    $carInfo = CarBuyInterface::getCarInfoById(array('id' => $value['car_id']));
                    if(empty($carInfo)) {
                        $carInfo = CarBuyBakInterface::getInfoById(array('id' => $value['car_id']));
                    }
                    $tag = 'B';
                }
            }
            $ret['reason'] = $value['reason'] ? $value['reason'] : 0;
            $ret['car_id'] = $value['car_id'] ? $value['car_id'] : 0;
            $ret['brand'] = $carInfo['title'] ? $carInfo['title'] : 0;
            $ret['price'] = $carInfo['price'] ? $carInfo['price'] : 0;
            $ret['kilometer'] = $carInfo['kilometer'] ? $carInfo['kilometer'] : 0;
            $ret['info_id'] = $tag.$value['car_id'];
            $ret['info_id'] = $tag.$value['car_id'];
            $ret['is_self'] = $carInfo['follow_user_id'] == AppServAuth::$userInfo['user']['username'] ? 1 : 0;
            $rs['info'][] = $ret;
        }
        return $rs;
    }
    /*
     * @brief   审核消息列表--消息中心
    *
    * @params
    *
    */
    public function getMessageBytime($params) {
//         $status = $params['status'] ? $params['status'] : 0;
//         $params['filters'][] = array('status', '=', $status);
//         $prams['filters'][] = array('reason', 'in', array(1, 3, 36, 37));
//         $params['filters'][] = array('accept_id', '=', AppServAuth::$userInfo['user']['username']);
//         $params['limit'] = $params['end'] - $params['start'] +1;
//         $params['offset'] = $params['start'];
//         $params['orderBy'] = array('id' => 'desc');
//         $postList = MbsVisitMessageInterface::getList($params);
        unset($postList['total']);
        return '';
    }

    /**
     * 审核通过：卖、买车审核，车源终止,取消终止，修改联系方式，修改身份证号
     */
    public function checkPass($params) {
        $user = AppServAuth::$userInfo['user'];
        $deptInfo  = MbsDeptInterface::getDeptInfoByDeptId(array('id' => $user['dept_id']));
        $reason    = $params['reason'];
        $checkCont = $params['check_content'];
        $carId     = substr(trim($params['info_id']), 1);
        $checkId   = $params['id'];
        $createId  = $params['create_id'];
    
        switch ($reason) {
            case 5:
                $syncContent = json_decode(stripslashes($params['sync_content']) , true);
                /*$rs = $this->_Sync2OtherSite($syncContent);
                if(empty($rs)) {
                    return 0;
                }*/
                //卖车录入同步
            case 6:
                //客服分配审核同步
                $price = $params['appraised_price'];
                if (empty($price) || empty($carId) || empty($reason) || empty($checkId)) {
                    throw new AppServException(AppServErrorVars::CUSTOM, '参数缺失，无法完成审核');
                }
                
                $sync_58   = $syncContent['sync_58'];
                $sync_xcar = $syncContent['sync_xcar'];
                
                $map_bid_58 = $syncContent['map_bid_58'];
                $map_sid_58 = $syncContent['map_sid_58'];
                $map_mid_58 = $syncContent['map_mid_58'];
                $map_bname_58 = $syncContent['map_bname_58'];
                $map_sname_58 = $syncContent['map_sname_58'];
                $map_mname_58 = $syncContent['map_mname_58'];
                
                $map_bid_xcar = $syncContent['map_bid_xcar'];
                $map_sid_xcar = $syncContent['map_sid_xcar'];
                $map_mid_xcar = $syncContent['map_mid_xcar'];
                $map_bname_xcar = $syncContent['map_bname_xcar'];
                $map_sname_xcar = $syncContent['map_sname_xcar'];
                $map_mname_xcar = $syncContent['map_mname_xcar'];
                
                $interfaceParams = array(
                    'car_id'     => $carId,
                    'user_info'  => $user,
                    'ext_params' => array(
                        'check_id'   => $checkId,
                        'check_cont' => $checkCont,
                        'price'      => $price,
                        'sync_xcar'  => $sync_xcar,
                        'sync_58'    => $sync_58,
                        'map_bid_58' => $map_bid_58,
                        'map_sid_58' => $map_sid_58,
                        'map_mid_58' => $map_mid_58,
                        'map_bname_58' => $map_bname_58,
                        'map_sname_58' => $map_sname_58,
                        'map_mname_58' => $map_mname_58,
                        
                        'map_bid_xcar' => $map_bid_xcar,
                        'map_sid_xcar' => $map_sid_xcar,
                        'map_mid_xcar' => $map_mid_xcar,
                        'map_bname_xcar' => $map_bname_xcar,
                        'map_sname_xcar' => $map_sname_xcar,
                        'map_mname_xcar' => $map_mname_xcar,
                    ),
                );
                $result = SaleOperationInterface::shoperCheckPassForMobile($interfaceParams);
                
                if ($result['errorCode']) {
                    //操作返回错误信息
                    $msg = !empty($result['msg']) ? $result['msg'] : '审核失败';
                    throw new AppServException(AppServErrorVars::CUSTOM, $msg);
                }
                break;
            case 26:
            case 27:
                //买车审核通过
                if (empty($carId) || empty($reason) || empty($checkId)) {
                    throw new AppServException(AppServErrorVars::CUSTOM, '参数缺失，无法完成审核');
                }
                CarBuyInterface::updateBuyInfo(array('updateInfo' => array('status' => 1), 'filters' => array(array('id', '=', $carId))));
                //完成审核事务
                MbsCheckMessageInterface::complete(array('id' => $checkId));
                break;
            case 4:
                //卖车，买车终止审核
                $interfaceParams = array(
                    'car_id'     => $carId,
                    'user_info'  => $user,
                    'ext_params' => array(
                        'check_id'   => $checkId,
                        'check_cont' => $checkCont,
                    ),
                );
                $result = SaleOperationInterface::stopCarPassForMobile($interfaceParams);
                if ($result['errorCode']) {
                    $msg = !empty($result['msg']) ? $result['msg'] : '审核失败';
                    throw new AppServException(AppServErrorVars::CUSTOM, $msg);
                }
                
                break;
            case 1:
                //修改联系方式审核
            case 3:
                //修改身份证审核
                $interfaceParams = array(
                    'car_id'     => $carId,
                    'user_info'  => $user,
                    'ext_params' => array(
                        'check_id' => $checkId,
                        'reason'   => $reason,
                    ),
                );
                $result = SaleOperationInterface::checkPhoneIdCardPassForMobile($interfaceParams);
                if ($result['errorCode']) {
                    $msg = !empty($result['msg']) ? $result['msg'] : '审核失败';
                    throw new AppServException(AppServErrorVars::CUSTOM, $msg);
                }
                break;
            case 56:
                //卖车车源取消终止
                $interfaceParams = array(
                    'car_id'     => $carId,
                    'user_info'  => $user,
                    'ext_params' => array(
                        'check_id'   => $checkId,
                    ),
                );
                $result = SaleOperationInterface::cancelStopPassForMobile($interfaceParams);
                if ($result['errorCode']) {
                    $msg = !empty($result['msg']) ? $result['msg'] : '审核失败';
                    throw new AppServException(AppServErrorVars::CUSTOM, $msg);
                }
                break;
            default :
                break;
        }
    
        return 1;
    }
    
    /**
     * 审核驳回：卖、买车审核驳回，车源终止，修改联系方式，修改身份证号
     */
    public function noCheckPass($params) {
        $user = AppServAuth::$userInfo['user'];
    
        $reason    = $params['reason'];
        $checkCont = $params['check_content'];
        $carId     = substr(trim($params['info_id']), 1);
        $checkId   = $params['id'];
        $createId  = $params['create_id'];
    
        if (empty($carId) || empty($reason) || empty($checkId)) {
            throw new AppServException(AppServErrorVars::CUSTOM, '参数缺失，无法完成审核驳回');
        }
    
        switch ($reason) {
            case 5:
                //卖车审核驳回
            case 6:
                //客服分配审核驳回
                $interfaceParams = array(
                    'car_id'     => $carId,
                    'user_info'  => $user,
                    'ext_params' => array(
                        'check_id'   => $checkId,
                        'check_cont' => $checkCont,
                    ),
                );
                $result = SaleOperationInterface::shoperCheckNoPassForMobile($interfaceParams);
                if ($result['errorCode']) {
                    //操作返回错误信息
                    $msg = !empty($result['msg']) ? $result['msg'] : '驳回失败';
                    throw new AppServException(AppServErrorVars::CUSTOM, $msg);
                }
                break;
            case 26:
            case 27:
                //买车审核驳回
                if (empty($carId) || empty($reason) || empty($checkId)) {
                    throw new AppServException(AppServErrorVars::CUSTOM, '参数缺失，无法完成审核');
                }
                $checkMessageInfo = MbsCheckMessageInterface::getInfoById(array('id' => $checkId));
    
                $carBuyInfo = CarBuyInterface::getCarInfoById(array('id' => $carId));
                if ($carBuyInfo['follow_user_id'] == $user['account']) {
                    //终止车源
                    $upParams = array(
                            'updateInfo' => array(
                                    'status' => 3,
                                    'stop_time' => time(),
                                    'update_time' => time(),
                                    'update_user' => $user['account'],
                            ),
                            'filters' => array(array('id', '=', $carId)),
                    );
                    CarBuyInterface::updateBuyInfo($upParams);
                    //终止客户
                    MbsCustomerInterface::stopCustomer(array('id' => $carBuyInfo['customer_id']));
                    //更新终止操作,把该carid的最新的一条终止操作日志
                    MbsStopOperationInterface::updateStopStatus(array('infoId' => $carId, 'infoDeptId' => $carBuyInfo['store_id']));
                }
    
                //发送提醒事务信息
                $messageInfo = array(
                        'reason' => 98,
                        'accept_id' => $checkMessageInfo['create_id'],
                        'car_id' => $carId,
                        'create_id' => $createId ? $createId : $user['account'],
                        'type' => 1,
                );
                MbsMessageInterface::saveMessage($messageInfo);
    
                //完成审核事务
                MbsCheckMessageInterface::complete(array('id' => $checkId));
                break;
            case 1:
                //修改联系方式 驳回
            case 3:
                //修改身份证 驳回
                $interfaceParams = array(
                    'car_id'     => $carId,
                    'user_info'  => $user,
                    'ext_params' => array(
                        'check_id'   => $checkId,
                        'check_cont' => $checkCont,
                        'reason'     => $reason,
                    ),
                );
                $result = SaleOperationInterface::checkPhoneIdCardNoPassForMobile($interfaceParams);
                if ($result['errorCode']) {
                    $msg = !empty($result['msg']) ? $result['msg'] : '驳回失败';
                    $this->render(array('errorCode' => 1, 'msg' => $msg));
                }
                break;
            case 4:
                //终止审核驳回
                $interfaceParams = array(
                    'car_id'     => $carId,
                    'user_info'  => $user,
                    'ext_params' => array(
                        'check_id'   => $checkId,
                        'check_cont' => $checkCont,
                    ),
                );
                $result = SaleOperationInterface::stopCarNoPassForMobile($interfaceParams);
                if ($result['errorCode']) {
                    $msg = !empty($result['msg']) ? $result['msg'] : '驳回失败';
                    throw new AppServException(AppServErrorVars::CUSTOM, $msg);
                }
                break;
            case 56:
                //卖车取消终止审核驳回
                $interfaceParams = array(
                    'car_id'     => $carId,
                    'user_info'  => $user,
                    'ext_params' => array(
                        'check_id'   => $checkId,
                        'check_cont' => $checkCont,
                    ),
                );
                $result = SaleOperationInterface::cancelStopCarNoPassForMobile($interfaceParams);
                if ($result['errorCode']) {
                    $msg = !empty($result['msg']) ? $result['msg'] : '驳回失败';
                    throw new AppServException(AppServErrorVars::CUSTOM, $msg);
                }
                break;
            default :
                break;
        }
    
        return 1;
    }
    
    public function messageCount($params) {
        $params['username'] = AppServAuth::$userInfo['user']['username'];
        $params['reason'] = '1, 3, 4, 5, 7, 8, 98, 129, 500, 87, 132';
        $checkMessage = MbsMessageInterface::getMessageCount(array(
            'filters' => array(
                array('reason', 'in', array(1, 3, 4, 5, 7, 8, 98, 129, 500, 87, 132)),
                array('accept_id', '=', AppServAuth::$userInfo['user']['username']),
                array('status', '=', 0),
                //array('is_read', '!=', 2),
            ),
        ));
        $ret['check_message'] = (int)$checkMessage;
    
        $params['reason'] = '1, 3, 36, 37';
        $count = MbsVisitMessageInterface::getMessageCount(array('filters' => array(
            //array('reason', 'in', array(1, 3, 36, 37)),
            array('accept_id', '=', AppServAuth::$userInfo['user']['username']),
            //array('is_read', '!=', 2),
            array('status', '=', 0),
        )));
        $ret['visit_message'] = $count;
    
        $params['reason'] = '26, 56, 4, 5, 1';
        //$where = $this->_getWhere($params);
        $checkStatus = !empty($params['status']) ? $params['status'] : 0;
        $where = array(
            array('dept_id', '=', AppServAuth::$userInfo['user']['dept_id']),
            array('status', '=', $checkStatus),
        );
        $ret['check_list'] = MbsCheckMessageInterface::getMessageCount(array('filters' => $where));
        
        return $ret;
    }
    
    private function _getWhere($info) {
        $wheres = array();
    
        $wheres[] = array('accept_id', '=', $info['username']);
    
        if($info['status']){
            $wheres[] = array('status', '=', intval($info['status']));
        } else {
            $wheres[] = array('status', '=', 0);
        }
        if($info['reason']){
            $wheres[] = array('reason', 'in', explode(',', $info['reason']));
        }
        if(isset($info['is_read'])){
            $wheres[] = array('is_read', '=', intval($info['is_read']));
        }else {
            $info['is_read'] = 0;
            $wheres[] = array('is_read', '=', intval($info['is_read']));
        }
    
        if ($info['is_read'] == 1) {
            $wheres[] = array('insert_time', '>=', strtotime('-20 days'));
        } elseif ($info['is_read'] == 0) {
            $wheres[] = array('insert_time', '>=', strtotime('-60 days'));
        }
        return $wheres;
    
    }
    
    /**
     * 更新已读未读或逻辑删除
     */
    public function updateRead($params) {
        $ret = array();
        if(empty($params['message_type'])) {
            return 0;
        }
        // 单条删除
        if(strlen($params['message_type']) == 1){
            //-------visit 和 message的置已读(status=1)或删除(is_read=2)-------
            $updateInfo = array();
            $filters = array(
                array('id', '=', $params['id']),
            );
            if (in_array($params['is_read'], array(0, 1))) {
                $updateInfo['status'] = $params['is_read'];
            } else {
                $updateInfo['is_read'] = 2;
            }
            //--------------
            switch ($params['message_type']) {
                case 1:
                    $ret = MbsCheckMessageInterface::updateReadForIds(array($params['id']), $params['is_read']);
                    break;
                case 2:
                    $ret = MbsVisitMessageInterface::updateInfo(array(
                        'info'    => $updateInfo,
                        'filters' => $filters,
                    ));
                    break;
                case 3:
                    $ret = MbsMessageInterface::updateInfo(array(
                        'info'    => $updateInfo,
                        'filters' => $filters,
                    ));
                    break;
                default:
                    break;
            }
            $ret = $ret ? 1 : 0;
        } else { // 批量删除
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
            if($checkId){
                $ret1 = MbsCheckMessageInterface::updateReadForIds($checkId, $params['is_read']);
            }
            if($visitId){
                if (in_array($params['is_read'], array(0, 1))) {
                    $ret2 = MbsVisitMessageInterface::updateReadForIds($visitId, $params['is_read']);
                } else { // '删除'
                    $ret2 = MbsVisitMessageInterface::updateInfo(array(
                        'filters'    => array(
                            array('id', 'in', $visitId),
                        ),
                        'info'       => array(
                            'is_read'   => 2,
                        ),
                    ));
                }
            }
            if($messageId){
                if (in_array($params['is_read'], array(0, 1))) {
                    $ret3 = MbsMessageInterface::updateReadForIds($messageId, $params['is_read']);
                } else {
                    $ret3 = MbsMessageInterface::updateInfo(array(
                        'filters'    => array(
                            array('id', 'in', $messageId),
                        ),
                        'info'       => array(
                            'is_read' => 2,
                            'status'  => 1,
                        )
                    ));
                }
            }
            $ret = ($ret1 || $ret2 || $ret3) ? 1 : 0;
        }
    
        return  $ret;
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
    
    //事务审核，获取车源信息
    private function _getCarInfoByMessageCheck($checkId, $carId, $reason) {
        $checkMessageInfo = MbsCheckMessageInterface::getInfoById(array('id' => $checkId));
        //考虑是否是买车
        if ($checkMessageInfo['type'] == 1) {
            //买车的审核事务
            $carInfo = CarBuyInterface::getCarInfoById(array('id' => $carId));
        } else {
            $carInfo = CarSaleInterface::getCarDetail(array('id' => $carId));
        }
        if (empty($carInfo)) {
            //此时如果是取消终止的话，车源信息可能在bak表中
            if ($reason == 56) {
                $carInfo = CarSaleBakInterface::getCarDetail(array('id' => $carId));
                $isBak = true;
            }
            if (empty($carInfo)) {
                throw new AppServException(AppServErrorVars::CUSTOM, '找不到车源信息');
            }
        }
    
        return $carInfo;
    }
    
    
    /*
     * @brief 手机端同步到58同城,爱卡网
    * @author guoch
    * @params array(
            *     brand_id => 273品牌id
            *     series_id => 273车系id
            *     model_id => 273车型id
            *     sync_58 => 同步到58同城
            *     map_bid_58 => 58品牌id
            *     map_sid_58 => 58车系id
            *     map_mid_58 => 58车型id
            *     map_bname_58 => 58品牌name
            *     map_sname_58 => 58车系name
            *     map_mname_58 => 58车型name
            *     sync_xcar => 同步到爱卡网
            *     map_bid_xcar => 爱卡品牌id
            *     map_sid_xcar => 爱卡车系id
            *     map_mid_xcar => 爱卡车型id
            *     map_bname_xcar => 爱卡品牌name
            *     map_sname_xcar => 爱卡车系name
            *     map_mname_xcar => 爱卡车型name
            *     car_id => 车源id
            * )
    * @return int 1 加入同步队列成功，0加入同步队列失败
    */
    private function _Sync2OtherSite($params) {
        try {
            
            //人工匹配外站车系车型功能
            $bid = intval($params['brand_id']);
            $sid = intval($params['series_id']);
            $mid = intval($params['model_id']);
            
            if(empty($bid) || empty($sid) || empty($mid) || empty($params['car_id'])) {
                return '参数不足';
            }
            
            $sync_58 = $params['sync_58'];
            if( $sync_58 ) {
                //假如需要同步至58，并且品牌，车系，车型都选中，则将对应关系入库
                $map_bid_58 = $params['map_bid_58'];
                $map_sid_58 = $params['map_sid_58'];
                $map_mid_58 = $params['map_mid_58'];
                $map_bname_58 = $params['map_bname_58'];
                $map_sname_58 = $params['map_sname_58'];
                $map_mname_58 = $params['map_mname_58'];
                //品牌，车系为必须，车型id非必须
                if( $bid && $sid && $map_bid_58 && $map_sid_58 ) {
                    $map_array = array(
                            'bid'=>$bid,
                            'sid'=>$sid,
                            'mid'=>$mid,
                            'map_bid'=>$map_bid_58,
                            'map_sid'=>$map_sid_58,
                            'map_mid'=>$map_mid_58,
                            'map_bname'=>$map_bname_58,
                            'map_sname'=>$map_sname_58,
                            'map_mname'=>$map_mname_58,
                            'sync_site_id'=>1,
                    );
                    SyncMapInterface::insertModelMap($map_array);
                }
            }
    
            $sync_xcar = $params['sync_xcar'];
            if( $sync_xcar ) {
                $map_bid_xcar = $params['map_bid_xcar'];
                $map_sid_xcar = $params['map_sid_xcar'];
                $map_mid_xcar = $params['map_mid_xcar'];
                $map_bname_xcar = $params['map_bname_xcar'];
                $map_sname_xcar = $params['map_sname_xcar'];
                $map_mname_xcar = $params['map_mname_xcar'];
                //品牌, 车系, 车型均必须
                if( $bid && $sid && $mid && $map_bid_xcar && $map_sid_xcar && $map_mid_xcar ) {
                    $map_array = array(
                            'bid'=>$bid,
                            'sid'=>$sid,
                            'mid'=>$mid,
                            'map_bid'=>$map_bid_xcar,
                            'map_sid'=>$map_sid_xcar,
                            'map_mid'=>$map_mid_xcar,
                            'map_bname'=>$map_bname_xcar,
                            'map_sname'=>$map_sname_xcar,
                            'map_mname'=>$map_mname_xcar,
                            'sync_site_id'=>SyncCommonVars::$SITE_CONFIG['xcar'],
                    );
                    SyncMapInterface::insertModelMap($map_array);
                }
            }
            //人工匹配外站车系车型功能 end
    
            //同步至其他网站，存如队列
            $sync_array = array();
            $sync_array['id'] = $params['car_id'];
    
            //同步车源数据至58同城
            if( $sync_58 ) {
                $sync_array['s58'] = 1;
            }
            if( $sync_xcar ) {
                $sync_array['xcar'] = 1;
            }
            if( $sync_58 || $sync_xcar ) {
                //更新已同步字段
                $up_arr = array(
                        'info'=>array( 'is_sync'=>'1',),
                        'filter'=>array(0=>array( 'id','=',$params['car_id'],),),
                );
                CarSaleInterface::updateCarInfo($up_arr);
                SyncInfoInterface::setQueue($sync_array);
            }
            //同步至其他网站，存如队列 end
            return 1;
        } catch (Exception $e) {
            //加入队列失败，记录日志
            LoggerGearman::logInfo(array('data'=>$params, 'identity'=>'Sync2OtherSite'));
            return 0;
        }
    }
    
    
    /**
     * @desc 删除草稿接口
     * @param $params['id'] 草稿车源编号
     */
    public function deleteDraft($params) {
        $carId = (int) $params['id'];
        $ret = SaleOperationInterface::delDrafitById(array('car_id' => $carId, 'user_info' => AppServAuth::$userInfo['user']));
        if ($ret) {
            return 1;
            //throw new AppServException(AppServErrorVars::CUSTOM, '删除草稿成功！');
        } else {
            throw new AppServException(AppServErrorVars::CUSTOM, '删除草稿失败');
        }
    }
    
    /**
     * @desc 通过carstatus获取是否去查询bak表
     * @return bool  true:是查询bak表，false：不查询bak表
     */
    public function isSearchBak($carStatus) {
        if (empty($carStatus)) {
            return false;
        }
        $carStatus = (int) $carStatus;
        if (in_array($carStatus, array(4030,2021,2022,5020,5021,5022,6010,6020,7010,7020,7030,7040))) {
            return true;
        }
        
        return false;
    }
}