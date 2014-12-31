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

require_once dirname(__FILE__) . '/../include/bootstrap.php';
$apiParams = AppServGlobalHelper::getAppServRequestParams();
$allParams   = AppServGlobalHelper::getAllRequestParams();
$ret = array(
        //错误码 /msapi/config/AppServErrorVars.class.php
        'errorCode'   => 0,
        'errorMessge' => '',
        'data'        => '',
        );
$format = 'json';
try {
    if (!isset(AppServNoCheck::$NO_CHECK_METHOD[$apiParams['_api_method']])) {
        exit;
    }
    if (!empty($allParams['type'])) {
        $format = $allParams['type'];
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

//responce 格式化
AppServGlobalHelper::responseClient($ret, $format);
