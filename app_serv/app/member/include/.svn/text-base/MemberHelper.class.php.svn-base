<?php
/**
 * 会员辅助类
 * @author 林祥水<linxs@273.cn>
 * @Copyright (c) 2003-2014 273 Inc. (http://www.273.cn/)
 * @since 2014-11-5
 **/

class MemberHelper {

    /**
     * 创建reids
     *
     */
    public static function _createRedis() {
        require_once FRAMEWORK_PATH . '/util/redis/RedisClient.class.php';
        require_once CONF_PATH . '/cache/RedisConfig.class.php';
        $redisClient = new RedisClient(RedisConfig::$REDIS_CAR);
        return $redis = $redisClient->getMasterRedis();
    }
    /**
     * 设置redis值
     * @param $params
     */
    public static function _setRedis($key, $expireTime, $info) {
        require_once FRAMEWORK_PATH . '/util/redis/RedisClient.class.php';
        require_once CONF_PATH . '/cache/RedisConfig.class.php';
        $redisClient = new RedisClient(RedisConfig::$REDIS_CAR);
        $redis = $redisClient->getMasterRedis();
        $redis->setex($key, $expireTime, json_encode($info));
    }

    /**
     * 获取redis值
     * @param $params
     */
    public static function _getRedis($key) {
        require_once FRAMEWORK_PATH . '/util/redis/RedisClient.class.php';
        require_once CONF_PATH . '/cache/RedisConfig.class.php';
        $redisClient = new RedisClient(RedisConfig::$REDIS_CAR);
        $redis = $redisClient->getMasterRedis();
        $info = $redis->get($key);
        return json_decode($info,true);
    }

    /**
     *@breif 解析passport
     */
    public static function parsePassport($passport) {
        if (empty($passport)) {
            return false;
        }
        require_once API_PATH . '/interface/car/CarMemberV2Interface.class.php';
        $passport = urldecode($passport);
        include_once FRAMEWORK_PATH . '/util/des/DesUtil.class.php';
        $key = '1d387351b3023e0e';
        $iv = 'dfh^&(89';
        $encrypt = DesUtil::create(DesUtil::MODE_3DES, $key, $iv);
        $text = $encrypt->decrypt($passport);
        list($username, $passwd, $createTime, $checkTime) = explode('-', $text);
        $userInfo = CarMemberV2Interface::getInfoByUsername(array('username' => $username));
        if ($userInfo['passwd']==$passwd) {
            return $userInfo;
        }
        return false;
    }

    /**
     *@breif 获取passport
     */
    public static function getPassport($username, $passwd, $createTime, $checkTime) {
        include_once FRAMEWORK_PATH . '/util/des/DesUtil.class.php';
        $key = '1d387351b3023e0e';
        $iv = 'dfh^&(89';
        if (!$createTime) {
            $createTime = time();
        }
        if (!$checkTime) {
            $checkTime = $createTime;
        }
        $text = $username . '-' . $passwd . '-' . $createTime . '-' . $checkTime;
        $encrypt = DesUtil::create(DesUtil::MODE_3DES, $key, $iv);
        return urlencode($encrypt->encrypt($text));
    }
}
