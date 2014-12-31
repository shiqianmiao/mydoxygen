<?php
/**
 * @package              v3
 * @subpackage           
 * @author               $Author:   wulvming$
 * @file                 $HeadURL$
 * @version              $Rev$
 * @lastChangeBy         $LastChangedBy$
 * @lastmodified         $LastChangedDate$
 * @copyright            Copyright (c) 2014, www.273.cn
 */
error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED ^ E_WARNING );
ini_set('display_errors', 1);
require_once API_PATH . '/interface/sync2site/SyncInfoInterface.class.php';
require_once API_PATH . '/interface/sync2site/Sync58InfoInterface.class.php';
require_once API_PATH . '/interface/sync2site/SyncXcarInterface.class.php';
require_once API_PATH . '/interface/CarSaleInterface.class.php';
require_once API_PATH . '/interface/VehicleV2Interface.class.php';
require_once API_PATH . '/interface/CarAttachInterface.class.php';
require_once FRAMEWORK_PATH . '/util/gearman/LoggerGearman.class.php';
require_once API_PATH . '/interface/MbsCheckCarNumberInterface.class.php';
require_once API_PATH . '/interface/MbsCheckCarPhotoInterface.class.php';
require_once API_PATH . '/interface/mbs/MbsCarSaleInterface.class.php';
require_once API_PATH . '/interface/CarSaleBakInterface.class.php';
require_once API_PATH . '/interface/mbs/MbsCarSaleInterface.class.php';
require_once API_PATH . '/interface/sync2site/CarSyncInterface.class.php';
require_once API_PATH . '/interface/mbs/MbsAttachInterface.class.php';
require_once API_PATH . '/interface/mbs/MbsSmsEmailInterface.class.php';
require_once API_PATH . '/interface/mbs/MbsCarSaleInterface.class.php';
require_once API_PATH . '/interface/sync2site/CarSyncCarVerifyCodeInterface.class.php';


class AutoappServiceApp {

    //双方约定的密钥
    public static $SECRET       = 'Hhidwi2JXC6OP7GEML8jrNFLQ0j2H4nS';
    
    /**
     *
     * @brief 生成接口跳转地址
     */
    public function execTask($params) {
        header('Content-Type:text/html;charset=UTF-8');
        try {
            
            $taskId = self::getTask();
            if (intval($_REQUEST['task_id'])) {
                $taskId   = intval($_REQUEST['task_id']);
            }
            if (!$taskId) {
                throw new Exception('没有需要执行的任务！');
            }
            
            $taskInfo = CarSyncInterface::getSynCarDetail($taskId);
            if (!$taskInfo) {
                throw new Exception('无效任务！');
            }
            
            $cronClassName = 'syncCronClass_' . $taskInfo['sys_site_code'];
            if (!isset($isIncludeClass[$cronClassName])) {
                require_once API_PATH . '/cron/sync2site/class/' . $cronClassName . '.class.php';
            }
            
            // 调用具体的方法执行任务
            $cronObject = new $cronClassName();
            $execResult = $cronObject -> $taskInfo['act_type']($taskInfo);
            $execResult['get_status'] = 0;
            
            // 任务提取失败的话，重置任务为等待执行状态
            if ($execResult['err']) {
                CarSyncInterface::setSynCarDetailReStart($taskId);
                
                throw new Exception($execResult['message']);
            }
            
        } catch (Exception $e) {
            $execstr = $e->getMessage();
            $execResult = array(
                              
                              'get_err'    => $execstr,
                              'get_status' => -1,
            );
        }
        //print_r($execResult); exit;
        echo json_encode($execResult);
        exit;
    }
    
    /**
     *
     * @brief 生成接口跳转地址
     */
    public function execTaskReturn($params) {
        
        $data = $params['php_input'];
        $data = json_decode($data, true);
        
        self::setTaskReturn($data);
        
        //print_r($data);
        $returnData = array(
            'err'         => 0,
            'err_message' => '成功处理数据！',
        );
        print_r($returnData); exit;
        echo json_encode($returnData);
        exit;
    }
    
