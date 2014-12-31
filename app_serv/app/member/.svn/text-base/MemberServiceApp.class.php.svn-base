<?php
/**
 * 会员相关接口
 * @author 戴志君
 * @version 1.0
 * @date 2014-11-03
 */
require_once APP_SERV . '/config/AppServConfVars.class.php';
require_once API_PATH . '/interface/car/CarMemberV2Interface.class.php';
require_once dirname(__FILE__) . '/include/MemberHelper.class.php';

class MemberServiceApp {

    /**
     * @desc 短信配置
     * @var array
     **/
    private $_smsConfig;

    /**
     * @desc 短信配置Key
     * @var string
     **/
    private $_configKey = 'app_member_validcode';

    /**
     * @desc 快速登录默认密码,待配置
     * @var string
     **/
    private $_defaultPW = 'www273cn';


    /**
     * 登录
     * @param  : 参数说明如下表格
     * 参数名称                 | 参数类型         | 参数补充描述
     * ------------|----------|------------------------------------------------
     * username    | string   | 用户名
     * passwd      | string   | 密码
     *
     * @return string
     * 返回值名称                | 返回值类型     | 返回值补充描述
     * -------------|----------|------------------------------------------------
     * passport     | string   | 用户身份串，用于需要登录访问的接口
     */
    public function login($params) {
        if (!$params['username'] || !$params['passwd']) {
            throw new AppServException(AppServErrorVars::CUSTOM, '缺少方法参数');
        }

        //验证账号
        $userInfo = CarMemberV2Interface::getInfoByUsername($params);
        if (empty($userInfo)) {
            throw new AppServException(AppServErrorVars::CUSTOM, '用户不存在');
        }
        $passwd = md5($params['passwd']);
        if ($passwd != $userInfo['passwd']) {
            throw new AppServException(AppServErrorVars::CUSTOM, '用户名或者密码错误');
        }

        //更新登录信息
        CarMemberV2Interface::updateLoginInfo(array('id' => $userInfo['id']));

        //通过验证，生成passport验证串
        $passport = MemberHelper::getPassport($params['username'], $passwd, 0, 0);
        if (!$passport) {
            throw new AppServException(AppServErrorVars::ERROR_DEFAULT);
        }
        return $passport;
    }

    /**
     * 快速登录
     * @param  : 参数说明如下表格
     * 参数名称                 | 参数类型         | 参数补充描述
     * ------------|----------|------------------------------------------------
     * username    | string   | 用户名
     * code        | string   | 验证码
     * @return array
     * 返回值名称                | 返回值类型     | 返回值补充描述
     * -------------|----------|------------------------------------------------
     * member_id    |  int     | 会员id
     * passport     | string   | 用户身份串，用于需要登录访问的接口
     */
    public function fastLogin($params) {
        if (!$params['username'] || !$params['code']) {
            throw new AppServException(AppServErrorVars::CUSTOM, '缺少方法参数');
        }

        //验证验证码是否正确
        if (!$this->_checkCode($params['username'], $params['code'])) {
            throw new AppServException(AppServErrorVars::CUSTOM, '验证码错误');
        }

        $userInfo = CarMemberV2Interface::getInfoByUsername($params);

        $username = $params['username'];
        $passwd   = '';
        if (empty($userInfo)) {
            //不存在则注册用户，密码采用默认
            $passwd = md5($this->_defaultPW);
            $registerInfo = array(
               'username'        => $username,
               'passwd'          => $passwd,
               'passwd_strength' => $this->_checkStrength($this->_defaultPW),
               'mobile'          => $username
            );
            CarMemberV2Interface::register($registerInfo);
        } else {
            $passwd = $userInfo['passwd'];
        }
        //更新登录信息
        CarMemberV2Interface::updateLoginInfo(array('id' => $userInfo['id']));

        $passport = MemberHelper::getPassport($params['username'], $passwd, 0, 0);
        if (!$passport) {
            throw new AppServException(AppServErrorVars::ERROR_DEFAULT);
        }
        $loginInfo = array('member_id' =>$userInfo['id'],'passport' => $passport);
        return $loginInfo;
    }

