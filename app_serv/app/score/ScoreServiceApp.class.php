<?php
/**
 * @brief         积分排名远程接口
 * @author        daiyuancheng<daiyc@273.cn>
 * @date          2013-6-28
 */

require_once API_PATH . '/interface/MbsScoreInterface.class.php';
require_once API_PATH . '/interface/ScoreInfoInterface.class.php';
require_once FRAMEWORK_PATH . '/util/cache/CacheNamespace.class.php';
require_once CONF_PATH . '/cache/MemcacheConfig.class.php';

class ScoreServiceApp {

    const MEM_KEY_USER_RANK = 'user_score_rank_list_p%s_c%s_t%sv4_4'; // province city rank_type
    const MEM_KEY_DEPT_RANK = 'dept_score_rank_list_p%s_c%sv4_4'; // province city rank_type
    const MEM_KEY_USER_INFO = 'user_score_info_rank_u%sv4_4';       // userid

    static $DURATION = 18000; // 5小时

    /**
     * 获取用户排名信息
     * @param array $params
     */
    public function getUserRankInfo($params) {
        if (isset($params['user_id']) && $params['user_id']) {
            $memKey = sprintf(self::MEM_KEY_USER_INFO, $params['user_id']);
            $memcached = CacheNamespace::createCache(CacheNamespace::MODE_MEMCACHE, MemcacheConfig::$GROUP_WEB);
            if ($res = $memcached->read($memKey)) {
                return $res;
            } else {
                // 成交信息
                $res =  MbsScoreInterface::getUserScore(array(
                    'user_id'  => $params['user_id'],
                ));
                // 取当前车源数
                list ($scoreInfo) = ScoreInfoInterface::getScoreInfo(array(
                    'field'   => 'car_num, complain',
                    'filters' => array(array('username', '=', $params['user_id'])),
                    'role_id' => false
                ));
                unset($res['car_rank']);
                $res['complaint_count'] = $scoreInfo['complain'];
                $res['car_num'] = $scoreInfo['car_num'];

                $memcached->write($memKey, $res, self::$DURATION);
                return $res;
            }
        }
        return array();
    }

    public function getUserComplaintCount($params) {
        if (isset($params['user_id']) && $params['user_id']) {
            return MbsScoreInterface::getScoreDetailCount(array(
                'user_id'  => $params['user_id'],
                'type'     => 3,
            ));
        }
        return -1;
    }

    /**
     * 获取排名列表rank_type指定排名类型
     * @param unknown_type $params
     */
    public function getUserOtherRankList($params) {
        if (isset($params['rank_type'])) {
            $limit = 5;
            $orderby = array();
            $filtersArray = array();
            if (isset($params['city_id']) && $params['city_id']) {
                $filtersArray[] = array('city_id', '=', $params['city_id']);
            }
            if (isset($params['province_id']) && $params['province_id']) {
                $filtersArray[] = array('province_id', '=', $params['province_id']);
            }
            if (isset($params['limit'])) {
                $limit = $params['limit'];
            }

            //成交数
            if ($params['rank_type'] == 'sale_rank') {
                $orderby = array('sale_num' => 'desc');
            } elseif ($params['rank_type'] == 'car_rank') {
                $orderby = array('car_num' => 'desc');
            } else if ($params['rank_type'] == 'score') {
                $orderby = array('score'  => 'desc');
            }

            $memKey = sprintf(self::MEM_KEY_USER_RANK, (int) $params['province_id'], (int) $params['city_id'], $params['rank_type']);
            $memcached = CacheNamespace::createCache(CacheNamespace::MODE_MEMCACHE, MemcacheConfig::$GROUP_WEB);
            if ($res = $memcached->read($memKey)) {
                return $res;
            } else {
                $res = ScoreInfoInterface::getScoreInfo(array(
                    'field'   => 'username user_id, car_num, sale_num, score, ext_phone',
                    'filters' => $filtersArray,
                    'order'   => $orderby,
                    'limit'   => $limit,
                ));

                if ($res) {
                    $memcached->write($memKey, $res, self::$DURATION);
                }
                return $res;
            }
        }
        return array();
    }

    /**
     * 获取门店排名（列表）
     */
    public function getDeptRankList($params) {
        $filters = array();
        if (isset($params['province_id'])) {
            $filters['province_id'] = $params['province_id'];
        }
        if (isset($params['city_id'])) {
            $filters['city_id'] = $params['city_id'];
        }
        if (isset($params['dept_id'])) {
            $filters['dept_id'] = $params['dept_id'];
        }
        if (isset($params['limit'])) {
            $filters['limit'] = $params['limit'];
        }
        $filters['orderby'] = array('score' => 'desc');

        $memKey = sprintf(self::MEM_KEY_DEPT_RANK, (int)$filters['province_id'], (int)$filters['city_id']);
        $memcached = CacheNamespace::createCache(CacheNamespace::MODE_MEMCACHE, MemcacheConfig::$GROUP_WEB);
        if ($res = $memcached->read($memKey)) {
            return $res;
        } else {
            $res = MbsScoreInterface::getDeptScore($filters);
            $memcached->write($memKey, $res, self::$DURATION);
            return $res;
        }
    }
}
