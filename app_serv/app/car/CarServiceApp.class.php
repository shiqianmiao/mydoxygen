<?php

/**
 *
 * @author    陈朝阳 <chency@273.cn>
 * @since     2014-5-21
 * @copyright Copyright (c) 2003- 273 Inc. (http://www.273.cn)
 * @desc      车源列表（初始为二手车之家提供车源接口）
 * 
 */
require_once API_PATH . '/interface/CarSaleInterface.class.php';
require_once API_PATH . '/interface/LocationInterface.class.php';
require_once API_PATH . '/interface/MbsDeptInterface.class.php';
require_once API_PATH . '/interface/VehicleV2Interface.class.php';
require_once API_PATH . '/interface/CarAttachInterface.class.php';
require_once COM_PATH . '/car/CarVars.class.php';
require_once API_PATH . '/interface/CarConditionInterface.class.php';
require_once dirname(__FILE__) . '/include/CheckReportHelper.class.php';
class CarServiceApp {
    /*
     * @desc获取在售车源列表
     * @param array
     */
    public function getSaleList($param) {
        $searchParam = $this->_getSearchParam($param);
        $searchRet = CarSaleInterface::getPostByFilters($searchParam);
        if (!empty($searchRet)) {
            $searchRet = $this->_formatResultData($searchRet);
        }
        return $searchRet;
    }
    
    /**
     * @desc 检索参数生成
     * @param array(
     *          offset //
     *          limit  //最大为100，默认100
     *          min_id //取大于min_id的帖子
     *       ) 
     */
    private function _getSearchParam($param) {
        $searchParam = array(
            'fields'    => 'id,type_id,model_id,brand_id,series_id,title,car_color,deal_province_id,deal_city_id,deal_district_id,description,'.
                       'price,card_time,kilometer,create_time,store_id,ext_phone,gearbox_type,displacement,condition_id',
            'filters'  => array(
                array('order_status','>=', 100),
                array('status', '=', 1),
                array('sale_status', '=', 0),
                array('follow_user_id', '>', 0),
                array('source_type', '!=', 20),
            ),
            'order' => array('id' => 'asc')
        );
        if (isset($param['min_id']) && $param['min_id'] > 0) {
            $searchParam['filters'][] = array('id', '>', $param['min_id']);
        }
        $searchParam['offset'] = isset($param['offset']) && $param['offset'] >=0 ? $param['offset'] : 0;
        $searchParam['limit'] = isset($param['limit']) && $param['limit'] > 0 && $param['limit'] <= 100 ? $param['limit'] : 100;
        return $searchParam;
    }
    