    /**
     * 注册
     * @param  : 参数说明如下表格
     * 参数名称                 | 参数类型         | 参数补充描述
     * ------------|----------|------------------------------------------------
     * username    | string   | 用户名
     * passwd      | string   | 密码
     * code        | string   | 验证码
     *
     * @return  string
     * 返回值名称                | 返回值类型     | 返回值补充描述
     * -------------|----------|------------------------------------------------
     * passport     | string   | 用户身份串，用于需要登录访问的接口
     */
    public function register($params) {
        if (!$params['username'] || !$params['passwd'] || !$params['code']) {
            throw new AppServException(AppServErrorVars::CUSTOM, '缺少方法参数');
        }
        //验证是否已注册
        $userInfo = CarMemberV2Interface::getInfoByUsername($params);
        if (!empty($userInfo)) {
            throw new AppServException(AppServErrorVars::CUSTOM, '该号码已被注册');
        }

        //验证验证码是否正确
        if ($this->_checkCode($params['username'], $params['code'])) {
            $registerInfo = array(
               'username'        => $params['username'],
               'passwd'          => md5($params['passwd']),
               'passwd_strength' => $this->_checkStrength($params['passwd']),
               'mobile'          => $params['username']
            );
            CarMemberV2Interface::register($registerInfo);
        } else {
            throw new AppServException(AppServErrorVars::CUSTOM, '验证码错误');
        }
        $passwd = md5($params['passwd']);
        $passport = MemberHelper::getPassport($params['username'], $passwd, 0, 0);
        if (!$passport) {
            throw new AppServException(AppServErrorVars::ERROR_DEFAULT);
        }
        return $passport;
    }

    /**
     * 取会员信息
     * @param  : 参数说明如下表格
     * 参数名称                 | 参数类型         | 参数补充描述
     * ------------|----------|------------------------------------------------
     * username    | string   | 用户名
     * passport    | string   | 用户身份串
     *
     * @return array()
     * 返回值名称             | 返回值类型      | 返回值补充描述
     * ------------|----------|------------------------------------------------
     * member_id   | int      | 用户id
     * username    | string   | 用户名
     * real_name   | string   | 姓名
     * mobile      | string   | 手机号
     * email       | string   | 邮箱
     * address     | string   | 看车地点
     */
    public function getInfo($params) {
        if (!$params['username'] || !$params['passport']) {
            throw new AppServException(AppServErrorVars::CUSTOM, '缺少方法参数');
        }
        
        $userInfo = MemberHelper::parsePassport($params['passport']);
        if ($userInfo) {
            $backData = array (
               'member_id'   => $userInfo['id'],
               'username'    => $userInfo['username'],
               'real_name'   => $userInfo['real_name'],
               'mobile'      => $userInfo['moblie'],
               'email'       => $userInfo['email'],
               'address'     => $userInfo['address']
            );
            return $backData;
        }
        throw new AppServException(AppServErrorVars::CUSTOM, '验证信息过期或有误');
    }

    /**
     * 修改会员基本信息
     * @param  : 参数说明如下表格
     * 参数名称                 | 参数类型         | 参数补充描述
     * ------------|----------|------------------------------------------------
     * username    | string   | 用户名
     * passport    | string   | 用户身份串
     * real_name   | string   | 用户真实姓名(根据修改内容传值)
     * address     | string   | 看车地址(根据修改内容传值)
     *
     * @return bool
     * 返回值名称             | 返回值类型      | 返回值补充描述
     * ------------|----------|------------------------------------------------
     * flag        | bool     | 是否修改成功
     */
    public function updateInfo($params) {
        if (!$params['username'] || !$params['passport']) {
            throw new AppServException(AppServErrorVars::CUSTOM, '缺少方法参数');
        }

        $userInfo = MemberHelper::parsePassport($params['passport']);
        if ($userInfo) {
            $update = array (
               'id'          => $userInfo['id'],
               'real_name'   => $params['real_name'],
               'address'     => $params['address'],
            );
            return $change = CarMemberV2Interface::changeUserInfo($update);
        }
        throw new AppServException(AppServErrorVars::CUSTOM, '验证信息过期或有误');
    }


