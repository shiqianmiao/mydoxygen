<?php
require_once API_PATH . '/interface/mbs/MbsDefriendInterface.class.php';
require_once APP_SERV . '/config/AppServConfVars.class.php';
require_once API_PATH . '/interface/CarSaleInterface.class.php';
class CarSale {
    //图片后缀
    const IMG_TYPE = '.jpg';
    //中等图片文件名
    const IMG_MEDIUM_NAME = '_266x200';
    //小图文件名
    const IMG_SMALL_NAME = '_min';
    
    public static function submmitSale($params) {
        $user =  AppServAuth::$userInfo['user'];
        $deptInfo = MbsDeptInterface::getDeptInfoByDeptId(array('id' => $user['dept_id']));
        //是否被3天惩罚(质检)
        $followUserInfo = MbsUserInterface2::getInfoByUser($user);
        $punishType = $followUserInfo['punish_type'];
        $lastPunishedTime = $followUserInfo['punish_time'];
        $inThreeDays = time() - $lastPunishedTime < 259200 ? true : false;
        if ($punishType == 1 or $punishType == 2) {
            if ($inThreeDays) {
                throw new AppServException(AppServErrorVars::CUSTOM, '您因为发布虚假车源，3日内无法使用刷新与发布车源功能');
            }
        }
        
        $is_nologin = $params['is_nologin'] ? 1 : 0;
        $from_site = $params['from_site'];
        $authCity = 1; //是否是强制认证城市，目前默认全国都强制
        $isCkCity = AppServConfVars::getCkConfigCity($user['city']); //是否车况承诺城市
        $configOutDate = 0;
        if ($isCkCity) {
            if (empty($params['disclaimer_selected'])) {
                //客户端未上传该参数，并且为车况承诺城市，抛异常
                $configOutDate = 1;
            }
        } else {
            if (!empty($params['disclaimer_selected'])) {
                //非车况城市，却上传该参数
                $configOutDate = 1;
            }
        }

        $params = self::getParams($params);
        $params['deal_city_id'] = !empty($deptInfo['city']) ? $deptInfo['city'] : $params['deal_city_id'];
        $params['deal_province_id'] = !empty($deptInfo['province']) ? $deptInfo['province'] : $params['deal_province_id'];
        //草稿去除后端验证
        $validate = true;
        $id = $params['id'];
        //登录者的信息
        if(!$is_nologin) $user = AppServAuth::$userInfo['user'];
        if ($validate) {
            //验证通过
            $carSaleInfo = $carExtInfo = $customerInfo = array();

            $postInfo = $params;
            $conditionInfo = !empty($params['condition_info']) ? json_decode(stripslashes($params['condition_info']) , true) : array();
            
            if ($id) {
                //如果是编辑的话，获取一些编辑所需的隐藏表单信息
                $postInfo['id'] = $id;
                $postInfo['customer_id'] = $params['customer_id'];
                $postInfo['status'] = $params['status'];
                $postInfo['old_price'] = $params['old_price'];
                $postInfo['follow_user_id'] = $params['follow_user_id'];
                
                $carInfo = CarSaleInterface::getCarDetail(array('id' => $id));
            }
//             print_r($postInfo);exit;
            $carImages = $params['images'];  //车源图片
//             print_r($carImages);exit;
            $postInfo['images'] = $carImages;
        
            //判断该客户是否被列入屏蔽名单，是的话不保存
            $defriendParams['mobile'][] = $postInfo['mobile'];
            if (!empty($postInfo['telephone1'])) {
                $defriendParams['mobile'][] = $postInfo['telephone1'];
            }
            if (!empty($postInfo['telephone2'])) {
                $defriendParams['mobile'][] = $postInfo['telephone2'];
            }

            if (MbsDefriendInterface::checkDefriend($defriendParams)) {
                throw new AppServException(AppServErrorVars::CUSTOM, '该号码已经被列入屏蔽名单，请不要再跟进');
            }
            
            if (empty($postInfo['id'])) {
                //添加才修改follow_time
                $carSaleInfo['follow_time'] = $customerInfo['follow_time'] = time();
                $carSaleInfo['car_status'] = 1020;
        
                //假如从联盟站发布过来的信息，不需要设置店长业务员等信息
                if( $is_nologin ) {
                    if( $from_site == 'xcar' ) {
                        $carSaleInfo['source_type'] = 16;
                    }
                    $customerInfo['owner_user_id'] = 0;
                    $customerInfo['follow_time'] = 0;
                    $customerInfo['follow_user'] = 0;

                }else{
                    //只有添加的时候才去自动分配和修改source type等
                    if (RoleInterface::isSaleConsult(array('roleId' => $user['role_id']))) {
                        //如果是 业务员，店长，业务主任的话（发的帖子归其自己所有）
                        $carSaleInfo['follow_user_id'] = $customerInfo['follow_user'] = $customerInfo['owner_user_id'] = $user['account'];
                        $carSaleInfo['source_type'] = 13;
                    } else {
                        $customerInfo['owner_user_id'] = 0;
                        $customerInfo['follow_time'] = 0;
                        if (RoleInterface::isSecretary(array('roleId' => $user['role_id']))) {
                            //店秘
                            $carSaleInfo['source_type'] = 3;
                            if (empty($postInfo['id'])) {
                                //如果是店秘新发帖的话，自动分配给店内人员
                                $carSaleInfo['store_id'] = $user['dept_id'];
            
                                $autoUserInfo = MbsUserInterface2::getRandUser(array('dept_id' => $user['dept_id'])); //自动分配给的那个用户的信息
                                $carSaleInfo['follow_user_id'] = $customerInfo['follow_user'] = $autoUserInfo['username'];
                            }
                        } else {
                            //客服(连锁中心什么的，总部的都归为呼叫中心发的帖子)
                            $carSaleInfo['source_type'] = 8;
                            //自动分配给随机门店的随机人员
                            $autoParams = array(
                                    'province' => $postInfo['deal_province_id'],
                                    'city' => $postInfo['deal_city_id'],
                            );
                            $autoDeptId = MbsDeptInterface2::getRandDept($autoParams);
            
                            if (!empty($autoDeptId)) {
                                $carSaleInfo['store_id'] = $autoDeptId;
                                $autoUserInfo = MbsUserInterface2::getRandUser(array('dept_id' => $autoDeptId)); //自动分配给的那个用户的信息
            
                                $carSaleInfo['follow_user_id'] = $customerInfo['follow_user'] = $autoUserInfo['username'];
                            }
                        }
                    }
            
                    if (RoleInterface::isStoreUser(array('roleId' => $user['role_id']))) {
                        //如果是店内人员的话（店长，店秘，业务员，业务主任）
                        $carSaleInfo['store_id'] = $customerInfo['dept_id'] = $user['dept_id'];
                    }
                }
            } else {
                //编辑
                $carSaleInfo['id'] = $carExtInfo['car_id'] = (int) $postInfo['id'];
                $customerInfo['id'] = $postInfo['customer_id'];
            }
            
            if ($carInfo['status'] == -1) {
                $carSaleInfo['car_status'] = 1020;
            }
        
            //customer 和 car_sale,car_sale_ext公共的字段处理
            $carExtInfo['seller_name'] = $customerInfo['real_name'] = !empty($postInfo['seller_name']) ? $postInfo['seller_name'] : '';
            //$carExtInfo['telephone']   = $customerInfo['mobile']    = !empty($postInfo['telephone']) ? $postInfo['telephone'] : '';
            //$carExtInfo['telephone2']  = $customerInfo['telephone'] = !empty($postInfo['telephone1']) ? $postInfo['telephone1'] : '';
            $carSaleInfo['deal_province_id'] = $customerInfo['province'] = !empty($postInfo['deal_province_id']) ? $postInfo['deal_province_id'] : 0;
            $carSaleInfo['deal_city_id'] = $customerInfo['city'] = !empty($postInfo['deal_city_id']) ? $postInfo['deal_city_id'] : 0;
            if (empty($postInfo['id'])) {
                //添加时
                $carSaleInfo['update_time']  = $carSaleInfo['index_update_time'] = $carSaleInfo['create_time'] = $customerInfo['insert_time'] = time();
            } else {
                $carSaleInfo['index_update_time'] = time();
            }
        
            //customer独有的字段处理
            if (empty($postInfo['id']) ) {
                //暂时不修改 customer信息
                //$customerInfo['telephone2'] = !empty($postInfo['telephone2']) ? $postInfo['telephone2'] : '';
                $customerInfo['idcard'] = !empty($postInfo['idcard']) ? $postInfo['idcard'] : '';
                $customerInfo['insert_user_id'] = $user['account'];
            }
        
            //车源独有的字段处理
            $carSaleInfo['type_id']   = !empty($postInfo['type_id']) ? $postInfo['type_id'] : 0;
            $carSaleInfo['brand_id']  = !empty($postInfo['brand_id']) ? $postInfo['brand_id'] : 0;
            $carSaleInfo['series_id'] = !empty($postInfo['series_id']) ? $postInfo['series_id'] : 0;
            if (!empty($postInfo['year_type'])) {
                if (is_numeric($postInfo['year_type'])) {
                    $carSaleInfo['model_id'] = $postInfo['year_type'];
                } else {
                    $carSaleInfo['model_text'] = $postInfo['year_type'];
                }
            } else {
                $carSaleInfo['model_id'] = 0;
            }
            //处理封面图片
            if (!empty($carImages) && count($carImages) > 0) {
                foreach($carImages as $key => $value) {
                    if($value['is_cover'] == 1) {
                        if (empty($value['file_path'])) {
                            //出现图片上来后file_path为空，记录日志
                            include_once API_PATH . '/interface/mbs/MbsSmsEmailInterface.class.php';
                            $smsObj = MbsSmsEmailInterface::getInstance();
                            $smsCont = print_r($params, true);
                            $smsObj->sendEmail('手机业管编辑车源file_path空', "参数：{$smsCont}");
                        }
                        $carSaleInfo['cover_photo'] = $value['file_path'] ? $value['file_path'] : '';
                        // 有车源ID的话，才执行数据库操作
                        if (intval($postInfo['id'])) {
	                        //去除封面图片
	                        $imgParams = array(
	                                'filters' => array(array('object_id', '=', $postInfo['id'])),
	                                'updateInfo' => array('is_cover'=> 0),
	                        );
	                        MbsAttachInterface::updateCover($imgParams);
                        }
                    }
                }
            } else {
                $carSaleInfo['cover_photo'] = '';
            }
            
            $carSaleInfo['deal_district_id'] = !empty($postInfo['district']) ? $postInfo['district'] : 0;
            
            $carSaleInfo['ad_note'] = !empty($postInfo['ad_note']) ? $postInfo['ad_note'] : '';
            
            $carSaleInfo['car_color'] = !empty($postInfo['color']) ? $postInfo['color'] : 0;
            $carSaleInfo['price'] = !empty($postInfo['price']) ? $postInfo['price'] * 10000 : 0;
            $carSaleInfo['safe_business_cash'] = !empty($postInfo['safe_business_cash']) ? $postInfo['safe_business_cash'] * 10000 : 0;
            $carSaleInfo['kilometer'] = !empty($postInfo['kilometer']) ? $postInfo['kilometer'] * 10000 : 0;
            $carSaleInfo['description'] = !empty($postInfo['description']) ? $postInfo['description'] : '';
            $carSaleInfo['card_time'] = (!empty($postInfo['plate_year']) && !empty($postInfo['plate_month'])) ? strtotime($postInfo['plate_year'].'-'.$postInfo['plate_month']) : 0;
            if (($authCity) && empty($postInfo['id'])) {
                //福州有车牌质检，需审核通过才在外网展示
                $carSaleInfo['order_status'] = 99;
            }
            if( $from_site == 'xcar' ) {
                $carSaleInfo['order_status'] = 99;
            }
            $modelInfo = array();
            if (!empty($carSaleInfo['model_id'])) {
                $modelInfo = VehicleV2Interface::getModelById(array('model_id' => $carSaleInfo['model_id']));
            }
            if (!empty($modelInfo['gearbox_type'])) {
                if ($modelInfo['gearbox_type'] == '1' || $modelInfo['gearbox_type'] == '手动') {
                    $carSaleInfo['gearbox_type'] = 1;
                } else if ($modelInfo['gearbox_type'] == '2' || $modelInfo['gearbox_type'] == '自动') {
                    $carSaleInfo['gearbox_type'] = 2;
                } else if ($modelInfo['gearbox_type'] == '3' || $modelInfo['gearbox_type'] == '手自一体') {
                    $carSaleInfo['gearbox_type'] = 3;
                }
            } else {
                $carSaleInfo['gearbox_type'] = 0;
            }
            //$carSaleInfo['gearbox_type'] = !empty($modelInfo['gearbox_type']) ? $modelInfo['gearbox_type'] : 0;
            $carSaleInfo['buy_price'] = !empty($modelInfo['guide_price']) ? $modelInfo['guide_price'] * 10000 : 0;
            $carSaleInfo['year_group'] = !empty($modelInfo['produce_year']) ? $modelInfo['produce_year'] : 0;
            if (!empty($postInfo['safe_force_time'])) {
                $carSaleInfo['safe_force_time'] = $postInfo['safe_force_time'] == -1 ? -1 : strtotime($postInfo['safe_force_time']);
            } else {
                $carSaleInfo['safe_force_time'] = 0;
            }
            
            if (!empty($postInfo['year_check_time'])) {
                $carSaleInfo['year_check_time'] = $postInfo['year_check_time'] == -1 ? -1 : strtotime($postInfo['year_check_time']);
            } else {
                $carSaleInfo['year_check_time'] = 0;
            }
            $carSaleInfo['safe_business_time'] = !empty($postInfo['safe_business_time']) ? strtotime($postInfo['safe_business_time']) : 0;
            $carSaleInfo['transfer_num'] = !empty($postInfo['transfer_num']) ? $postInfo['transfer_num'] : 0;
            $carSaleInfo['maintain_address'] = !empty($postInfo['maintain_address']) ? $postInfo['maintain_address'] : 0;
            $carSaleInfo['use_type'] = !empty($postInfo['use_type']) ? $postInfo['use_type'] : 0;
            $carSaleInfo['sale_type'] = !empty($postInfo['sale_type']) ? $postInfo['sale_type'] : 0;
            $carSaleInfo['title'] = !empty($postInfo['title']) ? $postInfo['title'] : '';
            if (!empty($postInfo['busi_insur_checked'])) {
                $carSaleInfo['is_safe_business'] = $postInfo['busi_insur_checked'] == 1 ? 2 : 1;
            } else {
                $carSaleInfo['is_safe_business'] = 0;
            }
            $carSaleInfo['safe_business_cash']   = !empty($postInfo['busi_insur_price']) ? $postInfo['busi_insur_price'] * 10000 : 0;
            
            //增加emission_standads字段
            $modelInfo = array();
            if (!empty($postInfo['model_id'])) {
                $modelInfo = VehicleV2Interface::getModelById(array('model_id' => $postInfo['model_id']));
            }
            
            $carSaleInfo['emission_standards'] = !empty($modelInfo['emission_standards']) ? self::getEmissionValue(trim($modelInfo['emission_standards'])) : 0;
            
            $carExtInfo['plate_province_id'] = !empty($postInfo['plate_province_id']) ? $postInfo['plate_province_id'] : 0;
            $carExtInfo['plate_city_id'] = !empty($postInfo['plate_city_id']) ? $postInfo['plate_city_id'] : 0;
            $carExtInfo['create_user_id'] = !empty($user['account']) ? $user['account'] : 0;
            $carExtInfo['car_number'] = !empty($postInfo['car_number']) ? $postInfo['car_number'] : '';
            
            if ($isCkCity) {
                $carExtInfo['is_response'] = 1;
                $carSaleInfo['is_look_ck'] = !empty($conditionInfo['scratched']) ? 1 : 0;
                $carExtInfo['is_frame_problem'] = !empty($conditionInfo['scratched']) ? $conditionInfo['scratched'] : 0;
                $carExtInfo['is_water_problem'] = !empty($conditionInfo['soaked']) ? $conditionInfo['soaked'] : 0;
                $carExtInfo['is_engine_problem'] = !empty($conditionInfo['engine_fixed']) ? $conditionInfo['engine_fixed'] : 0;
                $carExtInfo['is_kilometer_problem'] = !empty($conditionInfo['odometer_fixed']) ? $conditionInfo['odometer_fixed'] : 0;
                $carExtInfo['condition_detail'] = (!empty($carSaleInfo['is_look_ck']) && !empty($conditionInfo['scratches'])) ? serialize($conditionInfo['scratches']) : '';
            }
        
        
            $params = array(
                    'customer_info' => $customerInfo,
                    'car_sale_info' => $carSaleInfo,
                    'car_ext_info'  => $carExtInfo,
            );

//             try {
                $carId = MbsCarSaleInterface::saleAddOrEdit($params);
                
                //电话号码加密后存放到别的地方
                include_once API_PATH . '/interface/MbsCarProtectInfoInterface.class.php';
                if (!empty($postInfo['id'])) {
                    //编辑
                    $params = array(
                        'cond' => array('car_id' => $carId),
                        'update' => array(
                            'mobile' => !empty($postInfo['telephone']) ? $postInfo['telephone'] : '',
                            'mobile2' => !empty($postInfo['telephone1']) ? $postInfo['telephone1'] : '',
                            'mobile3' => !empty($postInfo['telephone2']) ? $postInfo['telephone2'] : '',
                        ),
                    );
                    MbsCarProtectInfoInterface::edit($params);
                } else {
                    //添加
                    $params = array(
                        'car_id' => $carId,
                        'mobile' => !empty($postInfo['telephone']) ? $postInfo['telephone'] : '',
                        'mobile2' => !empty($postInfo['telephone1']) ? $postInfo['telephone1'] : '',
                        'mobile3' => !empty($postInfo['telephone2']) ? $postInfo['telephone2'] : '',
                    );
                    MbsCarProtectInfoInterface::add($params);
                }
                
                //添加车源日志
                $carLogInfo = array(
                    'car_id' => $carId,
                    'car_status' => !empty($carSaleInfo['car_status']) ? $carSaleInfo['car_status'] : 0,
                    'note'   => !empty($postInfo['id']) ? '卖车编辑' : '卖车发布',
                    'create_user' => $user['account'],
                    'create_time' => time(),
                    'create_ip'   => $_SERVER['REMOTE_ADDR'],
                    'client_type' => 2,
                );
                MbsCarSaleStatusInterface::insertInfo(array('info' => $carLogInfo));
                
                if (!empty($postInfo['id'])) {
                    //车况承诺进队列处理(编辑的时候进队列,添加的时候质检那边会进队列)
                    MbsCarOrderListInterface::insertQueue(array('car_id' => $carId, 'type' => 1));
                }
               
                //添加车源图片
                if (!empty($carImages) && $carId > 0) {
                    $imagesInfo = array();
                     if (!empty($postInfo['id'])) {
                         //编辑的话，删出原图片
                         MbsAttachInterface::deleteByFilter(array(
                             'filters' => array(
                                 array('object_id', '=', $postInfo['id']),
                                 array('object_type', '=', 1),
                             ),
                         ));
                     }
                    
                    foreach ($carImages as $key => $img) {
                        if (empty($img['file_path'])) {
                            continue;
                        }
                        $imagesInfo = array(
                                'object_id'   => $carId,
                                'object_type' => 1,    //1:卖车
                                'file_path'   => $img['file_path'],
                                'insert_time' => time(),
                                'status'      => 1,
                                'sort_order'  => $img['sort_order'],
                                'is_cover'    => $img['is_cover'],
                                'insert_user_id' => $user['account'],
                        );
                        //添加或者更新
                        if (empty($img['id'])) {
                            MbsAttachInterface::insertImageInfo(array('images' => $imagesInfo));
                        } else {
                            //更新
                            $imgParams = array(
                                    'object_id' => $postInfo['id'],
                                    'filters' => array(array('object_id', '=', $postInfo['id']), array('id', '=', $img['id'])),
                                    'images' => $imagesInfo,
                            );
                            MbsAttachInterface::updateImageInfo($imgParams);
                        }
                    }
                } else if ($postInfo['id']) {
                    //说明图片都被删掉了
                    MbsAttachInterface::deleteByFilter(array(
                         'filters' => array(
                             array('object_id', '=', $postInfo['id']),
                             array('object_type', '=', 1),
                         ),
                     ));
                }
                
                $carNumberImages = $postInfo['car_number_images'];
                $driveImages = $postInfo['images_drive'];
                $carPeopleImages = $postInfo['images_car_people'];
                //质检图片上传
                self::_submitCarNumber($postInfo, $carId);
        
        
                //车牌图片信息 和 质检信息
                $carNumberInfo = $checkPhotoInfo = array();
                $carNumberInfo['photo'] = $carNumberImages[0]['file_path'];
                $carNumberInfo['create_time'] = time();
                if (empty($postInfo['id'])) {
                    $carNumberInfo['car_id'] = $checkPhotoInfo['car_id'] = $carId;
                }
                $carNumberInfo['car_number'] = $postInfo['car_number'];
                $carNumberInfo['brand'] = $postInfo['title'];
                $carNumberInfo['contact_user'] = $postInfo['seller_name'];
                $carNumberInfo['follow_user'] = $checkPhotoInfo['follow_user'] = $carSaleInfo['follow_user_id'] ? $carSaleInfo['follow_user_id'] : '';
                $carNumberInfo['dept_id'] = $carSaleInfo['store_id'];
                $carNumberInfo['car_telephone'] = $postInfo['telephone'];
                
                
                if ($authCity) {
                    //福州
                    if (empty($postInfo['id'])) {
                        ///添加的时候处理
                        //车源真实性只针对福州
                        if (!empty($driveImages)) {
                            $checkPhotoInfo['photo'] = $carNumberInfo['photo'] = $driveImages[0]['file_path'];
                            $checkPhotoInfo['photo_type'] = 2;
                            MbsCheckCarPhotoInterface::add($checkPhotoInfo);
                        }
                        if (!empty($carPeopleImages)) {
                            $checkPhotoInfo['photo'] = $carNumberInfo['photo'] = $carPeopleImages[0]['file_path'];
                            $checkPhotoInfo['photo_type'] = 3;
                            MbsCheckCarPhotoInterface::add($checkPhotoInfo);
                        }
                    }
                }
        
                //添加事务内容
                if (empty($postInfo['id'])) {
                    //添加
                    if (!empty($carNumberImages)) {
                        $checkPhotoInfo['photo'] = $carNumberInfo['photo'] = $carNumberImages[0]['file_path'];
                        $checkPhotoInfo['photo_type'] = 1;
                        MbsCheckCarPhotoInterface::add($checkPhotoInfo);
                    }
        
                    if (!empty($carNumberInfo['photo']) && !empty($carNumberInfo['car_number'])) {
                        MbsCheckCarNumberInterface::add($carNumberInfo);
                        $logInfo = array(
                                'car_id' => $carNumberInfo['car_id'],
                                'car_number' => $carNumberInfo['car_number'],
                                'operate_user' => $user['real_name'],
                                'operate_time' => time(),
                                'operate_type' => 1,
                                'value_msg' => '新增车牌号',
                                'log_from' => 2,
                        );
                        MbsCarNumberOperateLogInterface::recordOperation(array('log' => $logInfo));
                    }
        
        
                    if(!$is_nologin) {
                        //自己录入的车源，自己进行T+1回访
                        if ($carSaleInfo['follow_user_id'] && RoleInterface::isSaleConsult(array('roleId' => $user['role_id']))) {
                            //店长，业务员，业务主任
                            $title = sprintf(CarPostVars::$VISIT_MESSAGE_TITLE[2], $carId);
                            $visitInfo = array(
                                    'create_id' => $user['account'],
                                    'accept_id' => $carSaleInfo['follow_user_id'],
                                    'insert_time' => time(),
                                    'finish_time' => 0,
                                    'car_id' => $carId,
                                    'status' => 0,
                                    'reason' => 2,
                                    'title' => $title,
                            );
                            //添加回访事务信息
                            MbsVisitMessageInterface::insertInfo(array('info' => $visitInfo));
                        }
                    }
                } else {
                    //编辑的时候独有的处理
                    if ($postInfo['price'] * 10000 != $carSaleInfo['price']) {
                        //如果出售价格有经过修改，则发送给跟单人提醒事务
                        $messageParams = array(
                                'accept_id' => $postInfo['follow_user_id'],
                                'car_id' => $carId,
                                'reason' => 122,
                                'create_id' => $user['account'],
                                'old_price' => $postInfo['old_price'],
                                'price' => $carSaleInfo['price'] / 10000,
                        );
                        MbsMessageInterface::saveMessage($messageParams);
                    }
                    
                    if (!empty($postInfo['follow_user_id']) && $user['account'] != $postInfo['follow_user_id']) {
                        //非自己修改信息时发送提醒
                        $messageParams = array(
                                'accept_id' => $postInfo['follow_user_id'],
                                'car_id' => $carId,
                                'reason' => 9,
                                'create_id' => $user['account'],
                        );
                        MbsMessageInterface::saveMessage($messageParams);
                    }
                }
                if (!empty($postInfo['id'])) {
                    //编辑的时候，看看是否已经存在该车源的审核事务了，存在则不发送事务了。
                    $isExit = MbsCheckMessageInterface::isExitByFilters(array('filters' => array(array('car_id', '=', $postInfo['id']), array('status', '=', 0), array('reason', '=', 5), array('dept_id', '>', 0))));
                    
                    //更改身份证和手机号码时添加审核信息
                    $idCardCheckInfo['idcard'] = $postInfo['idcard'];
                    $idCardCheckInfo['reason'] = $postInfo['reason'];
                    $idCardCheckInfo['car_id'] = $postInfo['id'];
                    $idCardCheckInfo['customer_id'] = $postInfo['customer_id'];
                    self::_editCard($idCardCheckInfo);
                    
                    $telephoneCheckInfo['reason'] = $postInfo['reason'];
                    $telephoneCheckInfo['car_id'] = $postInfo['id'];
                    $telephoneCheckInfo['customer_id'] = $postInfo['customer_id'];
                    $telephoneCheckInfo['mobile'] = $postInfo['mobile'];
                    $telephoneCheckInfo['telephone1'] = $postInfo['telephone1'];
                    $telephoneCheckInfo['telephone2'] = $postInfo['telephone2'];
                    self::_editPhone($telephoneCheckInfo);
                    
                }
                
                //如果是新添加的车源，或者状态为0的像店长发送审核事务,发送店长审核
                if( !$is_nologin ) {
                    if (empty($postInfo['id']) || !$isExit && $carInfo['status'] <= 0) {
                        $checkInfo = array(
                                'car_id' => $carId,
                                'reason' => '5',
                                'create_id' => $user['account'],
                        );
                        if (RoleInterface::isSalesManager(array('roleId' => $user['role_id']))) {
                            //如果是店长
                            $checkInfo['accept_id'] = $user['account'];
                        } else {
                            //通过dept_id去获取店长信息
                            $thisManagerInfo = MbsUserInterface2::getManageUserByDept(array('dept_id' => $user['dept_id']));
                            $checkInfo['accept_id'] = $thisManagerInfo['username'];
                        }
                        //添加审核事务信息
                        MbsCheckMessageInterface::saveMessage(array('checkInfo' => $checkInfo));
                        
                        //修改车源详细状态为店长待审核
                        MbsCarSaleInterface::updateCarInfo(array('info' => array('index_update_time' => time(), 'car_status' => 1020), 'filter' => array(array('id', '=', $carId))));
                    }
                }
                
                //成功，转向成功页面
                if (empty($postInfo['id']) && $carId) {
                    return $configOutDate ? array('car_id' => $carId, 'config_outdated' => 1) : array('car_id' => $carId);
                } else {
                    //编辑成功,考虑是否需要进同步车源的编辑队列
                    $search_arr['filters'][] = array( 'car_sale_id', '=', $carId);
                    $syncList = SyncInfoInterface::getSyncSaleInfoListByWhere( $search_arr );
                    if( $syncList[0] ) { //同步成功过，才进入编辑队列
                        $edit_queue_array = array(
                            'id'=>$carId,
                            'edittime'=>time(),
                    );
                        SyncInfoInterface::setEditQueue($edit_queue_array);
                    }
                    return 3;
                }
//             } catch (Exception $e) {
//                  throw new AppServException(AppServErrorVars::CUSTOM,'发布卖车失败!');
//             }
        
        } else {
            //验证失败
            throw new AppServException(AppServErrorVars::CUSTOM,'字段验证失败!');
        }
    }
    
