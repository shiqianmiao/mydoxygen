<?php
require_once FRAMEWORK_PATH . '/util//redis/RedisClient.class.php';
require_once CONF_PATH . '/cache/RedisConfig.class.php';
/**
 * @package              V3
 * @author               chenchaoyang
 * @file                 $HeadURL$
 * @desc                 页面浏览量统计
 * @version              $Rev$
 * @lastChangeBy         $LastChangedBy$
 * @lastmodified         $LastChangedDate$
 * @copyright            Copyright (c) 2012, www.273.cn
 */

class PageServiceApp {
    
    private static $redis;
    private $keyPrefix = 'page_view_count';
    
    public function __construct() {
        $redisClient = new RedisClient(RedisConfig::$REDIS_CAR);
        self::$redis = $redisClient->getMasterRedis();
    }
    
    /**
     * 根据页面id从redis获取页面浏览量
     * @param 页面id $id
     * @return $num:浏览量;-1:redis错误
     */
    public function getViewCount($params) {
        if (isset($params['id'])) {
            $key = $this->_createKey($params['id']);
            if (!empty(self::$redis) && self::$redis instanceof Redis) {
                $val = self::$redis->get($key);
                if (!empty($val)) {
                    return $val;
                } else {
                    return 0;
                }
            }
        }
        return -1;
    }
    
    /**
     * 更新帖子浏览量
     * @param 帖子id $id
     * @param 浏览量值 $val
     * @param 过期时间 $expire，不设置默认永不过期
     * @return boolean
     */
    public function setViewCount($params) {
        if (isset( $params['id']) && isset($params['value'])) {
            $id = $params['id'];
            $val = $params['value'];
        } else {
            return false;
        }
        $expire = isset($params['expire']) ? $params['expire'] : null;
        $key = $this->_createKey($id);
        if (!empty(self::$redis) && self::$redis instanceof Redis) {
            if ($expire) {
                self::$redis->setex($key, $expire, $val);
            } else {
                self::$redis->set($key, $val);
            }
            return true;
        }
        return false;
    }
    
    private function _createKey($id){
        return $this->keyPrefix.'_'.$id;
    }
}
?>