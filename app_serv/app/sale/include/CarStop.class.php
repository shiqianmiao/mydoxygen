<?php
class CarStop {
    public static function stop($params) {
        // 适配参数，符合接口标准
        $stopType = ( int ) $params ['stop_type'];
        $user = AppServAuth::$userInfo ['user'];
        $carId = ( int ) $params ['car_id'];
        $stopReason = $params ['stop_reason'];
        
        if ($params ['type'] == 'sale') {
            //卖车申请终止
            $interfaceParams = array(
                'car_id'     => $carId,
                'user_info'  => $user,
                'ext_params' => array(
                    'stop_type' => $stopType,
                    'stop_reason' => $stopReason,
                ),
            );
            $result = SaleOperationInterface::sponsorCarStopForMobile($interfaceParams);
            if ($result['errorCode']) {
                $msg = !empty($result['msg']) ? $result['msg'] : '提交终止失败';
                throw new AppServException(AppServErrorVars::CUSTOM, $msg);
            }
        } else {
            include_once API_PATH . '/interface/mbs/BuyOperationInterface.class.php';
            // 买车终止
            $interfaceParams = array(
                'car_id'     => $carId,
                'user_info'  => $user,
                'ext_params' => array(
                    'stop_type' => $stopType,
                    'stop_reason' => $stopReason,
                ),
            );
            $result = BuyOperationInterface::sponsorCarStopForMobile($interfaceParams);
            if ($result['errorCode']) {
                $msg = !empty($result['msg']) ? $result['msg'] : '提交终止失败';
                throw new AppServException(AppServErrorVars::CUSTOM, $msg);
            }
        }
        
        return 1;
    }
}



