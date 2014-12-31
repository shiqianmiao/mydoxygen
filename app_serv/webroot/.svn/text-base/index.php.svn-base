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
header('Access-Control-Allow-Origin:http://www.273.cn');
header('Access-Control-Allow-Credentials:true');
header('Access-Control-Allow-Methods:GET, POST, OPTIONS');

list($usec, $sec) = explode(" ", microtime());
$cTime = ((float)$usec + (float)$sec);
require_once dirname(__FILE__) . '/../include/bootstrap.php';
require_once FRAMEWORK_PATH . '/mvc/XhprofLog.class.php';
$apiParams = AppServGlobalHelper::getAppServRequestParams();
$allParams   = AppServGlobalHelper::getAllRequestParams();
XhprofLog::beginXhprof();
$ret = array(
        //错误码 /msapi/config/AppServErrorVars.class.php
        'errorCode'   => 0,
        'errorMessge' => '',
        'data'        => '',
        );
$format = 'json';
try {
    if (!isset(AppServNoCheck::$NO_CHECK_METHOD[$apiParams['_api_method']])) {
        include_once APP_SERV . '/include/AppServCheck.class.php';
        $errorCode = AppServCheck::check($apiParams, $allParams);
        if ($errorCode > 0) {
            throw new AppServException($errorCode);
        }
    } else {
        $format = 'xml';
    }
    require_once APP_SERV . '/include/AppServDispatch.class.php';
    $data = AppServDispatch::run($apiParams['_api_method'], $allParams);
    $ret['data'] = $data;
} catch (Exception $e) {
    $ret['errorMessge'] = $e->getMessage();
    $ret['errorCode']   = $e->getCode() ? $e->getCode() : AppServErrorVars::CUSTOM;
}

if (isset($allParams['_api_debug']) && $allParams['_api_debug'] || $format == 'xml') {
    AppServGlobalHelper::logInfo($allParams, $ret);
}
list($usec, $sec) = explode(" ", microtime());
$eTime = ((float)$usec + (float)$sec);
if (!empty($allParams['show_time']) && $allParams['show_time'] == 1) {
    $ret['spent_time'] = round(($eTime - $cTime) * 1000, 4) . ' ms';
}
//responce 格式化
AppServGlobalHelper::responseClient($ret, $format);
XhprofLog::logXhprof('appserv');
exit;