    /**
     * @desc 获取排放标准的枚举值
     */
    public static function getEmissionValue($emission) {
        if (empty($emission)) {
            return 0;
        } else {
            foreach (CarVars::$EMISSION_CONF as $value => $text) {
                if ($text == $emission) {
                    return $value;
                }
            }
    
            return 0;
        }
    }
    
    public static function getParams($params) {
        if(!empty($params['id'])) {
            $ret['id'] = $params['id'];
        }
        $ret['type_id'] = $params['car_type'];
        $ret['brand_id'] = $params['brand_id'];
        $ret['series_id'] = $params['series_id'];
        $ret['year_type'] = $params['model_id'];
        $ret['deal_province_id'] = $params['province'];
        $ret['plate_province_id'] = $params['plate_province'];
        $ret['deal_city_id'] = $params['city'];
        $ret['district'] = $params['district'];
        $ret['plate_city_id'] = $params['plate_city'];
        $ret['color']  = $params['car_color'];
        $ret['price'] = $params['price'];
        $ret['kilometer'] = $params['kilometer'];
        $ret['car_number'] = $params['car_number'];
        $ret['card_time'] = $params['card_time'];
        $ret['ad_note'] = $params['ad_note'];
        $ret['seller_name'] = $params['contact_user'];
        $ret['telephone'] = $params['telephone'];
        $ret['telephone1'] = $params['telephone2'];
        $ret['idcard'] = $params['idcard'];
        $ret['safe_force_time'] = $params['safe_time'];
        $ret['year_check_time'] = $params['year_check_time'];
        $ret['safe_business_time'] = $params['busi_insur_time'];
        $ret['transfer_num'] = $params['transfer_num'];
        $ret['condition_info'] = $params['condition_info'];
        $ret['busi_insur_checked'] = $params['busi_insur_checked'];
        $ret['busi_insur_price']   = $params['busi_insur_price'];
        
        // 来源站的年款ID
        $mapMid = $params['model_id'];
        
        if (!$params['model_id'] && (!$params['series_id'] || !$params['brand_id'])) {
            $brind_name = '';
        } else {
            $brind_name = VehicleV2Interface::getModelCaption($params) ? VehicleV2Interface::getModelCaption($params) : '';
        }

        $ret['title'] = $brind_name;

        $is_nologin = $params['is_nologin'] ? 1 : 0;
        if( !$ret['title'] && $is_nologin && ( !$params['series_id'] || !$params['brand_id'] ) ) {
            $brind_name = $params['brind_name'] ? $params['brind_name'] : '';
            $ret['title'] = $brind_name;
        }
        
            // 如果是爱卡的车源才进行品牌车型转换
        if ($params['from_site'] == 'xcar') {
            $paramsModel  = array(
                                'filters' => array(
                                                 array('map_mid', '=', $params['model_id']),
                                             ),
                            );
            $chgModelInfo = SyncMapInterface::getModelMapByWhere($paramsModel);
            $ret['brand_id']  = $chgModelInfo[0]['bid'];
            $ret['series_id'] = $chgModelInfo[0]['sid'];
            $ret['model_id']  = $chgModelInfo[0]['mid'];
            $ret['year_type'] = $ret['model_id'];
        }
        
        $mapSid           = $chgModelInfo[0]['map_sid'];

        if ($params['brind_name']) {
            $ret['title'] = $params['brind_name'];
        }
        /*
        $xcarModelData = json_decode(@file_get_contents(DATA_PATH . '/sync2site/data/arr_xcar_mid2mname.data'), true);
        if ($xcarModelData[$mapSid]) {
            $ret['title'] = $xcarModelData[$mapSid][$mapMid]['name'];
        }
        else {
            foreach ($xcarModelData as $childData) {
                if (isset($childData[$mapMid])) {
                    $ret['title'] = $childData[$mapMid]['name'];
                    $mapSid       = $childData[$mapMid]['psid'];
                    break;
                }
            }
        }

        $xcarSidData = json_decode(@file_get_contents(DATA_PATH . '/sync2site/data/arr_xcar_sid2sname.data'), true);
        if (is_array($xcarSidData)) {
            foreach ($xcarSidData as $childData) {
                if (isset($childData[$mapSid])) {
                    $ret['title'] = $childData[$mapSid]['psname'] . ' ' . $ret['title'];
                    $mapBid       = $childData[$mapSid]['bid'];
                    break;
                }
            }
        }

        $xcarBidData = json_decode(@file_get_contents(DATA_PATH . '/sync2site/data/arr_xcar_pbid2pbname.data'), true);
        if (isset($xcarBidData[$mapBid])) {
            $ret['title'] = $xcarBidData[$mapBid] . ' ' . $ret['title'];
        }
        */
        
        $ret['displacement'] = $params['air_displacement'];
        
        $ret['maintain_address'] = $params['maintain_address'];
        $ret['use_type'] = $params['use_quality'];
        $ret['sale_type'] = $params['sale_quality'];
        $ret['plate_year'] = '';
        $ret['plate_month'] = '';
        if(!empty($params['card_time'])) {
            $cardArr = explode ( '-', $params['card_time'] );
            $ret['plate_year'] = $cardArr[0];
            $ret['plate_month'] = $cardArr[1];
        }
        
        if ($params['note']) {
            $params['description'] = $params['note'];
        }
        
        $ret['customer_id'] = $params['customer_id'] ? $params['customer_id'] : '';
        //web端有手机端没有
        $ret['description'] = $params['description'] ? $params['description'] : '';
        $ret['year_type_text'] = '';
        $ret['is_edit'] = '';
        $ret['auth_city'] = '';
        
        
        //手机端有web端没有
        $ret['info_type'] = '';
        //图片处理
        $images = self::savePhotoes($params['image']);
        $ret['images'] = $images['images'];
        $ret['images_car_people'] = $images['images_car_people'];
        $ret['images_drive'] = $images['images_drive'];
        $ret['car_number_images'] = $images['car_number_images'];
        return $ret;
    }
    
    
    /*
     * @brief 保存图片
    *
    * @旧版本的手机端图片处理机制，新版以后要重新修改，（业管上线急，先用旧版本）
    */
    public  static function savePhotoes($photoes){
        if (empty($photoes)) {
            return array();
        }
        $ret = array();
        foreach($photoes as $key => $photo){
            //保存数据
            $photoInfo['file_path']= $photo['file_path'];
            $photoInfo['is_cover']=$photo['cover']?1:0;
            $photoInfo['sort_order']=$key;
            if(!empty($photo['id'])) {
                $photoInfo['id'] = $photo['id'];
            }
            switch($photo['type']) {
                case 97:
                    $ret['images_car_people'][] = $photoInfo;
                    break;
                case 98:
                    $ret['images_drive'][] = $photoInfo;
                    break;
                case 99:
                    $ret['car_number_images'][] = $photoInfo;
                    break;
                default:
                    $ret['images'][] = $photoInfo;
                    break;
            }
        }
        return $ret;
    }
    
