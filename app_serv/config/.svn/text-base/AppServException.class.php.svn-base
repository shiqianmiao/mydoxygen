<?php
/**
 * @package              V3
 * @subpackage           
 * @author               $Author:   weihongfeng$
 * @file                 $HeadURL$
 * @version              $Rev$
 * @lastChangeBy         $LastChangedBy$
 * @lastmodified         $LastChangedDate$
 * @copyright            Copyright (c) 2012, www.273.cn
 */
require_once FRAMEWORK_PATH . '/util/gearman/LoggerGearman.class.php';
require_once APP_SERV . '/config/AppServErrorVars.class.php';
require_once FRAMEWORK_PATH . '/util/common/Ip.class.php';
require_once FRAMEWORK_PATH . '/util/http/RequestUtil.class.php';

class AppServException extends Exception {
    public function __construct($code, $message = '') {
        if (!isset(AppServErrorVars::$ERROR_MSG[$code])) {
            $code   = AppServErrorVars::ERROR_DEFAULT;
        }
        $message = ($code != AppServErrorVars::CUSTOM && isset(AppServErrorVars::$ERROR_MSG[$code]))
            ? AppServErrorVars::$ERROR_MSG[$code]
            : $message;

        //每次调用异常类的时候，统计日志。
        $logMessage = ($code != AppServErrorVars::CUSTOM && isset(AppServErrorVars::$ERROR_MSG_DEBUG[$code]))
            ? AppServErrorVars::$ERROR_MSG_DEBUG[$code]
            : $message;
        $serverIp = Ip::getLocalIp();
        $url = RequestUtil::getCurrentUrl();
        if(!empty($code) && !empty($message)){
        	LoggerGearman::logInfo(array('data'=>array('code' => $code, 'message'=>$logMessage,'ip' =>$serverIp,'url' =>$url), 'identity'=>'appservexception'));	
        }
        return parent::__construct($message, $code);
    }
}




