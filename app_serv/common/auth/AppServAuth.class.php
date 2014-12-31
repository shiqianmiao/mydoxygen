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
require_once FRAMEWORK_PATH . '/util/des/DesUtil.class.php';

class AppServAuth { 
    const ACCESS_KEY = 'eb3f532dccc8a194';
    const ACCESS_IV = '^&$j9hu)';
    public static $userInfo = array();
    //生成返回给外部的accessToken
    public static function generateAccessToken($apiKey) {
        $generateTime = time();
        $apiKey .= $generateTime;
        $encrypt = DesUtil::create(DesUtil::MODE_3DES, self::ACCESS_KEY, self::ACCESS_IV);
        return urlencode($encrypt->encrypt($apiKey));
    }
    //解出accessToken对应的客户端信息
    public static function parseAccessToken($accessToken) {
        $encrypt = DesUtil::create(DesUtil::MODE_3DES, self::ACCESS_KEY, self::ACCESS_IV);
        $coded = $encrypt->decrypt($accessToken);
        $clientInfo = array();
        $clientInfo[0] = substr($coded, 0, 32);
        $clientInfo[1] = substr($coded, 32);
        return $clientInfo;
    }
}
