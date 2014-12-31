<?php
/**
 * @brief  消息订阅推送手机接口
 * @test   file_path: app_serv/app/subscribe/testSubscribeSerivice.class.php
 * @author guoch
 */

require_once API_PATH . '/interface/SubscribeInterface.class.php';
require_once API_PATH . '/interface/VehicleV2Interface.class.php';
require_once API_PATH . '/interface/LocationInterface.class.php';
require_once API_PATH . '/interface/CarSaleInterface.class.php';
require_once API_PATH . '/interface/CarAttachInterface.class.php';
require_once API_PATH . '/interface/MbsCheckCarPhotoInterface.class.php';
require_once API_PATH . '/interface/MbsUserInterface.class.php';
require_once APP_SERV . '/common/helper/AppServGlobalHelper.class.php';
class SubscribeServiceApp {
    /*
     * @ brief 根据client_id
     * @ params array(
     *     'client_id' => '手机唯一标示符',
     *     'push_id'    => '消息推送id',
     *     'push_type'  => '推送类型',
     *     )
     * @ return int 1更新成功，0更新失败,-2参数不完整
     */
    public static function updateClientId($params) {
        $ret = SubscribeInterface::updateClientId($params);
        return $ret;
    }
    
    /**
     * @brief 根据关系id删除订阅关系表中数据
     * @params int id 关系id
     * @params int ids 关系id用逗号隔开
     * @return int 1（删除成功！） 0删除失败！
     */
    public static function deleteSubscribeById($params) {
        $ret = SubscribeInterface::deleteSubscribeById($params);
        return $ret;
    }
    
    /**
     * @brief 根据车源条件id，查询相关车源列表
     * @param array(
     *        condition_id 车源条件id
     *        limit        查询条数
     *        offset       车源偏移量
     *        )
     * 
     * @return 同search.getSaleList
     */
    public function getCarListByConditionId($params) {
        $carList = SubscribeInterface::getCarListByConditionId($params);
        $ret = $this->_formatCarList($carList);
        $ret['total'] = SubscribeInterface::getCarTotal($params);
        return $ret;
    }
    /**
     * @brief 根据订阅条件id,获取车源数量
     * @params int condition_id 车源条件id
     * @return int total 订阅的车源的数量
     */
    public function getCarTotalBycondtion($params) {
        $ret['total'] = SubscribeInterface::getCarTotalBycondtion($params);
        return $ret;
    }
    
    /**
     * @brief 根据手机唯一标识码，查询相关车源列表
     * @param int client_id  条件id
     * @return 同search.getSaleList
     */
    public function getCarListByClientId($params) {
        $key = 'subscribe_tag_fix_bug_' . $params['client_id'];
        include_once FRAMEWORK_PATH . '/util/redis/RedisClient.class.php';
        include_once CONF_PATH . '/cache/RedisConfig.class.php';
        $redisClient = new RedisClient(RedisConfig::$REDIS_QUEUE);
        $redis = $redisClient->getMasterRedis();
        $flag = $redis->get($key);
        if ($flag == 1) {
            $params['offset'] = 0;
            $redis->delete($key);
        }
        $carList = SubscribeInterface::getCarListByClientId($params);
        if($carList == -1) {
            throw new AppServException(AppServErrorVars::CUSTOM, -1);
        }
        $total = $carList['total'];
        unset($carList['total']);
        $ret = $this->_formatCarList($carList);
        $ret['total'] = $total;
        return $ret;
    }
    
    /**
     * @brief 增加订阅条件
     * @params array(
     *      brand_id        => 品牌id,
     *      series_id       => 车系id,
     *      model_id        => 车型id,
     *      city_id         => 城市id,
     *      high_price      => 价格范围上限,
     *      low_price       => 价格范围下限,
     *      high_kilometer  => 公里数范围上限,
     *      low_kilometer   => 公里数范围下限,
     *      high_car_age    => 上牌时间范围上限,
     *      low_car_age     => 上牌时间范围下限,
     *      client_id       => 手机唯一标示符,
     *      push_id         => 个推id,用于推送消息
     *      status          => 推送状态：0，不推送；1拉取方式；2实时推送,
     *      )
     * @return 1添加订阅条件成功；0，添加订阅条件失败
     * 
     */
    
