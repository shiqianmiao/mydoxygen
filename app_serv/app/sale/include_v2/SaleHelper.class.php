<?php
/**==================================================================================================================
 * @desc 卖车的辅助工具类
 * @since - 2014-9-17
 * @author miaoshiqian
 * ==================================================================================================================
 */

require_once APP_SERV . '/app/sale/include/config.php';
require_once COM_PATH . '/car/CarVars.class.php';
require_once COM_PATH . '/car/CarStatusVars.class.php';
require_once COM_PATH . '/car/CarMbsVars.class.php';
require_once API_PATH . '/interface/MbsDeptInterface.class.php';
require_once API_PATH . '/interface/MbsUserInterface.class.php';
require_once API_PATH . '/interface/LocationInterface.class.php';
require_once API_PATH . '/interface/LocationTmpInterface.class.php';
require_once FRAMEWORK_PATH . '/util/common/Util.class.php';

class SaleHelper {
    
    public static $saleTags = array(
        "0" => "http://sta.273.com.cn/app/mbs/img/sale_tag1.png", //设置优质车源的标签
        "1" => "http://sta.273.com.cn/app/mbs/img/sale_tag2.png", //设置合并车源的标签
        "2" => "http://sta.273.com.cn/app/mbs/img/sale_tag3.png", //设置全国合作的标签
        "3" => "http://sta.273.com.cn/app/mbs/img/sale_tag4.png", //设置为急售车源的标签
        "4" => "http://sta.273.com.cn/app/mbs/img/sale_tag5.png", //设置为寄售车源的标签
        "5" => "http://sta.273.com.cn/app/mbs/img/sale_tag6.png", //提交了车况包检测报告的车源标签
    );
    
    /**
     * @desc 图片格式话方法
     * @param $path 图片的file_path
     * @param $width 需要的图片的宽度
     * @param $height 需要的图片高度
     * @return 返回格式化后的图片url
     */
    public static function formatImgUrl($path, $width = 120, $height = 90) {
        if (empty($path)) {
            return '';
        }
        $path = str_replace('273.cn', '273.com.cn', $path);
        $imgDomain = IMG_DOMAIN;
        if (strpos($path, 'http://') === 0) {
            $imgDomain = '';
        }
        
        $mode = '.jpg';
        if (strpos($path, '.png')) {
            $mode = '.png';
        }
        if (strpos($path, '.jpeg')) {
            $mode = '.jpeg';
        }
        $path = str_replace('_min'. $mode, $mode, $path);
        return $imgDomain . str_replace($mode, '_' .$width . '-' . $height . '_6_0_0' . $mode, $path);
    }
    
