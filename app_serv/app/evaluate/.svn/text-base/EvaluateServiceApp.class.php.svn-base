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
require_once API_PATH . '/interface/CarEvaluateInterface.class.php';
require_once API_PATH . '/interface/VehicleV2Interface.class.php';

class EvaluateServiceApp {

    public function getHistoryEvaluate($params) {
        if (!isset($params['brand_name']) && !isset($params['series_name'])) {
            throw new AppServException(AppServErrorVars::CUSTOM, '缺少参数');
        }
        return CarEvaluateInterface::getHistoryEvaluate($params);
    }

    public function getResult($params) {
        if (!isset($params['id']) || !is_numeric($params['id'])) {
            throw new AppServException(AppServErrorVars::CUSTOM, '缺少参数id');
        }
        return  CarEvaluateInterface::getResult($params);
    }

    public function publish($params) {
        unset($params['_api_version']);
        return CarEvaluateInterface::publish($params);
    }
    
    /**
     * @desc 查看出场报价
     */
    public function getFactoryPrice($params) {
        if (empty($params['model_id'])) {
            throw new AppServException(AppServErrorVars::CUSTOM, '缺少参数model_id');
        }
        
        $modelInfo = VehicleV2Interface::getModelById(array('model_id' => $params['model_id']));
        $ret = array(
            'price' =>  !empty($modelInfo['guide_price']) ? (string)$modelInfo['guide_price'] : '',
            'tax'   =>  !empty($modelInfo['guide_price']) ? (string)number_format($modelInfo['guide_price'] / 11.7, 2) : '',
        );
        return $ret;
    }
}
