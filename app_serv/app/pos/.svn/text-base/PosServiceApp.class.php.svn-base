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
require_once APP_SERV . '/app/pos/include/PosHelper.class.php';
require_once API_PATH . '/interface/CfbOrderInterface.class.php';
require_once FRAMEWORK_PATH . '/util/gearman/LoggerGearman.class.php';
class PosServiceApp {


    public function posMethod($params) {
        list($params, $company, $method) = PosHelper::getCompanyAndMethod($params);
        $params['company'] = $company;
        $ret = array();
        if ($method == PosHelper::GET_ORDER_METHOD) {
            $ret = $this->getOrderInfo($params);
        } elseif ($method == PosHelper::PAY_NOTICE_METHOD) {
            $ret = $this->orderPayNotice($params);
        } elseif ($method == PosHelper::NOTICE_SIGN_URL) {
            $ret = $this->noticeSignUrl($params);
        }
        return PosHelper::formatRet($ret, $company, $method, $params);
    }
    
    public function noticeSignUrl($params) {
        LoggerGearman::logInfo(array('data' => $params, 'identity' => 'cfb.sign'));
        CfbOrderInterface::updatePayDetailSignUrl(array(
            'order_no' => $params['company_data']['ACTION_INFO']['ORDER_NO'],
            'voucher_no' => $params['company_data']['ACTION_INFO']['REFER_NO'],
            'sign_url' => $params['company_data']['ACTION_INFO']['SIGN_URL'],
            ));
    }
            

    public function getOrderInfo($params) {
        if ($params['company'] ==  PosHelper::HZFPAY) {
            $orderNo = $params['company_data']['ACTION_INFO']['ORDER_ID'];
        } else {
            $orderNo = $params['company_data']['transaction_body']['order_no'];
        }
        LoggerGearman::logInfo(array('data' => 'appserv 接收查询请求'.var_export($params,true), 'identity' => 'cfb.' . $orderNo));
        if ($params['company'] == PosHelper::ALLINPAY) {
            $data = $this->_allinpayGetOrder($params);
        } else if ($params['company'] == PosHelper::YEEPAY) {
            $data = $this->_yeepayGetOrder($params);
        } else if ($params['company'] == PosHelper::HZFPAY) {
            $data = $this->_hzfpayGetOrder($params);
        }
        LoggerGearman::logInfo(array('data' => 'appserv 返回查询结果'.var_export($data,true), 'identity' => 'cfb.' . $orderNo));
        return $data;
    }


    public function orderPayNotice($params) {
        if ($params['company'] ==  PosHelper::HZFPAY) {
            $orderNo = $params['company_data']['ACTION_INFO']['ORDER_NO'];
        } else {
            $orderNo = $params['company_data']['transaction_body']['order_no'];
        }
        LoggerGearman::logInfo(array('data' => 'appserv 接收支付请求'.var_export($params,true), 'identity' => 'cfb.' . $orderNo));
        if($params['company'] == PosHelper::ALLINPAY) {
            $data = $this->_allinpayPayNotice($params);
        } else if ($params['company'] == PosHelper::YEEPAY) {
            $data = $this->_yeepayPayNotice($params);
        } else if ($params['company'] == PosHelper::HZFPAY) {
            $data = $this->_hzfpayPayNotice($params);
        }
        LoggerGearman::logInfo(array('data' => 'appserv 返回支付结果'.var_export($data,true), 'identity' => 'cfb.' . $orderNo));
        return $data;
    }

    private function _allinpayGetOrder($params) {
        $ret = array();
        $orderNo = $params['company_data']['transaction_body']['order_no'];
        $orderInfo = CfbOrderInterface::getOrderInfo(array('order_no' => $orderNo));
        if (empty($orderInfo)) {
            throw new AppServException(AppServErrorVars::CUSTOM, '无此订单号');
        }
        $ret['order_no'] = $orderNo;
        $ret['total_amt'] = sprintf("%.2f", (float)$orderInfo['fee']/100);
        $ret['pay_amt'] = sprintf("%.2f", (float)$orderInfo['paid_fee']/100);
        $ret['unpay_amt'] = sprintf("%.2f", $ret['total_amt'] - $ret['pay_amt']);
        return $ret;
    }

    private function _yeepayGetOrder($params) {
        $ret = array();
        $orderNo = $params['company_data']['SessionBody']['OrderNo'];
        $orderInfo = CfbOrderInterface::getOrderInfo(array('order_no' => $orderNo));
        if (empty($orderInfo)) {
            throw new AppServException(AppServErrorVars::CUSTOM, '无此订单号');
        }
        $ret['OrderNo'] = $orderNo;
        $ret['total_amt'] = sprintf("%.2f", (float)$orderInfo['fee']/100);
        $ret['pay_amt'] = sprintf("%.2f", (float)$orderInfo['paid_fee']/100);
        $ret['Amount'] = sprintf("%.2f", $ret['total_amt'] - $ret['pay_amt']);
        $ret['status'] = $orderInfo['status'];
        return $ret;
    }
    
