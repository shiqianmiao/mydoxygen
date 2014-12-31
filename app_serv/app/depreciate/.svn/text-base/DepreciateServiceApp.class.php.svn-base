<?php
/**
 * 我要出价（降价通知）接口
 * @author 王煜
 * @version 1.0
 * @date 2014-11-11
 */
require_once API_PATH . '/interface/CarSaleInterface.class.php';
require_once API_PATH . '/interface/car/CarDepreciateNoticeInterface.class.php';
require_once API_PATH . '/interface/mbs/MbsUserInterface2.class.php';
require_once API_PATH . '/interface/car/CarUserUuidInterface.class.php';

class DepreciateServiceApp {

    private static $_CARS = array();

    /**
     * 提交我要出价信息
     * @param  : 参数说明如下表格
     * 参数名称                 | 参数类型         | 参数补充描述
     * -----------------|----------|------------------------------------------------
     * car_id           | int      | 车源ID，必选
     * mobile           | string   | 手机，必选
     * price            | int      | 价格（单位：元），必选
     * source           | int      | 来源，默认值为2，可选
     * @return bool
     * 返回值名称              | 返回值类型     | 返回值补充描述
     * -------------|----------|------------------------------------------------
     * return       | bool     | 成功则返回true
     */
    public function submit($params) {
        $mobile = $params['mobile'];
        $price  = $params['price'];
        $carId  = (int) $params['car_id'];
        //来源：2主站app
        $source = isset($params['source']) ? (int) $params['source'] : 2;
        $time = time();

        //@TODO:安全验证
        //if ($attack) {
        //    include_once FRAMEWORK_PATH . '/util/gearman/LoggerGearman.class.php';
        //    LoggerGearman::logProb(array(
        //        'data'      => 'ip:' . RequestUtil::getIp(false) . ' phone:' . $mobile . ' ua:' . $_SERVER['HTTP_USER_AGENT'],
        //        'identity'  => 'web_attack_log',
        //        'prob'      => 0.001
        //    ));
        //    throw new AppServException(AppServErrorVars::CUSTOM, '操作非法');
        //}

        if ($carId) {
            $carInfo = self::_getCarInfoById($carId);
        }
        $priceOk  = $price > ($carInfo['price'] * 0.7) && $price <= $carInfo['price'];
        $mobileOk = preg_match('/^1[3458]\d{9}$/', $mobile);
        if (empty($carInfo) || !$priceOk || !$mobileOk) {
            throw new AppServException(AppServErrorVars::CUSTOM, '参数有误');
        }

        $existInfo = $this->_getDepreciateRow($carId, $mobile);
        try {
            if (!empty($existInfo['id'])) { //update
                $updateInfo = array();
                //距上次发送已超过24小时才通知跟单业务员
                if ($existInfo['last_send_time'] + 86400 < $time) {
                    $this->_sendToFollower($carId, $mobile, $price);
                    $updateInfo['last_send_time'] = $time;
                    $updateInfo['send_count']     = 'send_count + 1';
                }
                $updateInfo['price']        = $price;
                $updateInfo['update_time']  = $time;
                $updateInfo['save_count']   = 'save_count + 1';
                $updateInfo['source']       = $source;
                $ret = CarDepreciateNoticeInterface::updateInfo(array(
                    'info'    => $updateInfo,
                    'filters' => array(
                        array('id', '=', $existInfo['id']),
                    ),
                ));
            } else { //insert
                $this->_sendToFollower($carId, $mobile, $price);
                $info = array(
                    'car_id'  => $carId,
                    'mobile'  => $mobile,
                    'last_send_time'  => $time,
                    'send_count'      => 1,
                    'price'           => $price,
                    'current_price'   => (int) $carInfo['price'],
                    'source'          => $source
                );
                $ret = CarDepreciateNoticeInterface::insertInfo(array(
                    'info' => $info
                ));
            }
        } catch(Exception $e) {
            throw new AppServException(AppServErrorVars::CUSTOM, '操作失败');
        }

        return true;
    }

    /**
     * 根据车源id和出价者的手机获取一条我要出价的记录
     */
    private function _getDepreciateRow($carId, $mobile) {
        return CarDepreciateNoticeInterface::getRow(array(
            'filters'   => array(
                array('car_id', '=', $carId),
                array('mobile', '=', $mobile),
            ),
        ));
    }

    /**
     * 发送短信通知跟单业务员
     */
    private function _sendToFollower($carId, $mobile, $price, $sid = 0) {
        $carInfo  = self::_getCarInfoById($carId);
        $username = (int) $carInfo['follow_user_id'];
        if (!$username) {
            throw new AppServException(AppServErrorVars::CUSTOM, '无法获取跟单业务员id');
        }

        $userInfo = MbsUserInterface2::getInfoByUser(array(
            'username'   => $username,
        ));
        if (empty($userInfo) || empty($userInfo['mobile'])) {
            throw new AppServException(AppServErrorVars::CUSTOM, '无法获取跟单业务员手机');
        }

        $price     = sprintf('%.2f', $price / 10000);
        $brandInfo = VehicleV2Interface::getBrandById(array(
            'brand_id'    => $carInfo['brand_id'],
        ));
        $seriesInfo = VehicleV2Interface::getSeriesById(array(
            'series_id'   => $carInfo['series_id'],
        ));
        $carCaption = "{$brandInfo['name']}{$seriesInfo['name']}";
        $text       = "{$userInfo['real_name']}，你好！电话为{$mobile}的客户关注了编号为{$carId}的{$carCaption}。意愿价格为{$price}万元。【273二手车】";
        //@TODO delete 测试环境统一发送到开发者手机上
        if (DEBUG_STATUS) {
            $text = "测试{$userInfo['mobile']}-{$userInfo['real_name']}，你好！电话为{$mobile}的客户关注了编号为{$carId}的{$carCaption}。意愿价格为{$price}万元。【273二手车】";
            $userInfo['mobile'] = '15059130243';
        }
        return SmsMobileInterface::send(array(
            'phone_list'   => $userInfo['mobile'],
            'content'      => $text,
            'server_id'    => 22,
        ));
    }

    /**
     * 根据车源id获取车源信息，并保存到属性中
     */
    private function _getCarInfoById($id) {
        $id = (int) $id;
        if (!$id) {
            return array();
        }
        if (isset(self::$_CARS[$id])) {
            return self::$_CARS[$id];
        }
        return self::$_CARS[$id] = CarSaleInterface::getCarInfoById(array(
            'id' => $id
        ));
    }

    /**
     * 获取发送次数,使用redis hash结构记录每天短信发送次数
     * 暂时没用到
     */
    private function _getSendCount($mobile, $carId) {
        //hash结构对应的key
        $redisKey = 'web_pc_detail_depreciate_hash';
        include_once FRAMEWORK_PATH . '/util/redis/RedisClient.class.php';
        $redisClient = new RedisClient(RedisConfig::$REDIS_CAR);
        $redis = $redisClient->getMasterRedis();
        if (empty($redis)) {
            //redis创建失败时，返回默认计数为1
            return 1;
        } else {
            $exist    = $redis->exists($redisKey);
            $fieldKey = $mobile . '_' . $carId;
            //对应手机号码与车源id key增1
            $count = $redis->hIncrBy($redisKey, $fieldKey, 1);
            //第一次设置时要给hash结构设置过期时间
            if (!$exist) {
                //失效时间：第二天0点
                $time = strtotime(date('Y-m-d', time())) + 3600 * 24;
                $redis->expireAt($redisKey, $time);
            }
            return $count;
        }
    }

} //class end