    /**==============================================================================================================
     * @desc 卖车信息格式化显示，主要是将数据库的数据格式化为客户端需要的格式或者内容
     * @param $postInfo 车源的信息 Array
     * @return array()
     * ==============================================================================================================
     */
    public static function formatSale($postInfo) {
        $result = array();
        if (is_array($postInfo) && !empty($postInfo)) {
            $p = &$postInfo;
            $result = array(
                'id'                   => Util::getFromArray('id', $p, ''),
                'car_type'             => Util::getFromArray('type_id', $p, ''),
                'brand_id'             => Util::getFromArray('brand_id', $p, ''),
                'series_id'            => Util::getFromArray('series_id', $p, ''),
                'model_id'             => Util::getFromArray('model_id', $p, ''),
                'province'             => Util::getFromArray('deal_province_id', $p, ''),
                'city'                 => Util::getFromArray('deal_city_id', $p, ''),
                'district'             => Util::getFromArray('deal_district_id', $p, ''),
                'follow_user'          => Util::getFromArray('follow_user_id', $p, ''),
                'plate_province'       => Util::getFromArray('plate_province_id', $p, ''),
                'plate_city'           => Util::getFromArray('plate_city_id', $p, ''),
                'insert_user_id'       => Util::getFromArray('create_user_id', $p, ''),
                'brand_caption'        => Util::getFromArray('title', $p, ''),
                'use_quality'          => Util::getFromArray('use_type', $p, ''),
                'sale_quality'         => Util::getFromArray('sale_type', $p, ''),
                'car_status'           => Util::getFromArray('car_status', $p, 0),
                'status'               => Util::getFromArray('status', $p, 0),
                'sale_status'          => Util::getFromArray('sale_status', $p, 0),
                'description'          => Util::getFromArray('description', $p, ''),
                'customer_id'          => Util::getFromArray('customer_id', $p, ''),
                'car_color'            => Util::getFromArray('car_color', $p, ''),
                'car_number'           => Util::getFromArray('car_number', $p, ''),
                'ad_note'              => Util::getFromArray('ad_note', $p, ''),
                'transfer_num'         => Util::getFromArray('transfer_num', $p, ''),
                'maintain_address'     => Util::getFromArray('maintain_address', $p, ''),
                'displacement'         => Util::getFromArray('displacement', $p, ''),
                'cs_note'              => Util::getFromArray('inside_note', $p, ''),
                'is_look_ck'           => Util::getFromArray('is_look_ck', $p, ''),
                'condition_detail'     => Util::getFromArray('condition_detail', $p, ''),
                'is_frame_problem'     => Util::getFromArray('is_frame_problem', $p, 0),
                'is_water_problem'     => Util::getFromArray('is_water_problem', $p, 0),
                'is_engine_problem'    => Util::getFromArray('is_engine_problem', $p, 0),
                'is_kilometer_problem' => Util::getFromArray('is_kilometer_problem', $p, 0),
                'hurry_sale'            => Util::getFromArray('hurry_sale', $p, ''),
                'storage_sale'          => Util::getFromArray('storage_sale', $p, ''),
                'is_cooperation'        => Util::getFromArray('is_cooperation', $p, ''),
                'inner_type'            => Util::getFromArray('inner_type', $p, ''),
                'use_type'              => Util::getFromArray('use_type', $p, ''),
                'car_color'             => Util::getFromArray('car_color', $p, ''),
                'is_draft'             => $p['status'] == -1 ? 1 : 0,
                'emission_standard'    => CarVars::$EMISSION_CONF[$p['emission_standards']]
                                          ? CarVars::$EMISSION_CONF[$p['emission_standards']] : '',
                'gearbox_type'         => CarVars::$GEARBOX_TYPE_CONF[$p['gearbox_type']]
                                          ? CarVars::$GEARBOX_TYPE_CONF[$p['gearbox_type']] : '',
                'price'                => !empty($p['price']) ? number_format($p['price'] / 10000, 2) : '',
                'kilometer'            => !empty($p['kilometer']) ? number_format($p['kilometer'] / 10000, 1) : '',
                'photo'                => !empty($p['cover_photo']) ? self::formatImgUrl($p['cover_photo']) : '',
                'status_color'         => self::getCarStatusColor($p['car_status'], $p['follow_user_id']),
                'status_show'          => !empty($p['car_status']) ? CarVars::$MOBILE_CAR_STATUS_CONF[$p['car_status']] : '',
                'sale_status_show'     => $p['sale_status'] == 1 ? '已售出' : '未售出',
                'insert_time'          => !empty($p['create_time']) ? date('Y-m-d H:i', $p['create_time']) : '',
                'busi_insur_time'      => !empty($p['safe_business_time']) ? date('Y-m-d', $p['safe_business_time']) : '',
                'card_time'            => !empty($p['card_time']) ? date('Y-m-d', $p['card_time']) : '',
                'update_time'          => !empty($p['update_time']) ? date('Y-m-d H:i', $p['update_time']) : '',
                'refresh_time'         => !empty($p['update_time']) ? date('Y-m-d H:i', $p['update_time']) : '',
                'safe_time'            => !empty($p['safe_force_time']) ? ($p['safe_force_time'] == -1 ? -1 
                                          : date('Y-m-d', $p['safe_force_time'])) : '',
                'year_check_time'      => !empty($p['year_check_time']) ? ($p['year_check_time'] == -1 ? -1 
                                          : date('Y-m-d', $p['year_check_time'])) : '',
                 //由于历史原因，手机这边这个字段的值与pc相反，因此，特殊处理
                'busi_insur_checked'   => !empty($p['is_safe_business']) ? ($p['is_safe_business'] == 1 ? 2 : 1) : 0,
                'busi_insur_price'     => !empty($p['safe_business_cash']) ? number_format($p['safe_business_cash'] / 10000, 2) : '',
            );
        }

        return $result;
    }
    
