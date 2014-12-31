<?php
require_once API_PATH . '/interface/mbs/MbsDefriendInterface.class.php';
require_once API_PATH . '/interface/CarBuySeriesInterface.class.php';
require_once API_PATH . '/interface/MbsDeptConfigInterface.class.php';
class CarBuy {
    public static function submmitBuy($params) {
        $params = self::getParams($params);
        $validate = true;
        //登录者的信息
        $user = AppServAuth::$userInfo['user'];
        
        if ($validate) {
            //验证通过
            $postInfo = $params;
            
            //判断该客户是否被列入屏蔽名单，是的话不保存
            $defriendParams = array('mobile' => array(
                $postInfo['mobile'],
            ));
            if (!empty($postInfo['telephone1'])) {
                $defriendParams['mobile'][] = $postInfo['telephone1'];
            }
            if (!empty($postInfo['telephone2'])) {
                $defriendParams['mobile'][] = $postInfo['telephone2'];
            }
            if (MbsDefriendInterface::checkDefriend($defriendParams)) {
                exit('该号码已经被列入屏蔽名单，请不要再跟进');
            }
            
            $sendMessageToCustomer = false; //是否发短信给客户
            $sendMessageToFollow = false;  //是否发短信给跟单人
            
            $carBuyArr = $customerArr = array(); //要insert到carbuy的信息和customer的信息
            $visitMessageInfo = $checkMessageInfo = array(); //审核和回访事务内容
            $followUserInfo = ''; //该帖子对应于 follow_user_id的用户信息，发送短信时需要使用
            
            $id = RequestUtil::getPost('id');
            if (!empty($id)) {
                $postInfo['id'] = $id;
            }
            
            $carBuyArr = $postInfo;
            $carBuyArr['min_price'] = $postInfo['min_price'] * 10000;
            $carBuyArr['max_price'] = $postInfo['max_price'] * 10000;
            $carBuyArr['model_id'] = $postInfo['year_type'];
            $carBuyArr['title'] = '';
            unset($carBuyArr['year_type']);
            
            $customerArr['real_name'] = $postInfo['buyer_name'];
            
            if (!empty($carBuyArr['type_id']) && !empty($carBuyArr['brand_id'])) {
                $captionParams = array(
                    'brand_id' => $carBuyArr['brand_id'],
                );
                if (!empty($carBuyArr['series_id'])) {
                    $captionParams['series_id'] = $carBuyArr['series_id'];
                }
                if (!empty($carBuyArr['model_id'])) {
                    $captionParams['model_id'] = $carBuyArr['model_id'];
                }
                $carBuyArr['title'] = VehicleV2Interface::getModelCaption($captionParams);
            }
            
            //print_r($postInfo);exit;
            if (empty($postInfo['id'])) {
                //添加信息
                $carBuyArr['create_time'] = $carBuyArr['update_time'] = $customerArr['insert_time'] = time();
                
                $customerArr['mobile'] = $postInfo['telephone'];
                $customerArr['telephone'] = $postInfo['telephone1'];
                $customerArr['telephone2'] = $postInfo['telephone2'];
                $customerArr['province'] = $postInfo['deal_province_id'];
                $customerArr['city'] = $postInfo['deal_city_id'];
                $customerArr['idcard'] = $postInfo['idcard'];
                $customerArr['insert_user_id'] = $customerArr['owner_user_id'] = $user['account'];
                
//                if (!empty($postInfo['idcard'])) {
//                    //如果有提交身份证的话，去mbs customer里面查看是否已经有此人存在了
//                    $repeatParams = array(
//                        'filters' => array(
//                            array('idcard', '=', trim($postInfo['idcard'])),
//                            array('province', '=', $postInfo['deal_province_id']),
//                            array('city', '=', $postInfo['deal_city_id']),
//                        ),
//                    );
//                    try {
//                        $repeatInfo = MbsCustomerInterface::getRowInfo($repeatParams);
//                    } catch (Exception $e) {
//                        //todo 记录日志，
//                        exit('获取重复身份证时异常:'.$e->getMessage());
//                    }
//
//                    if (!empty($repeatInfo) && is_array($repeatInfo)) {
//                        $carBuyArr['store_id'] = $repeatInfo['dept_id'];
//                        $carBuyArr['follow_user_id'] = $repeatInfo['follow_user'];
//                        $carBuyArr['follow_time'] = time();
//                        $carBuyArr['create_user_id'] = $repeatInfo['owner_user_id'];
//                    }
//                }
                $repeatInfo = array();
                    if (RoleInterface::isSaleConsult(array('roleId' => $user['role_id']))) {
                        if (empty($repeatInfo)) {
                            //如果是 业务员，店长，业务主任的话（发的帖子归其自己所有）
                            $carBuyArr['store_id'] = $customerArr['dept_id'] = $user['dept_id'];
                            $carBuyArr['follow_user_id'] = $carBuyArr['create_user_id'] = $customerArr['follow_user'] = $user['account'];
                            $carBuyArr['follow_time'] = $customerArr['follow_time'] = time();
                            $sendMessageToCustomer = true;
                        }
                        $followUserInfo = AppServAuth::$userInfo['user'];
                        $visitMessageInfo['reason'] = 4;
                        $checkMessageInfo['reason'] = 26;
                        $carBuyArr['source_type'] = 13;
                        
                    } else if (RoleInterface::isSecretary(array('roleId' => $user['role_id']))) {
                        if (empty($repeatInfo)) {
                            //店秘自动分配
                            $carBuyArr['store_id'] = $customerArr['dept_id'] = $user['dept_id'];
                            $carBuyArr['create_user_id'] = 0;
                            
                            $autoUserInfo = MbsUserInterface2::getRandUser(array('dept_id' => $user['dept_id'])); //自动分配给的那个用户的信息
                            
                            $carBuyArr['follow_user_id'] = $customerArr['follow_user'] = $autoUserInfo['username'];
                            $carBuyArr['follow_time'] = $customerArr['follow_time'] = time();
                            
                            $followUserInfo = $autoUserInfo;
                            $sendMessageToCustomer = true;
                            $sendMessageToFollow = true;
                        }
                        $visitMessageInfo['reason'] = 3;
                        $carBuyArr['source_type'] = 3;
                        
                    } else if (RoleInterface::isCallCenterUser(array('roleId' => $user['role_id']))) {
                        //呼叫中心人员
                        $carBuyArr['source_type'] = 8;
                        $visitMessageInfo['reason'] = 3;
                        $checkMessageInfo['reason'] = 27;
                        
                        if (empty($repeatInfo)) {
                            $autoParams = array(
                                'province' => $postInfo['deal_province_id'],
                                'city' => $postInfo['deal_city_id'],
                            );
                            $autoDeptId = MbsDeptInterface2::getRandDept($autoParams);
                            
                            if ($autoDeptId) {
                                $carBuyArr['store_id'] = $customerArr['dept_id'] = $autoDeptId;
                                $carBuyArr['create_user_id'] = 0;
                                
                                $autoUserInfo = MbsUserInterface2::getRandUser(array('dept_id' => $autoDeptId)); //自动分配给的那个用户的信息
                                
                                $carBuyArr['follow_user_id'] = $customerArr['follow_user'] = $autoUserInfo['username'];
                                $carBuyArr['follow_time'] = $customerArr['follow_time'] = time();
                                
                                $followUserInfo = $autoUserInfo;
                                $sendMessageToCustomer = true;
                                $sendMessageToFollow = true;
                            } else {
                                //获取自动分配的门店id失败，记录日志；todo
                            }
                        }
                    } else {
                        //保存成功,旧代码并未在此处执行什么
                    }
                //}
                //print_r($carBuyArr);exit;
                //保存到mbs_customer并返回id
                if (empty($repeatInfo)) {
                    $customerId = MbsCustomerInterface::customerSave(array('info' => $customerArr));
                } else {
                    $customerId = $repeatInfo['id'];
                }
                //var_dump($customerId);exit;
                $carBuyArr['customer_id'] = $customerId;
                $carBuyId = CarBuyInterface::insertInfo(array('info' => $carBuyArr));

                if ($carBuyId) {
                    // 手机也一次只能 插入一个车系
                    if (!empty($carBuyArr['brand_id'])) {
                        $insertArr = array(
                            'car_buy_id' => $carBuyId,
                            'brand_id' => $carBuyArr['brand_id'],
                            'series_id' => $carBuyArr['series_id'],
                            'isdefault' => CarBuySeriesInterface::ISDEFAULT,
                        );
                        CarBuySeriesInterface::add($insertArr);
                    }

//                    // 新增审核事务
                    $deptConfig = MbsDeptConfigInterface::getDeptConfig($user['dept_id']);
//  ($deptConfig['buyer_audit'] == MbsDeptConfigInterface::$BUYER_AUDIT['ab_check']) && !in_array($this->_buyerInfo['buyer_rank'], array(CarBuyInterface::BUYER_RANK_A, CarBuyInterface::BUYER_RANK_B)
                    //  手机业管没有等级制度， 只有设置无需审核的选项 才有效
                    if ($deptConfig['buyer_audit'] == MbsDeptConfigInterface::$BUYER_AUDIT['no_check']) {
                        $updateParams = array(
                            'updateInfo' => array('status' => CarBuyInterface::$STATUS['check']),
                            'filters' => array(array('id', '=', $carBuyId))
                        );
                        CarBuyInterface::updateBuyInfo($updateParams);
                    } else {
                        //添加审核事务
                        $checkMessageInfo['car_id'] = $carBuyId;
                        $checkMessageInfo['create_id'] = $user['username'];
                        $checkMessageInfo['type'] = 1;
                        $checkMessageInfo['dept_id'] = $followUserInfo['dept_id'];
                        if ($followUserInfo['role_id'] == 27) {
                            $checkMessageInfo['accept_id'] = $followUserInfo['username'];
                        } else {
                            $managerInfo = MbsUserInterface2::getManageUserByDept(array('dept_id' => $followUserInfo['dept_id']));
                            $checkMessageInfo['accept_id'] = $managerInfo['username'];
                        }
                        $result = MbsCheckMessageInterface::saveMessage(array('checkInfo' => $checkMessageInfo));
                        //更新买主审核人id
                        $updateParams = array(
                            'updateInfo' => array('check_user_id' => $checkMessageInfo['accept_id']),
                            'filters' => array(array('id', '=', $carBuyId))
                        );
                        CarBuyInterface::updateBuyInfo($updateParams);
                    }
                }
                
                if ($sendMessageToCustomer) {
                    //发短信通知给客户
                    $smsParams = array(
                        'server_id' => 0,
                        'phone_list' => $customerArr['telephone'],
                        'content'   => CarPostVars::$TITLE['7'],
                    );
                    SmsMobileInterface::send($smsParams);
                }
                if ($sendMessageToFollow) {
                    //给更单人发送短信通知
                    $cardTime = '';
                    if (!empty($carBuyArr['start_car_time']) && !empty($carBuyArr['end_car_time'])) {
                        $cardTime = $carBuyArr['start_car_time'].'-'.$carBuyArr['end_car_time'].'年';
                    }
                    $smsFollowParams = array(
                        'server_id' => 0,
                        'phone_list' => $followUserInfo['telephone'],
                        'content'   => sprintf(CarPostVars::$TITLE['3'], $customerArr['real_name'], $cardTime, $carBuyArr['title'], $carBuyArr['mobile']),
                    );
                    SmsMobileInterface::send($smsFollowParams);
                }
                return $carBuyId;
            } else {
                //更新买主信息
                $customerName = RequestUtil::getPost('customer_name');
                $customerOldId = RequestUtil::getPost('customer_id');
                if ($customerName != $customerArr['real_name'] && $customerOldId) {
                    //名字发生改变时更新
                    $upParams = array(
                        'info' => array('real_name' => $customerArr['real_name']),
                        'filters' => array(
                            array('id', '=', $customerOldId),
                        ),
                    );
                    MbsCustomerInterface::customerInfoUpdate($upParams);
                }
                
                //更新车源信息
                /*if (RoleInterface::isSaleConsult(array('roleId' => $user['role_id']))) {
                    $checkMessageInfo['reason'] = 26;
                } else if (RoleInterface::isCallCenterUser(array('roleId' => $user['role_id']))) {
                    $checkMessageInfo['reason'] = 27;
                }*/
                
                $carBuyArr['update_user'] = $user['account'];
                $carBuyArr['update_time'] = time();
                $updateParams = array(
                    'updateInfo' => $carBuyArr,
                    'filters' => array(
                        array('id', '=', $id),
                    ),
                );
                CarBuyInterface::updateBuyInfo($updateParams);

                if (!empty($carBuyArr['brand_id'])) {
                    $insertArr = array(
                        'car_buy_id' => $id,
                        'brand_id' => $carBuyArr['brand_id'],
                        'series_id' => $carBuyArr['series_id'],
                        'isdefault' => CarBuySeriesInterface::ISDEFAULT,
                    );
                }
                CarBuySeriesInterface::add($insertArr);

                //获取更单人信息
                $followUserId = RequestUtil::getPost('follow_user_id');
                $followUserInfo = MbsUserInterface2::getInfoByUser(array('username' => $followUserId));
                
                //如果非跟单人自己修改，发送提醒事务
                if (!RoleInterface::isSaleConsult(array('roleId' => $user['role_id']))) {
                    $messageParams = array(
                        'accept_id' => $followUserId,
                        'car_id' => $id,
                        'reason' => 9,
                        'create_id' => $user['account'],
                    );
                    MbsMessageInterface::saveMessage($messageParams);
                }
                return 1;
            }
            
        } else {
            //验证失败
            throw new AppServException(AppServErrorVars::CUSTOM, '提交参数验证失败');
        }
    }
    
    public static function getParams($params) {
        $ret['type_id'] = $params['car_type'];
        $ret['brand_id'] = $params['brand_id'];
        $ret['series_id'] = $params['series_id'];
        $ret['year_type'] = $params['model_id'];
        $ret['deal_province_id'] = $params['province'];
        $ret['plate_province_id'] = $params['plate_province'];
        $ret['deal_city_id'] = $params['city'];
        $ret['deal_district_id'] = $params['district'];
        $ret['plate_city_id'] = $params['plate_city'];
        $ret['note'] = $params['note'];
        $ret['telephone'] = $params['telephone'];
        $ret['telephone1'] = $params['telephone1'];
        $ret['telephone2'] = $params['telephone2'];
        $ret['kilometer'] = $params['kilometer'];
        $ret['idcard'] = $params['idcard'];
        $ret['images'] = $params['images'];
        $ret['min_price'] = $params['min_price'];
        $ret['max_price'] = $params['max_price'];
        $ret['buyer_name'] = $params['contact_user'];
        $ret['end_car_time'] = $params['end_card_time'];
        $ret['start_car_time'] = $params['start_card_time'];
        $ret['displacement'] = $params['air_displacement'];
        $ret['car_age'] = $params['card_age'];
        return $ret;
    }
}



