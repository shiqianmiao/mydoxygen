<?php
require_once API_PATH . '/interface/PriceEvaluateInterface.class.php';
require_once API_PATH . '/interface/LocationInterface.class.php';
require_once API_PATH . '/interface/CarBrandOutQczjInterface.class.php';
require_once API_PATH . '/interface/mbs/SiteCityDomainInterface.class.php';
require_once API_PATH . '/interface/VehicleV2Interface.class.php';

/**
*
* 车价评估API
* @author    WangYu <wangyu@273.cn>
* @since     2014-05-20
* @copyright Copyright (c) 2003-2014 273 Inc. (http://www.273.cn)
*
*/
class PriceevaluateServiceApp {

    //完整的参数，province和city可以是对应的ID也可以是具体中文名称
    private $_keys = array('model_id', 'year', 'month', 'province', 'city', 'kilometer');

    //评估返回信息约束，默认只返回评估价格
    private $_info = array('price_c');

    /**
     * 主站app接口
     * @param $params
     * @return mixed
     * @throws AppServException
     */
    public function priceEvaluate($params) {
        if(empty($params['model_id'])||empty($params['year'])||empty($params['month'])||(empty($params['city']) && empty($params['province']))||empty($params['kilometer'])){
            throw new AppServException(AppServErrorVars::CUSTOM, '参数不完整');
        }
        $params['kilometer'] *= 10000;
        $cityInfo = LocationInterface::getCityById(array(
            'city_id'   => $params['city'],
        ));
        $params['province'] = $cityInfo['province_id'];
        $ret = PriceEvaluateInterface::evaluateV2($params);
        if ($ret === false) {
            throw new AppServException(AppServErrorVars::CUSTOM, '参数不完整，无法评估');
        } else if (empty($ret)) {
            throw new AppServException(AppServErrorVars::CUSTOM, '该车属于冷门车型暂时无法评估，您可以：');
        }
        $rs['head'] = $ret['brand_name'].$ret['series_name'].$ret['model_year'].$ret['sale_name'];
        $rs['type'] = $ret['type'] ? $ret['type'] : '';
        $rs['price'] = $ret['price'] ? $ret['price'] : 0;
        $rs['price_c'] = $ret['price_c'] ? $ret['price_c'] : 0;
        $rs['high_price'] = $ret['max_price_c'] ? $ret['max_price_c'] : 0;
        $rs['low_price'] = $ret['min_price_c'] ? $ret['min_price_c'] : 0;
        $rs['kilometer'] = $ret['kilometer'] ? $ret['kilometer'] : 0;
        if($rs['price'] < 0 || $rs['kilometer']< 0) {
            throw new AppServException(AppServErrorVars::CUSTOM, '建议您调整车辆里程或上牌时间至正常范围,您可以:');
        }
        $modeInfo = VehicleV2Interface::getModelById(array(
            'model_id'  => $params['model_id'],
        ));
        $rs['factory_price_title'] = $modeInfo['product_status'] === '在产' ? '新车价' : '厂商指导价';
        $rs['factory_price'] = (float) $modeInfo['guide_price'];
        return $rs;
    }