    /**
     * @desc 格式话卖车信息的格式,在formatSale的基础上面加上一些扩展
     * @param $postInfo 一维数组，车源信息
     * @param $isReview 是否是审核车源的时候获取详情,这种情况下有些特别处理
     * @return  array(array()) 一维数组
     * @attention 获取卖车详情页等信息通常用此方法
     */
    public static function formatSaleInfo($postInfo, $isReview = false) {
        if (empty($postInfo) || !is_array($postInfo)) {
            return array();
        }
        $result = self::formatSale($postInfo);
        //获取车源图片
        $result['image'] = $result['id'] ? self::getCarImagesById($result['id']) : array();
        
        //获取卖主信息
        $customerInfo = MbsCustomerInterface::getInfoForId(array('id' => $result['customer_id']));
        $result['contact_user'] = Util::getFromArray('real_name', $customerInfo, '');
        $loginUser = AppServAuth::$userInfo['user'];
        if ($result['follow_user'] != $loginUser['username'] && !$isReview) {
            $result['contact_telephone'] = $result['contact_telephone2'] = '********';
        } else if (!empty($result['id'])) {//如果是业务员本人的话，去取加密后的内容
            include_once API_PATH . '/interface/MbsCarProtectInfoInterface.class.php';
            $params = array(
                'fields' => '*',
                'cond'   => array('car_id' => $result['id']),
            );
            $r = MbsCarProtectInfoInterface::getRow($params);
            $result['contact_telephone']  = Util::getFromArray('mobile', $r, '');
            $result['contact_telephone2'] = Util::getFromArray('mobile2', $r, '');
        }
        
        //获取跟单人的信息
        if (!empty($result['follow_user'])) {
            $fUser = MbsUserInterface::getInfoByUser(array('username' => $result['follow_user']));
        }
        $result['follow_user_name'] = Util::getFromArray('real_name', $fUser, '');
        $result['telephone'] = Util::getFromArray('mobile', $fUser, '');
        //获取dept_name
        if (!empty($fUser['dept_id'])) {
            $dept = MbsDeptInterface::getDeptInfoByDeptId(array('id' => $fUser['dept_id']));
        }
        $result['dept_name'] = Util::getFromArray('dept_name', $dept, '');
        
        //上牌地址
        $proInfo  = LocationTmpInterface::getProvinceById(array('province_id' => $result['plate_province']));
        $cityInfo = LocationTmpInterface::getCityById(array('city_id' => $result['plate_city']));
        $result['plate_location'] = $proInfo['name'] . ' ' . $cityInfo['name'];
        
        //车辆所在地
        $proInfo  = LocationTmpInterface::getProvinceById(array('province_id' => $result['province']));
        $cityInfo = LocationTmpInterface::getCityById(array('city_id' => $result['city']));
        $districtInfo = LocationTmpInterface::getDistrictById(array('district_id' => $result['district']));
        $result['deal_location'] = $proInfo['name'] . ' ' . $cityInfo['name'] . ' ' . $districtInfo['name'];;

        //品牌车型的名称获取
        include_once API_PATH . '/interface/VehicleV2Interface.class.php';
        if (!empty($result['brand_id'])) {
            $brandInfo = VehicleV2Interface::getBrandById(array('brand_id' => $result['brand_id']));
            $result['brand_name'] = !empty($brandInfo['name']) ? $brandInfo['name'] : '';
        }
        if (!empty($result['series_id'])) {
            $seriesInfo = VehicleV2Interface::getSeriesById(array('series_id' => $result['series_id']));
            $result['series_name'] = !empty($seriesInfo['name']) ? $seriesInfo['name'] : '';
        }
        
        //如果是质检审核不通过的话，需要下发质检内容
        $result['status_details'] = self::getQualityInfo($result['id']);
        
        if ($result['follow_user'] == $loginUser['username']) {
            //如果是本人的话，获取车源状态下的操作详情,其中pop_operators是客户端需要弹出才显示操作入口的操作
            list($result['operators'], $result['pop_operators']) = self::getCarOpDetail($result['car_status'], $loginUser);
        } else {
            $result['operators'] = CarMbsVars::$SALE_OTHER_OP;
        }
        //车源电话量
        include_once API_PATH . '/interface/mbs/MbsCarSaleInterface.class.php';
        $result['call_num'] = MbsCarSaleInterface::getPhoneCountBySaleId(array('sale_id' => $result['id']));
        
        //回访量
        include_once API_PATH . '/interface/mbs/MbsVisitInterface.class.php';
        $result['callback_num'] = MbsVisitInterface::getCountByCarId(array('car_id' => $result['id']));
        
        //带看量
        include_once API_PATH . '/interface/mbs/CarLookInterface.class.php';
        $result['takelook_num'] = CarLookInterface::getCountByFilters(array('filters'=>array(array('car_id', '=', $result['id']))));
        
        //浏览量
        $result['pageview_num'] = MbsCarSaleInterface::getLocalPV(array('carid' => $result['id']));
        
        //车源标签图片数组
        if (!empty($postInfo['condition_id'])) { //有提交车况包检测报告
            $result['tags'][] = self::$saleTags['5'];
        }
        if ($postInfo['hurry_sale'] == 1) { //是急售车源
            $result['tags'][] = self::$saleTags['3'];
        }
        if ($postInfo['storage_sale'] == 1) { //是寄售车源
            $result['tags'][] = self::$saleTags['4'];
        }
        if ($postInfo['is_cooperation'] == 1) { //是全国合作车源
            $result['tags'][] = self::$saleTags['2'];
        }
        if ($postInfo['is_quality_car'] == 1) { //是优质车源
            $result['tags'][] = self::$saleTags['0'];
        }
        if ($postInfo['is_repeat'] == 1) { //是合并车源
            $result['tags'][] = self::$saleTags['1'];
        }

        $result['hurry_sale'] = $postInfo['hurry_sale'];
        $result['storage_sale'] = $postInfo['storage_sale'];
        $result['is_cooperation'] = $postInfo['is_cooperation'];
        $result['inner_type'] = $postInfo['inner_type'];

        //使用性质?1非营运,2营运,3营转非,4租赁车,5特种车,6教练车
        $result['use_type'] = CarVars::$USE_TYPE[$postInfo['use_type']];
        $result['car_color'] = CarVars::$CAR_COLOR[$postInfo['car_color']];

        //获取车况承诺数据
        if ($result['is_look_ck']) {
            $conditionDetail = array();
            if (!empty($result['condition_detail'])) {
                $conditionDetail = unserialize($result['condition_detail']);
                if ($conditionDetail) {
                    foreach ($conditionDetail as $key => $val) {
                        $conditionDetail[$key] = (int) $val;
                    }
                } else {
                    $conditionDetail = array();
                }
            }
            //免责声明相关的数据
            $conditionArr = array(
                'scratched'      => !empty($result['is_frame_problem']) ? $result['is_frame_problem'] : 0,
                'soaked'         => !empty($result['is_water_problem']) ? $result['is_water_problem'] : 0,
                'engine_fixed'   => !empty($result['is_engine_problem']) ? $result['is_engine_problem'] : 0,
                'odometer_fixed' => !empty($result['is_kilometer_problem']) ? $result['is_kilometer_problem'] : 0,
                'scratches'      => !empty($result['condition_detail']) ? $conditionDetail : array(),
            );
            $result['condition_info'] = $conditionArr;
        }
        return $result;
    }
    
