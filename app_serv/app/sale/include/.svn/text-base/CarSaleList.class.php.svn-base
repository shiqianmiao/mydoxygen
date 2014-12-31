<?php
require_once API_PATH . '/interface/MbsDeptInterface.class.php';
require_once API_PATH . '/interface/MbsUserInterface.class.php';
require_once APP_SERV . '/app/sale/include/CarUtil.class.php';
require_once APP_SERV . '/app/sale/include/BcPost.class.php';
require_once COM_PATH . '/car/CarVars.class.php';
require_once COM_PATH . '/car/CarMbsVars.class.php';
require_once FRAMEWORK_PATH . '/util/common/Util.class.php';
require_once API_PATH . '/interface/VehicleV2Interface.class.php';
require_once API_PATH . '/interface/mbs/MbsUserInterface2.class.php';
require_once APP_SERV . '/app/sale/include/config.php';

class CarSaleList {

    /**
     *@brief    格式化SaleList参数，符合接口参数格式
     *格式化查询参数
     *
     */
    public static function getSearchParams($params) {
        //按照关键字删选
        $keyword = $params['keyword'];
        
        /*
        •信息编号、转接分机号
        •品牌车系、价格区间（8-12）、变速箱（手动、自动）
        •关键词如：按揭、贷款、省油、准新车
        •交易顾问手机号、交易顾问姓名、车主联系方式
        */
        if ($keyword) {
            //搜索电话号码
            if (is_numeric($keyword) && in_array(strlen($keyword),array(10,11,12))) {
                include_once API_PATH . '/interface/MbsCarProtectInfoInterface.class.php';
                $carParams = array(
                    'mobile' => $keyword,
                    'order' => array('car_id' => 'asc'),
                    'limit' => 5000,
                    'offset' => 0
                );
                $carInfoData = MbsCarProtectInfoInterface::getListByMobile($carParams);
                $carIds = array();
                if (!empty($carInfoData)) {
                    foreach ($carInfoData as $item) {
                        $carIds[] = intval($item['car_id']);
                    }
                }
                
                $ret['filters'][] = array('id', 'in', $carIds);
                $ret['filters'][] = array('store_id', '=', AppServAuth::$userInfo['user']['dept_id']);
            } else if (preg_match('/^[\x{4e00}-\x{9fa5}]{1}[A-Z]{1}[A-Z0-9]{4}[A-Z0-9|\x{4e00}-\x{9fa5}]{1}$/u', trim(urldecode($keyword)))) {
                $ret['filters'][] = array('car_number', '=', trim(urldecode($keyword)));
            } else if (is_numeric($keyword) && strlen($keyword) == 6) {
                $ret['filters'][] = array('ext_phone', '=', $keyword);
            } else if (is_numeric($keyword) && ($keyword > 10000) && strlen($keyword) < 10) {
                $ret['filters'][] = array('id', '=', (int)$keyword);
            } else if(strstr($keyword, '-')){ 
                $arrPrice = explode('-', $keyword);
                $ret['filters'][] = array('price', '>=', $arrPrice[0]*10000);
                $ret['filters'][] = array('price', '<=', $arrPrice[1]*10000);
            }else if($keyword == '手动'){
                $ret['filters'][] = array('gear_type', '=', 1);
            }else if($keyword == '自动'){
                $ret['filters'][] = array('gear_type', '=', 2);
            }else{    //搜索车型品牌
                if ($params['brand_id']) {      //品牌关键词和筛选品牌都存在时，以关键词为准
                    unset($params['brand_id']);
                    unset($params['series_id']);
                }
                $ret['filters'] = array(array('title', 'like', '%'.trim(urldecode($keyword)).'%'),);
            }
        }
        
        if ($params['query_type'] == 'my' && !empty(AppServAuth::$userInfo['user']['username'])) {//我的
            $ret['filters'][] = array('follow_user_id', '=', AppServAuth::$userInfo['user']['username']);
        }
        //query_type值为store时为查询店内的卖车列表
        if($params['query_type'] == 'store') {//店内
            $ret['filters'][] = array('store_id', '=', AppServAuth::$userInfo['user']['dept_id']);
        }

        //店的参数
        if(!empty($params['dept'])) {
            $ret['filters'][] = array('store_id', '=', $params['dept']);
        }

        if(!empty($params['dept_list'])){
            $ret['filters'][] = array('store_id', 'in', $params['dept_list']);
        }

        $ret['isExt'] = true;
        //默认参数
        $limit = $params['limit'] ? $params['limit'] : 10;
        $ret['limit'] = $limit;
        $ret['offset'] = ($params['start']) ? ($params['start']-1) : 0;
        $ret['order'] = array('create_time' => 'desc');
        if (!empty($params['status'])) {
            if ($params['status'] == 7010) {
                //已成交
                $ret['filters'][] = array('sale_status', '=', 1);
            } else {
                $ret['filters'][] = array('car_status', '=', $params['status']);
            }
        }
        
        switch ((int)$params['sale_status']) {
            case 1: 
                $ret['filters'][] = array('sale_status', '=', 0);
                break;
            case 2: 
                $ret['filters'][] = array('sale_status', '=', 1);
                $ret['filters'][] = array('status', '=', 1);
                break;
            default:
//                 $ret['filters'][] = array('sale_status', 'in', array(0,1));
                break;
        }
    
        //按价格删选
        if (!empty($params['price'])) {
            $priceArray = explode('-', $params['price']);
            if (isset($priceArray[0])) {
                $ret['filters'][] = array('price', '>=', $priceArray[0]*10000);
            }
            if (isset($priceArray[1])) {
                $ret['filters'][] = array('price', '<=', $priceArray[1]*10000);
            }
        }

        if (!empty($params['year'])) {
            $arrYear = explode('-', $params['year']);

            $ret['filters'][] = array('card_time', '>', strtotime($arrYear[0].'-01-01'));
            $ret['filters'][] = array('card_time', '<', strtotime($arrYear[1].'-12-31'));
        }

        //按上牌时间筛选
        $timeStamp = time();
        if ($params['time']) {
            $timeArray = explode('-', $params['time']);
            $cardAge = $timeArray[0];
            switch($cardAge) {
                case '0':
                    $ret['filters'][] = array('card_time', '>', $timeStamp-3600*24*365);
                    $ret['filters'][] = array('card_time', '<', $timeStamp);
                    break;
                case '1':
                    $ret['filters'][] = array('card_time', '<', $timeStamp-3600*24*365);
                    $ret['filters'][] = array('card_time', '>', $timeStamp-3600*24*365*3);
                    break;
                case '3':
                    $ret['filters'][] = array('card_time', '<', $timeStamp-3600*24*365*3);
                    $ret['filters'][] = array('card_time', '>', $timeStamp-3600*24*365*5);
                    break;
                case '5':
                    $ret['filters'][] = array('card_time', '<', $timeStamp-3600*24*365*5);
                    $ret['filters'][] = array('card_time', '>', $timeStamp-3600*24*365*8);
                    break;
                case '8':
                    $ret['filters'][] = array('card_time', '<', $timeStamp-3600*24*365*8);
                    $ret['filters'][] = array('card_time', '>', $timeStamp-3600*24*365*10);
                    break;
                case '10':
                    $ret['filters'][] = array('card_time', '<', $timeStamp-3600*24*365*10);
                    $ret['filters'][] = array('card_time', '>', $timeStamp-3600*24*365*99);
                    break;
                default:
                    break;
            }
        }
        if (!empty($params['province'])) {
            $ret['filters'][] = array('deal_province_id', '=', $params['province']);
        }
        if (!empty($params['city'])) {
            $ret['filters'][] = array('deal_city_id', '=', $params['city']);
        }
        if (!empty($params['brand_id'])) {
            $ret['filters'][] = array('brand_id', '=', $params['brand_id']);
        }
        if ($params['series_id']) {
            $ret['filters'][] = array('series_id', '=', $params['series_id']);
        }
        if ($params['type_id']) {
            $ret['filters'][] = array('type_id', '=', $params['type_id']);
        }
    
        //按发布时间进行筛选
        switch (intval($params['published'])) {
            case 1: //一周内
                $ret['filters'][] = array('create_time', '>', $timeStamp-24*7*3600);
                break;
            case 2: //半个月内
                $ret['filters'][] = array('create_time', '>', $timeStamp-24*15*3600);
                break;
            case 3: //一个月内
                $ret['filters'][] = array('create_time', '>', $timeStamp-24*30*3600);
                break;
            case 4: //三个月内
                $ret['filters'][] = array('create_time', '>', $timeStamp-24*90*3600);
                break;
        }
    
        //按车源来源筛选
        if($params['username'] == AppServAuth::$userInfo['user']['username']) {
            switch (intval($params['originate'])) {
                case 1: //个人录入
                    $ret['filters'][] = array('create_user_id', '=', AppServAuth::$userInfo['user']['username']);
                    break;
                case 2: //系统分配
                    $ret['filters'][] = array('create_user_id', '<>', AppServAuth::$userInfo['user']['username']);
                    break;
            }
        }
    
        //按归属人进行筛选,仅当查询店内卖车列表时可筛选
        if ($params['dept_id'] == AppServAuth::$userInfo['user']['dept_id']) {
            if($params['follow_user']) {
                $ret['filters'][] = array('follow_user_id', '=', $params['follow_user']);
            }
        }
    
        //按车的颜色筛选
        if (intval($params['color'])) {
            $ret['filters'][] = array('car_color', '=', $params['color']);
        }
    
        //变速器类型删选
        if(!empty($params['gear_type'])){
            $ret['filters'][] = array('gearbox_type', '=', $params['gear_type']);
        }

        //只获取本站车源
//         $ret['filters'][] = array('order_source', '=', 10);
        $ret['filters'][] = array('store_id', '>=', 1);
        return $ret;
    }

