<?php
require_once FRAMEWORK_PATH . '/util/cache/CacheNamespace.class.php';
require_once dirname(__FILE__) . '/MemberHelper.class.php';

/**
 * 主站发送短信组件
 *
 * @author 王煜 <wangyu@273.cn>
 * @Copyright (c) 2003-2014 273 Inc. (http://www.273.cn/)
 * @since 2014-09-18
 * @TODO:状态码用常量表示
 * 99 => 初始状态
 * 0 => 短信接口调用成功
 * 1 => 配置文件不存在
 * 2 => 配置项目不存在
 * 3 => UUID等检测无法通过
 * 4 => 屏蔽短信
 * 5 => 时间段内不能发送短信
 * 6 => 短信接口调用失败
 * 7 => 初始化失败
 * 8 => 图像验证码错误
 **/
class SmsUtil {

    /**
     * 配置key
     *
     * @var string
     **/
    private $_key;

    /**
     * 配置项信息
     *
     * @var array
     **/
    private $_configInfo;

    /**
     * 错误码
     *
     * @var int
     **/
    public $error = 99;

    public function __construct($key) {
        $this->_key = $key;
        $config = $this->_getConfig();

        //配置文件不存在
        if ($config === false) {
            $this->error = 1;
            return false;
        }

        $item = Util::getFromArray($this->_key, $config);
        //配置项不存在
        if (!$item) {
            $this->error = 2;
            return false;
        }

        //配置信息
        $this->_configInfo = $item;
        //屏蔽发短信
        if ($this->_configInfo['block'] === true) {
            $this->error = 4;
            return false;
        }
    }

    /**
     * 发送短信
     * $param string            $mobile
     * $param string            $content
     * $param string | false    $randomCode     是否需要发送动态码，默认否
     * $param boolean           $imageCode      图像验证码是否正确，默认是
     * return boolean
     **/
    public function send($mobile, $content, $randomCode = false) {
        //初始化之前有发生错误
        if ($this->error != 99) {
            return false;
        }

        include_once API_PATH . '/interface/SmsMobileInterface.class.php';

        $noSendKey = md5($this->_configInfo['interval_time_key'] . $mobile);
        $noSend    = MemberHelper::_getRedis($noSendKey);
        //某个时间段内不能发送手机动态码
        if ($noSend == 'true') {
            $this->error = 5;
            return false;
        }

        //开启动态码时
        if ($randomCode !== false) {
            $codeKey   = md5($this->_configInfo['dynamic_code_key'] . $mobile);
            $code      = MemberHelper::_getRedis($codeKey);
            //缓存为空时才重新写入动态码
            if (empty($code)) {
                MemberHelper::_setRedis($codeKey, $this->_configInfo['expire_time'], $randomCode);
                //第一次写入需重新给code赋值，因为之前的code为空
                $code = $randomCode;
            }
            $content = sprintf($content, $code);
        }

        //保存发送短信的间隔时间
        MemberHelper::_setRedis($noSendKey, $this->_configInfo['interval_time'], 'true');
        //发送短信
        $send = SmsMobileInterface::send(array(
            'phone_list'   => $mobile,
            'content'      => $content,
            'server_id'    => $this->_configInfo['server_id'],
        ));

        //成功
        if ($send) {
            $this->error = 0;
            return true;
        }
        //失败
        $this->error = 6;
    }

    /**
     * 检测动态码是否正确
     * @param  stirng $mobile
     * @param  stirng $yourCode
     * @return boolean
     **/
    public function checkCode($mobile, $yourCode) {
        if (empty($yourCode) || empty($mobile)) {
            return false;
        }
        $mem     = CacheNamespace::createCache(CacheNamespace::MODE_MEMCACHE, MemcacheConfig::$GROUP_WEB);
        $codeKey = md5($this->_configInfo['dynamic_code_key'] . $mobile);
        $code    = $mem->read($codeKey);
        if ($yourCode == $code) {
            return true;
        }
        return false;
    }

    /**
     * 获取配置文件
     * 存放在同级目录下
     **/
    private function _getConfig() {
        $path = dirname(__FILE__) . '/SmsUtilConf.php';
        if (!is_file($path)) {
            return false;
        }
        $config = include_once $path;
        return $config;
    }

    /**
     * 检测，目前只检测UUID
     *
     **/
    private function _check() {
        include_once FRAMEWORK_PATH . '/util/common/Util.class.php';
        $uuid = Util::getFromArray('eqs_uuid', $_COOKIE, '');
        if (empty($uuid) || !Util::isValidUuid($uuid)) {
            return false;
        }
        return true;
    }

    /**
     * 创建倒计时按钮
     *
     * @TODO
     **/
    public function createButtonHtml() {

    }

    /**
     * 检测配置文件参数完整性
     *
     * @TODO
     **/
    private function _checkConf() {

    }

}

//Example
//$obj        = new SmsUtil('car_member_mobile_login');
//$randomCode = rand(100000, 999999);
//$content    = '王尼玛，您中了大奖了，兑奖码是：%s【273二手车交易网】';
//$obj->send('15059130243', $content, $randomCode);
//echo $obj->error;//错误码