    /**
     * @desc 格式话卖车列表的格式,在formatSale的基础上面加上一些扩展
     * @param $postList 二维数组，车源列表
     * @return  array(array()) 二维数组
     */
    public static function formatSaleList($postList) {
        if (empty($postList) || !is_array($postList)) {
            return array();
        }
        
        $result = array();
        foreach ($postList as $p) {
            $ret = array(
                'id'             => Util::getFromArray('id', $p, ''),
                'status'         => Util::getFromArray('car_status', $p, 0), //客户端要求status来表示car_status
                'photo'          => !empty($p['cover_photo']) ? self::formatImgUrl($p['cover_photo']) : '',
                'brand_caption'  => Util::getFromArray('title', $p, ''),
                'price'          => !empty($p['price']) ? number_format($p['price'] / 10000, 2) : '',
                'card_time'      => !empty($p['card_time']) ? date('Y-m-d', $p['card_time']) : '',
                'status_show'    => !empty($p['car_status']) ? CarVars::$MOBILE_CAR_STATUS_CONF[$p['car_status']] : '',
                'update_time'    => Util::getFromArray('update_time', $p, ''),
                'insert_time'    => Util::getFromArray('create_time', $p, ''),
                'is_draft'       => $p['status'] == -1 ? 1 : 0,
                'status_color'   => self::getCarStatusColor($p['car_status'], $p['follow_user_id']),
                'is_refresh'     => ($p['car_status'] == CarStatusVars::ON_LINE || $p['car_status'] == CarStatusVars::STOP_CHECK_UNPASS) ? 1 : 0,
                'is_self'        => $p['follow_user_id'] == AppServAuth::$userInfo['user']['username'] ? 1 : 0,
                'is_local_draft' => 0, //客户端本地草稿标识，注意: 不是服务端草稿
            );
            
            //获取车源的卖主电话
            include_once API_PATH . '/interface/MbsCarProtectInfoInterface.class.php';
            $params = array(
                'fields' => '*',
                'cond'   => array('car_id' => $p['id']),
            );
            $r = MbsCarProtectInfoInterface::getRow($params);
            $ret['contact_telephone']  = Util::getFromArray('mobile', $r, '');
            
            if (!empty($ret)) {
                $result[] = $ret;
            }
        }
        
        return $result;
    }
    