    /**
     *@brief    转化SaleList参数为检索参数，符合检索接口参数格式
     *
     */
    public static function adjustParams($searchParams) {
        foreach ($searchParams['filters'] as $key => $field) {
            if ($field[0] == 'price') {
                $priceRange[] = $field[2];
            }
            if ($field[0] == 'card_time') {
                $cardTime[] = $field[2];
            }
            if ($field[0] == 'create_time') {
                $createTime[] = $field[2];
                $createTime[] = time();
            }
            if (strstr($field[2], '%')) {
                $field[2] = str_replace('%', '', $field[2]);
            }
            if ($field[0] == 'store_id' && $field[1] == '=') {  //查询本店
                $myStore = $field[2];
            }
            if ($field['0'] == 'car_status' && $field['1'] == '=') {
                $searchParams[$field[0]] = $field[2];
            }
            $field[0] = $field[0] == 'title' ? 'kw' : $field[0];
            $searchParams[$field[0]] = $field[2];
        }
        if (!empty($priceRange)) {
            $searchParams['price'] = array($priceRange[0], $priceRange[1]);
        }
        if (!empty($cardTime)) {
            $searchParams['card_time'] = array($cardTime[1], $cardTime[0]);
        }
        if (!empty($createTime)) {
            $searchParams['create_time'] = array($createTime[0], $createTime[1]);
        }
        $orderField = array_keys($searchParams['order']);
        $searchParams['sort'][] = array($orderField[0], $searchParams['order'][$orderField[0]]);
        if (!empty($myStore)) {
            $searchParams['store_id'] = $myStore;
        } else {
            $searchParams['store_id'] = array(1,10000);
        }
        unset($searchParams['isExt']);
        unset($searchParams['filters']);
        unset($searchParams['order']);
        
        return $searchParams;
    }

