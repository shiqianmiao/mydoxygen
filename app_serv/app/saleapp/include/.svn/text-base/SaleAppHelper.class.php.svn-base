<?php
require_once API_PATH . '/interface/VehicleV2Interface.class.php';
require_once API_PATH . '/interface/LocationInterface.class.php';
/**
 *
 * @author
 * @since     2014-2-8
 * @copyright Copyright (c) 2003-2014 273 Inc. (http://www.273.cn)
 * @desc
 */
class SaleAppHelper {

    /**
     * 车源信息格式化
     */
    public static function _formatCarData($carInfo) {

     //car_photo             | array     | 车源照片，必填，不超过20张
     $carInfo['car_photo'] = json_decode($carInfo['car_photo'],true);
     if (!empty($carInfo['car_photo'])) {
         foreach ($carInfo['car_photo'] as &$sigle) {
            $sigle = 'http://img.273.com.cn/' . $sigle;
         }
     }

     //品牌
     $brandInfo = VehicleV2Interface::getBrandById(array('brand_id' => $carInfo['brand_id']));
     $carInfo['brank_name'] = $brandInfo['name'];

     //车系
     $seriesInfo = VehicleV2Interface::getSeriesById(array('series_id' => $carInfo['series_id']));
     $carInfo['series_name'] = $seriesInfo['name'];

     //车型
     $modelInfo = VehicleV2Interface::getModelById(array('model_id' => $carInfo['model_id']));
     if (!empty($modelInfo)) {
         $name = $modelInfo['model_year'] && $modelInfo['model_year'] > 0 ? ($modelInfo['model_year'] . '款 ') : '';
     if ($modelInfo['model_name'] != $modelInfo['series_name'] && !empty($modelInfo['model_name'])) {
         $name .= $modelInfo['model_name'] . ' ';
     }
         $name .= $modelInfo['sale_name'];
         $carInfo['model_name'] = $name;
     }

     //车牌属地
     $carInfo['plate_city'] = '';
     if ($carInfo['plate_city_id'] > 0) {
         $cityName = LocationInterface::getCityNameById($carInfo['plate_city_id']);
         if ($cityName) {
             $carInfo['plate_city'] = $cityName;
         }
     }

     //交易省份ID
     $carInfo['deal_province'] = '';
     if ($carInfo['deal_province_id'] > 0) {
         $province = LocationInterface::getProNameById($carInfo['deal_province_id']);
         if ($province) {
             $carInfo['deal_province'] = $province;
         }
     }
     //交易城市
     $carInfo['deal_city'] = '';
     if ($carInfo['deal_city_id'] > 0) {
         $cityName = LocationInterface::getCityNameById($carInfo['deal_city_id']);
         if ($province) {
             $carInfo['deal_city'] = $cityName;
         }
     }

     //driving_licence_photo 行驶证照片，选填
     if(!empty($carInfo['driving_licence_photo'])) {
         $carInfo['driving_licence_photo'] = json_decode($carInfo['driving_licence_photo'], true);
         if (!empty($carInfo['driving_licence_photo'])){
             foreach ($carInfo['driving_licence_photo'] as &$sigle) {
                $sigle = 'http://img.273.com.cn/' . $sigle;
             }
         }
     }

     //车源颜色
     if ($carInfo['car_color'] && isset(CarVars::$CAR_COLOR[$carInfo['car_color']])) {
         $carInfo['car_color_name'] = CarVars::$CAR_COLOR[$carInfo['car_color']];
     }

     return $carInfo;
    }

}