    /**
     * 获取车价评估信息，同时进行历史记录，对外接口调用此方法
     * @param  : 参数说明如下表格
     * 参数名称     | 参数类型  | 参数补充描述
     * ----------- |-----------|------------------------------------------------
     * model_id     | int    | 车型id
     * year         | int    | 上牌年份
     * month        | int    | 上牌月份
     * province     | int    | 交易省份
     * kilometer    | int    | 行驶里程
     * @return array
     * 返回值名称       | 返回值类型  | 返回值补充描述
     * -----------------|-------------|----------------------------------------
     * type           | int    |评估类型
     * head           | string | 头部返回语句（如：全国范围内奥迪A42008款1.8T 无级 豪华型）
     * price          | float  | (估价)车源报价
     * price_c        | float  |(估价)成交价格
     * kilometer      | int    | 行驶里程
     * key            | int    |价格评估唯一标识
     **/
    public function getEvaluate($params) {
        //判断参数中是否存在mouth，存在则调用旧方法
        if (isset($params['mouth'])) {
            return $this->_getOldEvaluate($params);
        }
        //检测参数完整性
        $this->_checkParams($params);
        //month转mouth，原始接口有误
        $params['mouth'] = $params['month'];
        unset($params['month']);
        //省份城市中文名转ID
        $this->_changeToLocationId($params);
        //调用车价评估接口
        $data = PriceEvaluateInterface::evaluate($params);
        //TODO:接口调用信息统计
        //
        if($data == 1 || empty($data)) {
            throw new AppServException(AppServErrorVars::CUSTOM, '暂无此车源信息');
        }
        //返回过滤后的信息
        return $this->_dataFilter($data);
    }
    /**
     * 将中文省份、城市信息转换成对应的ID
     * @param array 查询参数
     **/
    private function _changeToLocationId(& $params) {
        //@TODO:评价接口是根据城市来评估的，与省份无关，可以考虑删除
        //传入的不是省份ID
        if (!is_numeric($params['province'])) {
            $province = LocationInterface::getProvinceByName(array('name' => $params['province']));
            if (empty($province) || !is_array($province)) {
                throw new AppServException(AppServErrorVars::CUSTOM, '省份信息有误!');
            }
            //省份ID
            $params['province'] = $province['id'];
        }

        //传入的不是城市ID
        if (!is_numeric($params['city'])) {
            $city = LocationInterface::getLocationByName(array('name' => $params['city']));
            if (empty($city) || !is_array($city)) {
                throw new AppServException(AppServErrorVars::CUSTOM, '城市信息有误!');
            }
            //城市ID
            $params['city'] = $city['id'];
        }

    }

    /**
     * 参数完整性验证，不完整则抛出异常
     * @param array params 原始参数
     * @return void
     **/
    private function _checkParams($params) {
        foreach ($this->_keys as $key) {
            if (empty($params[$key])) {
                throw new AppServException(AppServErrorVars::CUSTOM, '参数不完整!');
            }
        }
    }

    /**
     * 信息过滤，保留指定的评估信息字段
     * @param array $data 完整的评估信息
     * @return array 返回约束后的信息
     **/
    private function _dataFilter($data) {
        if (empty($this->_info)) {
            return $data;
        }
        foreach ($this->_info as $v) {
            $result[$v] = $data[$v];
        }
        return $result;
    }

