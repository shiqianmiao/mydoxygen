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
require_once APP_SERV . '/app/var/include/OptionVars.class.php';
require_once APP_SERV . '/app/var/include/OptionFormat.class.php';
require_once API_PATH . '/interface/VehicleInterface.class.php';
require_once API_PATH . '/interface/VehicleV2Interface.class.php';
require_once API_PATH . '/interface/LocationInterface.class.php';
class VarServiceApp {

    public function getPriceOption($params) {
        return OptionVars::$PRICE_OPTION;
    }

    public function getCarAgeOption($params) {
        return OptionVars::$AGE_OPTION;
    }

    public function getKilometerOption($params) {
        return OptionVars::$KILOMETER_OPTION;
    }

    public function getCarTypeOption($params) {
        if ($params['_api_version'] == '1.0') {
            return OptionVars::$TYPE_OPTION;
        }
        return OptionVars::$TYPE_OPTION_V2;
    }

    private function _getBrands($params) {
        if (isset($params['type_id']) && $params['type_id']) {
            //if ($params['_api_version'] == '1.0') {
            //    $hotBrands = VehicleInterface::getBrandListByTypeId(array('type_id' => $params['type_id']));
            //} else {
                $hotBrands = VehicleV2Interface::getBrandListByTypeId(array('type' => $params['type_id']));
            //}
        } else {
            //if ($params['_api_version'] == '1.0') {
            //    $hotBrands = VehicleInterface::getBrandList();
            //} else {
                $hotBrands = VehicleV2Interface::getBrandList(array('orderby' => 'hot'));
            //}
        }
        return $hotBrands;
    }
        
    
    public function getHotCarBrandOption($params) {
        $hotBrands = $this->_getBrands($params);
        if ($params['_api_version'] == '1.0') {
            return OptionFormat::formatBrands($hotBrands, 11);
        }
        return OptionFormat::formatBrandsV2($hotBrands, 11);
    }

    /**
     * 品牌列表
     * @param   : 参数说明如下表格
     * 参数名称     | 参数类型 | 参数补充描述
     * ------------|----------|------------------------------------------------
     * type_id     | string   | 车类型 非必填
     * @return array
     * 返回值名称   | 返回值类型 | 返回值补充描述
     * ------------|----------|------------------------------------------------
     * id         | int      | id
     * full_spell         | string      | 拼音
     * type_id         | int      | 车类型
     * icon_path         | string      | 图标URL
     * initial         | string      | 首字母
     * text         | string      | 显示名称
     * path         | string      | 网站路径
     */
    public function getCarBrandOption($params) {
        $hotBrands = $this->_getBrands($params);
        //if ($params['_api_version'] == '1.0') {
        //    return OptionFormat::formatBrands($hotBrands, 0);
        //}
        return OptionFormat::formatBrandsV2($hotBrands, 0);
    }

    /**
     * 车系列表
     * @param   : 参数说明如下表格
     * 参数名称     | 参数类型 | 参数补充描述
     * ------------|----------|------------------------------------------------
     * brand_id     | string   | 品牌ID 必填
     * @return array
     * 返回值名称   | 返回值类型 | 返回值补充描述
     * ------------|----------|------------------------------------------------
     * id         | int      | id
     * brand_id         | int      | 品牌ID
     * series_id         | int      | 厂商ID
     * full_spell         | string      | 拼音
     * type_id         | int      | 车类型
     * import_id         | int      | 1进口 2合资 3国产
     * initial         | string      | 首字母
     * full_name  | string | 原名称
     * text         | string      | 显示名称
     * path         | string      | 网站路径
     */
    public function getCarSeriesOption($params) {
        if(!isset($params['brand_id']) || !$params['brand_id']) {
            throw new AppServException(AppServErrorVars::CUSTOM, '缺少品牌参数');
        }
//        if ($params['_api_version'] == '1.0') {
//            $series = VehicleInterface::getSeriesListByBrandId($params);
//            return OptionFormat::formatBrands($series, 0, 1);
//        }
        $params['type'] = $params['type_id'];
        unset($params['type_id']);
        $series = VehicleV2Interface::getSeriesListByBrandId($params);
        return OptionFormat::formatBrandsV2($series, 0, 1);
    }
    
    /**
     * 车型列表
     * @param   : 参数说明如下表格
     * 参数名称     | 参数类型 | 参数补充描述
     * ------------|----------|------------------------------------------------
     * series_id     | string   | 车系ID 必填
     * @return array
     * 返回值名称   | 返回值类型 | 返回值补充描述
     * ------------|----------|------------------------------------------------
     * id         | int      | id
     * model_year  | string | 年款
     * text         | string      | 显示名称
     */
    public function getCarModelOption($params) {
        if(!isset($params['series_id']) || !$params['series_id']) {
            throw new AppServException(AppServErrorVars::CUSTOM, '缺少车系参数');
        }
        if($params['_api_version'] == 1.1) {
            $model = VehicleV2Interface::getModelListBySeriesId($params);
            return $model;
        }
        $params['return_type'] = 'array';
        $model = VehicleV2Interface::getModelListForSelect($params);
        $ret = array();
        $i = 0;
        foreach ( $model as $key => $value) {
            if(empty($value['model_name']) && empty($value['sale_name'])) {
                continue; 
            }
            $ret[$i]['id'] = $value['id'];
            $ret[$i]['model_year'] = $value['model_year'];
            $ret[$i]['text'] = $value['model_name']. ' ' .$value['sale_name'];
            $i++;
        }
        return $ret;
    }

    public function getHotCity($params) {
        return OptionVars::$HOT_CITY_OPTION;
    }
    
    /**
     * @desc 获取“有开设门店”的城市列表
     * @param $params 来自手机客户端的参数
     * @return array()
     */
    public function getStoreCity($params) {
        $ret = LocationInterface::getAllChainCity();
        $allCity = LocationInterface::getCityList();
        $result = $city = array();
        if (!empty($ret) && is_array($ret) && !empty($allCity) && is_array($allCity)) {
            foreach ($allCity as $c) {
                $city[$c['id']] = $c['name'];
            }
            foreach ($ret as $r) {
                if (isset($city[$r])) {
                    $result[] = array(
                        'id'   => $r,
                        'name' => $city[$r],
                    );
                }
            }
        }
        return $result;
    }

    /**
     * @brief 是否有精品推荐
     * @author 陈朝阳<chency@273.cn>
     * @param : 参数说明如下表格：
     * 参数名称     | 参数类型   | 参数补充描述
     * ------------|----------|------------------------------------------------
     * identity | string | 系统，android或ios
     * @return json格式数据
     * 返回值名称  | 返回值类型   | 返回值补充描述
     * ------------|----------|------------------------------------------------
     * show   | int    | 0不显示精品推荐,1显示精品推荐
     */
    public function showRecommended($params) {
        if (!in_array($params['identity'], array('android', 'ios'))) {
            throw new AppServException(AppServErrorVars::CUSTOM, '缺少参数');
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
        if ($params['identity'] == 'android') {
            return array(
                'show' => count($ardList) > 0 ? 1 : 0,
            );
        } else {
            //ios应用未审核通过之前不展示精品推荐
            return array(
                'show' => 0,//count($iosList) > 0 ? 1 : 0,
            );
        }
    }
}
