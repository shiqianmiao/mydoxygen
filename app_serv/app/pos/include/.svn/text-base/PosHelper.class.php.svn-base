<?php
/**
 * @package              v3
 * @subpackage           
 * @author               $Author:   weihongfeng$
 * @file                 $HeadURL$
 * @version              $Rev$
 * @lastChangeBy         $LastChangedBy$
 * @lastmodified         $LastChangedDate$
 * @copyright            Copyright (c) 2013, www.273.cn
 */
require_once FRAMEWORK_PATH . '/util/xml/Array2XML.class.php';
require_once FRAMEWORK_PATH . '/util/xml/XML2Array.class.php';
require_once FRAMEWORK_PATH . '/util/gearman/LoggerGearman.class.php';
class PosHelper {

    const ALLINPAY = 'allinpay';
    const ALLINPAY_ORDER = 'MP0020';
    const ALLINPAY_PAY_NOTICE = 'MP0021';
    const ALLINPAY_SIGN = 'xQ4mm0YD7o5YBolR5om0';
    const ALLINPAY_HANDLING_PERCENT = 0.01;

    const YEEPAY = 'yeepay';
    const YEEPAY_ORDER = 'COD402';
    const YEEPAY_PAY_NOTICE = 'COD403';
    const YEEPAY_SIGN = '1ntcvsfo4k9emskjtnhqnrwq467soctcudxaknxf6evksibh8qsakyw43119';
    const YEEPAY_HANDLING_PERCENT = 0.01;

    const HZFPAY = 'hzfpay';
    const HZFPAY_ORDER = 'QUERY_ORDER_273';
    const HZFPAY_PAY_NOTICE = 'NOTICE_ORDER_RESULT_273';
    const HZFPAY_SIGN_NOTICE = 'NOTICE_ORDER_SIGN_URL';
    const HZFPAY_3DES_ECB_KEY = '5B7Cc95ab626599Fdd37FBf080ccaB0e';
    const HZFPAY_HANDLING_PERCENT = 0.01;
    
    const GET_ORDER_METHOD = 'order';
    const PAY_NOTICE_METHOD = 'pay';
    
    const NOTICE_SIGN_URL = 'sign_url';

    public static function getCompanyAndMethod($params) {
        $company = '';
        $method = '';
        if ($params['_api_from_company'] == self::ALLINPAY) { 
            AppServGlobalHelper::$PARAMS_REQUEST_POS['company'] = self::ALLINPAY;
            $company = self::ALLINPAY;
            $params = self::parse($params);
            $companyData = $params['post_data'];
            $params['company_data'] = $companyData['transaction'];
            if ($companyData['transaction']['transaction_header']['transaction_type'] == self::ALLINPAY_ORDER) {
                $method = self::GET_ORDER_METHOD;
            } elseif ($companyData['transaction']['transaction_header']['transaction_type'] == self::ALLINPAY_PAY_NOTICE) {
                $method = self::PAY_NOTICE_METHOD;
            }
        } elseif ($params['_api_from_company'] == self::YEEPAY) {
            AppServGlobalHelper::$PARAMS_REQUEST_POS['company'] = self::YEEPAY;
            $company = self::YEEPAY;
            $params = self::parse($params);
            $companyData = $params['post_data'];
            $params['company_data'] = $companyData['COD-MS'];
            if ($companyData['COD-MS']['SessionHead']['ServiceCode'] == self::YEEPAY_ORDER) {
                $method = self::GET_ORDER_METHOD;
            } elseif ($companyData['COD-MS']['SessionHead']['ServiceCode'] == self::YEEPAY_PAY_NOTICE) {
                $method = self::PAY_NOTICE_METHOD;
            }
        } elseif ($params['_api_from_company'] == self::HZFPAY) {
            AppServGlobalHelper::$PARAMS_REQUEST_POS['company'] = self::HZFPAY;
            $params['company'] = self::HZFPAY;
            $company = self::HZFPAY;
            $params = self::parse($params, 'json');
            $companyData = $params['post_data'];
            $params['company_data'] = $companyData;
            if ($companyData['ACTION_NAME'] == self::HZFPAY_ORDER) {
                $method = self::GET_ORDER_METHOD;
            } elseif ($companyData['ACTION_NAME'] == self::HZFPAY_PAY_NOTICE) {
                $method = self::PAY_NOTICE_METHOD;
            } elseif ($companyData['ACTION_NAME'] == self::HZFPAY_SIGN_NOTICE) {
                $method = self::NOTICE_SIGN_URL;
            }
        }
        unset($params['post_data']);
        AppServGlobalHelper::$PARAMS_REQUEST_POS['method'] = $method;
        AppServGlobalHelper::$PARAMS_REQUEST_POS['params'] = $params;
        if (!$company || !$method) {
            throw new AppServException(AppServErrorVars::CUSTOM, '不存在pos机合作方或查询代码有误');
        }
        self::_authRequest($params, $company);
        return array($params, $company, $method);
    }

