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
require_once API_PATH . '/interface/ApnsInterface.class.php';
class ApnsServiceApp {
    public function push($params) {
        return ApnsInterface::push($params);
    } 
    
    public function getCarInfoByIds($params) {
        return ApnsInterface::getCarInfoByids($params['ids']);
    }
    
    //apns苹果消息推送
    public function getApnsInfoById($params) {
        $info =  ApnsInterface::getApnsInfoById($params);
        return $info;
    }
    
    //android消息推送
    public function getAndroidInfoByTime($params) {
        $info =  ApnsInterface::getAndroidInfoByTime($params);
        return $info;
    }
    
    public function addDeviceToken($params) {
        return ApnsInterface::addDeviceToken($params);
    }

    public function addYgDeviceToken($params) {
        return ApnsInterface::addYgDeviceToken($params);
    }

    
    public function getOneDeviceToken($params) {
        return ApnsInterface::getOneDeviceToken($params);
    }
    
    public function updateDeviceToken($params) {
        return ApnsInterface::updateDeviceToken($params);
    }
    
    public function updateYgDeviceToken($params) {
        return ApnsInterface::updateYgDeviceToken($params);
    }
    //向固定用户推送消息
    public function PushOneMessage($params) {
        ApnsInterface::PushOneMessage($params);
        return 1;
    }

    public function pushMessageToSingle($params) {
        return ApnsInterface::pushMessageToSingle($params);
    }

    //消息推送延迟计算
    public function spendTime($params) {
        return  ApnsInterface::spendTime($params);
    }
    
}
