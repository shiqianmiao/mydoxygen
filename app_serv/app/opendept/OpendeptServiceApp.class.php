<?php
ini_set('display_errors', 1);
error_reporting(1);
require_once FRAMEWORK_PATH . '/util/redis/RedisClient.class.php';
require_once CONF_PATH . '/cache/RedisConfig.class.php';
require_once API_PATH . '/interface/CarSaleInterface.class.php';
class OpendeptServiceApp {
    public function getUserData($params) {
        if (empty($params['username'])) {
            throw new AppServException(AppServErrorVars::CUSTOM, '缺少业务员账号username参数');
        }
        $username = $params['username'];
        $fix1 = 'appserv_opendept_key_';
        $key = $fix1 . $username;
        $redisClient = new RedisClient(RedisConfig::$REDIS_CAR);
        $redis = $redisClient->getMasterRedis();
        $result = $redis->get($key);
        $result = json_decode($result, true);
        if (empty($result)) {
            throw new AppServException(AppServErrorVars::CUSTOM, '无此业务员数据');
        }
        $ret = array();
        $ret['rank'] = $result['rank'];
        $ret['car_total'] = $result['car_total'];
        $ret['car_month'] = $result['car_month'];
        $ret['car_pv'] = $result['car_pv'];
        $ret['refresh_num'] = $result['refresh_num'];
        $ret['call_num'] = $result['call_num'];
        $ret['car_look'] = $result['car_look'];
        $ret['commission'] = $result['commission'];
        return $ret;
    }

    public function getDeptData($params) {
        if (empty($params['dept_id'])) {
            throw new AppServException(AppServErrorVars::CUSTOM, '缺少门店ID参数');
        }
        
        if (empty($params['statDate']) && empty($params['endDate'])) {
            //不传这2个参数，默认从本月1号开始到目前
            $fix = 'appserv_opendept_users_';
            $fix1 = 'appserv_opendept_key_';
            $deptId = $params['dept_id'];
            $redisClient = new RedisClient(RedisConfig::$REDIS_CAR);
            $redis = $redisClient->getMasterRedis();
            $userKey = $fix . $deptId;
            $users = $redis->get($userKey);
            $users = json_decode($users, true);
            $ret = array();
            $row = array();
            foreach($users as $user) {
                $key = $fix1 . $user;
                $row = $redis->get($key);
                $row = json_decode($row, true);
                $ret[] = $row;
            }
            return $ret;
        } else { //下面的逻辑性能较差，不建议使用
            $monthTime = mktime(0, 0, 0, date('m'), 1, date('Y'));
            $statDate = !empty($params['statDate']) ? strtotime($params['statDate']) : $monthTime;
            $endDate  = !empty($params['endDate']) ? strtotime($params['endDate']) + (24 * 3600) : time();
            if ($statDate > $endDate) {
                return array();
            }
            $filters = array(
                array('dept_id', '=', $params['dept_id']),
                array('report_date', '>=', $statDate),
                array('report_date', '<=', $endDate),
            );
            include_once API_PATH . '/interface/mbs/MbsShopReportInterface.class.php';
            $params = array(
                'fields'  => 'id,username,real_name,car_total,call_count,look_car_count,grade',
                'filters' => $filters,
                'order'   => array('id' => 'desc'),
            );
            $reportList = MbsShopReportInterface::getListByFilters($params);
            
            $list = array();
            if (!empty($reportList) && is_array($reportList)) {
                foreach ($reportList as $k => $v) {
                    if (isset($list[$v['username']])) {
                        $list[$v['username']]['call_num']   += $v['call_count'];
                        $list[$v['username']]['car_look']   += $v['look_car_count'];
                        $list[$v['username']]['commission'] += $v['grade'];
                    } else {
                        $list[$v['username']] = array(
                            'username'   => $v['username'],
                            'real_name'  => $v['real_name'],
                            'car_total'  => $v['car_total'],
                            'call_num'   => $v['call_count'],
                            'car_look'   => $v['look_car_count'],
                        );
                    }
                }
                
                //门店报表里面没有 car_month，car_pv，refresh_num。需要单独统计
                include_once API_PATH . '/model/MbsUserPvModel.class.php';
                include_once API_PATH . '/model/MbsRefreshLogModel.class.php';
                include_once API_PATH . '/model/MbsTradingModel.class.php';
                include_once API_PATH . '/model/MbsContractModel.class.php';
                
                $pvModel = new MbsUserPvModel();
                $mbsRefreshLogModel = new MbsRefreshLogModel();
                $mbsTradingModel = new MbsTradingModel();
                $mbsContractModel = new MbsContractModel();
                
                foreach ($list as $key => &$info) {
                    //统计本月车源量
                    $username = $info['username'];
                    $params = array(
                        'filters' => array(
                            array('follow_user_id', '=', $username),
                            array('create_time', '>=', $statDate),
                            array('create_time', '<=', $endDate),
                        ),
                    );
                    $info['car_month'] = CarSaleInterface::getCountByFilters($params);
                    //统计外网浏览量
                    $params = array(
                        array('username', '=', $username),
                        array('date', '>=', $statDate),
                        array('date', '<=', $endDate),
                    );
                    $info['car_pv'] = $pvModel->getOne('sum(pv)', $params);
                    //统计刷新次数
                    $params = array(
                        array('follow_user', '=', $username),
                        array('refresh_date', '>=', $statDate),
                        array('refresh_date', '<=', $endDate),
                    );
                    $info['refresh_num'] = $mbsRefreshLogModel->getOne('count(1)', $params);
                    //统计业绩
                    $params = array(
                        array('sale_follow_user', '=', $username),
                        array('insert_time', '>=', $statDate),
                        array('insert_time', '<=', $endDate),
                    );
                    $salePrice = $mbsTradingModel->getOne('sum(sale_gold_price)', $params);
                    $params = array(
                        array('buy_follow_user', '=', $username),
                        array('insert_time', '>=', $statDate),
                        array('insert_time', '<=', $endDate),
                    );
                    $buyPrice = $mbsTradingModel->getOne('sum(buy_gold_price)', $params);
                    $params = array(
                        array('sale_tracer', '=', $username),
                        array('cont_status', '=', 1),
                        array('is_print', '=', 1),
                        array('insert_time', '>=', $statDate),
                        array('insert_time', '>=', $endDate),
                    );
                    $contractSalePrice = $mbsContractModel->getOne('sum(owner_commission)', $params);
                    $params = array(
                        array('buy_tracer', '=', $username),
                        array('cont_status', '=', 1),
                        array('is_print', '=', 1),
                        array('insert_time', '>=', $statDate),
                        array('insert_time', '<=', $endDate),
                    );
                    $contractBuyPrice = $mbsContractModel->getOne('sum(second_commission)', $params);
                    $info['commission'] = (int)((int)$salePrice + (int)$buyPrice + (int)$contractSalePrice + (int)$contractBuyPrice);
                }
                
                //按照业绩排序
                usort($list, array('OpendeptServiceApp', 'arrCmp'));
                foreach ($list as $k => &$v) {
                    $v['rank'] = $k + 1;
                }
            }
            
            return $list;
        }
    }
    