    /**
     * 修改密码
     * @param  : 参数说明如下表格
     * 参数名称                 | 参数类型         | 参数补充描述
     * ------------|----------|------------------------------------------------
     * username    | string   | 用户名
     * passport    | string   | 用户身份串
     * passwd      | string   | 新密码
     *
     * @return  string
     * 返回值名称             | 返回值类型     | 返回值补充描述
     * ------------|----------|------------------------------------------------
     * passport    | string   | 新的用户身份串，用于需要登录访问的接口
     */
    public function changePasswd($params) {
        if (!$params['username'] || !$params['passport'] || !$params['passwd']) {
            throw new AppServException(AppServErrorVars::CUSTOM, '缺少方法参数');
        }
        $userInfo = MemberHelper::parsePassport($params['passport']);
        if ($userInfo) {
            $changePwd = CarMemberV2Interface::changePassword(array(
                'id'              => $userInfo['id'],
                'new_password'    => $params['passwd'],
                'passwd_strength' => $this->_checkStrength($params['passwd'])
            ));
            $passport = MemberHelper::getPassport($params['username'], md5($params['passwd']), 0, 0);
            return $passport;
        }
        throw new AppServException(AppServErrorVars::CUSTOM, '验证信息有误');
    }

    /**
     * 重置密码验证
     * @param  : 参数说明如下表格
     * 参数名称                 |  参数类型      | 参数补充描述
     * ------------|----------|------------------------------------------------
     * username    | string   | 用户名
     * code        | string   | 验证码
     *
     * @return  string
     * 返回值名称             | 返回值类型     | 返回值补充描述
     * ------------|----------|------------------------------------------------
     * passport    | string   | 用户身份串，用于需要登录访问的接口
     */
    public function checkResetCode($params) {
        if (!$params['username'] || !$params['code']) {
            throw new AppServException(AppServErrorVars::CUSTOM, '缺少方法参数');
        }

        //验证验证码是否正确
        if (!$this->_checkCode($params['username'], $params['code'])) {
            throw new AppServException(AppServErrorVars::CUSTOM, '验证码错误');
        }

        $userInfo = CarMemberV2Interface::getInfoByUsername($params);
        $passport = MemberHelper::getPassport($userInfo['username'], $userInfo['passwd'], 0, 0);
        if (!$passport) {
            throw new AppServException(AppServErrorVars::ERROR_DEFAULT);
        }
        return $passport;
    }

    /**
     * 获取验证码
     * @param  : 参数说明如下表格
     * 参数名称                 |  参数类型      | 参数补充描述
     * ------------|----------|------------------------------------------------
     * username    | string   | 手机号码
     * type        | string   | 值 = register，login，reset 分别表示:注册，快速登录，找回密码
     *
     * @return  bool
     * 返回值名称             | 返回值类型     | 返回值补充描述
     * ------------|----------|------------------------------------------------
     * flag        | bool     | 是否成功
     */

    public function sendMobileValidCode($params) {
        $mobile   = $params['username'];
        require_once FRAMEWORK_PATH . '/util/common/Util.class.php';
        require_once dirname(__FILE__) . '/include/SmsUtil.class.php';
        $content  = '您的验证码为：%s，30分钟内有效。如非本人操作，请致电4006000273【273二手车】';
        $sms      = new SmsUtil('app_member_validcode');
        $sms->send($mobile, $content, rand(100000, 999999));
        $error = $sms->error;
        
        switch($error) {
        	case 0:
        		return true;
        		break;
        	case 3:
        		throw new AppServException(AppServErrorVars::CUSTOM, '参数验证有误');
        		break;
        	case 5:
        		throw new AppServException(AppServErrorVars::CUSTOM, '发送太频繁，请间隔60秒发送');
        		break;
        	default:
        		throw new AppServException(AppServErrorVars::CUSTOM, '异常错误');
        		break;
        }
    }

    /**
     * @desc 校对验证码
     * @return
     */
    private function _checkCode($mobile, $postCode) {
        require_once FRAMEWORK_PATH . '/util/common/Util.class.php';
        $config = $this->_getConfig();

        //配置文件不存在
        if ($config === false) {
            return false;
        }

        $item = Util::getFromArray($this->_configKey, $config);
        $this->_smsConfig = $item;

        $codeKey   = md5($this->_smsConfig['dynamic_code_key'] . $mobile);
        $code = MemberHelper::_getRedis($codeKey);

        if (empty($code)) {
            return false;
        } elseif ($code == $postCode) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取短信配置
     *
     **/
    private function _getConfig() {
        $path = dirname(__FILE__) . '/include/SmsUtilConf.php';
        if (!is_file($path)) {
            return false;
        }
        $config = include_once $path;
        return $config;
    }

    /**
     * @desc 验证密码强度
     * @param string 密码
     * @return int   密码强度等级
     */
    private function _checkStrength($password) {
        include_once FRAMEWORK_PATH . '/util/common/PasswordStrength.class.php';
        $obj      = new PasswordStrength;
        return $rank = $obj->check($password);
    }
}