    /**
     * @brief 格式化SaleList
     *
     */
    public static function _formatSaleList($data, $query_type = '') {
        $rs = array();
        foreach($data as $k => $v) {
            $ret = BcPost::_basicFormat($v, $query_type);
            //从oracle到mysql数据库字段变化
            $ret['id'] = $v['id'] ? $v['id'] : '';
            $ret['info_id'] = 'S'.$v['id'];
            $ret['car_type'] = $v['type_id'] ? $v['type_id'] : '';
            $ret['province'] = $v['deal_province_id'] ? $v['deal_province_id'] : '';
            $ret['city'] = $v['deal_city_id'] ? $v['deal_city_id'] : '';
            $ret['district'] = $v['deal_district_id'] ? $v['deal_district_id'] : '';
            $ret['follow_user'] = $v['follow_user_id'] ? $v['follow_user_id'] : '';
            $ret['plate_province'] = $v['plate_province_id'] ? $v['plate_province_id'] : '';
            $ret['plate_city'] = $v['plate_city_id'] ? $v['plate_city_id'] : '';
            $ret['insert_user_id'] = $v['create_user_id'] ? $v['create_user_id'] : '';
            if (!empty($v['safe_force_time'])) {
                $ret['safe_time'] = $v['safe_force_time'] == -1 ? -1 : date('Y-m-d', $v['safe_force_time']);
            } else {
                $ret['safe_time'] = '';
            }
            $ret['insert_time'] = !empty($v['create_time']) ? date('Y-m-d H:i:s', $v['create_time']) : '';
//             if (!$v['model_id'] && (!$v['series_id'] || !$v['brand_id'])) {
//                 $brind_name = '';
//             } else {
//                 $brind_name = VehicleV2Interface::getModelCaption($v) ? VehicleV2Interface::getModelCaption($v) : '';
//             }
            $ret['brand_caption'] = $v['title'] ? $v['title'] : '';
            $ret['busi_insur_time'] = $v['safe_business_time'] ? date('Y-m-d', $v['safe_business_time']) : '';
            if (!empty($v['is_safe_business'])) {
                $ret['busi_insur_checked'] = $v['is_safe_business'] == 1 ? 2 : 1;
            } else {
                $ret['busi_insur_checked'] = 0;
            }
            $ret['busi_insur_price']   = !empty($v['safe_business_cash']) ? number_format($v['safe_business_cash'] / 10000, 2) : '';
            $ret['use_quality'] = $v['use_type'] ? $v['use_type'] : '';
            $ret['sale_quality'] = $v['sale_type'] ? $v['sale_type'] : '';
            //格式化price，和kilometer
            $ret['price'] = !empty($v['price']) ? number_format($v['price'] / 10000, 2) : '';
            $ret['kilometer'] = !empty($v['kilometer']) ? number_format($v['kilometer'] / 10000, 1) : '';
            $ret['status_color'] = self::getCarStatusColor($v['car_status'], $v['follow_user_id']);
            $ret['status_show'] = !empty(CarVars::$MOBILE_CAR_STATUS_CONF[$v['car_status']]) ? CarVars::$MOBILE_CAR_STATUS_CONF[$v['car_status']] : '';
            $ret['car_status'] = $v['car_status'] ? (int) $v['car_status'] : 0;
            
            if ($v['is_look_ck']) {
                $conditionDetail = array();
                if (!empty($v['condition_detail'])) {
                    $conditionDetail = unserialize($v['condition_detail']);
                    foreach ($conditionDetail as $key => $val) {
                        $conditionDetail[$key] = (int) $val;
                    }
                }
                //免责声明相关的数据
                $conditionArr = array(
                    'scratched' => !empty($v['is_frame_problem']) ? $v['is_frame_problem'] : 0,
                    'soaked' => !empty($v['is_water_problem']) ? $v['is_water_problem'] : 0,
                    'engine_fixed' => !empty($v['is_engine_problem']) ? $v['is_engine_problem'] : 0,
                    'odometer_fixed' => !empty($v['is_kilometer_problem']) ? $v['is_kilometer_problem'] : 0,
                    'scratches' => !empty($v['condition_detail']) ? $conditionDetail : array(),
                );
                $ret['condition_info'] = $conditionArr;
            }
            
            //没有改变字段，
            $ret['description'] = $v['description'] ? $v['description'] : '';
//             $ret['title'] = $brind_name ? $brind_name : '';
            $ret['brand_id']=  $v['brand_id'] ? $v['brand_id'] : '';
            $ret['series_id'] = $v['series_id'] ? $v['series_id'] : '';
            $ret['model_id'] =$v['model_id'] ? $v['model_id'] : '';
            $ret['customer_id'] = $v['customer_id'] ? $v['customer_id'] : '';
            $ret['car_color'] = $v['car_color'] ? $v['car_color'] : '';
            $ret['car_number'] = $v['car_number'] ? $v['car_number'] : '';
            $ret['ad_note'] = $v['ad_note'] ? $v['ad_note'] : '';
            $ret['transfer_num'] = $v['transfer_num'] ? $v['transfer_num'] : '';
            $ret['maintain_address'] = $v['maintain_address'] ? $v['maintain_address'] : '';
            if (!empty($v['year_check_time'])) {
                $ret['year_check_time'] = $v['year_check_time'] == -1 ? -1 : date('Y-m-d', $v['year_check_time']);
            } else {
                $ret['year_check_time'] = '';
            }
            $ret['card_time'] = !empty($v['card_time']) ? date('Y-m-d', $v['card_time']) : '';
            $ret['update_time'] = !empty($v['update_time']) ? date('Y-m-d H:i:s', $v['update_time']) : '';
            $ret['telephone'] =$ret['mobile'] ? $ret['mobile'] : '';
            $ret['status'] = $v['status'] ? $v['status'] : 0;
            $ret['sale_status'] = $v['sale_status'] ? $v['sale_status'] : 0;
            //强制质检的三张图片：人车合照图，行驶证图，车牌正面图
            $checkCarPhoto = array();
            if(!empty($v['id'])) {
                $checkCarPhoto = BcPost::getCheckCarPhoto($v['id']);
            }
            if(!empty($checkCarPhoto)) {
                $ret['image'] = Util::getFromArray('image', $checkCarPhoto,array());
                $ret['image_plate'] = Util::getFromArray('image_plate', $checkCarPhoto,array());
                $ret['advisor_status'] = Util::getFromArray('advisor_status', $checkCarPhoto,'');
                $ret['driving_status'] = Util::getFromArray('driving_status', $checkCarPhoto,'');
                $ret['identified_status'] = Util::getFromArray('identified_status', $checkCarPhoto,'');
                $ret['order_status'] = Util::getFromArray('order_status', $checkCarPhoto,'');
            }
            
            $ret['contact_user'] = $ret['contact_user'] ? $ret['contact_user'] : '';
            $ret['contact_telephone'] = $ret['contact_telephone'] ? $ret['contact_telephone'] : '';
            $ret['contact_telephone2'] = $ret['contact_telephone2'] ? $ret['contact_telephone2'] : '';
            $ret['idcard'] = $ret['idcard'] ? $ret['idcard'] : '';
            $ret['contact_telephone_addr'] = $ret['contact_telephone_addr'] ? $ret['contact_telephone_addr'] : '';
            $ret['follow_user_name'] = $ret['follow_user_name'] ? $ret['follow_user_name'] : '';
            $ret['is_draft'] = $v['status'] == -1 ? 1 : 0;
            
            if (!empty($v['brand_id'])) {
                $brandInfo = VehicleV2Interface::getBrandById(array('brand_id' => $v['brand_id']));
                $ret['brand_name'] = !empty($brandInfo['name']) ? $brandInfo['name'] : '';
            }
            
            if (!empty($v['series_id'])) {
                $seriesInfo = VehicleV2Interface::getSeriesById(array('series_id' => $v['series_id']));
                $ret['series_name'] = !empty($seriesInfo['name']) ? $seriesInfo['name'] : '';
            }
            //详情业不再允许查看卖主电话,除了自己
            $loginUser = AppServAuth::$userInfo['user'];
            if ($ret['follow_user'] != $loginUser['username']) {
                $ret['contact_telephone'] = $ret['contact_telephone2'] = '********';
            } else {
                //如果是业务员本人的话，去取加密后的内容
                if (!empty($v['id'])) {
                    include_once API_PATH . '/interface/MbsCarProtectInfoInterface.class.php';
                    $params = array(
                        'fields' => '*',
                        'cond'   => array('car_id' => $v['id']),
                    );
                    $result = MbsCarProtectInfoInterface::getRow($params);
                    $ret['contact_telephone'] = !empty($result['mobile']) ? $result['mobile'] : '';
                    $ret['contact_telephone2'] = !empty($result['mobile2']) ? $result['mobile2'] : '';
                }
            }
            $ret['is_self'] = $v['follow_user_id'] == $loginUser['username'] ? 1 : 0;

            //手机业管搜索新增返回字段
            $ret['inner_type'] = $v['inner_type'];  // 1001个人独家车源 2001门店独家车源
            $ret['hurry_sale'] = $v['hurry_sale'];  // >0 急售车源
            $ret['storage_sale'] = $v['storage_sale'];  // >0 寄售车源
            $ret['is_cooperation'] = $v['is_cooperation'];  // 1合作车源

            $rs[] = $ret;
        }
        return $rs;
    }
    
   /**
    * @desc 根据车源状态获取状态文本颜色值
    */
    public static function getCarStatusColor($carStatus, $followUser) {
        $carStatus = (int) $carStatus;
        $loginUser = AppServAuth::$userInfo['user']['username'];
        if (empty($carStatus) || empty(CarMbsVars::$CAR_STATUS_COLOR) || empty($followUser) || ($loginUser != $followUser)) {
            return '';
        }
        
        foreach (CarMbsVars::$CAR_STATUS_COLOR as $key => $val) {
            if (in_array($carStatus, $val)) {
                return $key;
            }
        }
        
        return '';
    }
}