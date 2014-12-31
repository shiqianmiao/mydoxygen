<?php
/**
 * 全网搜车使用的爬虫电话信息录入接口
 * @author chenhan <chenhan@273.cn>
 */
require_once API_PATH . '/interface/CarPhoneLibInterface.class.php';

class PhoneServiceApp {

    /**
     * 插入第三方手机号码信息
     * @return boolean true是个人车源可以入线上库/false不是个人车源不能入线上库
     * @see CarPhoneLibInterface::insertThirdPartyInfo
     */
    public static function insertThirdPartyInfo($params) {
        return CarPhoneLibInterface::insertThirdPartyInfo($params);
    }
    public static function phoneProtect($params) {
        include_once API_PATH . '/interface/MbsCarProtectInfoInterface.class.php';
        return MbsCarProtectInfoInterface::add($params);
    }
}