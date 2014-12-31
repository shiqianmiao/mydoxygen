<?php


require_once API_PATH . '/interface/VehicleV2Interface.class.php';

class VehicleServiceApp {
    
    public function vin($params) {
        if (!isset($params['vin'])) {
            throw new AppServException(AppServErrorVars::CUSTOM, '缺少参数vin');
        }
        
        return  VehicleV2Interface::getModelByVIN($params);
    }
    public function getInfoByVIN($params) {
        if (!isset($params['vin'])) {
            throw new AppServException(AppServErrorVars::CUSTOM, '缺少参数vin');
        }   
    
        $Info = VehicleV2Interface::getInfoByVIN($params);
        if(empty($Info)) {
            throw new AppServException(AppServErrorVars::CUSTOM, "抱歉，没有查询到车型信息。\n请确认输入的VIN码无误！");
        }
        return $Info;
    }
    public function getModelCaption($params) {
        return  VehicleV2Interface::getModelCaption($params);
    }
    
    public function getBrandList($params) {
        return  VehicleV2Interface::getBrandList($params);
    }
    
    public function getBrandListByTypeId($params) {
        return  VehicleV2Interface::getBrandListByTypeId($params);
    }
    
    public function getBrandListByChar($params) {
        return  VehicleV2Interface::getBrandListByChar($params);
    }
    
    public function getBrandIdListByKeyword($params) {
        return  VehicleV2Interface::getBrandIdListByKeyword($params);
    }
    
    public function getVehicleFromWord($params) {
        return  VehicleV2Interface::getVehicleFromWord($params);
    }
    
    public function getBrandById($params) {
        return  VehicleV2Interface::getBrandById($params);
    }

    public function getBrandByIds($params) {
        $ids = (empty($params['brand_ids'])) ? array() : explode(',', $params['brand_ids']);
        $ret = array();
        foreach ($ids as $id) {
            $id = (int) $id;
            if ($id <= 0) {
                $ret[$id] = array();
                continue;
            }

            if (!isset($ret[$id])) {
                $ret[$id] = VehicleV2Interface::getBrandById(array(
                    'brand_id'  => $id,
                ));
            }
        }
        return $ret;
    }

    public function getBrandByUrl($params) {
        return  VehicleV2Interface::getBrandByUrl($params);
    }
    
    public function getMakerById($params) {
        return  VehicleV2Interface::getMakerById($params);
    }
    
    public function getMakerListByBrandId($params) {
        return  VehicleV2Interface::getMakerListByBrandId($params);
    }
    
    public function getMakerList($params) {
        return  VehicleV2Interface::getMakerList($params);
    }
    
    public function getSeriesById($params) {
        return  VehicleV2Interface::getSeriesById($params);
    }
    
    public function getSeriesByUrl($params) {
        return  VehicleV2Interface::getSeriesByUrl($params);
    }
    
    public function getSeriesListByBrandId($params) {
        return  VehicleV2Interface::getSeriesListByBrandId($params);
    }
    
    public function getSeriesList($params) {
        return  VehicleV2Interface::getSeriesList($params);
    }
    
    public function getSeriesListByMakerId($params) {
        return  VehicleV2Interface::getSeriesListByMakerId($params);
    }
    
    public function getModelById($params) {
        return  VehicleV2Interface::getModelById($params);
    }
    
    public function getModelListBySeriesId($params) {
        return  VehicleV2Interface::getModelListBySeriesId($params);
    }
    
    public function getModelList($params) {
        return  VehicleV2Interface::getModelList($params);
    }
    
    public function getModelListForSelect($params) {
        return  VehicleV2Interface::getModelListForSelect($params);
    }

    public function getSeriesByCodes($params) {
        return VehicleV2Interface::getSeriesByCodes($params);
    }
}