    /**
     * 手机业管车价评估
     **/
    public function mbsPriceEvaluate($params) {
        if(empty($params['model_id'])||empty($params['year'])||empty($params['month'])||empty($params['province'])||empty($params['kilometer'])){
            throw new AppServException(AppServErrorVars::CUSTOM, '参数不完整');
        }
        if (!empty($params['kilometer'])) {
            $params['kilometer'] *= 10000;
        }
        //是否只需要价格
        $isPriceOnly = !empty($params['is_price_only']) ? $params['is_price_only'] : 0;
        
        $ret = PriceEvaluateInterface::evaluate($params);
        if($ret == 1|| empty($ret)) {
            throw new AppServException(AppServErrorVars::CUSTOM, '该车属于冷门车型暂时无法评估，您可以：');
        }
        if($ret['type'] ==1) {
            $province = $ret['province'] ? $ret['province'].'地区' : '全国范围内';
            $date = $ret['year'] ? ('在'.$ret['year'].'年'.$ret['month'].'月上牌的') : '';
        }elseif ($ret['type'] == 2) {
            $province = $ret['province'] ? $ret['province'].'地区' : '全国范围内';
        }
        
        if ($isPriceOnly == 1) {
            //只返回价格
            return array('price' => !empty($ret['price']) ? (string)sprintf('%.1f',$ret['price'] / 10000) : '');
        }
        
        $rs['type'] = $ret['type'] ? $ret['type'] : '';
        $rs['price'] = !empty($ret['price']) ? (string)sprintf('%.1f',$ret['price'] / 10000) : '';
        $rs['price_c'] = !empty($ret['price_c']) ? (string)sprintf('%.1f',$ret['price_c'] / 10000) : '';
        $rs['kilometer'] = !empty($ret['kilometer']) ? (string)sprintf('%.1f',$ret['kilometer'] / 10000) : '';
        $r = implode($rs, '_');
        $rs['key'] = $params['model_id'].'_'.$params['year'].'_'.$params['month'].'_'.$params['province'].'_'.$params['kilometer'].'|||'.$r;
        //求取报价范围
        $recommondPrice = !empty($ret['min_price']) ? sprintf('%.1f',$ret['min_price'] / 10000) : '';
        $recommondMaxPrice = !empty($ret['max_price']) ? sprintf('%.1f',$ret['max_price'] / 10000) : '';
        if (!empty($recommondPrice) && !empty($recommondMaxPrice)) {
            $rs['price_range'] = $recommondPrice . ' ~ ' . $recommondMaxPrice;
        }
        //求取预计成交价范围
        $recommondCPrice = !empty($ret['min_price_c']) ? sprintf('%.1f',$ret['min_price_c'] / 10000) : '';
        $recommondCMaxPrice = !empty($ret['max_price_c']) ? sprintf('%.1f',$ret['max_price_c'] / 10000) : '';
        if (!empty($recommondCPrice) && !empty($recommondCMaxPrice)) {
            $rs['price_c_range'] = $recommondCPrice . ' ~ ' . $recommondCMaxPrice;
        }
        
        //求取其他网站的报价
        $modelInfo = VehicleV2Interface::getModelById(array('model_id' => $params['model_id']));
        $matchUrl = !empty($modelInfo['series_id']) ? CarBrandOutQczjInterface::getMatchSeries($modelInfo['series_id']) : '';

        $cityPrefix = !empty($params['city']) ? SiteCityDomainInterface::getCityInfoByCityId(array('city_id' => $params['city'])) : '';
        $autoHomeUrl = empty($matchUrl) ? 'http://car.autohome.com.cn/price/' : $matchUrl;
        //新车价格,汽车之家
        $rs['new_price_links'] = array(array('title' => '汽车之家', 'url' => $autoHomeUrl));
        
        $otherLinks = array(); //其他估价，58,ganji
        $wuBaUrl = 'http://' . $cityPrefix['domain_58'] . '.58.com/ershouche/?key=' . $modelInfo['model_year'] . ' 款 ' . $modelInfo['model_name'] . ' ' . $modelInfo['sale_name'];
        $ganJiUrl = 'http://' . $cityPrefix['domain_ganji'] . '.ganji.com/che/s/_' . $modelInfo['model_year'] . ' 款 ' . $modelInfo['model_name'] . ' ' . $modelInfo['sale_name'];
        $otherLinks = array(
            array('title' => '58同城', 'url' => $wuBaUrl),
            array('title' => '赶集网', 'url' => $ganJiUrl),
        );
        $rs['other_price_links'] = $otherLinks;
        
        if($rs['price'] < 0 || $rs['kilometer']< 0) {
            throw new AppServException(AppServErrorVars::CUSTOM, '建议您调整车辆里程或上牌时间至正常范围,您可以:');
        }
        return $rs;
    }

    /**
     * @param $params
     *      - model_id
     *      - limit
     *      - offset
     * @return array
     */
    public function getSimilarCars($params) {
        $modelId = (int) $params['model_id'];
        $offset = isset($params['offset']) ? ((int) $params['offset']) : 0;
        $offset = $offset < 0 ? 0 : $offset;
        $limit = isset($params['limit']) ? (int) $params['limit'] : 5;
        $limit = $limit < 0 ? 0 : $limit;
        if ($modelId <= 0) {
            throw new AppServException(AppServErrorVars::CUSTOM, '参数错误');
        }
        include_once API_PATH . '/interface/CarSaleInterface.class.php';
        $params = array(
            'model_id' => $modelId,
            'cover_photo'   => 1,
            'status'       => 1,
            'offset'       => $offset,
            'limit'        => $limit,
            'sort'         => array(
                array('order_source', 'asc'),
                array('create_time', 'desc'),
            ),
            'order_status' => array(100, 100000),
        );
        $ret = CarSaleInterface::search($params);
        if (!empty($ret) && !empty($ret['info'])) {
            include_once APP_SERV . '/common/helper/SaleListHelper.class.php';
            $ret['info'] = SaleListHelper::formatSaleList($ret['info']);
        }
        $ret['total'] = $ret['total'] ? $ret['total'] : 0;
        $ret['fix_num'] = '400-0018-273';
        return $ret;
    }
    