    /**
     *
     * @brief 生成接口跳转地址
     */
    public function setTaskReturn($data) {
    
        $taskInfo = $data['task_info'];
        $params['id']          = $taskInfo['postInfo']['task_id'];
        $params['car_sale_id'] = $taskInfo['postInfo']['car_id'];
        $params['note']        = $data['accountItem']['ganji_user_name'];
        $params['return_str']  = json_encode($data);
        $params['err_code']    = $data['err'];
        $params['err_message'] = $data['err_message'];
    
        // 更新用户在赶集的帐号状态
        $accountNeedUpdata = $data['account_need_update'];
        if ($accountNeedUpdata && is_array($accountNeedUpdata)) {
            $sysDataDept = array();
            foreach ($accountNeedUpdata as $key => $item) {
                if ($item && is_array($item)) {
                    foreach ($item as $cKey => $cItem) {
                        $sysDataDept[CarSyncImpl::SITE_GANJI_AUTO]['user_account_' . $taskInfo['postInfo']['follow_user_id']][$key][$cKey] = $cItem;
                        $sysDataDept[CarSyncImpl::SITE_GANJI_AUTO]['user_account_' . $taskInfo['postInfo']['follow_user_id']][$key][$cKey] = $cItem;
                    }
                }
    
            }
            CarSyncInterface::setDeptData($taskInfo['postInfo']['store_id'], $sysDataDept);
        }
    
        // 发帖成功
        if ($data['err'] === 0) {
            $chgStatus = CarSyncInterface::setSynCarDetailSucess($params);
    
            $code      = $taskInfo['postInfo']['sys_site_code'];
            if ($taskInfo['postInfo']['act_type'] == 'saleAddAct') {
                $sysData   = array(
                        $code => array(
                                $code . '_status'         => 2,
                                $code . '_car_id'         => $data['post_puid'],
                                $code . '_car_id_edit'    => $data['postEditId'],
                                $code . '_use_account'    => $data['accountItem']['ganji_user_name'],
                                $code . '_url'            => 'http://' . $taskInfo['fromCityDomain'] . '.ganji.com/ershouche/' . $data['post_puid'] . 'x.htm',
                                $code . '_picAreadyTrans' => $data['ganjiAutoPicAreadyTrans'],
                        ),
                );
                $saveData  = CarSyncInterface::editSyncCarData($params['car_sale_id'], $sysData);
            } elseif ($taskInfo['postInfo']['act_type'] == 'saleDelAct') {
                $sysData   = array(
                        $code => array(
                                $code . '_status'         => -2,
                        ),
                );
                $saveData  = CarSyncInterface::editSyncCarData($params['car_sale_id'], $sysData);
            }
        // 发帖失败，但是需要重新尝试，那么要修改任务状态
        } elseif ($data['err'] === 1 || $params['err_message'] == '失败：发帖需要验证码，请核对帐号是否发帖过快过多' 
            || $params['err_message'] == '失败：写入临时图片内容失败，本地磁盘无写入权限！'
            || $params['err_message'] == '失败：发布车源失败，原因：“          忘记选择区域啦        ”'
            || $params['err_message'] == '失败：发布车源失败，原因：“”') { 
            $chgStatus = CarSyncInterface::setSynCarDetailReStartMore($params);
        } else { // 发帖失败
            $chgStatus = CarSyncInterface::setSynCarDetailFaile($params);
    
            $code      = $taskInfo['postInfo']['sys_site_code'];
            $sysData   = array(
                    $code  => array(
                            $code . '_status'      => -1, //同步失败
                            $code . '_err_message' => $data['err_message'], // 错误提示
//                             $code . '_task_id'     => $taskInfo['postInfo']['task_id'], // 最后一次执行的任务ID
                    ),
            );
            // 发帖失败的才记录最后一次执行失败的任务ID
            if ($taskInfo['postInfo']['act_type'] == 'saleAddAct') {
                $sysData[$code][$code . '_task_id']     = $taskInfo['postInfo']['task_id'];
            }
            $saveData  = CarSyncInterface::editSyncCarData($params['car_sale_id'], $sysData);
        }
    
        return $chgStatus;
    }
    
    /**
     *
     * @brief 取一条任务来执行
     * @param string $imgUrl 图片地址
     * @param array $params 相关参数
     * @return array $imgUpInfo 图片上传结果
     */
    public static function getTask() {
        
        // 循环次数
        $doLoop = 0;
        // 最多循环20次
        while ($doLoop < 20) {
            $params = array(
                'fields'  => 'id, sys_site_id',
                'cond'    => array(
                    //'dept_id'          => $deptId,
                    'post_status'        => '100',
                    'create_time'        => (time() - 15*24*3600), // 只重复处理3天内的任务
                    'update_time_e'      => (time() - 360), // 6分钟以上才会尝试一次
                    'sys_site_code_auto' => CarSyncImpl::$SYNC_SITE_CODE_AUTO,
                    //'act_type'           => 'saleAddAct',
                ),
                'order'  => array('update_time' => 'asc'),
                'limit'  => 50,
                'offset' => 0,
            );
            $taskList = CarSyncInterface::setNeedAutoTask($params);
            
            // 没有符合条件的任务，那么直接返回空值
            if (!$taskList) {
                return 0;
            }

            foreach ( $taskList as $item ) {
                $taskId    = $item['id'];
                $startTask = CarSyncInterface::setSynCarDetailStart($taskId);
                // 设置开始成功就返回
                if ($startTask) {
                    return $taskId;
                }
            }
            
            ++$doLoop;
        }
        
        return  0;
    }
    