    /**
     * @desc 根据车源状态获取车源操作以及各个操作的具体属性
     * @param $carStatus 车源的详细状态
     * @return array($op, $popOp) $op:车源操作 $popOp：需要客户端弹出才显示的操作
     */
    public static function getCarOpDetail($carStatus, $userInfo) {
        $op = $popOp = array();
        if (empty($carStatus) || empty(CarMbsVars::$CAR_STATUS_MOBILE_OP[$carStatus])) {
            return array($op, $popOp);
        }
        foreach (CarMbsVars::$CAR_STATUS_MOBILE_OP[$carStatus] as $o) {
            $opDetail = isset(CarMbsVars::$SALE_MOBILE_OP_ATTR[$o]) ? CarMbsVars::$SALE_MOBILE_OP_ATTR[$o] : array();
            if (empty($opDetail) || (isset($opDetail['role_id']) && !in_array($userInfo['role_id'], $opDetail['role_id']))) {
                continue;
            }
            $opAttr = CarMbsVars::$SALE_MOBILE_OP_ATTR[$o];
            if ($opAttr['is_pop'] && $carStatus != CarStatusVars::DRAFT) {
                $popOp[] = $opAttr;
            } else {
                $op[] = $opAttr;
            }
        }
        return array($op, $popOp);
    }
    
    /**==============================================================================================================
     * @desc 获取车源的图片,根据车源的id
     * @param $carId 车源编号
     * @return array()
     * ==============================================================================================================
     */
    public static function getCarImagesById($carId) {
        if (empty($carId) || !is_numeric($carId)) {
            return array();
        }
        
        include_once API_PATH . '/interface/CarAttachInterface.class.php';
        $fields = 'id,file_path,sort_order,is_cover,object_type';
        $filters = array(
            array('object_id' ,'=' ,$carId),
            array('object_type', '=', 1),
            array('status', '=', 1)
        );
        $imageList = CarAttachInterface::getImageInfoByPostIds($fields, $filters);
        $result = array();
        if (!empty($imageList) && is_array($imageList)) {
            foreach ($imageList as $img) {
                $result[] = array(
                    'file_path' => $img['file_path'],
                    'cover'     => $img['is_cover'],
                    'url'       => self::formatImgUrl($img['file_path']),
                    'type'      => $img['object_type'],
                    'index'     => $img['sort_order'],
                );
            }
            unset($imageList);
        }
        return $result;
    }
    
    /**==============================================================================================================
     * @desc 获取车源质检的信息,根据车源的id,此处只获取质检不通过的信息
     * @param $carId 车源编号
     * @return array()
     * ==============================================================================================================
     */
    public static function getQualityInfo($carId) {
        if (empty($carId) || !is_numeric($carId)) {
            return array();
        }
        include_once API_PATH . '/interface/MbsCheckCarPhotoInterface.class.php';
        $carPhotoInfo = array();
        $checkCarPhotoData = MbsCheckCarPhotoInterface::getPhotoByCarId(array('car_id' => $carId));
        if (!empty($checkCarPhotoData) && is_array($checkCarPhotoData)) {
            foreach ($checkCarPhotoData as $p) {
                //只获取质检不通过的信息
                $photoText = $p['photo_type'] == 1 ? '车牌正面图' : ($p['photo_type'] == 2 ? '行驶证图' : '人车合照图');
                if ($p['status'] > 2) {
                    $carPhotoInfo[] = array(
                        'type' => 1, //无含义，只是客户端显示X图标用
                        'text' => '该车源' . $photoText . '审核未通过，原因：' . $p['remark'] . '!',
                    );
                }
            }
            unset($checkCarPhotoData);
        }
        return $carPhotoInfo;
    }
    