    public static function parse($params, $type = 'xml') {
        if (empty($params['php_input'])) {
            throw new AppServException(AppServErrorVars::CUSTOM, '没有接收到报文');
        }
        if ($type == 'xml') {
            $params['post_data'] = XML2Array::createArray($params['php_input']);
        } else if ($type == 'json') {
            $params['post_data'] = json_decode($params['php_input'], true);
        }
        if (empty($params['post_data'])) {
            throw new AppServException(AppServErrorVars::CUSTOM, '无法解析报文');
        }
        return $params;
    }

    private static function _getDefaultValue($ret, $field, $default='') {
        if (empty($ret[$field])) {
            return $default;
        }
        return $ret[$field];
    }

    public static function formatRet($ret, $company, $method, $params, $suc=true, $msg='', $errorCode = 0) {
        $result = array();
        $xmlArray = array();
        if ($company == self::ALLINPAY) {
            $xmlArray['_api_xml_header'] = 'transaction';
            $result['transaction_header']['requester'] = 'ALLINPAY';
            if ($suc) {
                $result['transaction_header']['resp_code'] = '00';
                $result['transaction_header']['resp_msg'] = '成功';
            } else {
                $result['transaction_header']['resp_code'] = '01';
                $result['transaction_header']['resp_msg'] = $msg;
            }
            $result['transaction_body']['order_no'] = $params['company_data']['transaction_body']['order_no'];
            $result['transaction_body']['total_amt'] = self::_getDefaultValue($ret, 'total_amt');
            $result['transaction_body']['pay_amt'] = self::_getDefaultValue($ret, 'pay_amt');
            $result['transaction_body']['unpay_amt'] = self::_getDefaultValue($ret, 'unpay_amt');
            $result['transaction_body']['remark'] = '';
            if ($method == self::GET_ORDER_METHOD) {
                $result['transaction_header']['transacton_type'] = self::ALLINPAY_ORDER;
            } else {
                $result['transaction_header']['transacton_type'] = self::ALLINPAY_PAY_NOTICE;
            }
        } else if ($company == self::ALLINPAY) {
            $xmlArray['_api_xml_header'] = 'COD-MS';
            if ($suc) {
                $result['SessionHead']['ResultCode'] = 2;
                $result['SessionHead']['ResultMsg'] = '成功';
            } else {
                $result['SessionHead']['ResultCode'] = 4;
                $result['SessionHead']['ResultMsg'] = $msg;
            }
            $result['SessionHead']['Version'] = $params['company_data']['SessionHead']['Version'];
            $result['SessionHead']['ServiceCode'] = $params['company_data']['SessionHead']['ServiceCode'];
            $result['SessionHead']['TransactionID'] = $params['company_data']['SessionHead']['TransactionID'];
            $result['SessionHead']['SrcSysID'] = $params['company_data']['SessionHead']['SrcSysID'];
            $result['SessionHead']['DstSysID'] = $params['company_data']['SessionHead']['DstSysID'];
            $result['SessionHead']['RespTime'] = date('YmdHis');
            if ($method == self::GET_ORDER_METHOD) {
                $result['SessionHead']['ServiceCode'] = self::YEEPAY_ORDER;;
                $result['SessionBody']['Item']['EmployeeID'] = $params['company_data']['SessionBody']['EmployeeID'];
                $result['SessionBody']['Item']['OrderNo'] = $params['company_data']['SessionBody']['OrderNo'];
                $result['SessionBody']['Item']['TicketNo'] = '0';
                $result['SessionBody']['Item']['ReceiverOrderNo'] = $params['company_data']['SessionBody']['OrderNo'];
                $result['SessionBody']['Item']['DistributionNo'] = '0';
                $result['SessionBody']['Item']['MobileNo'] = '0';
                $result['SessionBody']['Item']['ConfirmInfo'] = '0';
                $result['SessionBody']['Item']['TravelRemark'] = '0';
                $result['SessionBody']['Item']['PassengerInfo'] = '0';
                $result['SessionBody']['Item']['ReceiverName'] = '0';
                $result['SessionBody']['Item']['RceiverAddr'] = '0';
                $result['SessionBody']['Item']['RceiverTel'] = '0';
                $result['SessionBody']['Item']['BizName'] = '0';
                $result['SessionBody']['Item']['SubStationName'] = '0';
                $result['SessionBody']['Item']['CheckedItems'] = '0';
                $result['SessionBody']['Item']['Amount'] = self::_getDefaultValue($ret, 'Amount');
                if (!isset($ret['status'])) {
                    $result['SessionBody']['Item']['OrderStatus'] = 20;
                } elseif ($ret['status'] < 2) {
                    $result['SessionBody']['Item']['OrderStatus'] = 23;
                } else {
                    $result['SessionBody']['Item']['OrderStatus'] = 22;
                }
                $result['SessionBody']['Item']['OrderStatusMsg'] = '';
            } else {
                $result['SessionBody']['OrderNo'] = $params['company_data']['SessionBody']['OrderNo'];
                if (!$suc) {
                    $result['SessionHead']['ResultCode'] = 3;
                }
            }
        } else if ($company == self::HZFPAY) {
            $result = self::_formatHzfpay($ret, $company, $method, $params, $suc, $msg, $errorCode);
            return $result;
        }
        $xmlArray['data'] = $result;
        return $xmlArray;
    }
    