    /**
     *
     * @brief 取一条任务来执行
     * @param string $imgUrl 图片地址
     * @param array $params 相关参数
     * @return array $imgUpInfo 图片上传结果
     */
    public static function getTaskCode() {
    
        // 循环次数
        $doLoop = 0;
        // 最多循环20次
        while ($doLoop < 20) {
            $params = array(
                    'fields'  => 'id',
                    'cond'    => array(
                            'status'        => '0',
                    ),
                    'order'  => array('id' => 'asc'),
                    'limit'  => 50,
                    'offset' => 0,
            );
            $taskList = CarSyncCarVerifyCodeInterface::getList($params);
    
            // 没有符合条件的任务，那么直接返回空值
            if (!$taskList) {
                return 0;
            }
    
            foreach ( $taskList as $item ) {
                $taskCodeId    = $item['id'];
                $params = array(
                    'cond'   => array('id' => $taskCodeId),
                    'update' => array('status'     => 1,)
                );
                
                //修改，并返回是否成功的标志
                $startTask = CarSyncCarVerifyCodeInterface::edit($params);
                // 设置开始成功就返回
                if ($startTask) {
                    return $taskCodeId;
                }
            }
    
            ++$doLoop;
        }
    
        return  0;
    }
    
    /**
     *
     * @brief 生成接口跳转地址
     */
    public function execTaskCode($params) {
        header('Content-Type:text/html;charset=UTF-8');
        try {
        
            $taskCodeId = self::getTaskCode();
            if (intval($_REQUEST['task_id'])) {
                $taskCodeId   = intval($_REQUEST['task_id']);
            }
            if (!$taskCodeId) {
                throw new Exception('没有需要执行的任务！');
            }
        
            // 读取服务端客户是否仍然在活跃的时间，超过15秒没有活动的认为是一个无效的任务
            $severAliveTime = CarSyncCarVerifyCodeInterface::getTaskData($taskCodeId . '_server_alive_time');
            if ((time() - $severAliveTime) > 15) {
                throw new Exception('任务无效，15秒无活动，不是一个有效的任务!');
            }
            
            $params = array(
                        'fields'  => '*',
                        'cond'    => array('id' => $taskCodeId,)
                );
            $infoData = CarSyncCarVerifyCodeInterface::getRow($params);

            $execResult = array();
            // 属于登录验证码任务时
            if ($infoData['code_type'] == 1) {
                $execResult = array(
                    'get_status' => 0,
                    'err'        => 0, 
                    'message'    => '', 
                    'postInfo'   => $infoData,
                    'code_type'  => $infoData['code_type'],
                    'task_code_id'  => $infoData['id'],
                );
            } elseif ($infoData['code_type'] == 2) {
                $taskId = $infoData['source_id'];
                $taskInfo = CarSyncInterface::getSynCarDetail($taskId);
                if (!$taskInfo) {
                    throw new Exception('无效任务！');
                }
                
                $startTask = CarSyncInterface::setSynCarDetailFaileStart($taskId);
                // 设置开始失败就返回错误
                if (!$startTask) {
                    
                    $params = array(
                        'cond'   => array('id' => $taskCodeId),
                        'update' => array('status'     => -1, 'err_str' => '当前帖子任务可能正在执行，稍后请刷新页面', 'update_time' => time())
                    );
                    //修改，并返回是否成功的标志
                    $editTask = CarSyncCarVerifyCodeInterface::edit($params);
                    
                    // 写入到标志缓存，为等待服务器端输入
                    $saveTaskStatus = CarSyncCarVerifyCodeInterface::setTaskData($taskCodeId, 'do_finish');
                    
                    throw new Exception('设置发帖任务开始失败，可能发生并发冲突！');
                }
                
                // 需要输入验证码的帐号也一起提取
                $taskInfo['get_need_code_account'] = 1;
                
                $cronClassName = 'syncCronClass_' . $taskInfo['sys_site_code'];
                if (!isset($isIncludeClass[$cronClassName])) {
                    require_once API_PATH . '/cron/sync2site/class/' . $cronClassName . '.class.php';
                }
                
                // 调用具体的方法执行任务
                $cronObject = new $cronClassName();
                $execResult = $cronObject -> $taskInfo['act_type']($taskInfo);
                $execResult['get_status'] = 0;
                $execResult['code_type']  = $infoData['code_type'];
                $execResult['task_code_id']  = $infoData['id'];
                
                // 任务提取失败的话，重置任务为等待执行状态
                if ($execResult['err']) {
                    CarSyncInterface::setSynCarDetailReStart($taskId);
                    throw new Exception($execResult['message']);
                }
            } else {
                throw new Exception('未定义的任务类型！');
            }
        
        } catch (Exception $e) {
            $execstr = $e->getMessage();
            $execResult = array(
        
                    'get_err'    => $execstr,
                    'get_status' => -1,
            );
        }
        //print_r($execResult); exit;
        echo json_encode($execResult);
        exit;
    }
    
