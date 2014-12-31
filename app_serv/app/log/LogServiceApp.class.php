<?php
/**
 * @package              V3
 * @author               chenchaoyang
 * @file                 $HeadURL$
 * @desc                 日志统计远程接口
 * @version              $Rev$
 * @lastChangeBy         $LastChangedBy$
 * @lastmodified         $LastChangedDate$
 * @copyright            Copyright (c) 2012, www.273.cn
 */
require_once FRAMEWORK_PATH . '/util/gearman/LoggerGearman.class.php';

class LogServiceApp {
    public function insertLog($params) {
        if (empty($params['type'])) {
            throw new AppServException(AppServErrorVars::CUSTOM, '缺少错误类型参数');
        }
        switch ($params['type']) {
            case 'warn':
                LoggerGearman::logWarn($params);
                break;
            case 'debug':
                LoggerGearman::logDebug($params);
                break;
            case 'info':
                LoggerGearman::logInfo($params);
                break;
            case 'fatal':
                LoggerGearman::logFatal($params);
                break;
            case 'prob':
                LoggerGearman::logProb($params);
                break;
            default:
                LoggerGearman::logInfo($params);
                break;
        }
    }
}