    public function arrCmp($a, $b) {
        if($a['commission'] == $b['commission']){  
            return 0;
        }   
        return($a['commission']>$b['commission']) ? -1 : 1;
    }

    public function getDeptNum($params) {
        if (empty($params['dept_id'])) {
            throw new AppServException(AppServErrorVars::CUSTOM, '缺少门店ID参数');
        }
        $fix = 'appserv_opendept_dept_info_';
        $deptId = $params['dept_id'];
        $redisClient = new RedisClient(RedisConfig::$REDIS_CAR);
        $redis = $redisClient->getMasterRedis();
        $deptKey = $fix . $deptId;
        $deptInfo = $redis->get($deptKey);
        $deptInfo = json_decode($deptInfo, true);
        return $deptInfo;
    }

    public function getDeptList($params) {
        if (empty($params['p_id']) && empty($params['c_id'])) {
            throw new AppServException(AppServErrorVars::CUSTOM, '缺少参数');
        }
        include_once API_PATH . '/interface/MbsDeptInterface.class.php';
        $ret = array();
        if ($params['c_id']) {
           $ret = MbsDeptInterface::getDeptsByCity(array('id' => $params['c_id'])); 
        } else {
           $ret = MbsDeptInterface::getDeptsByProvince(array('id' => $params['p_id']));
        }
        $deptInfo = array();
        foreach ($ret as $row) {
            $dept = array();
            if ($row['id'] == 1354) {
                continue;
            }
            $dept['id'] = $row['id'];
            $dept['name'] = $row['dept_name'];
            $deptInfo[] = $dept;
        }
        return $deptInfo;
    }
    
    public function getUserInfoByUsername($params) {
        if (empty($params['username'])) {
            throw new AppServException(AppServErrorVars::CUSTOM, '缺少参数');
        }
        
        include API_PATH . '/interface/mbs/MbsUserInterface2.class.php';
        $userInfo = MbsUserInterface2::getInfoByUser(array('username' => $params['username']));
        return !empty($userInfo) ? $userInfo : array();
    }
    
    public function getDeptInfoById($params) {
        if (empty($params['dept_id'])) {
            throw new AppServException(AppServErrorVars::CUSTOM, '缺少参数');
        }
        
        include_once API_PATH . '/interface/MbsDeptInterface.class.php';
        $deptInfo = MbsDeptInterface::getDeptInfoByDeptId(array('id' => $params['dept_id']));
        return !empty($deptInfo) ? $deptInfo : array();
    }
    
    public function isLoginMbs($params) {
        if (empty($params['passport'])) {
            throw new AppServException(AppServErrorVars::CUSTOM, '缺少passport参数');
        }
        $passport = urlencode($params['passport']);
        include_once API_PATH . '/interface/SsoInterface.class.php';
        if (!SsoInterface::login(array('passport' => $passport))) {
            return array();
        }
        
        include_once API_PATH . '/../sso/app/include/SsoData.class.php';
        
        $username = SsoData::decodeSsoTicket($passport);
        return array('username' => $username);
    }
}
