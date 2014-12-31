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
require_once APP_SERV . '/common/auth/AppServAuth.class.php';
require_once API_PATH . '/interface/CkbWhiteListInterface.class.php';
require_once APP_SERV . '/config/AppServWhiteListVars.class.php';
class AuthServiceApp {
    public function getAccessToken($params) {
        
        //设备编号是否在检测平台白名单中
        $apiParams = AppServGlobalHelper::getAppServRequestParams();
        $apiKeyConfig = AppServAuthVars::getApiKeyConfig($apiParams['_api_key']);
        if ($apiKeyConfig['id'] == 6) {
            if(empty($params['device_id'])){
                throw new AppServException(AppServErrorVars::CUSTOM, '设备编号不存在');
            }
            $whiteParams = array('fields' => 'status', 'cond' => array(array('IMEI', '=', $params['device_id'])));
            $whiteInfo = CkbWhiteListInterface::getRow($whiteParams);
            if(!isset($whiteInfo['status']) || $whiteInfo['status'] != 0) {
                throw new AppServException(AppServErrorVars::CUSTOM, '设备编号不在白名单之内，请联系客服！');
            }
            // $white_name = AppServWhiteListVars::getWhiteListConfig($params['device_id']);
            // if(empty($white_name)) {
            //     throw new AppServException(AppServErrorVars::CUSTOM, '设备编号不在白名单之内，请联系客服！');
            // }
        }
        
        return AppServAuth::generateAccessToken($params['_api_key']);
    } 
}