    /**
     * @desc 格式化结果集
     */
    private function _formatResultData(&$resultData) {
        //结果集
        $formatData = array();
        //品牌程序内部缓存
        $brandArray = array();
        //车系程序内部缓存
        $seriesArray = array();
        //省份程序内部缓存
        $provinceArray = array();
        //城市程序内部缓存
        $cityArray = array();
        //区县程序内部缓存
        $districetArray = array();
        //门店程序内部缓存
        $deptArray = array();
        foreach($resultData as $key => $value) {
            $formatData[$key]['id'] = $value['id'];
            //级别
            $formatData[$key]['type'] = '';
            if ($value['type_id'] > 0) {
                $formatData[$key]['type'] = CarVars::$TYPE_ID_NAME[$value['type_id']];
            }
            //品牌
            $formatData[$key]['make'] = '';
            if ($value['brand_id'] > 0) {
                if (key_exists($value['brand_id'], $brandArray)) {
                    $formatData[$key]['make'] = $brandArray[$value['brand_id']];
                } else {
                    $brandInfo = VehicleV2Interface::getBrandById(array('brand_id' => $value['brand_id']));
                    if ($brandInfo) {
                        $formatData[$key]['make'] = $brandInfo['name'];
                        $brandArray[$value['brand_id']] = $brandInfo['name'];
                    }
                }
            }
            //车系
            $formatData[$key]['family'] = '';
            if ($value['series_id'] > 0) {
                if (key_exists($value['series_id'], $seriesArray)) {
                    $formatData[$key]['family'] = $seriesArray[$value['series_id']];
                } else {
                    $seriesInfo = VehicleV2Interface::getSeriesById(array('series_id' => $value['series_id']));
                    if ($seriesInfo) {
                        $formatData[$key]['family'] = $seriesInfo['name'];
                        $seriesArray[$value['series_id']] = $seriesInfo['name'];
                    }
                }
            }
            //车型
            $formatData[$key]['model_year'] = '';
            $formatData[$key]['model'] = '';
            if ($value['model_id'] > 0) {
                $modelInfo = VehicleV2Interface::getModelById(array('model_id' => $value['model_id']));
                if (!empty($modelInfo)) {
                    if ($modelInfo['model_year'] > 0) {
                        $formatData[$key]['model_year'] = $modelInfo['model_year'];
                    }
                    $name = $modelInfo['model_year'] && $modelInfo['model_year'] > 0 ? ($modelInfo['model_year'] . '款 ') : '';
                    if ($modelInfo['model_name'] != $modelInfo['series_name'] && !empty($modelInfo['model_name'])) {
                        $name .= $modelInfo['model_name'] . ' ';
                    }
                    $name .= $modelInfo['sale_name'];
                    $formatData[$key]['model'] = $name;
                }
            }
            
            //标题
            $formatData[$key]['title'] = $value['title'] ? $value['title'] : '';
            //颜色
            $formatData[$key]['car_color'] = '';
            if ($value['car_color'] && isset(CarVars::$CAR_COLOR[$value['car_color']])) {
                 $formatData[$key]['car_color'] = CarVars::$CAR_COLOR[$value['car_color']];
            }
            //省份
            $formatData[$key]['province'] = '';
            if ($value['deal_province_id'] > 0) {
                if (key_exists($value['deal_province_id'], $provinceArray)) {
                    $formatData[$key]['province'] = $provinceArray[$value['deal_province_id']];
                } else {
                    $province = LocationInterface::getProNameById($value['deal_province_id']);
                    if ($province) {
                        $formatData[$key]['province'] = $province;
                        $provinceArray[$value['deal_province_id']] = $formatData[$key]['province'];
                    }
                }
            }
            //城市
            $formatData[$key]['city'] = '';
            if ($value['deal_city_id'] > 0) {
                if (key_exists($value['deal_city_id'], $cityArray)) {
                    $formatData[$key]['city'] = $cityArray[$value['deal_city_id']];
                } else {
                    $cityName = LocationInterface::getCityNameById($value['deal_city_id']);
                    if ($cityName) {
                        $formatData[$key]['city'] = $cityName;
                        $cityArray[$value['deal_city_id']] = $formatData[$key]['city'];
                    }
                }
            }
            //区县
            $formatData[$key]['district'] = '';
            if ($value['deal_district_id'] > 0) {
                if (key_exists($value['deal_district_id'], $districetArray)) {
                    $formatData[$key]['district'] = $districetArray[$value['deal_district_id']];
                } else {
                    $district = LocationInterface::getDistrictById(array('district_id'=>$value['deal_district_id']));
                    if ($district) {
                        $formatData[$key]['district'] = $district['name'];
                        $districetArray[$value['deal_district_id']] = $formatData[$key]['district'];
                    }
                }
            }
             //车主说明
            $formatData[$key]['note'] = $value['description'];
            //价格
            $formatData[$key]['price'] = $value['price'];
            //上牌时间
            $formatData[$key]['card_time'] = $value['card_time'] > 0 ? date('Y-m',$value['card_time']) : '';
            //公里数
            $formatData[$key]['kilometer'] = $value['kilometer'];
            //图片列表
            $formatData[$key]['images'] = $this->_getImage($value['id']);
            //发布时间
            $formatData[$key]['insert_time'] = $value['create_time'] > 0 ? date('Y-m-d',$value['create_time']) : '';
            //联系人
            $formatData[$key]['contact'] = '';
            if ($value['store_id'] > 0) {
                if (key_exists($value['store_id'], $deptArray)) {
                    $formatData[$key]['contact'] = $deptArray[$value['store_id']];
                } else {
                    $deptInfo = MbsDeptInterface::getDeptInfoByDeptId(array('id' => $value['store_id']));
                    if ($deptInfo) {
                        $formatData[$key]['contact'] = $deptInfo['dept_name'];
                        $deptArray[$value['store_id']] = $formatData[$key]['contact'];
                    }
                }
            }
            //电话号码
            $formatData[$key]['telephone'] = '';
            if ($value['store_id'] > 0) {
                $extPhone = MbsDeptInterface::getShowExtPhoneByDept(array('dept_id' => $value['store_id']));
                if (!empty($extPhone)) {
                     $formatData[$key]['telephone'] = '400-6030-273 转 ' . $extPhone;
                }
            }
            //变速器
            $gearbox = array(
                1 => '手动',
                2 => '自动',
                3 => '手自一体',
            );
            $formatData[$key]['gear_type'] = $gearbox[$value['gearbox_type']] ? $gearbox[$value['gearbox_type']] : '';
            //排量
            $formatData[$key]['engine'] = $value['displacement'];
             //检测报表
            if ($value['condition_id'] > 0) {
                $checkData = CarConditionInterface::getInfoById(array('id' => $value['condition_id'], 'format' => true));
                $checkReport = CheckReportHelper::formatCheckReport($checkData);
                $formatData[$key]['condition'] = $checkReport;
            }
        }
        return $formatData;
    }
    
    /**
     * @desc 获取车源对应的图片列表
     * @param $id 车源id
     * @return array 车源图片数组
     */
    private function _getImage($id) {
        $imageList = CarAttachInterface::getImageInfoByCar(array('id' => $id));
        $imageRet = array();
        foreach($imageList as $image) {
            $imageRet[] = Util::formatImageUrl($image['file_path'], array(
                'width'   => 900,
                'height'  => 0,
                'cut'     => true,
                'quality' => 6,
                'mark'    => 0,
                'version' => 1,
                'replace_ad' => true,
            ));
        }
        return $imageRet;
    }
}
