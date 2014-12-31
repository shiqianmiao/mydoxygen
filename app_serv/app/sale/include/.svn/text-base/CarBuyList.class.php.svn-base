<?php
class CarBuyList {
    /**
     * @brief 格式化SaleList参数，符合接口参数格式
     * 格式化查询参数
     */
    public static function getSearchParams($params) {
        if ($params ['query_type'] == 'my') { // 我的
            $ret ['filters'] [] = array (
                'follow_user_id','=',AppServAuth::$userInfo ['user'] ['username'] 
            );
        }
        // query_type值为store时为查询店内的卖车列表
        if ($params ['query_type'] == 'store') { // 店内
            $ret ['filters'] [] = array (
                'store_id','=',AppServAuth::$userInfo ['user'] ['dept_id'] 
            );
        }
        
        // 默认参数
//         $ret['isExt'] = true;
        $ret ['limit'] = $params ['end'] - $params ['start']+1;
        $ret ['offset'] = $params ['start']-1;
        $ret ['order'] = array (
            'create_time' => 'desc' 
        );
        $user = $params['username'];
        if(empty($params['query_type'])) { // 搜索车
            $ret['filters'][] = array('deal_city_id','=', AppServAuth::$userInfo ['user'] ['city']);
        }
        switch (intval ( $params['status'] )) {
            case 1 :
                $ret['filters'][] = array('status', '=', 0);
                break;
            case 2 :
                $ret['filters'][] = array('status', '=', 1);
                break;
            case 3 :
                $ret['filters'][] = array('sale_status', '=', 1);
                break;
            case 4 :
                $ret['filters'][] = array('sale_status', '=', 1);
                $ret['filters'][] = array('status', '=', 1);
                break;
            case 5 :
                $ret['filters'][] = array('status', '=', 2);
                break;
            case 6 :
                $ret['filters'][] = array('status', '=', 3); // 终止
                break;
            default :
                $where .= ' and (status=1 or status=0)';
                $ret['filters'][] = array('status', 'in', array(0,1));
                break;
        }
        $keyword = $params['keyword'];
        if ($keyword) {
            //搜索电话号码
            if (is_numeric($keyword) && in_array(strlen($keyword),array(10,11,12))) {
                $customerIds =array();
                $customerIdArr = MbsCustomerInterface::getAllInfo(array('field' => 'id', 'filters' => array(array('or' =>
                        array(array('mobile', '=', $keyword),array('telephone', '=', $keyword),array('telephone2', '=', $keyword))))));
                if (!empty($customerIdArr)) {
                    foreach ($customerIdArr as $id) {
                        $customerIds[] = $id['id'];
                    }
                } else {
                    return $customerIds;
                }
                $ret['filters'][] = array('customer_id', 'in', $customerIds);
                $ret['filters'][] = array('store_id', '=', $this->deptInfo['id']);
            } else if (is_numeric($keyword) && in_array(strlen($keyword),array(5,6,7,8))) {
                $ret['filters'] = array(
                        array('id', '=', $keyword),
                );
            } else {    //搜索车型品牌
                $ret['filters'] = array(
                        array('title', 'like', '%'.trim(urldecode($keyword)).'%'),
                );
            }
        }
        
        if (! empty ( $params['insert_time'] ) and preg_match ( '/^\d{4}-\d{1,2}-\d{1,2} \d{1,2}:\d{1,2}:\d{1,2}$/i', $params['insert_time'] )) {
            $ret['filters'] = array('insert_time', '<', $params['insert_time']);
        }
        if ($params['price']) {
            $priceArray = explode ( '-', $params['price'] );
            if (isset ( $priceArray [0] )) {
                $ret['filters'][] = array('min_price', '>=', $priceArray [0]);
                if (isset ( $priceArray [1] )) {
                    $ret['filters'][] = array('max_price', '<=', $priceArray [1]);
                }
            }
        }
        $timeStamp = time();
        if ($params['time']) {
            $timeArray = explode('-', $params['time']);
            $cardAge = $timeArray[0];
            switch($cardAge) {
                case '0':
                    $ret['filters'][] = array('car_age', '=', 1);
                    
                    break;
                case '1':
                    $ret['filters'][] = array('car_age', '=', 103);

                    break;
                case '3':
                    $ret['filters'][] = array('car_age', '=', 305);
                    break;
                case '5':
                    $ret['filters'][] = array('car_age', '=', 508);
                    break;
                case '8':
                    $ret['filters'][] = array('car_age', '=', 8010);
                    break;
                case '10':
                    $ret['filters'][] = array('car_age', '=', 1099);
                    break;
                default:
                    break;
            }
            
        }

        if ($params['brand_id']) {
            $ret['filters'][] = array('brand_id', '=', $params['brand_id']);
        }
        if ($params['series_id']) {
            $ret['filters'][] = array('series_id', '=', $params['series_id']);
        }
        if ($params['type_id']) {
            $ret['filters'][] = array('type_id', '=', $params['type_id']);
        }
        return $ret;
    }
    
