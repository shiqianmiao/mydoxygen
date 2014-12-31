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
require_once API_PATH . '/interface/LocationInterface.class.php';
class GeoServiceApp {
    public function getHotCity() {
    } 

    public function getProvinceList($params) {
        $list = LocationInterface::getProvinceList();
        $ret = array();
        foreach ($list as $row) {
            $data = array();
            $data['id'] = $row['id'];
            $data['name'] = $row['name'];
            $ret[] = $data;
        }
        return $ret;
    }

    public function getCityList($params) {
        if (empty($params['p_id'])) {
            throw new AppServException(AppServErrorVars::CUSTOM, '缺少省份参数p_id');
        }
        $list = LocationInterface::getCityListByProvinceId(array('province_id' => $params['p_id']));
        $deptNums = LocationInterface::getCityDeptNums();
        if (!is_array($deptNums)) {
            $deptNums = array();
        }
        $ret = array();
        foreach ($list as $row) {
            $data = array();
            $data['id'] = $row['id'];
            $data['name'] = $row['name'];
            $data['full_spell'] = $row['full_spell'];
            $data['dept_num'] = (int)$deptNums[$row['id']];
            $ret[] = $data;
        }
        return $ret;
    }
}



