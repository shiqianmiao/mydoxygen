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

class AppServErrorVars {
    const SUCCESS                  = 0; //成功
    const CUSTOM                   = 1; //自定义错误
    const API_METHOD_NOT_EXIST     = 2; // 此方法不存在
    const ACCESS_CONTROL           = 3; // 粒度控制,接口的调用请求次数达到上限,适用于外部网站调用
    const CLIENT_IP_NO_AUTHORIZE   = 4; // 客户端ip没有授权
    const APP_KEY_INVALID          = 5; // appkey无效
    const TOKEN_SIGN_ERROR         = 6; // token签名出错
    const APP_SERV_TIME_EXPIRE     = 7; // 请求过期
    const ACCESS_ERROR             = 8; // 获取accessToken失败
    const TOKEN_OUT_TIME           = 9; // token过期
    const ERROR_DEFAULT            = 10; //未知错误
    const PASSPORT_ERROR           = 11; //passport错误
    const LOGING_USER_CODE_ERROR   = 12; //登陆用户不具备此方式的权限
    const PASSWD_ERROR             = 13; //旧密码错误
    const CHANGE_PASSWD_ERROR      = 14; //修改密码失败

    const PARAM_INVALID            = 100; // 参数无效
    const DISPATCH_PARAM_INVALID   = 101; // DISPATCH时参数无效
    const API_METHOD_NO_AUTHORIZE  = 102; // api方法没有授权
    const USER_NO_AUTHORIZE        = 103; // 用户没有授权
    const MANDATORY_UPDATE         = 104; // 强制升级

    
    public static $ERROR_MSG_DEBUG = array(
            self::SUCCESS                 => '',
            self::CUSTOM                  => '',
            self::API_METHOD_NOT_EXIST    => '此方法不存在', 
            self::ACCESS_CONTROL          => '接口的调用请求数达到上限',
            self::CLIENT_IP_NO_AUTHORIZE  => '客户端ip没有授权',
            self::APP_KEY_INVALID         => 'appkey无效',
            self::TOKEN_SIGN_ERROR        => 'token 签名出错',
            self::APP_SERV_TIME_EXPIRE    => '请求已过期',
            self::ACCESS_ERROR            => '获取accessToken失败',
            self::TOKEN_OUT_TIME          => 'token过期',
            self::ERROR_DEFAULT           => '未知错误',
            self::PASSPORT_ERROR          => 'passport错误',
            self::LOGING_USER_CODE_ERROR  => '登陆用户不具备调用此方法的权限',

            self::PARAM_INVALID           => '参数无效',
            self::DISPATCH_PARAM_INVALID  => '分发参数无效',
            self::API_METHOD_NO_AUTHORIZE => 'api方法没有授权',
            self::USER_NO_AUTHORIZE       => '用户没有授权',
            self::MANDATORY_UPDATE        => '客户端版本过低',
    );


     public static $ERROR_MSG_ONLINE = array(
            self::SUCCESS                 => '',
            self::CUSTOM                  => '',
            self::API_METHOD_NOT_EXIST    => '服务器异常，请重试', 
            self::ACCESS_CONTROL          => '请求太频繁，请重试',
            self::CLIENT_IP_NO_AUTHORIZE  => '请求失败，您不具备该权限，请重试！',
            self::APP_KEY_INVALID         => '账号过期，请重新登录',
            self::TOKEN_SIGN_ERROR        => '账号过期，请重新登录',
            self::APP_SERV_TIME_EXPIRE    => '登录失败，请把您设备时间设置为北京时间',
            self::ACCESS_ERROR            => '登录失败，请重新登录',
            self::TOKEN_OUT_TIME          => '账号过期，请重新登录',
            self::ERROR_DEFAULT           => '服务器异常，请重试',
            self::PASSPORT_ERROR          => '登录失败，请重新登陆',
            self::LOGING_USER_CODE_ERROR  => '请求失败，您不具备该权限，请重试！',

            self::PARAM_INVALID           => '请求失败，请稍后重试',
            self::DISPATCH_PARAM_INVALID  => '请求失败，请稍后重试',
            self::API_METHOD_NO_AUTHORIZE => '请求失败，您不具备该权限，请重试！',
            self::USER_NO_AUTHORIZE       => '请求失败，您不具备该权限，请重试！',
            self::MANDATORY_UPDATE        => '您的客户端版本过低，请更新！',
    );
    public static $ERROR_MSG = array();
}
if(DEBUG_STATUS == false) {
    AppServErrorVars::$ERROR_MSG = AppServErrorVars::$ERROR_MSG_ONLINE;   
} else {
    AppServErrorVars::$ERROR_MSG = AppServErrorVars::$ERROR_MSG_DEBUG;
}


