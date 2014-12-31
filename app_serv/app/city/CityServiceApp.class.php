<?php
/**
 * @package              v3
 * @subpackage           
 * @author               $Author:   guoch
 * @file                 $HeadURL$
 * @version              $Rev$
 * @lastChangeBy         $LastChangedBy$
 * @lastmodified         $LastChangedDate$
 * @copyright            Copyright (c) 2012, www.273.cn
 */
require_once API_PATH . '/interface/LocationInterface.class.php';
class CityServiceApp {
    public function getFuzzyCityByName($params) {
        return LocationInterface::getFuzzyCityByName($params['city_name']);
    } 
    
    public function isChainCity($params) {
        return LocationInterface::isChainCity($params);
    }
}



