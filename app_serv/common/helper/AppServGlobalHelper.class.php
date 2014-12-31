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
require_once API_PATH . '/interface/car/CarCommonInterface.class.php';
require_once APP_SERV . '/config/AppMsSaleVars.class.php';
class AppServGlobalHelper {

    public static function getParam($arr, $name, $def = null) {
        return isset($arr[$name]) ? $arr[$name] : $def;
    }

    public static function formatCarTags($info) {
        // --------------图标-----------------
        $tags = array();
        switch ($info['mark_type']) {
            case 1 :
                $tags['xsz'] = 1;
                break;
            case 2 :
                $tags['gwkc'] = 1;
                break;
            case 3 :
                $tags['xsz'] = $tags['gwkc'] = 1;
                break;
        }
        if ($info['condition_id'] > 0) {
            $tags['ckb'] = 1;
        }
        if ($info['is_seven']) {
            $tags['sev'] = 1;
        }
        if ($info['is_look_ck']) {
            $tags['mdyyc'] = 1;
        }
        if (CarCommonInterface::isInstallment($info)) {
            $tags['dk'] = 1;
        }
        if ($tags) {
            $tagList = array();
            foreach (AppMsSaleVars::$TAG_ICONS as $key => $tag) {
                if (!empty($tags[$key])) {
                    $tagList[] = $key;
                }
            }
            if (count($tagList) === 1) {
                $info['tag_title'] = AppMsSaleVars::$TAG_ICONS[$tagList[0]]['title'];
            }
            $info['tags'] = $tagList;
        }
        // --------------图标-----------------
        return $info;
    }
    /**
     * 列表页车源信息格式化
     * @param $info
     * @return mixed
     */
    public static function formatSaleListItem($info) {
        $info = self::formatCarTags($info);
        if (!empty($info['tags'])) {
            $info['tags'] = array_slice($info['tags'], 0, 5);
        }
        return $info;
    }

    public static function getAllRequestParams() {
        static $_ALL_PARAMS = array();
        if (count($_ALL_PARAMS)) {
            return $_ALL_PARAMS;
        }
        if (isset($_GET['_api_passport'])) {
            $_GET['_api_passport'] = urlencode($_GET['_api_passport']); 
        }
        $params =  array_merge($_GET, $_POST);
        $params['php_input'] = file_get_contents('php://input');
        $_ALL_PARAMS = $params;
        return $params;
    }
    public static function getAppServRequestParams() {
        $params = self::getAllRequestParams();
        return array(
                '_api_version'     => self::getParam($params, '_api_version', 1),
                '_api_method'      => self::getParam($params, '_api_method'),
                '_api_key'         => self::getParam($params, '_api_key'),
                '_api_time'        => self::getParam($params, '_api_time'),
                '_api_token'       => self::getParam($params, '_api_token'),
                '_api_passport'    => self::getParam($params, '_api_passport'),
                '_api_app'         => self::getParam($params, '_api_app'),
                );
    }
    public static function logInfo($allParams, $ret) {
        include_once FRAMEWORK_PATH . '/util/gearman/LoggerGearman.class.php';
        $content = '<br>参数：<br>';
        $identity = 'appserv.' . $allParams['_api_method'];
        $content .= var_export($allParams, true);
        $content .= '<br>返回：<br>';
        $content .= var_export($ret, true);
        LoggerGearman::logInfo(array('data'=>$content, 'identity'=>$identity));
    }

    public static $PARAMS_REQUEST_POS = array('params'=>array(), 'company'=>'', 'method'=>'');

    public static function responseClient($ret = array(), $format = 'json') {
        if ($format == 'xml') {
            require_once APP_SERV . '/app/pos/include/PosHelper.class.php';
            require_once FRAMEWORK_PATH . '/util/xml/Array2XML.class.php';
            $param = self::$PARAMS_REQUEST_POS;
            if ($ret['errorCode'] > 0) {
                $ret['data'] = PosHelper::formatRet(array(), $param['company'], $param['method'], $param['params'], false, $ret['errorMessge']);
            }
            $str = self::getXmlStr($param, $ret);
            header("Content-type: text/xml; charset=utf-8");
            Header('Content-Length: ' . strlen($str));
            echo $str;
            exit;
        }
        $param = self::$PARAMS_REQUEST_POS;
        if (!empty($param['company'])) {
            require_once APP_SERV . '/app/pos/include/PosHelper.class.php';
            include_once FRAMEWORK_PATH . '/util/gearman/LoggerGearman.class.php';
            if ($param['company'] == PosHelper::HZFPAY) {
                if ($ret['errorCode'] > 0) {
                    LoggerGearman::logInfo(array('data' => 'appserv hzf错误信息'.var_export($ret, true), 'identity' => 'cfb_err'));
                    $ret = PosHelper::formatRet(array(), $param['company'], $param['method'], $param['params'], false, $ret['errorMessge'], $ret['errorCode']);
                } else {
                    $ret = $ret['data'];
                }
            }
        }
        $ret = json_encode($ret);
        header("Content-type: application/json; charset=utf-8");
        //消除BOM头部
        if(substr($ret,0,3) == pack("CCC",0xEF,0xBB,0xBF)){
            $ret = substr($ret,3);
        }
        echo $ret;
    }
    public static function getXmlStr($param, $ret) {
        $result = Array2XML::createXML($ret['data']['_api_xml_header'], $ret['data']['data']);
        $str = $result->saveXml();
        $str = preg_replace("/([<>])\s+/", "$1", $str);
        if ($param['company'] == PosHelper::ALLINPAY) {
            $str = str_replace('<?xml version="1.0" encoding="UTF-8"?><transaction>', '', $str);
            $str = str_replace('</transaction>', '', $str);
            $sign = md5($str . PosHelper::ALLINPAY_SIGN);
            $ret['data']['data']['sign_type'] = 'MD5';
            $ret['data']['data']['sign'] = $sign;
        } else {
            $str = str_replace('<?xml version="1.0" encoding="UTF-8"?><COD-MS>', '', $str);
            $str = str_replace('</COD-MS>', '', $str);
            $sign = md5($str . PosHelper::YEEPAY_SIGN);
            $ret['data']['data']['SessionHead']['HMAC'] = $sign;
        }
        $result = Array2XML::createXML($ret['data']['_api_xml_header'], $ret['data']['data']);
        $str = $result->saveXml();
        $str = preg_replace("/([<>])\s+/", "$1", $str);
        return $str;
    }
    
    /**
     * @biref将数组或者字符串中的null转化成''
     * @param  array $vars
     * @return array $retsult
     * @example 
     */
    public static function changeNull($vars,$from=null,$to='') {
        if (is_array($vars)) {
            $result = array();
            foreach ($vars as $key => $value) {
                $result[$key] = self::changeNull($value,$from,$to);
            }
        } else {
            $result = ($vars===null || $vars == 'null' ) ? '' : $vars;
        }
        return $result;
    }

}