    private static function _formatHzfpay($ret, $company, $method, $params, $suc=true, $msg='', $errorCode = 0) {
        if ($method == self::GET_ORDER_METHOD) {
            $result['ACTION_NAME'] = self::HZFPAY_ORDER;
            if ($suc) {
                $result['ACTION_RETURN_CODE'] = 0;
                $result['MESSAGE'] = '成功';
                $result['ACTION_INFO'] = array(
                        'ORDER_NO'  => self::_getDefaultValue($ret, 'OrderNo'),
                        'TOTAL_AMT' => self::_getDefaultValue($ret, 'total_amt'),
                        'PAY_AMT'   => self::_getDefaultValue($ret, 'pay_amt'),
                        'UNPAY_AMT' => self::_getDefaultValue($ret, 'Amount'),
                        'REMARK'    => ''
                );
                $result['ACTION_INFO'] = json_encode($result['ACTION_INFO']);
                LoggerGearman::logInfo(array('data' => 'appserv 返回查询未加密结果'.var_export($result,true), 'identity' => 'cfb.' . $ret['OrderNo']));
                $des = DesUtil::create(DesUtil::MODE_3DES_ECB, self::HZFPAY_3DES_ECB_KEY, '');
                $result['ACTION_INFO'] = $des->encryptEcb($result['ACTION_INFO']);
                LoggerGearman::logInfo(array('data' => 'appserv 返回查询加密结果'.var_export($result,true), 'identity' => 'cfb.' . $ret['OrderNo']));
            } else {
                if ($msg == '无此订单号') {
                    $result['ACTION_RETURN_CODE'] = 3;
                    $result['MESSAGE'] = '没有检索到数据';
                } else {
                    $result['ACTION_RETURN_CODE'] = $errorCode;
                    $result['MESSAGE'] = $msg;
                }
                $result['ACTION_INFO'] = $params['company_data']['ACTION_INFO'];
            }
        } else if ($method == self::PAY_NOTICE_METHOD) {
            $result['ACTION_NAME'] = self::HZFPAY_PAY_NOTICE;
            if ($suc) {
                $result['ACTION_RETURN_CODE'] = 0;
                $result['MESSAGE'] = '成功';
            } else {
                if ($msg == '交易凭证号已存在') {
                    $result['ACTION_RETURN_CODE'] = 4;
                    $result['MESSAGE'] = '交易重复';
                } else {
                    $result['ACTION_RETURN_CODE'] = $errorCode;
                    $result['MESSAGE'] = $msg;
                }
            }
            return $result;
        } else if ($method == self::NOTICE_SIGN_URL) {
            $result['ACTION_RETURN_CODE'] = 0;
            $result['ACTION_NAME'] = self::HZFPAY_SIGN_NOTICE;
            
        } else {
            $result['ACTION_RETURN_CODE'] = $errorCode;
            $result['MESSAGE'] = $msg;
        }
        return $result;
    }
    
    private static function _authRequest(& $params, $company) {
        $paramsArray = $params;
        if ($company == self::ALLINPAY) {
            unset($paramsArray['company_data']['sign_type']);
            unset($paramsArray['company_data']['sign']);
            $xml = Array2XML::createXML('transaction', $paramsArray['company_data']);
            $xml = $xml->saveXML();
            $xml = preg_replace("/([<>])\s+/", "$1", $xml);
            $xml = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $xml);
            $xml = str_replace('<transaction>', '', $xml);
            $xml = str_replace('</transaction>', '', $xml);
            $sign = md5($xml . self::ALLINPAY_SIGN); 
            if ($sign != $params['company_data']['sign']) {
                throw new AppServException(AppServErrorVars::CUSTOM, '签名出错' . $sign);
            }
        } elseif ($company == self::YEEPAY) {
            unset($paramsArray['company_data']['SessionHead']['HMAC']);
            $xml = Array2XML::createXML('COD-MS', $paramsArray['company_data']);
            $xml = $xml->saveXML();
            $xml = preg_replace("/([<>])\s+/", "$1", $xml);
            $xml = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $xml);
            $xml = str_replace('<COD-MS>', '', $xml);
            $xml = str_replace('</COD-MS>', '', $xml);
            $sign = md5($xml . self::YEEPAY_SIGN); 
            if ($sign != $params['company_data']['SessionHead']['HMAC']) {
                throw new AppServException(AppServErrorVars::CUSTOM, '签名出错');
            }
        } elseif ($company == self::HZFPAY) {
            $des = DesUtil::create(DesUtil::MODE_3DES_ECB, self::HZFPAY_3DES_ECB_KEY, '');
            $info = $des->decryptEcb($params['company_data']['ACTION_INFO']);
            $info = json_decode($info, true);
            if (empty($info)) {
                throw new AppServException(AppServErrorVars::CUSTOM, '验证失败');
            }
            $params['company_data']['ACTION_INFO'] = $info;
        }
    }
}
