<?php
/**
 * 发送短信组件配置
 *
 * @author 王煜 <wangyu@273.cn>
 * @Copyright (c) 2003-2014 273 Inc. (http://www.273.cn/)
 * @since 2014-09-18
 *
 **/
return [
    //会员app验证码
    'app_member_validcode' => [
        'interval_time'         => 120,                              //可重新发送间隔时间，默认60秒，必填
        'expire_time'           => 1800,                            //动态码保存时间，默认半小时，选填
        'server_id'             => 18,                              //用于标识发送短信的类型，必填（目前貌似没有利用起来）
        'intervar_time_key'     => 'app_member_validcode_interval',    //间隔时间key，必填
        'dynamic_code_key'      => 'app_member_validcode_',        //动态码key，选填
        'block'                 => false,                           //是否屏蔽发短信，默认否，必填
    ]
];
