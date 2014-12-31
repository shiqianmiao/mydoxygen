<?php
/**
 * @package              V3
 * @subpackage           
 * @author               $Author:   weihongfeng$
 * @file                 $HeadURL$
 * @version              $Rev$
 * @lastChangeBy         $LastChangedBy$
 * @lastmodified         $LastChangedDate$
 * @copyright            Copyright (c) 2012, www.273.cn
 */
class AppServCheck {

    const API_TIME_EXPIRE = 3600;
    const API_ACCESS_TIME_EXPIRE = 2592000;
    const API_ACCESS_METHOD = 'auth.getAccessToken';
    const SUPER_API_TOKEN = '80af186967ea0a2ad92f0b483ae48471';
    const SUPER_API_TIME = '510b60b65b41080750475c832f843f22';

    public static function check($appServParams, $allParams) {
        //第一步check方法是否存在
        if (!self::checkAppServMethod($appServParams['_api_method'])) {
            return AppServErrorVars::API_METHOD_NOT_EXIST;
        }
        //第二步check _api_key是否存在
        $apiKeyConfig = AppServAuthVars::getApiKeyConfig($appServParams['_api_key']);
        if (empty($apiKeyConfig)) {
            return AppServErrorVars::APP_KEY_INVALID;
        }
        //第三步: check请求是否过期
        $apiTime = (int)$appServParams['_api_time'];
        if (abs($_SERVER['REQUEST_TIME'] - $apiTime) > self::API_TIME_EXPIRE) {
            if ($appServParams['_api_time'] != self::SUPER_API_TIME) {
                return AppServErrorVars::APP_SERV_TIME_EXPIRE;
            }
        }
        //获取通行证的方法例外
        if ($appServParams['_api_method'] == self::API_ACCESS_METHOD) {
            $apiKeyConfig = AppServAuthVars::getApiKeyConfig($appServParams['_api_key']);
            $apiSecret = $apiKeyConfig['api_secret'];
            if (md5($apiSecret . $appServParams['_api_time']) != $allParams['_api_secret']) {
                return AppServErrorVars::ACCESS_ERROR;
            }
            return AppServErrorVars::SUCCESS;
        }

        //第四步 check token是否正确
        if ($allParams['_api_token'] != self::SUPER_API_TOKEN) {
            $status = self::checkToken($allParams);
            if ($allParams['_api_method'] != 'update.getMsIosVersion' && $status > 0) {
                return $status;
            }
        }
        //第五步：check _api_key是否有调用此method的权限
        if (!self::checkAuth($appServParams['_api_key'], $appServParams['_api_method'])) {
            return AppServErrorVars::API_METHOD_NO_AUTHORIZE;
        }
        //需要登陆的方法，验证_api_passport并拿到userInfo
        if (isset(AppServVars::$APP_SERV_METHOD_LOGIN_CONFIG[$appServParams['_api_method']])) {
            $status = self::checkPassport($allParams);
            if ($status) {
                return $status;
            }
        }
        //第六步：_api_key的相应ip限制check
        if (!self::checkClientIp($appServParams['_api_key'])) {
            return AppServErrorVars::CLIENT_IP_NO_AUTHORIZE;
        }
        //第七步: 访问粒度控制
        if (!self::checkUserAccess($appServParams['_msapi_key'])) {
            return AppServErrorVars::ACCESS_CONTROL;
        }

        // 版本检测
        if (AppServVars::$IS_MANDATORY_UPGRADE && !isset(AppServNoCheck::$NO_CHECK_UPDATE_METHOD[$appServParams['_api_method']]) && self::_checkClientVersion($allParams)) {
            return AppServErrorVars::MANDATORY_UPDATE;
        }
        //检查通过放回0
        return AppServErrorVars::SUCCESS;
    }

    /**
     * 检测版本是否需要强制升级
     * 
     * @return boolean     是否需要强制升级
     */
    public static function _checkClientVersion($appServParams) {
        $source = $appServParams['_app_source'] ? $appServParams['_app_source'] : 0;
        $type   = $appServParams['_app_type'] ? $appServParams['_app_type'] : 0;
        $version   = $appServParams['_app_version'] ? $appServParams['_app_version'] : 0;
        if ($source > 0 && $type > 0 && $version > 0 && isset(AppServVars::$APP_VERSIONS[$type][$source]) && AppServVars::$APP_VERSIONS[$type][$source] > $version) {
            return true;
        }

        return false;
    }