    /**
    * @desc 根据车源状态获取状态文本颜色值
    * @param $carStatus 车源详细状态car_status
    * @param $followUser 跟单人
    * @return string
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
    
    /**
     * @brief 通过电话号码（固话+手机号）获取相应的号码归属地,参考：mbs_v3/phone/app/MultiShopRecordPage.class.php
     * @author 缪石乾
     * @param $mobile 手机或这固话的号码
     * @return string [福建-福州]，这个格式的字符串
     */
    public static function mobileToCity ($tel) {
        include_once FRAMEWORK_PATH . '/util/cache/CacheNamespace.class.php';
        include_once CONF_PATH . '/cache/MemcacheConfig.class.php';
        include_once API_PATH . '/interface/MobileToCityInterface.class.php';
        $memcacheHandle = CacheNamespace::createCache(CacheNamespace::MODE_MEMCACHE, MemcacheConfig::$GROUP_DEFAULT);
        $rs = '';
        
        if (is_numeric($tel) && strlen($tel) == 11 && substr($tel, 0, 1) != '0') {// 手机
            $memcacheKey = 'phone_to_cityi_v1_' . $tel;
            $zerotel     = $memcacheHandle->read($memcacheKey);
            if ($zerotel == false) {
                $phones = MobileToCityInterface::mtc(array('tel' => $tel));
                $rs = '[未知]';
                if ($phones) {
                    $province = $phones[1];
                    $cityname = $phones[4];
                    if ($province != $cityname) {
                       $rs = "[$province-$cityname]";
                    } else {
                        $rs = "[$cityname]";
                    }
                    $memcacheHandle->write($memcacheKey, $rs, 36000);
                }
            } else {
                $rs = "$zerotel";
            }
        } elseif (is_numeric($tel) && substr($tel, 0, 1) != '0') {
            $rs = '[福建-福州]';
        } elseif (is_numeric($tel) && substr($tel, 0, 1) == '0') {//电话
            $params['domain'] = substr($tel, 0, 4);
            $city = LocationTmpInterface::getLocationByDomain($params);
            if(!$city) {
                $params['domain'] = substr($tel, 0, 3);
                $city = LocationTmpInterface::getLocationByDomain($params);
            }
            if (!$city) {
                $rs = '[未知]';
            } else {
                $params['province_id'] = $city['parent_id'];
                $province = LocationTmpInterface::getProvinceById($params);
                $rs = '['.$province['name'].'-'.$city['name'].']';
            }
        }
        $rs = trim($rs);
        $rs = ltrim($rs, '[');
        $rs = rtrim($rs, ']');
        return $rs;
    }
    