    private function _hzfpayGetOrder($params) {
        $ret = array();
        $orderNo = $params['company_data']['ACTION_INFO']['ORDER_ID'];
        $orderInfo = CfbOrderInterface::getOrderInfo(array('order_no' => $orderNo));
        if (empty($orderInfo)) {
            throw new AppServException(AppServErrorVars::CUSTOM, '无此订单号');
        }
        if ($orderInfo['status'] == '99') {
            throw new AppServException(AppServErrorVars::CUSTOM, '订单已取消');
        }
        if (empty($orderInfo['fee'])) {
            throw new AppServException(AppServErrorVars::CUSTOM, '异常订单');
        }
        LoggerGearman::logInfo(array('data' => 'appserv 支付接口返回结果'.var_export($orderInfo,true), 'identity' => 'cfb.' . $orderNo));
        $ret['ACTION_NAME'] = PosHelper::HZFPAY_ORDER;
        $ret['OrderNo'] = $orderNo ? $orderNo : $orderInfo['order_no'];
        $ret['total_amt'] = sprintf("%.2f", (float)$orderInfo['fee']/100);
        $ret['pay_amt'] = sprintf("%.2f", (float)$orderInfo['paid_fee']/100);
        $ret['Amount'] = sprintf("%.2f", $ret['total_amt'] - $ret['pay_amt']);
        $ret['status'] = $orderInfo['status'];
        return $ret;
    }
    
    private function _allinpayPayNotice($params) {
        $ret = array();
        $param = array();
        $body = $params['company_data']['transaction_body'];
        $param['order_no'] = $body['order_no'];
        $param['voucher_no'] = $body['refer_no'];
        $param['fee'] = (int)($body['pay_amt'] * 100);
        //$param['handling_fee'] = (int)(PosHelper::ALLINPAY_HANDLING_PERCENT * $param['fee']);
        $param['handling_fee'] = 0;
        $param['card_type'] = $body['card_type'];
        $param['card_no'] = $body['card_no'];
        $param['card_bank'] = $body['bank_id'];
        $param['terminal_id'] = $body['terminal_id'];
        $param['status'] = 1;
        $param['pay_channel'] = 1;
        $ret = CfbOrderInterface::pay($param);
        if (!$ret) {
            throw new AppServException(AppServErrorVars::CUSTOM, '订单更新失败');
        }
        $ret['total_amt'] = sprintf("%.2f", (float)$ret['fee']/100);
        $ret['pay_amt'] = sprintf("%.2f", (float)$ret['paid_fee']/100);
        $ret['unpay_amt'] = sprintf("%.2f", $ret['total_amt'] - $ret['pay_amt']);
        return $ret;
    }

    private function _yeepayPayNotice($params) {
        $ret = array();
        $param = array();
        $body = $params['company_data']['SessionBody'];
        $param['order_no'] = $body['OrderNo'];
        $param['voucher_no'] = $body['PosRequestID'];
        $param['fee'] = (int)($body['Amount'] * 100);
        //$param['handling_fee'] = (int)(PosHelper::YEEPAY_HANDLING_PERCENT * $param['fee']);
        $param['handling_fee'] = 0;
        $param['card_type'] = 0;
        $param['card_no'] = $body['BankCardNo'];
        $param['card_bank'] = $body['BankCardName'];
        $param['status'] = 1;
        $param['pay_channel'] = 2;
        $ret = CfbOrderInterface::pay($param);
        if (!$ret) {
            throw new AppServException(AppServErrorVars::CUSTOM, '订单更新失败');
        }
        return $ret;
    }
    private function _hzfpayPayNotice($params) {
        $ret = array();
        $param = array();
        $info = $params['company_data']['ACTION_INFO'];
        $param['order_no'] = $info['ORDER_NO'];
        $param['terminal_id'] = $info['TERMINAL_ID'];
        $param['fee'] = (int) ($info['AMT'] * 100);
        $param['pay_time'] = strtotime($info['TRANS_TIME']);
        $param['voucher_no'] = $info['REFER_NO'];
        $param['handling_fee'] = 0;
        $param['card_type'] = $info['CARD_TYPE'];
        $param['card_no'] = $info['CARD_NO'];
        $param['merchant_id'] = $info['MERCHANT_ID'];
        $param['status'] = 1;
        $param['pay_channel'] = 3;
        LoggerGearman::logInfo(array('data' => 'appserv 支付成功通知'.var_export($param, true), 'identity' => 'cfb.' . $param['order_no'] . '.' . $info['ORDER_QDONE_NO']));
        $ret = CfbOrderInterface::pay($param);
        if (!$ret) {
            throw new AppServException(AppServErrorVars::CUSTOM, '订单更新失败');
        }
        return $ret;
    }
}