    public function addSubscribeCondition($params) {
        $key = 'subscribe_tag_fix_bug_' . $params['client_id'];
        include_once FRAMEWORK_PATH . '/util/redis/RedisClient.class.php';
        include_once CONF_PATH . '/cache/RedisConfig.class.php';
        $redisClient = new RedisClient(RedisConfig::$REDIS_QUEUE);
        $redis = $redisClient->getMasterRedis();
        $redis->set($key, 1);
        $ret = SubscribeInterface::addSubscribeCondition($params);
        return $ret;
    }
    /**
     * 
     */
    
    /**
     * @brief 根据手机唯一标示查询订阅条件
     * 
     * @params array(
     *     'client_id' => 手机唯一标示
     *     )
     *     
     * @return array(
     *     'id'              => 'int', // 消息订阅推送条件唯一id
     *     'car_attach'      => 'varchar', // brand_id,series_id,model_id条件组合
     *     'high_price'      => 'int', // 订阅价格范围上限
     *     'low_price'       => 'int', // 订阅价格范围上限
     *     'high_car_age'  => 'int', // 订阅时间范围上限
     *     'low_car_age'   => 'int', // 订阅时间范围上限
     *     'city_id'         => 'int', // 车源所在城市id
     *     'high_kilometer'  => 'int', // 订阅里程范围上限
     *     'low_kilometer'   => 'int', // 订阅价格范围下限
     *     'create_time'     => 'int', // 订阅条件创建时间
     *     'city_name'       => 'string'//城市名称
     *     'title'           => 'string'//车源品牌车系车型名称
     *     'icon_path'       => 'string'//品牌图标url
     *     'car_age'         => 'string'//车龄
     *     'price'           => 'string'//价格
     *     'last_time'       => 'string'//更新时间
     *    )
     */
    public function getSubscribeCondition($params) {
        $ret = SubscribeInterface::getSubscribeCondition($params);
        $rs = self::_formatCondition($ret);
        return $rs;
    }
    
    /**
     * @brief 更新订阅推送条件
     * @params array(
     *      brand_id        => 品牌id,
     *      series_id       => 车系id,
     *      model_id        => 车型id,
     *      city_id         => 城市id,
     *      high_price      => 价格范围上限,
     *      low_price       => 价格范围下限,
     *      high_kilometer  => 公里数范围上限,
     *      low_kilometer   => 公里数范围下限,
     *      high_car_age    => 上牌时间范围上限,
     *      low_car_age     => 上牌时间范围下限,
     *      client_id       => 手机唯一标示,
     *      )
     * @return int id 更新成功，0更新失败
     */
    public function updateSubscribeCondition($params) {
        if(empty($params['id']) ||empty($params['client_id'])) {
            return -1;
        }
        
        $ret = SubscribeInterface::updateSubscribeCondition($params);
        return $ret;
    }
    
    /**
     * @ brief 更新手机接收状态
     * @ params array(
     *     'client_id' => '手机唯一标示符',
     *     'status'    => '要更新的状态',
     *     )
     * @ //推送状态：0，不推送；1拉取方式；2实时推送,
     * @ return int 1更新成功，0更新失败
     */
    public function updateConfigStatus ($params) {
        $ret = SubscribeInterface::updateConfigStatus($params);
        return $ret;
    }
    /*
     * @ brief 获取手机接收状态
     * @ params array(
     *     'client_id' => '手机唯一标示符',
     *     )
     * @ return array(
     *     push_time  => '推送时间'
     *     status     => '推送状态'
     *     )
     *注释：//推送状态：0，不推送；1拉取方式；2实时推送,
     */
    public function getConfig($params) {
        $ret = SubscribeInterface::getConfig($params);
        return $ret;
    }