        /**
     * @desc 业管相似车源
     * @param $params
     *      - model_id
     *      - limit
     *      - offset
     * @return array
     */
    public function mbsSimilarCars($params) {
        $modelId = (int) $params['model_id'];
        $offset = isset($params['offset']) ? ((int) $params['offset']) : 0;
        $offset = $offset < 0 ? 0 : $offset;
        $limit = isset($params['limit']) ? (int) $params['limit'] : 5;
        $limit = $limit < 0 ? 0 : $limit;
        if ($modelId <= 0) {
            throw new AppServException(AppServErrorVars::CUSTOM, '参数错误');
        }
        include_once API_PATH . '/interface/CarSaleInterface.class.php';
        $params = array(
            'model_id' => $modelId,
            'status'       => 1,
            'offset'       => $offset,
            'limit'        => $limit,
            'sort'         => array(
                array('order_source', 'asc'),
                array('create_time', 'desc'),
            ),
            'order_status' => array(100, 100000),
        );
        $ret = CarSaleInterface::search($params);
        if (!empty($ret) && !empty($ret['info'])) {
            include_once APP_SERV . '/app/sale/include/CarSaleList.class.php';
            $info = CarSaleList::_formatSaleList($ret['info'], 'store');
            $ret['info'] = array();
            if (!empty($info) && is_array($info)) {
                foreach ($info as $row) {
                    $ret['info'][] = array(
                        'photo' => $row['photo'],
                        'price' => $row['price'],
                        'brand_caption' => $row['brand_caption'],
                        'kilometer' => $row['kilometer'],
                        'card_time' => $row['card_time'],
                        'follow_user_name' => $row['follow_user_name'],
                        'update_time' => $row['update_time'],
                        'insert_time' => $row['insert_time'],
                        'id' => $row['id'],
                        'is_self' => $row['follow_user'] == AppServAuth::$userInfo['user']['username'] ? 1 : 0,
                    );
                }
            }
        }
        $ret['total'] = $ret['total'] ? $ret['total'] : 0;
        return $ret;
    }

    /**
     * 为兼容原手机业管，本方法为原有旧方法
     **/
    private function _getOldEvaluate($params) {
        if(empty($params['model_id'])||empty($params['year'])||empty($params['mouth'])||empty($params['province'])||empty($params['kilometer'])){
            throw new AppServException(AppServErrorVars::CUSTOM, '参数不完整');
        }
        $ret = PriceEvaluateInterface::evaluate($params);
        if($ret == 1|| empty($ret)) {
            throw new AppServException(AppServErrorVars::CUSTOM, '该车属于冷门车型暂时无法评估，您可以：');
        }
        if($ret['type'] ==1) {
            $province = $ret['province'] ? $ret['province'].'地区' : '全国范围内';
            $date = $ret['year'] ? ('在'.$ret['year'].'年'.$ret['mouth'].'月上牌的') : '';
        }elseif ($ret['type'] == 2) {
            $province = $ret['province'] ? $ret['province'].'地区' : '全国范围内';
        }
        $rs['head'] =$province.$date.$ret['brand_name'].$ret['series_name'].$ret['model_year'].$ret['sale_name'];
        $rs['type'] = $ret['type'] ? $ret['type'] : '';
        $rs['price'] = $ret['price'] ? $ret['price'] : 0;
        $rs['price_c'] = $ret['price_c'] ? $ret['price_c'] : 0;
        $rs['kilometer'] = $ret['kilometer'] ? $ret['kilometer'] : 0;
        $r = implode($rs, '_');
        $rs['key'] = $params['model_id'].'_'.$params['year'].'_'.$params['mouth'].'_'.$params['province'].'_'.$params['kilometer'].'|||'.$r;
        if($rs['price'] < 0 || $rs['kilometer']< 0) {
            throw new AppServException(AppServErrorVars::CUSTOM, '建议您调整车辆里程或上牌时间至正常范围,您可以:');
        }
        return $rs;
    }
}
