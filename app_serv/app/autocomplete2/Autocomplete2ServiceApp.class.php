<?php
/**
 * @package              v3
 * @subpackage           
 * @author               $Author:   weihongfeng$
 * @file                 $HeadURL$
 * @version              $Rev$
 * @lastChangeBy         $LastChangedBy$
 * @lastmodified         $LastChangedDate$
 * @copyright            Copyright (c) 2013, www.273.cn
 */
require_once API_PATH . '/interface/VehicleV2Interface.class.php';
require_once API_PATH . '/interface/LocationInterface.class.php';
require_once APP_SERV . '/../web_v3/common/CityRedirect.class.php';

class Autocomplete2ServiceApp {
    public function find($params) {
        $areaInfo = CityRedirect::getLocationByDomain(array('domain'=>$params['city_domain']));
        if (!empty($areaInfo['type']) && !empty($areaInfo['id']) && $areaInfo['type'] == 'province') {
            $areaId = 'P' . $areaInfo['id'];
        } elseif (!empty($areaInfo['type']) && !empty($areaInfo['id']) && $areaInfo['type'] == 'city') {
            $areaId = 'C' . $areaInfo['id'];
        }
        $result = VehicleV2Interface::getVehicleFromWord(array(
                    'keyword' => $params['keyword'],
                    'type'    => 'all',
                    'area_id' => !empty($areaId) ? $areaId : 0,
                    'limit'   => 10
        ));
        if (empty($result)) {
            return array();
        }
        $keyArr = array();
        foreach ($result as $res) {
            $ret = array();
            $title = empty($res['brand_name']) ? '' : $res['brand_name'];
            if (!empty($res['series_id']) && $res['series_id'] > 0) {
                $temp = VehicleV2Interface::getSeriesById(array('series_id'=>$res['series_id']));
                if ($temp['import_id'] == 1){
                    $res['series_name'] = '进口' . $res['series_name'];
                }
                $title .= (" " . $res['series_name']);
            }
            $brandId= empty($res['brand_id']) ? 0 : $res['brand_id'];
            $seriesId= empty($res['series_id']) ? 0 : $res['series_id'];
            if ($brandId == 0 && $seriesId == 0) {
                continue;
            }
            $ret['series_id'] = $seriesId;
            $ret['brand_id'] = $brandId;
            $ret['title'] = $title;
            $keyArr[] = $ret;
        }
        return $keyArr;
    }
}