    /**
     * @brief 取手机接收状态，是否有精品推荐 ，subscribe.getConfig和var.showRecommend的合并
     * @author 王煜<wangyu@273.cn>
     * @param : 参数说明如下表格：
     * 参数名称     | 参数类型   | 参数补充描述
     * ------------|----------|------------------------------------------------
     * client_id| string | 手机唯一标识符
     * @return array
     * 返回值名称  | 返回值类型   | 返回值补充描述
     * -----------------|----------|------------------------------------------------
     * push_time        | int    |  推送时间
     * status           | int    |  推送状态：0，不推送；1拉取方式；2实时推送,
     * show_recommend   | int    |  0不显示精品推荐,1显示精品推荐
     */
    public function getConfigV2($params) {
        if (empty($params['client_id'])) {
            throw new AppServException(AppServErrorVars::CUSTOM, 'client_id不能为空');
        }
        include_once API_PATH . '/interface/CarCommonDataInterface.class.php';
        $appData = CarCommonDataInterface::getData('WEB_CLIENT_APPS_LIST');
        $ardList = array();
        $iosList = array();
        if (!empty($appData) && is_array($appData)) {
            foreach ($appData as $app) {
                if (!empty($app['app_ard_url'])) {
                    $ardList[] = $app;
                }
                if (!empty($app['app_ios_url'])) {
                    $iosList[] = $app;
                }
            }
        }

        if ($params['_app_source'] == 3) {
            //安卓
            $show = count($ardList) > 0 ? 1 : 0;
        } else {
            //ios应用未审核通过之前不展示精品推荐
            $show = 0;
        }

        $config = $this->getConfig($params);

        return array(
            'push_time'       => $config['push_time'],
            'status'          => $config['status'],
            'show_recommend'  => $show,
        );
    }
    