    /**
     *
     * @brief 生成接口跳转地址
     */
    public function execTaskCodeTrans($params) {
        // 等待业务员3分钟，3分钟还没有输入的算超时
        set_time_limit(300);
        
        header('Content-Type:text/html;charset=UTF-8');
        try {
            $taskCodeId = intval($_REQUEST['taskCodeId']);
            if (!$taskCodeId) {
                throw new Exception('任务ID为空！');
            }
            $imgContent = $_REQUEST['img_content'];
            
            $params = array(
                    'cond'   => array('id' => $taskCodeId),
                    'update' => array('status'     => 2, 'img_content' => $imgContent, 'update_time' => time())
            );
            
            //修改，并返回是否成功的标志
            $editTask = CarSyncCarVerifyCodeInterface::edit($params);
            
            // 写入到标志缓存，为等待服务器端输入
            $saveTaskStatus = CarSyncCarVerifyCodeInterface::setTaskData($taskCodeId, 'server_do_first');
            
            // 最长等待两分钟
            $waitTime = 0;
            while ($waitTime < 240) {
            
                // 读取标志缓存
                $taskStatus = CarSyncCarVerifyCodeInterface::getTaskData($taskCodeId);
                
                // 读取服务端客户是否仍然在活跃的时间，超过15秒没有活动的认为是一个无效的任务
                $severAliveTime = CarSyncCarVerifyCodeInterface::getTaskData($taskCodeId . '_server_alive_time');
                if ((time() - $severAliveTime) > 15) {
                    $execResult = array(
                        'get_status' => 0,
                        'err'        => -1,
                        'message'    => '任务无效，15秒无活动，不是一个有效的任务!',
                    );
                    break;
                }
            
                if ($taskStatus != 'server_do_first') {
                    // 需要重新输入验证码
                    if ($taskStatus == 'client_do_first') {
                        $execResult = array(
                                'get_status' => 0,
                                'err'        => -1,
                                'message'    => '状态标志异常',
                                'get_code_again' => 1,
                        );
                    } elseif ($taskStatus == 'client_do_second') {
                        $params = array(
                                'fields'  => '*',
                                'cond'    => array(
                                        'id' => $taskCodeId,
                                )
                        );
            
                        $infoData = CarSyncCarVerifyCodeInterface::getRow($params);
                        if ($infoData['img_content_code']) {
                            $execResult = array(
                                'get_status' => 0,
                                'err'        => 0, 
                                'message'    => '', 
                                'img_content_code'   => $infoData['img_content_code'],
                           );
                        } else {
                            $execResult = array(
                                'get_status' => 0,
                                'err'        => -1, 
                                'message'    => '验证码为空', 
                           );
                        }
                    } else {
                        $execResult = array(
                            'get_status' => 0,
                            'err'        => -1, 
                            'message'    => '状态标志异常', 
                       );
                    }
                    
                    break;
                }
            
                ++ $waitTime;
                sleep(1);
            }
            
            if (!$execResult) {
                $execResult = array(
                    'get_status' => 0,
                    'err'        => 0, 
                    'message'    => '', 
                    'editTask'   => $editTask,
               );
            }
            
        } catch (Exception $e) {
            $execstr = $e->getMessage();
            $execResult = array(
                        'get_err'    => $execstr,
                        'get_status' => -1,
            );
        }
        
        echo json_encode($execResult);
        exit;
    }
    
    /**
     *
     * @brief 生成接口跳转地址
     */
    public function execTaskCodeFinish($params) {
        
        $data = $params['php_input'];
        $data = json_decode($data, true);
        
        // 设置帖子状态
        self::setTaskReturn($data);
        
        // 发帖成功
        if ($data['err'] === 0) {
            $status = 9;
        } else {
            $status = -1;
        }
        
        $taskCodeId = $data['taskCodeId'];
        
        $params = array(
                'cond'   => array('id' => $taskCodeId),
                'update' => array('status'     => $status, 'err_str' => $data['err_message'], 'update_time' => time())
        );
        //修改，并返回是否成功的标志
        $editTask = CarSyncCarVerifyCodeInterface::edit($params);
        
        // 写入到标志缓存，为等待服务器端输入
        $saveTaskStatus = CarSyncCarVerifyCodeInterface::setTaskData($taskCodeId, 'do_finish');
        
        $returnData = array(
            'err'         => 0,
            'err_message' => '成功处理数据！',
        );
        print_r($returnData); exit;
        echo json_encode($returnData);
        exit;
    }
}