    /**
     *@brief    格式化mySaleList参数，符合接口参数格式
     *@author 缪石乾
     *@date 2014-10-10
     *@attention 没有重构过的，完全是3版本的
     *@todo 待重构
     */
    public static function getSearchParams($params) {
        $ret = array();
        $keyword = $params['keyword'];
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
            } elseif (preg_match('/^[\x{4e00}-\x{9fa5}]{1}[A-Z]{1}[A-Z0-9]{4}[A-Z0-9|\x{4e00}-\x{9fa5}]{1}$/u', trim(urldecode($keyword)))) {
                $ret['filters'][] = array('car_number', '=', trim(urldecode($keyword)));
            } else if (is_numeric($keyword) && strlen($keyword) == 6) {
                $ret['filters'][] = array('ext_phone', '=', $keyword);
            } else if (is_numeric($keyword) && ($keyword > 10000) && strlen($keyword) < 10) {
                $ret['filters'][] = array('id', '=', (int)$keyword);
            } else {//搜索车型品牌
                if ($params['brand_id']) {      //品牌关键词和筛选品牌都存在时，以关键词为准
                    unset($params['brand_id']);
                    unset($params['series_id']);
                }
                $ret['filters'] = array(array('title', 'like', '%'.trim(urldecode($keyword)).'%'),);
            }
        }
        
        $ret['filters'][] = array('follow_user_id', '=', AppServAuth::$userInfo['user']['username']);
        $ret['isExt'] = true;
        //默认参数
        $limit = $params['limit'] ? $params['limit'] : 10;
        $ret['limit'] = $limit;
        //客户端把分页改为用上传更新时间的方式来分页
        if (!empty($params['start_insert_time'])) {
            $ret['filters'][] = array('create_time', '<', $params['start_insert_time']);
        }
        $ret['order'] = array('create_time' => 'desc');
        if (!empty($params['status'])) {
            if ($params['status'] == 7010) {//已成交
                $ret['filters'][] = array('sale_status', '=', 1);
            } else {
                $ret['filters'][] = array('car_status', '=', $params['status']);
            }
        }
        
        switch ((int) $params['sale_status']) {
            case 1: 
                $ret['filters'][] = array('sale_status', '=', 0);
                break;
            case 2: 
                $ret['filters'][] = array('sale_status', '=', 1);
                $ret['filters'][] = array('status', '=', 1);
                break;
            default:
                break;
        }
        
        //按价格筛选
        if (!empty($params['price'])) {
            $priceArray = explode('-', $params['price']);
            if (isset($priceArray[0])) {
                $ret['filters'][] = array('price', '>=', $priceArray[0]);
                if (isset($priceArray[1])) {
                    $ret['filters'][] = array('price', '<=', $priceArray[1]);
                }
            }
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
        
        //按车的颜色筛选
        if (intval($params['color'])) {
            $ret['filters'][] = array('car_color', '=', $params['color']);
        }
        
        //变速器类型删选
        if(!empty($params['gear_type'])){
            $ret['filters'][] = array('gearbox_type', '=', $params['gear_type']);
        }
        
        //只获取本站车源
        $ret['filters'][] = array('store_id', '>=', 1);
        
        //委托车源来源类型的筛选
        if(!empty($params['source_type'])){
            $ret['filters'][] = array('source_type', '=', $params['source_type']);
        }
        return $ret;
    }
    
    /**
     * @desc 该car_status的车源是否在car_sale_bak表中
     * @param $carStatus 车源的详细状态，即：car_status
     * @return bool
     * @todo 待重构
     */
    public static function isSearchBak($carStatus) {
        if (empty($carStatus)) {
            return false;
        }
        $carStatus = (int) $carStatus;
        if (in_array($carStatus, array(4030,2021,2022,5020,5021,5022,6010,6020,7010,7020,7030,7040))) {
            return true;
        }
        
        return false;
    }
    
    /**
     * @brief 客户端本地草稿，按照时间混排到列表里面。对比的是列表的update_time desc。
     * @author 缪石乾
     * @date 2014-10-23
     * @param $post 帖子列表
     * @param $draftTime 客户端本地草稿时间列表
     */
    public static function sortLocalDraft($post, $draftTime, $startTime = false) {
        if (empty($draftTime) || empty($post) || !is_array($post)) {
            return $post;
        }
        rsort($draftTime);
        //帖子的数量
        $n = 10;
        $cn = count($post);
        if ($draftTime['0'] <= $post[$cn-1]['insert_time']) {
            //如果最后面的那条车源的时间都大于本地草稿最大的那条车的话，无须插入本地草稿，直接返回
            return $post;
        }
        
        $result = array();
        $draftKey = 0;
        $postKey  = 0;
        $dealCn   = 1;
        foreach ($post as $k => $p) {
            do {
                //处理混排
                if (!isset($draftTime[$draftKey]) || ($p['insert_time'] >= $draftTime[$draftKey]) || (!empty($startTime) && $startTime < $draftTime[$draftKey])) {
                    $result[] = $post[$postKey];
                    $postKey++;
                } else {
                    $result[] = array('insert_time' => $draftTime[$draftKey], 'is_local_draft' => 1);
                    $draftKey++;
                }
                $dealCn++;
            } while ($dealCn <= $n && $postKey == $k);
        }
        
        return $result;
    }
}