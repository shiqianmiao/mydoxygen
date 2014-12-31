<?php
define('SITE_PATH', APP_SERV . '/../../V2012/AutoComplete/v3/');
define('CORE_PATH', SITE_PATH.'lib/');
require_once APP_SERV . '/../../V2012/AutoComplete/v3/config.php';
require_once APP_SERV . '/../../V2012/AutoComplete/v3/lib/function.php';
require_once APP_SERV . '/../../V2012/AutoComplete/v3/lib/Model.class.php';
require_once API_PATH . '/interface/VehicleInterface.class.php';
class AutocompleteServiceApp {

    public function find($params) {
        $kbm=M('KeyBlock');
        $vbm = M('VehicleBlock');
        $sites = require CORE_PATH.'Dict/siteTable.php';
        if(key_exists($params['city_domain'], $sites))$areaId=$sites[$params['city_domain']];
        else {
            $areaId = '0';
        }
        $dictList = $kbm->sortBlock($areaId, urlencode($params['keyword']), 10);
        if (empty($dictList)) {
            return array();
        }
        $result = array();
        foreach ($dictList as $key => $dictInfo){
            $result[$key]['series_id'] = 0;
            $result[$key]['brand_id'] = 0;
            $title = '';
            $carInfo = $vbm->getBlock($dictInfo['dict_id']);
            if($carInfo['parent']){
                $makeInfo=$vbm->getBlock($carInfo['parent']);
                $seriesInfo = VehicleInterface::getSeriesByUrl(array('url_path' => $carInfo['path']));
                $result[$key]['series_id'] = $seriesInfo['id'];
                $brandInfo = VehicleInterface::getBrandByUrl(array('url_path' => $makeInfo['path']));
                $result[$key]['brand_id'] = $brandInfo['id']; 
                $title.=$makeInfo['name'].' ';
            } else {
                $brandInfo = VehicleInterface::getBrandByUrl(array('url_path' => $carInfo['path']));
                $result[$key]['brand_id'] = $brandInfo['id']; 
            }
            $result[$key]['title'] = $title.$carInfo['name'];
        }
        return $result;
    }
}
