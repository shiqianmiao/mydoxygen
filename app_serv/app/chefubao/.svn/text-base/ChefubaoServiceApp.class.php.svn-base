<?php
/**
 * 车付宝业管接口
 * @author 戴志君
 * @version 1.0
 * @date 2014-10-29
 */
require_once API_PATH . '/interface/CfbPayForInterface.class.php';

class ChefubaoServiceApp {

    public function payForList($params) {
        $params['cond']['start_create_time'] = time()-60;
        return CfbPayForInterface::getList($params);
    }
    
    /**
     * 取订单信息
     * @param array $params 
     * 参数说明如下表格
     * 参数名称     | 参数类型 | 参数补充描述
     * ------------|----------|------------------------------------------------
     * order_no    | string   | 订单号
     * @return array
     * 返回值名称   | 返回值类型   | 返回值补充描述
     * ------------|----------|------------------------------------------------
     * order_no    | string   | 订单号
     * car_number  | string   | 车牌号
     * total_amt   | float    | 订单总金额
     * pay_amt     | float    | 已付金额
     * amount      | float    | 未付金额
     * status      | int      | 状态
     * @throws BaseException
     */
    public function getOrderInfo($params) {
        $orderInfo = CfbOrderInterface::getOrderInfo($params);
        if (!empty($orderInfo)) {
            $ret['order_no']    = $orderInfo['order_no'];
            $ret['car_number']  = $orderInfo['car_number'];
            $ret['total_amt']   = sprintf("%.2f", (float)$orderInfo['fee'] / 100);
            $ret['pay_amt']     = sprintf("%.2f", (float)$orderInfo['paid_fee'] / 100);
            $ret['amount']      = sprintf("%.2f", $ret['total_amt'] - $ret['pay_amt']);
            $ret['status']      = $orderInfo['status'];
            LoggerGearman::logInfo(array('data' => $params, 'identity' => 'cfb.getOrderInfo.' . $ret['order_no']));
        } else {
            LoggerGearman::logDebug(array('data' => $params, 'identity' => 'cfb.getOrderInfo.err'));
            throw new BaseException('订单不存在');
        }
        return $ret;
    }
    
    public function finishPay($params) {
        return CfbPayForInterface::finishPay($params);
    }
}
