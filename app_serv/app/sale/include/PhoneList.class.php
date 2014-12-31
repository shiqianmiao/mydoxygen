<?php
require_once API_PATH . '/interface/PhoneCallLogInterface.class.php';
class PhoneList {
    /**
     *@brief    获取我的来电列表和店内来电列表
     *1.格式化查询参数
     *2.调用接口PhoneCallLogInterface
     *3.格式化输出结果
     */
    
    public static function getPhoneList($query) {
        $info = array();
        if ($query['query_type'] == 'store') {
            if ($query['salesman']) {
                $info['username'] = $query['salesman'];
            } else {
                $deptId = AppServAuth::$userInfo['user']['dept_id'];
                $info['dept_id'] = $deptId;
            }
        } elseif ($query['query_type'] == 'user') {
            $info['username'] = AppServAuth::$userInfo['user']['username'];
        }
    
        if ($query['lasting'] && is_numeric($query['lasting'])) {
            //当$query['lasting']为-1时查询未接通的来电
            if ($query['lasting'] == -1) {
                $info['calledtotallen'] = 0;
            }else {
                $info['calledtotallen'] = $query['lasting'];
            }
        }
        
        $callingStart = time() - 3600 * 24 * 30;
        if ($query['time']) {
            $timeArray = explode('_', $query['time']);
            $info['calling_start'] = strtotime($timeArray[0]);
            $info['calling_start'] = ($info['calling_start'] < $callingStart) ? $callingStart : $info['calling_start'];
            $info['calling_end'] = strtotime($timeArray[1]);
        } else {
            $info['calling_start'] = time() - 3600 * 24 * 30;
        }
        if ($query['make_code']) {
            $brandInfo = VehicleV2Interface::getBrandByCode(array('code' => $query['make_code']));
            $brandId = !empty($brandInfo['id']) ? $brandInfo['id'] : 0;
            $info['brand_id'] = $brandId;
        }
        if ($query['family_code']) {
            $seriesInfo = VehicleV2Interface::getSeriesByCode(array('code' => $query['family_code']));
            $seriesId = !empty($seriesInfo['id']) ? $seriesInfo['id'] : 0;
            $info['series_id'] = $seriesId;
        }
        if ($query['vehicle_type']) {
            $typeId = Cheyou::$CAR_TYPE[$query['vehicle_type']];
            $info['car_type'] =$typeId;
        }
        if ($query['brand_id']) {
            $info['brand_id'] =$query['brand_id'];
        }
        if ($query['series_id']) {
            $info['series_id'] = $query['series_id'];
        }
        if ($query['type_id']) {
            $info['type_id'] = $query['type_id'];
        }
        $limit['rowFrom'] = $query['start'];
        $limit['rowTo'] = $query['end'];
        $info['order_by'] = ' p.calling_time desc ';
        $data = PhoneCallLogInterface::getPhoneInfoByCondition($info, $limit);
        if (empty($data)) {
            return array();
        }
        foreach($data as $k =>$v) {
            $data[$k]['follow_user'] = $v['follow_user_id'] ? $v['follow_user_id'] : '';
            $user = MbsUserInterface::getInfoByUser(array('username' => $v['follow_user_id']));
            $data[$k]['follow_user_name'] = empty($user['real_name'])?'':$user['real_name'];
            $data[$k]['call_phone'] = $v['caller'];
            $data[$k]['title'] = $v['title'] ? $v['title'] : '';
            $data[$k]['photo'] = $v['cover_photo'];
            $data[$k]['card_time'] = $v['card_time'] ? $v['card_time'] : '';
            $data[$k]['calling_time'] = date('Y-m-d H:m:s', $v['calling_time']);
            unset($data[$k]['cover_photo']);
            unset($data[$k]['caller']);
            unset($data[$k]['follow_user_id']);
            unset($data[$k]['sms_satisfy']);
            unset($data[$k]['satisfy']);
            unset($data[$k]['dept_id']);
            unset($data[$k]['auto_id']);
        }
        return $data;
    }
    
    /**
     * @brief 查询是否为欠费
     *
     */
    private static function _getBalance() {
        $accountInfo = AccountInterface::get(array('fields'=>'*', 'cond'=>array('from_app'=>'backend','dept_id' => AppServAuth::$userInfo['user']['dept_id'])));
        $creditInfo = CreditInterface::get(array('fields'=>'*', 'cond'=>array('account_id'=>$accountInfo['id'],'source_id' => PaymentConfig::$app['backend']['phone']['source_id'])));
        $balance = sprintf("%.2f", ($accountInfo['available_balance'] + $creditInfo['available_balance'])/100);
        if (!$balance) {
            $balance = 0;
        }
        return $balance;
    }
    /**
     * @brief 格式化PhoneList
     * 1.查询是否为欠费状态
     * 2.由follow_user获取follow_user_name
     * 3.删除不需要的字段，只下发有用的字段
     * 4.格式化图片url
     * 5.格式化通话记录url
     *
     */
    public static function _formatPhoneList($data) {
        $balance = 0;
        //是否为欠费状态
        $balance = self::_getBalance();
        $cacheHandle = CacheNamespace::createCache(CacheNamespace::MODE_MEMCACHE, MemcacheConfig::$GROUP_DEFAULT);
        foreach($data as $k => $v) {
            //获取follow_user_name
            if(!empty($v['follow_user'])) {
                $user = MbsUserInterface::getInfoByUser(array('username' => $v['follow_user']));
            }
            $ret['follow_user_name'] = empty($user['real_name'])?'':$user['real_name'];
    
            //不需要的字段不下发，省流量，省时间
            unset($data[$k]['called']);
            unset($data[$k]['card_time']);
            unset($data[$k]['price']);
//             unset($data[$k]['follow_user']);
            //图片url
            $data[$k]['photo'] = CarUtil::buildPhoto($data[$k]['photo']);
            $data[$k]['is_self'] = $data[$k]['follow_user'] == AppServAuth::$userInfo['user']['username'] ? 1 : 0;
            if (strpos($data[$k]['photo'], '120-90_6_0_0_120-90_6_0_0')) {
                $data[$k]['photo'] = str_replace('120-90_6_0_0_120-90_6_0_0', '120-90_6_0_0', $data[$k]['photo']);
            }
    
            //通话记录url
            if (isset($data[$k]['file_path'])) {
                $file = str_replace('d:\recwav', '', $data[$k]['file_path']);
                $file = str_replace('e:\recwav', '', $file);
                $file = str_replace('\\', '/', $file);
                $file = "http://fzct.273.cn:90" . $file;
                if ($balance < -50) {
                    $file = '';
                    $data[$k]['call_phone'] = substr($data[$k]['call_phone'],0,strlen($data[$k]['call_phone'])-4) . '****';
                    $data[$k]['call_lasting'] = 0;
                }
                $data[$k]['audio_URL'] = $file;
    
                //获取电话号码归属地
                if($balance >= -50) {
                    $data[$k]['call_phoneAddr'] = CarUtil::getNameByTel($data[$k]['call_phone']);
                }
                unset($data[$k]['file_path']);
            }
        }
        return $data;
    }
    
}



