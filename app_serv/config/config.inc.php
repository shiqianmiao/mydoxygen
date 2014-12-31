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
define('APP_SERV_DEBUG_STATUS', true);

if (!defined('DEBUG_STATUS')) {                                                
    define('DEBUG_STATUS', true);
}

if (!defined('APP_SERV')) {
    define('APP_SERV',            dirname(dirname(__FILE__)));
}

if (!defined('CONF_PATH')) {
    if (APP_SERV_DEBUG_STATUS) {
        define('CONF_PATH',         APP_SERV . '/../conf/debug');
        //error_reporting(E_ALL & ~E_NOTICE);
        ini_set('display_errors', 0);
    } else {
        define('CONF_PATH',         APP_SERV . '/../conf/runtime');
    }
}

if (!defined('API_PATH')) {
    define('API_PATH',       APP_SERV . '/../api');
}

if (!defined('DATA_PATH')) {
    define('DATA_PATH',         APP_SERV . '/../data');
}

if(!defined('FRAMEWORK_PATH')) {
    define('FRAMEWORK_PATH',        APP_SERV . '/../framework');
}

if(!defined('COM_PATH')) {
    define('COM_PATH',         APP_SERV . '/../conf/common');
}

if(!defined('SSO_SITE')) {
    define('SSO_SITE',         APP_SERV . '/../sso');
}
