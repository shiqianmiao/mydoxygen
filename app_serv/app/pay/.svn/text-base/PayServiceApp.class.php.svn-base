<?php
/**
 * @package              v3
 * @subpackage           
 * @author               $Author:   weihongfeng$
 * @file                 $HeadURL$
 * @version              $Rev$
 * @lastChangeBy         $LastChangedBy$
 * @lastmodified         $LastChangedDate$
 * @copyright            Copyright (c) 2012, www.273.cn
 */
require_once API_PATH . '/interface/AccountInterface.class.php';
require_once API_PATH . '/interface/CreditInterface.class.php';
require_once CONF_PATH . '/payment/PaymentConfig.class.php';

class PayServiceApp {
    public function getPhoneBalance($params) {
        if (!isset($params['dept_id'])) {
            throw new AppServException(AppServErrorVars::CUSTOM, '门店id参数错误');
        }
        $accountInfo = AccountInterface::get(array('fields'=>'*', 'cond'=>array('from_app'=>'backend','dept_id' => $params['dept_id'])));
        $creditInfo = CreditInterface::get(array('fields'=>'*', 'cond'=>array('account_id'=>$accountInfo['id'],'source_id' => PaymentConfig::$app['backend']['phone']['source_id'])));
        $balance = sprintf("%.2f", ($accountInfo['available_balance'] + $creditInfo['available_balance'])/100);
        if (!$balance) {
            $balance = 0;
        }
        return $balance;
    }
    
    public function getPayBalance($params) {
    	if (!isset($params['dept_id'])) {
    		throw new AppServException(AppServErrorVars::CUSTOM, '门店id参数错误');
    	}
    	if (is_array($params['dept_id'])) {
	    	$accountInfo = AccountInterface::getList(array('fields'=>'*', 'cond'=>array('from_app'=>'backend','dept_ids' => $params['dept_id'])));
	    	$accountArr = array();
	    	$accountIds = array();
	    	if (!empty($accountInfo)) {
	    		foreach ($accountInfo as $a) {
	    		$accountIds[] = $a['id'];
	    		$accountArr[$a['id']] = $a;
		    	}
		    	$creditInfo = CreditInterface::getList(array('fields'=>'*', 'cond'=>array('account_ids'=>$accountIds,'source_id' => PaymentConfig::$app['backend']['bc']['source_id'])));
	    	}
	    	$result = array();
	    	if (!empty($creditInfo)) {
		    	foreach ($creditInfo as $c) {
		    		$accountId = $c['account_id'];
		    		$deptId = $accountArr[$accountId]['app_id'];
		    		$balance = sprintf("%.2f", ($accountArr[$accountId]['available_balance'] + $c['available_balance'])/100);
		    		$result[$deptId] =  $balance;
		    	}
	    	}
	    	return $result;
    	}
    	$accountInfo = AccountInterface::get(array('fields'=>'*', 'cond'=>array('from_app'=>'backend','dept_id' => $params['dept_id'])));
    	$creditInfo = CreditInterface::get(array('fields'=>'*', 'cond'=>array('account_id'=>$accountInfo['id'],'source_id' => PaymentConfig::$app['backend']['bc']['source_id'])));
    	$balance = sprintf("%.2f", ($accountInfo['available_balance'] + $creditInfo['available_balance'])/100);
    	if (!$balance) {
    		$balance = 0;
    	}
    	return $balance;
    }
    /**
     * 系统为账号加抵金券，用户不可见，用户不可操作
     *
     * @param   'app_id' 如果 from_app是backend，那就是用户的dept_id
     * @param   'from_app' backend
     * @param   'amount'  冲值金额单位为 元
     * @param   'source_type' 如电话转接为 phone，具体参考 conf/payment/PaymentConfig.class.php
     * @param   'source_id' 和source_type对应的，具体参考 conf/payment/PaymentConfig.class.php
     * @return bool 或抛出异常
     */
    public function depositCredit($params) {

        require_once FRAMEWORK_PATH . '/util/gearman/LoggerGearman.class.php';
        LoggerGearman::logDebug(var_export($params, true));
        try {
            return AccountInterface::depositCredit($params);
        } catch (Exception $e) {
            LoggerGearman::logDebug($e->getMessage());
        }
    }

    /**
     * 现电话中心用户迁移接口
     *
     * @param 'user_id' 调用方的用户id 没有就填0 但不可为null，会更新为第一次登陆的人
     * @param 'user_realname' 调用方用户真实姓名 没有就填'' 但不可为null，会更新为第一次登陆的人
     * @param 'available_balance' 可用余额 单位分
     * @param 'dept_id' 部门id
     * @param 'dept_name' 部门名
     * @return int account_id
     */
    public function migrate($params) {

        require_once FRAMEWORK_PATH . '/util/gearman/LoggerGearman.class.php';
        LoggerGearman::logDebug(var_export($params, true));
        try {
            return AccountInterface::migrate($params);
        } catch (Exception $e) {
            LoggerGearman::logDebug($e->getMessage());
        }
    }
}
        
            