    public static function formatBuyList($params,$query_type = '') {
        $rs = array();
        foreach($params as $v) {
            $ret = BcPost::_basicFormat($v, $query_type);
            //从oracle到mysql数据库字段变化
            $ret['id'] = $v['id'] ? $v['id'] : '';
            $ret['info_id'] = $v['id'] ? 'B'.$v['id'] : '';
            $ret['car_type'] = $v['type_id'] ? $v['type_id'] : '';
            $ret['province'] = $v['deal_province_id'] ? $v['deal_province_id'] : '';
            $ret['city'] = $v['deal_city_id'] ? $v['deal_city_id'] : '';
            $ret['district'] = $v['deal_district_id'] ? $v['deal_district_id'] : '';
            $ret['follow_user'] = $v['follow_user_id'] ? $v['follow_user_id'] : '';
            $ret['plate_province'] = $v['plate_province_id'] ? $v['plate_province_id'] : '';
            $ret['plate_city'] = $v['plate_city_id'] ? $v['plate_city_id'] : '';
            $ret['insert_user_id'] = $v['create_user_id'] ? $v['create_user_id'] : '';
            $ret['insert_time'] = date('Y-m-d H:i:s', $v['create_time']) ? date('Y-m-d H:i:s', $v['create_time']) : '';
            if (!$v['model_id'] && (!$v['series_id'] || !$v['brand_id'])) {
                $brind_name = '';
            } else {
                $brind_name = VehicleV2Interface::getModelCaption($v) ? VehicleV2Interface::getModelCaption($v) : '';
            }
            $ret['brind_name'] = $v['title'] ? $v['title'] : $brind_name;
            $ret['air_displacement'] = $v['displacement'] ? $v['displacement'] : '';
            $ret['card_age'] = $v['car_age'] ? $v['car_age'] : '';
            $ret['start_card_time'] = $v['start_car_time'] ? $v['start_car_time'] : '';
            $ret['end_card_time'] = $v['end_car_time'] ? $v['end_car_time'] : '';
            //没有变化字段
            $ret['customer_id'] = $v['customer_id'] ? $v['customer_id'] : '';
            $ret['update_time'] = date('Y-m-d H:i:s', $v['update_time']) ? date('Y-m-d H:i:s', $v['update_time']) : '';
            $ret['brand_id']=  $v['brand_id'] ? $v['brand_id'] : '';
            $ret['series_id'] = $v['series_id'] ? $v['series_id'] : '';
            $ret['model_id'] =$v['model_id'] ? $v['model_id'] : '';
            //$ret['telephone'] =$v['telephone'] ? $v['telephone'] : '';
            $ret['telephone'] =$ret['mobile'] ? $ret['mobile'] : '';
            $ret['status'] = $v['status'] ? $v['status'] : 0;
            $ret['kilometer'] = number_format($v['kilometer'], 2) ? number_format($v['kilometer'], 2) : '';
            $ret['min_price'] = number_format($v['min_price'] / 10000, 2) ? number_format($v['min_price'] / 10000, 2) : '';
            $ret['max_price'] = number_format($v['max_price'] / 10000, 2) ? number_format($v['max_price'] / 10000, 2) : '';
            $ret['note'] = $v['note'];
            $ret['operators'] = !empty(CarMbsVars::$BUY_OPERATION[$v['status']]) ? CarMbsVars::$BUY_OPERATION[$v['status']] : array();
            $rs[] = $ret;
        }
        
        return $rs;
    }
    
}