    /**
     * @brief 格式化getSubscribeCondition函数
     * @param array(
     *     'id'              => 'int', // 消息订阅推送条件唯一id
     *     'car_attach'      => 'varchar', // brand_id,series_id,model_id条件组合
     *     'high_price'      => 'int', // 订阅价格范围上限
     *     'low_price'       => 'int', // 订阅价格范围上限
     *     'high_car_age'  => 'int', // 订阅时间范围上限
     *     'low_car_age'   => 'int', // 订阅时间范围上限
     *     'city_id'         => 'int', // 车源所在城市id
     *     'high_kilometer'  => 'int', // 订阅里程范围上限
     *     'low_kilometer'   => 'int', // 订阅价格范围下限
     *     'create_time'     => 'int', // 订阅条件创建时间
     * )
     * @return array(参照getSubscribeCondition的return（返回值）)
     */
    private function _formatCondition($params) {
        if(empty($params)) {
            return array();
        }
        foreach ($params as $key => $value) {
            $ret['id'] = $value['id'];
            $ret['city_id'] = $value['city_id'];
            $ret['brand_id'] = $ret['series_id'] = $ret['model_id'] = '';
            
            //根据car_attach查询车型品牌车系并拼接起来
            if($value['car_attach']) {
                $carAttach = explode(',', $value['car_attach']);
                
                if($carAttach[0]) {
                    $ret['brand_id'] = $carAttach[0];
                    $brand = VehicleV2Interface::getBrandById($ret);
                }
    
                if($carAttach[1]) {
                    $ret['series_id'] = $carAttach[1];
                }
    
                if($carAttach[2]) {
                    $ret['model_id'] = $carAttach[2];
                }
            }
            $title = VehicleV2Interface::getModelCaption(array(
                'model_id' => $ret['model_id'],
                'series_id' => $ret['series_id'],
                'brand_id' => $ret['brand_id'],
            ));
            $ret['title'] = $title ? $title : '';
            $ret['icon_path'] = $brand['icon_path'] ? $brand['icon_path'] : '';
            
            //根据城市id查询城市名称
            $ret['city_name'] = '';
            if($value['city_id']) {
                $city = LocationInterface::getCityById($value);
                $ret['city_name'] = $city['name'];
            }
             
    
            //格式化更新时间
            $ret['last_time'] = $value['create_time'];
    
            
            //格式化车龄
            $ret['car_age'] = '';
            $ret['car_age_text'] = '';
            if($value['high_car_age'] || $value['low_car_age']) {
                if(empty($value['low_car_age'])) {//当high_price为空，low_price不为空时
                    $ret['car_age_text'] = $value['high_car_age'] . '年内';
                    $ret['car_age'] = '0-' . $value['high_car_age'];
                }
                if($value['high_car_age'] == 100) {//当low_price为空，high_price不为空时
                    $ret['car_age_text'] = $value['low_car_age'] . '年以上';
                    $ret['car_age'] = $value['low_car_age'];
                }
                if($value['high_car_age'] && $value['low_car_age'] && $value['high_car_age'] != 100) {//当low_price和high_price不为空时
                    $ret['car_age_text'] = $value['low_car_age'] . '-'. $value['high_car_age'].'年';
                    $ret['car_age'] = $value['low_car_age'] . '-'. $value['high_car_age'];
                }
            }
            
            
            //格式化价格
            $ret['price'] = $ret['price_text'] = '';
            if($value['high_price'] || $value['low_price']) {
                if(empty($value['low_price'])) {//当high_price为空，low_price不为空时
                    $ret['price_text'] = number_format($value['high_price']/10000) . '万以下';
                    $ret['price'] = '0-' . $value['high_price'];
                }
                if($value['high_price'] == 99999999) {//当low_price为空，high_price不为空时
                    $ret['price_text'] = number_format($value['low_price']/10000) . '万以上';
                    $ret['price'] = $value['low_price'];
                }
                if($value['high_price'] && $value['low_price'] && $value['high_price'] != 99999999) {//当low_price和high_price不为空时
                    $ret['price_text'] = number_format($value['low_price']/10000) . '-'. number_format($value['high_price']/10000).'万';
                    $ret['price'] = $value['low_price'] . '-'. $value['high_price'];
                }
            }
            
            $rs[] = $ret;
        }
        $rs = AppServGlobalHelper::changeNull($rs);
        return $rs;
    }
    
    /**
     * @brief 格式化车源列表参数
     * @params
     * @return 
     */
    private function _formatCarList($params) {
        //根据follow_user_id获取对应的follow_user_id
        $ret['info'] = array();
        foreach ($params as $key => $value) {
            $ret['info'][$key] = $this->_fomatInfo($value);
            
            $checkInfo = $this->getCheckCarPhoto($value['id']);
            $ret['info'][$key]['driving_status'] = $checkInfo['driving_status'] ? $checkInfo['driving_status'] : 0;
            $ret['info'][$key]['advisor_status'] = $checkInfo['advisor_status'] ? $checkInfo['advisor_status'] : 0;
            $user = MbsUserInterface::getInfoByUser(array('username' => $value['follow_user_id']));
            $ret['info'][$key]['follow_user_name'] = $user['real_name'] ? $user['real_name'] : '';
            unset($ret['info'][$key]['follow_user_id']);
            //处理cover_photo
            if (strpos($value['cover_photo'], 'http://') === 0) {
                $path = str_replace('http://img.273.com.cn/', '', $value['cover_photo']);
                $ret['info'][$key]['cover_photo'] = str_replace('_120-90_6_0_0', '', $path);
            }
        }
        $ret['fix_num'] = '400-0018-273';
        return $ret;
    }
    
    
    /*
     * 过滤CarSaleInterface::search返回的info数据
    */
    private function _fomatInfo($info) {
        $info = AppServGlobalHelper::formatSaleListItem($info);
        $data = array(
                'id'          => $info['id'],
                'create_time' => $info['create_time'],
                'update_time' => $info['update_time'],
                'cover_photo' => $info['cover_photo'],
                'title'       => $info['title'],
                'kilometer'   => $info['kilometer'],
                'card_time'   => $info['card_time'],
                'price'       => $info['price'],
                'follow_user_id' => $info['follow_user_id'],
                'ext_phone'   => $info['ext_phone'],
                'seller_name' => $info['seller_name'],
                'telephone'   => $info['telephone'],
                'ip'          => $info['ip'],
                'tags'        => !empty($info['tags']) ? $info['tags'] : array(),
                'tag_title'   => !empty($info['tag_title']) ? $info['tag_title'] : '',
        );
        return $data;
    }
    
