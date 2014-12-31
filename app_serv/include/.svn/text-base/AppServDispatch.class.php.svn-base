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
class AppServDispatch {
    public static function run($method, $cumtomParams = array()) {
        $obj = new AppServDispatch();
        return $obj->execute($method, $cumtomParams);
    }
    public function execute($method, $cumtomParams = array()) {
        //由于重构分多次上线，因此saleAppSve存在3、4版本同时使用的问题，该数组配置已经重构到4版的action
        $newVersionAction = array(
            'newSale',
            'addressBook',
            'mySaleList',
            'getSaleDetailByCarId',
            'saleStatusList',
            'callbackList',
            'potentialUserList',
            'execOperator',
            'todoTypeList',
            'updateSale',
            'todoList',
            'getCarTabs',
        );
        try {
            list($module, $action) = explode('.', $method);
            $module = strtolower($module);
            $className = ucfirst($module) . 'ServiceApp';
            if($module == 'sale' && $cumtomParams['_api_version'] >= 2) {
                if (in_array($action, $newVersionAction) && ($cumtomParams['from_site'] != 'xcar')) {
                    $className = $className . '_' . $cumtomParams['_api_version'];
                    $filePath = APP_SERV . "/app/" . $module . '/' . $className .'.class.php';
                } else {
                    $filePath = APP_SERV . "/app/" . $module . '/' . $className .'_3.class.php';
                }
            } else {
                $filePath = APP_SERV . "/app/" . $module . '/' . $className . '.class.php';
            }
            if (!is_file($filePath)) {
                throw new AppServException(AppServErrorVars::CUSTOM, sprintf("filePath=%s不存在", $filePath));
            }
            include_once $filePath;
            $obj = new $className();
            if (!method_exists($obj, $action)) {
                throw new AppServException(AppServErrorVars::CUSTOM, sprintf("className=%s, action=%s不存在", $className, $action));
            }
        } catch (Exception $e) {
            throw new AppServException(AppServErrorVars::DISPATCH_PARAM_INVALID);
        }
        if (method_exists($obj, 'init')) {
            $obj->init();
        }
        unset($cumtomParams['_api_time']);
        unset($cumtomParams['_api_token']);
        unset($cumtomParams['_api_method']);
        return $obj->$action($cumtomParams);
    }
}