    /*
     * @brief 删除图片
     * 
     * @旧版本的手机端图片处理机制，新版以后要重新修改，（业管上线急，先用旧版本）
     */
    private function removePic($id){
        $sql="select file_path from car_attachs where id='".$id."'";
        $result=$this->getOne($sql);
        $file_path=$result['file_path'];
        $ext=substr($file_path, strrpos($file_path, '.'));
        $filePath=str_replace(IMG_DOMAIN,'',$file_path);
        $maxFile=IMG_PATH.$filePath;
        unlink($maxFile);
        return false;
    }
    
    /**
     * @desc 上传车源真实的图片
     */
    private static function _upCheckImages($carId, $objectType, $images = array(), $isUpdate = false) {
        $user = AppServAuth::$userInfo['user'];
        
        if (!empty($carId) && !empty($images)) {

            $imagesInfo = array();
            foreach ($images as $key => $img) {
                $imagesInfo = array(
                    'object_id'   => $carId,
                    'object_type' => $objectType,
                    'file_path'   => $img['file_path'],
                    'insert_time' => time(),
                    'status'      => 1,
                    'sort_order'  => $key + 1,
                    'is_cover'    => 0,
                    'insert_user_id' => $user['account'],
                );
                if ($isUpdate) {
                    //更新
                    $imgParams = array(
                        'object_id' => $carId,
                        'filters' => array(array('object_id', '=', $carId), array('object_type', '=', $objectType)),
                        'images' => $imagesInfo,
                    );
                    MbsAttachInterface::updateImageInfo($imgParams);
                } else {
                    //添加
                    $imgId = MbsAttachInterface::insertImageInfo(array('images' => $imagesInfo));
                }
            }
        }
    }
    
    
    /**
     * 
     * @brief 手机端更改电话号码
     * @params mobile
     * @return 1：成功；0：失败
     * 
     */
    private static function _editPhone($params) {
        $user = AppServAuth::$userInfo['user'];
        $mobile = $params['mobile'];
        $telephone1 = $params['telephone1'];
        $telephone2 = $params['telephone2'];
        $reason = $params['reason'];
        $carId = $params['car_id'];
        $customerId = $params['customer_id'];
    
        $customerInfo = MbsCustomerInterface::getInfoForId(array('id' => $customerId));
        if($customerInfo['mobile'] == $mobile || $customerInfo['telephone1'] == $telephone1 || $customerInfo['telephone2'] == $telephone2) {
            return 1;
        }
        $checkMessageInfo = array();
        if ($user['dept_type'] == 1) {
            //直营店发送呼叫中心审核
            $randCallUser = MbsUserInterface2::getRandUser(array('dept_id' => 2));
            if (empty($randCallUser)) {
                return 0;
                $this->render(array('errorCode' => 1, 'msg' => '获取自动分配的客服信息失败'));
            }
            $checkMessageInfo['accept_id'] = $randCallUser['username'];
        } else {
            //加盟店发送店秘审核
            if (RoleInterface::isSaleConsult(array('roleId' => $user['role_id']))) {
                //店长，业务员，业务主任 给店秘书审核
                $secretaryInfo = MbsUserInterface2::getSecretaryByDept(array('dept_id' => $user['dept_id']));
                if (empty($secretaryInfo)) {
                    return 0;
                    $this->render(array('errorCode' => 1, 'msg' => '获取店秘信息失败'));
                }
                $checkMessageInfo['accept_id'] = $secretaryInfo['username'];
            } else if(RoleInterface::isSecretary(array('roleId' => $user['role_id']))){
                //店秘 给店长审核
                $keeperInfo = MbsUserInterface2::getManageUserByDept(array('dept_id' => $user['dept_id']));
                if (empty($keeperInfo)) {
                    return 0;
//                     $this->render(array('errorCode' => 1, 'msg' => '获取店长信息失败'));
                }
                $checkMessageInfo['accept_id'] = $keeperInfo['username'];
            }
        }
    
        $operationInfo = array(
                'mobile' => $mobile,
                'telephone' => $telephone1,
                'telephone2' => $telephone2,
        );
        $oldValue = array(
                'mobile' => $customerInfo['mobile'],
                'telephone' => $customerInfo['telephone'],
                'telephone2' => $customerInfo['telephone2'],
        );
        $insertInfo = array(
                'car_id' => $carId,
                'update_type' => 1,
                'update_value' => serialize($operationInfo),
                'old_value' => serialize($oldValue),
                'update_reason' => $reason,
                'insert_time' => time(),
                'status' => 0,
                'insert_user_id' => $user['account'],
        );
    
        if (RoleInterface::isCallCenterUser(array('roleId' => $user['role_id']))) {
            //呼叫中心修改联系方式直接通过
            //添加更新日志
            MbsUpdateOperationInterface::insertInfo(array('info' => $insertInfo));
        }
    
        //保存更新信息
        //$insertInfo['check_message_id'] = $messageId;
        $objectId = MbsUpdateOperationInterface::insertInfo(array('info' => $insertInfo));
    
        $checkMessageInfo['reason'] = 1;
        $checkMessageInfo['car_id'] = $carId;
        $checkMessageInfo['create_id'] = $user['account'];
        $checkMessageInfo['object_id'] = $objectId;
        $checkMessageInfo['update_reason'] = $reason;
        
        $messageId = MbsCheckMessageInterface::saveMessage(array('checkInfo' => $checkMessageInfo));
        if(empty($messageId)) {
            return 0;
        }
        return 1;
    }
    