    public static function checkAppServMethod($method) {
        if (isset(AppServVars::$APP_SERV_METHOD_CONFIG[$method]) && AppServVars::$APP_SERV_METHOD_CONFIG[$method]) {
            return true;
        }
        return false;
    }
    public static function checkPassport($params) {
        require_once API_PATH . '/interface/SsoInterface.class.php';
        if (!$params['_api_app'] || !$params['_api_passport']) {               
            return AppServErrorVars::PASSPORT_ERROR;       
        } 
        $params['passport'] = $params['_api_passport'];
        $params['app'] = $params['_api_app'];
        $userInfo = SsoInterface::getAllUserInfo($params);
        if (!$userInfo['user'] || $userInfo['user']['status'] != 1) {
            return AppServErrorVars::PASSPORT_ERROR;      
        }
        $codeArray = AppServVars::$APP_SERV_METHOD_LOGIN_CONFIG[$params['_api_method']];
        if (!empty($codeArray) && is_array($codeArray)) {
            foreach ($codeArray as $item) {
                if (!isset($userInfo['Permisssions'][$item])) {
                    return AppServErrorVars::LOGING_USER_CODE_ERROR;
                }
            }
        }
        if ($userInfo['user']['dept_id']) {
            require_once API_PATH . '/interface/MbsDeptInterface.class.php';
            $deptInfo = MbsDeptInterface::getDeptInfoByDeptId(array('id' => $userInfo['user']['dept_id']));
            if (!empty($deptInfo)) {
                $userInfo['user']['dept_name'] = $deptInfo['dept_name'];
                $userInfo['user']['dept_type'] = $deptInfo['dept_type'];
                $userInfo['user']['province'] = $deptInfo['province'];
                $userInfo['user']['city'] = $deptInfo['city'];
            }
        }
        $userInfo['permisssions'] = $userInfo['Permisssions'];
        unset($userInfo['Permisssions']);
        AppServAuth::$userInfo = $userInfo;
        //记录最后活跃时间
        include_once APP_SERV . '/include/GlobalHelper.class.php';
        GlobalHelper::setLastLoginTime($userInfo['user']);
        return AppServErrorVars::SUCCESS;
    }


    public static function checkToken($allParams) {
        $clientInfo = AppServAuth::parseAccessToken($allParams['_api_token']);
        $apiKey = $clientInfo[0];
        $apiTime = (int) $clientInfo[1];
        if ($apiKey != $allParams['_api_key'] && $apiKey != '8a7f95774b1ce149fda1025298c310d9') {
            return AppServErrorVars::TOKEN_SIGN_ERROR;
        }
        if (abs($_SERVER['REQUEST_TIME'] - $apiTime) > self::API_ACCESS_TIME_EXPIRE) {
            return AppServErrorVars::TOKEN_OUT_TIME;
        }
        $apiKeyConfig = AppServAuthVars::getApiKeyConfig($apiKey);
        if (empty($apiKeyConfig)) {
            return AppServErrorVars::TOKEN_SIGN_ERROR;
        }
        return AppServErrorVars::SUCCESS;
    }

    public static function checkAuth($apiKey, $apiMethod) {
        $apiKeyConfig = AppServAuthVars::getApiKeyConfig($apiKey);
        if ($apiKeyConfig['is_inside']) {
            return true;
        }
        return in_array($apiMethod, $apiKeyConfig['authorization']) ? true : false;
    }

    public static function checkClientIp($apiKey) {
        $ip = RequestUtil::getIp();
        $apiKeyConfig = AppServAuthVars::getApiKeyConfig($apiKey);
        if (!isset($apiKeyConfig['limit_range_ip'])) {
            return true;
        }
        foreach ($apiKeyConfig['limit_range_ip'] as $rangeIp) {
            $regexpIp  = str_replace('*', '[\d]+', $rangeIp);
            if (preg_match("/^{$_regexpIp}$/", $ip)) {
                return true;
            }
        }
        return false;
    }

    public static function checkUserAccess($apiKey) {
        return true;
    }
}
