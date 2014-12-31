<?php
/**
 * @desc 订单支付相关接口
 * @author miaoshiqian
 * @since 2014-7-22
 */
require_once API_PATH . '/interface/CfbOrderInterface.class.php';
require_once API_PATH . '/interface/mbs/MbsDeptInterface2.class.php';
require_once API_PATH . '/interface/CfbContractInterface.class.php';

class PaymentServiceApp {
    /**
     * @desc 获取当前门店订单列表
     * @param state - 0=取全部列表；1=取待支付列表
     * @return array()
     */
     public function getOrderList($params) {
        //如果没传就默认去门店所以的订单列表
        $state = !empty($params['state']) ? $params['state'] : 0;
        if (!empty($state) && !in_array($state, array(0, 1))) {
            throw new AppServException(AppServErrorVars::CUSTOM, '获取类型有误');
        }
        
        $deptId = AppServAuth::$userInfo['user']['dept_id'];
        $cond = array(
            'dept_id' => $deptId,
        );
        if (!empty($state) && $state == 1) {
            $cond['status'] = '0,1'; //部分付款 和 待付款
        }
        
        $orderParams = array(
            'cond' => $cond,
            'limit' => !empty($params['limit']) ? $params['limit'] : 10,
            'offset' => !empty($params['offset']) ? $params['offset'] : 0,
        );
        $orderList = CfbOrderInterface::getOrderList($orderParams);
        
        return $this->_formatOrderList($orderList);
     }
     
     /**
      * @desc 搜索订单
      */
     public function searchOrderList($params) {
         if (empty($params['keyword'])) {
            return array();
         }
         $keyWord = $params['keyword'];
         
         include FRAMEWORK_PATH . '/util/form/rule/RegExpRuleConfig.class.php';
         $cond = array();
         if (preg_match(RegExpRuleConfig::PHP_CAR_NUMBER, $keyWord)) {
            //搜索车牌号
            $cond['car_number'] = $keyWord;
         } else {
            //搜索订单号
            $cond['order_no'] = $keyWord;
         }
         
         $orderParams = array(
            'cond' => $cond,
            'limit' => !empty($params['limit']) ? $params['limit'] : 10,
            'offset' => !empty($params['offset']) ? $params['offset'] : 0,
        );
        $orderList = CfbOrderInterface::getOrderList($orderParams);
        
        return $this->_formatOrderList($orderList);
     }
     
     /**
      * @格式化订单列表，返回客户端需要的内容
      */
     private function _formatOrderList($orderList) {
        $orderStatus = array(
            0 => '待收款',
            1 => '部分收款',
            2 => '等待过户确认',
            3 => '已收款完毕',
            4 => '已申请待财务付款',
            5 => '已过户等待放款',
            6 => '财务已付款',
            7 => '退款质检处理中',
            8 => '连锁中心审核中',
            9 => '财务审核中',
            10 => '已退款',
            99 => '订单取消',
        );
        if (empty($orderList) || !is_array($orderList)) {
            return array();
        }
        
        $ret = array();
        
        //获取慧支付绑定的号码，@see user.getUserInfo->hzf_mobile
        $userinfo = AppServAuth::$userInfo['user'];
        $deptId = $userInfo['dept_id'];
        $roleId = $userInfo['role_id'];
        if(in_array($roleId, array(26, 27))) {
            $userInfoArr = MbsDeptInterface2::getDeptInfoById(array('id' => $deptId));
        }
        
        foreach ($orderList as $order) {
            $ret[] = array(
                'total' => $order['fee'] / 100,
                'pays'  => $order['paid_fee'] / 100,
                'remains' => ($order['fee'] - $order['paid_fee']) / 100,
                'status'  => isset($orderStatus[$order['status']]) ? $orderStatus[$order['status']] : '',
                'plate'   => $order['car_number'],
                'order_id' => $order['order_no'],
                'order_time' => !empty($order['create_time']) ? date("Y-m-d", $order['create_time']) : '',
                'mobile' => !empty($userInfoArr['hzf_mobile']) ? $userInfoArr['hzf_mobile'] : '',
            );
        }
        
        return $ret;
     }
     
     /**
      * @desc 添加订单
      */
     public function newOrder($params) {
         $carNumber = !empty($params['plate']) ? $params['plate'] : '';
         $total = !empty($params['total']) ? $params['total'] : '';
         if (empty($carNumber) || empty($total)) {
            throw new AppServException(AppServErrorVars::CUSTOM, '参数缺失'); 
         }
         
         $orderParams = array(
             'car_number' => $carNumber,
             'fee'        => $total,
             'dept_id'    => AppServAuth::$userInfo['user']['dept_id'],
             'user_id'    => AppServAuth::$userInfo['user']['username'],
         );
         $orderInfo = CfbContractInterface::create($orderParams);
         $orderNum = !empty($orderInfo['order_no']) ? $orderInfo['order_no'] : '';
         return array('order_id' => $orderNum);
     }
}