    /**
     * 
     * @brief 手机端更改身份证号
     * @params mobile
     * @return 1：成功；0：失败
     * 
     */
    private static function _editCard($params) {
        $user = AppServAuth::$userInfo['user'];
        $idcard = $params['idcard'];
        $reason = $params['reason']; //修改原因
        $carId = $params['car_id'];
        $customerId = $params['customer_id'];
    
        $customerInfo = MbsCustomerInterface::getInfoForId(array('id' => $customerId));
        if($customerInfo['idcard'] == $idcard) {
            return 1;
        }
        $checkMessageInfo = array();
        if ($user['dept_type'] == 1) {
            //直营店发送呼叫中心审核
            $randCallUser = MbsUserInterface2::getRandUser(array('dept_id' => 2));
            if (empty($randCallUser)) {
//                 $this->render(array('errorCode' => 1, 'msg' => '获取自动分配的客服信息失败'));
                return 0;
            }
            $checkMessageInfo['accept_id'] = $randCallUser['username'];
        } else {
            //加盟店发送店秘审核
            if (RoleInterface::isSaleConsult(array('roleId' => $user['role_id']))) {
                //店长，业务员，业务主任 给店秘书审核
                $secretaryInfo = MbsUserInterface2::getSecretaryByDept(array('dept_id' => $user['dept_id']));
                if (empty($secretaryInfo)) {
//                     $this->render(array('errorCode' => 1, 'msg' => '获取店秘信息失败'));
                    return 0;
                }
                $checkMessageInfo['accept_id'] = $secretaryInfo['username'];
            } else if(RoleInterface::isSecretary(array('roleId' => $user['role_id']))){
                //店秘 给店长审核
                $keeperInfo = MbsUserInterface2::getManageUserByDept(array('dept_id' => $user['dept_id']));
                if (empty($keeperInfo)) {
                        return 0;
//                     $this->render(array('errorCode' => 1, 'msg' => '获取店长信息失败'));
                }
                $checkMessageInfo['accept_id'] = $keeperInfo['username'];
            }
        }
    
        $insertInfo = array(
                'car_id' => $carId,
                'update_type' => 3,
                'update_value' => $idcard,
                'old_value' => $customerInfo['idcard'],
                'update_reason' => $reason,
                'insert_time' => time(),
                'status' => 0,
                'insert_user_id' => $user['account'],
        );
    
        if (RoleInterface::isCallCenterUser(array('roleId' => $user['role_id']))) {
            //呼叫中心修改联系方式直接通过
            //添加更新日志
            MbsUpdateOperationInterface::insertInfo(array('info' => $insertInfo));
            return 1;
        }
    
        //保存更新信息
        //$insertInfo['check_message_id'] = $messageId;
        $objectId = MbsUpdateOperationInterface::insertInfo(array('info' => $insertInfo));
    
        $checkMessageInfo['reason'] = 3;
        $checkMessageInfo['car_id'] = $carId;
        $checkMessageInfo['create_id'] = $user['account'];
        $checkMessageInfo['object_id'] = $objectId;
        $checkMessageInfo['update_reason'] = $reason;
        
        $messageId = MbsCheckMessageInterface::saveMessage(array('checkInfo' => $checkMessageInfo));
        if(empty($messageId)) {
            return 0;
        }
        return 1;
    }
    
    
    /**
     * @desc 修改车牌号提交处理
     */
    private static function _submitCarNumber($params, $newCarId) {
        $user = AppServAuth::$userInfo['user'];
        $carId = $params['id'];
        
        
        //质检图片上传
        $carNumberImages = $params['car_number_images'];
        $driveImages = $params['images_drive'];
        $carPeopleImages = $params['images_car_people'];
        if (empty($carId)) {
            //添加
            self::_upCheckImages($newCarId, 99, $carNumberImages); //上传，车牌号图片
            self::_upCheckImages($newCarId, 98, $driveImages); //上传，行驶证图片
            self::_upCheckImages($newCarId, 97, $carPeopleImages); //上传，人车和照图片
            return 1;
        } else {
            $carInfo = CarSaleInterface::getCarDetail(array('id' => $carId));
        }
        $status = $carInfo['status'];
        $validate = true;
        
        if ($validate) {
            $postInfo = $params;
            //更新
            self::_upCheckImages($carId, 99, $carNumberImages, true); //上传，车牌号图片
            self::_upCheckImages($carId, 98, $driveImages, true); //上传，行驶证图片
            self::_upCheckImages($carId, 97, $carPeopleImages, true); //上传，人车和照图片
            //车牌图片信息 和 质检信息
            $carNumberInfo = $checkPhotoInfo = array();
            $carNumberInfo['photo'] = $carNumberImages[0]['file_path'];
            $carNumberInfo['update_time'] = time();
            $carNumberInfo['car_number'] = $postInfo['car_number'];
            if (!empty($postInfo['car_number']) && $carInfo['car_number'] != $postInfo['car_number']) {
                //改了car number
                if ($carInfo['status'] == 1) {
                    $carNumberInfo['status'] = 1;
                    $ret = MbsCheckCarPhotoInterface::updateInfo(array('info' => array('status' => 1), 'filters' => array(array('car_id', '=', $carId))));
                    if ($ret === false) {
//                         $this->_smsObj->sendEmail('业管BUG记录', "修改车牌号时，更新car_photo的status失败！");
                    }
                }
            }
    
            $photoData = MbsCheckCarPhotoInterface::getPhotoByCarId(array('car_id' => $carId));
            $checkPhotoData = array();
            if (!empty($photoData) && is_array($photoData)) {
                foreach ($photoData as $p) {
                    $checkPhotoData[$p['photo_type']] = $p;
                }
            }
    
            //此前可能为草稿，需要把status改为0
            if ($carInfo['status'] == -1) {
                MbsCarSaleInterface::updateCarInfo(array('info' => array('status' => 0, 'index_update_time' => time()), 'filter' => array(array('id', '=', $carId))));
                $checkInfo = array(
                        'car_id' => $carId,
                        'reason' => '5',
                        'create_id' => $user['account'],
                );
                if (RoleInterface::isSalesManager(array('roleId' => $user['role_id']))) {
                    //如果是店长
                    $checkInfo['accept_id'] = $user['account'];
                } else {
                    //通过dept_id去获取店长信息
                    $thisManagerInfo = MbsUserInterface2::getManageUserByDept(array('dept_id' => $user['dept_id']));
                    $checkInfo['accept_id'] = $thisManagerInfo['username'];
                }
                //添加审核事务信息
                MbsCheckMessageInterface::saveMessage(array('checkInfo' => $checkInfo));
            }

            if (true) {
                //强制认证地区
                //行驶证图片编辑逻辑处理
                if (!empty($driveImages)) {
                    $checkPhotoInfo['photo'] = $carNumberInfo['photo'] = $driveImages[0]['file_path'];
                    if (!empty($checkPhotoData['2'])) {
                        //更新
                        if ($checkPhotoData['2']['photo'] != $checkPhotoInfo['photo']) {
                            $checkPhotoInfo['status'] = $checkPhotoData['2']['status'] > 0 ? 1 : 0;
                            $filters = array(
                                    array('car_id', '=', $carId),
                                    array('photo_type', '=', 2),
                                    array('status', '!=', 2),
                            );
                            MbsCheckCarPhotoInterface::updateInfo(array('info' => $checkPhotoInfo, 'filters' => $filters));
                        }
                    } else {
                        //原来不存在，添加
                        $checkPhotoInfo['photo_type'] = 2;
                        $checkPhotoInfo['car_id'] = $carId;
                        $checkPhotoInfo['status'] = ($carInfo['status'] == 1) ? 1 : 0;
                        $checkPhotoInfo['follow_user'] = $carInfo['follow_user_id'];
                        if ($status == 1) {
                            $checkPhotoInfo['status'] = 1;
                            $checkPhotoInfo['access_time'] = time();
                        }
                        MbsCheckCarPhotoInterface::add($checkPhotoInfo);
                    }
                } else {
                    if (!empty($checkPhotoData['2'])) {
                        //原来有，现在没有，则要删除
                        $filters = array(
                                array('id', '=', $checkPhotoData['2']['id']),
                        );
                        MbsCheckCarPhotoInterface::deleteInfo(array('filters' => $filters));
                    }
                }
    
                //人车合照图片编辑逻辑处理
                if (!empty($carPeopleImages)) {
                    $checkPhotoInfo['photo'] = $carNumberInfo['photo'] = $carPeopleImages[0]['file_path'];
                    if (!empty($checkPhotoData['3'])) {
                        //更新
                        if ($checkPhotoData['3']['photo'] != $checkPhotoInfo['photo']) {
                            $checkPhotoInfo['status'] = $checkPhotoData['3']['status'] > 0 ? 1 : 0;
                            $filters = array(
                                    array('car_id', '=', $carId),
                                    array('photo_type', '=', 3),
                                    array('status', '!=', 2),
                            );
                            MbsCheckCarPhotoInterface::updateInfo(array('info' => $checkPhotoInfo, 'filters' => $filters));
                        }
                    } else {
                        //原来不存在，添加
                        $checkPhotoInfo['photo_type'] = 3;
                        $checkPhotoInfo['car_id'] = $carId;
                        $checkPhotoInfo['status'] = ($carInfo['status'] == 1) ? 1 : 0;
                        $checkPhotoInfo['follow_user'] = $carInfo['follow_user_id'];
                        if ($status == 1) {
                            $checkPhotoInfo['status'] = 1;
                            $checkPhotoInfo['access_time'] = time();
                        }
                        MbsCheckCarPhotoInterface::add($checkPhotoInfo);
                    }
                } else {
                    if (!empty($checkPhotoData['3'])) {
                        //原来有，现在没有，则要删除
                        $filters = array(
                                array('id', '=', $checkPhotoData['3']['id']),
                        );
                        MbsCheckCarPhotoInterface::deleteInfo(array('filters' => $filters));
                    }
                }
            }
    
            if (!empty($carNumberImages)) {
                $checkPhotoInfo['photo'] = $carNumberInfo['photo'] = $carNumberImages[0]['file_path'];
                if (!empty($checkPhotoData['1'])) {
                    //更新
                    if ($checkPhotoData['1']['photo'] != $checkPhotoInfo['photo']) {
                        $checkPhotoInfo['status'] = $checkPhotoData['1']['status'] > 0 ? 1 : 0;
                        $filters = array(
                                array('car_id', '=', $carId),
                                array('photo_type', '=', 1),
                                array('status', '!=', 2),
                        );
                        MbsCheckCarPhotoInterface::updateInfo(array('info' => $checkPhotoInfo, 'filters' => $filters));
                    }
                } else {
                    //原来不存在，添加
                    $checkPhotoInfo['photo_type'] = 1;
                    $checkPhotoInfo['car_id'] = $carId;
                    $checkPhotoInfo['status'] = ($carInfo['status'] == 1) ? 1 : 0;
                    $checkPhotoInfo['follow_user'] = $carInfo['follow_user_id'];
                    if ($status == 1) {
                        $checkPhotoInfo['status'] = 1;
                        $checkPhotoInfo['access_time'] = time();
                    }
                    MbsCheckCarPhotoInterface::add($checkPhotoInfo);
                }
            } else {
                if (!empty($checkPhotoData['1'])) {
                    //原来有，现在没有，则要删除
                    $filters = array(
                            array('id', '=', $checkPhotoData['1']['id']),
                    );
                    MbsCheckCarPhotoInterface::deleteInfo(array('filters' => $filters));
                }
            }
    
            if (!empty($postInfo['car_number'])) {
                $checkCarNumberInfo = MbsCheckCarNumberInterface::getCheckInfoByCarId(array('car_id' => $carId));
                if (!empty($checkCarNumberInfo)) {
                    $filters = array(
                            array('car_id', '=', $carId),
                            array('status', '!=', 2),
                    );
                    $ret = MbsCheckCarNumberInterface::updateInfo(array('info' => $carNumberInfo, 'filters' => $filters));
                } else {
                    //添加
                    $carNumberInfo['follow_user'] = $carInfo['follow_user_id'];
                    $carNumberInfo['photo'] = $carNumberImages[0]['file_path'];
                    $carNumberInfo['create_time'] = time();
                    $carNumberInfo['car_id'] = $carId;
                    $carNumberInfo['car_number'] = $postInfo['car_number'];
                    $carNumberInfo['brand'] = $carInfo['title'];
                    $carNumberInfo['contact_user'] = $carInfo['seller_name'];
                    $carNumberInfo['follow_user'] = $carInfo['follow_user_id'] ? $carInfo['follow_user_id'] : '';
                    $carNumberInfo['dept_id'] = $carInfo['store_id'];
                    $carNumberInfo['car_telephone'] = $carInfo['telephone'];
                    $ret = MbsCheckCarNumberInterface::add($carNumberInfo);
                    if (empty($ret)) {
//                         $this->_smsObj->sendEmail('业管BUG记录', "MbsCheckCarNumberInterface::add添加失败！");
                    }
                }
            }
            
            //是否需要重新质检审核
            $photoData = MbsCheckCarPhotoInterface::getPhotoByCarId(array('car_id' => $carId));
            $waitCheck = false;
            if (!empty($photoData) && is_array($photoData)) {
                foreach ($photoData as $p) {
                    if ($p['status'] == 2) {
                        $waitCheck = false;
                        break;
                    } else if ($p['status'] == 1) {
                        $waitCheck = true;
                    }
                }
            }
            if ($waitCheck) {
                MbsCarSaleInterface::updateCarInfo(array('info' => array('car_status' => 2010, 'index_update_time' => time()), 'filter' => array(array('id', '=', $carId))));
            }
    
            if ($ret) {
                $logInfo = array(
                        'car_id' => $carId,
                        'car_number' => $carNumberInfo['car_number'],
                        'operate_user' => $user['real_name'],
                        'operate_time' => time(),
                        'create_time' => time(),
                        'operate_type' => 1,
                        'value_msg' => '修改车牌号',
                        'log_from' => 2,
                );
                MbsCarNumberOperateLogInterface::recordOperation(array('log' => $logInfo));
            }
    
            return 1;
        } else {
            return 0;
        }
    }
}