    /*
     * @ brief 获取车源质检信息
    *
    * @ params carId车源id
    *
    * @ return int order_status,driving_status,identified_status 1，有，0没有
    */
    public function getCheckCarPhoto($carId) {
        $ret['image'] = array();
        $ret['image_plate'] = array();
        $fields = 'id,file_path,sort_order,is_cover,object_type';
        $filters[] = array('object_id' ,'=' ,$carId);
        $filters[] = array('object_type', 'in', array(1, 97, 98, 99));
        $filters[] = array('status', '=', 1);
        $imageList = CarAttachInterface::getImageInfoByPostIds($fields,$filters);
        $carPhotos = MbsCheckCarPhotoInterface::getCheckInfoByCarId(array('car_id' => $carId));
        $status = array();
        if (!empty($imageList)) {
            foreach ($imageList as $key => $images) {
                if ($images['object_type'] == 1) {
                    $imageList[$key]['cover'] =  $imageList[$key]['is_cover'];
                    $imageList[$key]['type'] =  $imageList[$key]['object_type'];
                    $imageList[$key]['index'] =  $imageList[$key]['sort_order'];
                    unset($imageList[$key]['sort_order']);
                    unset($imageList[$key]['object_type']);
                    unset($imageList[$key]['is_cover']);
                    $ret['image'][] = $imageList[$key];
                    unset($imageList[$key]);
                }else {
                    $h = '';
                    foreach($carPhotos as $m => $photo) {
                        if($photo['photo_type'] == (100 -$images['object_type'])) {
                            $h = $m;
                        }
                    }
                    switch(intval($carPhotos[$h]['status'])) {
                        case 0 : $imageList[$key]['image_status_name'] = '待审核';
                        break;
                        case 1 : $imageList[$key]['image_status_name'] = '待审核';
                        break;
                        case 2 : $imageList[$key]['image_status_name'] = '已审核';
                        break;
                        default: $imageList[$key]['image_status_name'] = '审核不通过';
                        break;
                    }
                    $imageList[$key]['remark'] = $carPhotos[$h]['remark'] ? $carPhotos[$h]['remark'] : '';
                    $status[$images['object_type']]['status'] = $carPhotos[$h]['status'];
                    $imageList[$key]['check_time'] = $carPhotos[$h]['check_time'] ? $carPhotos[$h]['check_time'] : '' ;
                    $imageList[$key]['cover'] =  $imageList[$key]['is_cover'];
                    $imageList[$key]['type'] =  $imageList[$key]['object_type'];
                    $imageList[$key]['index'] =  $imageList[$key]['sort_order'];
                    unset($imageList[$key]['sort_order']);
                    unset($imageList[$key]['object_type']);
                    unset($imageList[$key]['is_cover']);
                    //                     $ret['image_plate'][] = $imageList[$key];
                }
            }
            $ret['order_status'] = $ret['identified_status'] = $ret['driving_status'] = $ret['advisor_status'] = '';
            if($status[97]['status'] == 2 || $status[98]['status'] == 2 || $status[99]['status'] == 2) {
                $ret['order_status'] = 1;
            }
            if($status['98']['status'] == 2) {
                $ret['driving_status'] = 1;
                $ret['identified_status'] = 1;
            }
            if($status[97]['status'] == 2 || ($status[99]['status'] ==2 && $status[98]['status']) == 2) {
                $ret['advisor_status'] = 1;
                $ret['identified_status'] = 1;
            }
            return $ret;
        }
    }
}
