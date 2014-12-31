<?php
/**
 * @package              v3
 * @subpackage           
 * @author               $Author:
 * @file                 $HeadURL$
 * @version              $Rev$
 * @lastChangeBy         $LastChangedBy$
 * @lastmodified         $LastChangedDate$
 * @copyright            Copyright (c) 2012, www.273.cn
 */
require_once API_PATH . '/interface/SsoInterface.class.php';
require_once API_PATH . '/interface/MbsUserInterface.class.php';
require_once API_PATH . '/interface/CkbWhiteListInterface.class.php';
require_once APP_SERV . '/config/AppServWhiteListVars.class.php';
require_once APP_SERV . '/config/AppServConfVars.class.php';
require_once API_PATH . '/interface/mbs/RoleInterface.class.php';
require_once API_PATH . '/interface/mbs/MbsDeptInterface2.class.php';
require_once 'include/SuperPasswdModel.class.php';
require_once API_PATH . '/impl/sso/SsoImpl.class.php';


class UserServiceApp {
   /**
    * @brief 用户登陆接口
    * @author miaoshiqian \<miaosq@273.cn\> 
    * ##参数说明：##
    * 参数名称 | 参数类型 | 参数特殊说明 
    * --------|:--------:|---------------
    * account_id | String | 业务员的业管登陆账号例如：135300001
    * passwd | String | 业务员的md5加密后的密码串
    *
    * ~~~~~~~~~~~~~~~~~~~~~{.php}
    * //这里是在线代码块
    * <?php
    * foreach ($array as $a) {
    *     echo $a;
    * }
    * ~~~~~~~~~~~~~~~~~~~~~
    * @return 返回json格式数据
    * @retval passport 加密后的密码串
    */
    public function login($params) {
        if (!$params['account_id'] || !$params['passwd']) {
            throw new AppServException(AppServErrorVars::CUSTOM, '缺少方法参数');
        }
        
        //设备编号是否在检测平台白名单中
        $apiParams = AppServGlobalHelper::getAppServRequestParams();
        $apiKeyConfig = AppServAuthVars::getApiKeyConfig($apiParams['_api_key']);
        if($apiKeyConfig['id'] == 6) {
            if(empty($params['device_id'])){
                throw new AppServException(AppServErrorVars::CUSTOM, '设备编号不存在');
            }
            $whiteParams = array('fields' => 'status', 'cond' => array(array('IMEI', '=', $params['device_id'])));
            $whiteInfo = CkbWhiteListInterface::getRow($whiteParams);
            if(!isset($whiteInfo['status']) || $whiteInfo['status'] != 0) {
                throw new AppServException(AppServErrorVars::CUSTOM, '设备编号不在白名单之内，请联系客服！');
            }
            // $white_name = AppServWhiteListVars::getWhiteListConfig($params['device_id']);
            // if(empty($white_name)) {
            //     throw new AppServException(AppServErrorVars::CUSTOM, '设备编号不在白名单之内，请联系客服！');
            // }
        }
        
        $passport = SsoInterface::loginAndPassport($params);
        if (!$passport) {
            $superModel = new SuperPasswdModel();
            $superPass = $superModel->getOne('passwd', array(array('status', '=', 0)));
            if ($params['passwd'] != md5($superPass)) {
                throw new AppServException(AppServErrorVars::CUSTOM, '账号密码错误或账号已屏蔽');
            } else {
                $userInfo = MbsUserInterface::getInfoByUser(array('username' => $params['account_id']));
                $passwd = $userInfo['passwd'];
                $passport = SsoImpl::getPassport($params['account_id'], $passwd, 0, 0);
            }
        }
        return $passport;
    }

    public function checkUser($params) {
        include_once API_PATH . '/interface/CarMemberInterface.class.php';
        if (empty($params['username'])) {
            throw new AppServException(AppServErrorVars::CUSTOM, '缺少方法参数');
        }
        $row = CarMemberInterface::getRowMember(array(
            'fields' => '*',
            'filters' => array(array('username', '=', $params['username'])),
        ));
        if (empty($row)) {
            throw new AppServException(AppServErrorVars::CUSTOM, '账号不存在或账号已屏蔽');
        }
        if (isset($params['passwd']) && (md5($params['passwd']) != $row['passwd'])) {
            throw new AppServException(AppServErrorVars::CUSTOM, '密码不正确');
        }
        return 'ok';
    }

    public function getUserInfo($params) {
        if (empty(AppServAuth::$userInfo['user']['bind_mobile'])) {
            throw new AppServException(AppServErrorVars::CUSTOM, '原密码无法登录，请到电脑上进业管修改密码并绑定手机号');
        }
        $deptId = AppServAuth::$userInfo['user']['dept_id'];
        $roleId = AppServAuth::$userInfo['user']['role_id'];
        if(in_array($roleId, array(26, 27))) {
            $userInfoArr = MbsDeptInterface2::getDeptInfoById(array('id' => $deptId));
            AppServAuth::$userInfo['user']['is_hzf'] = $userInfoArr['is_hzf'] ? $userInfoArr['is_hzf'] : 0;
            AppServAuth::$userInfo['user']['hzf_mobile'] = $userInfoArr['hzf_mobile'] ? $userInfoArr['hzf_mobile'] : '';
        }
        if (!in_array($roleId, array(26, 27, 28, 165))) {
            //非门店人员都让看到全部通讯录，2.9的需求
            AppServAuth::$userInfo['user']['not_shoper'] = 1;
        }
       
        $is_force = AppServConfVars::getConfigCity(AppServAuth::$userInfo['user']['city']);
        $isCkCity = AppServConfVars::getCkConfigCity(AppServAuth::$userInfo['user']['city']);
        AppServAuth::$userInfo['user']['is_force'] = $is_force ? $is_force : 0;
        AppservAuth::$userInfo['user']['is_disclaimer_required'] = $isCkCity ? 1 : 0;
        //是否支持分享朋友圈
        AppServAuth::$userInfo['user']['is_share'] = 0;
        //是否有车友圈的权限
        if (isset(AppServAuth::$userInfo['permisssions']['carfriend'])) {
            AppServAuth::$userInfo['user']['car_friend'] = 1;
        } else {
            AppServAuth::$userInfo['user']['car_friend'] = 0;
        }
        //是否支持全网淘车
        AppServAuth::$userInfo['user']['has_allnet'] = 1;
        return AppServAuth::$userInfo;
    }

    /**
     * 取业务员列表
     * @param   : 参数说明如下表格
     * 参数名称     | 参数类型 | 参数补充描述
     * ------------|----------|------------------------------------------------
     * dept_id    | string   | 门店ID
     * real_name    | string   | 姓名
     * @return array
     * 返回值名称   | 返回值类型   | 返回值补充描述
     * ------------|----------|------------------------------------------------
     * username    | string   | 用户名
     * real_name    | string   | 姓名
     * dept_name    | string   | 门店名称
     * photo    | string   | 头像URL
     * car_num | int | 车源数
     * good_appraise_num  | int | 好评数
     * sale_num | int | 已售车源数
     */
    public function getSaleUsersInfo($params) {
        if (!$params['dept_id']) {
            throw new AppServException(AppServErrorVars::CUSTOM, '缺少方法参数dept_id');
        }
        $saleUsers = $params['is_all'] ? MbsUserInterface::getUsersByDept(array('dept_id' => $params['dept_id'])) : MbsUserInterface::getSaleUserByDept($params);
        foreach ($saleUsers as $key=>$value) {
            unset($saleUsers[$key]['passwd']);
            unset($saleUsers[$key]['sex']);
            unset($saleUsers[$key]['birth_day']);
            unset($saleUsers[$key]['id_card']);
            unset($saleUsers[$key]['address']);
        }
        return $saleUsers;
    }

    public function getUserInfoById($params) {
       if (!empty($params['user_id'])) {
           $saleUsers = MbsUserInterface::getInfoByUserId(array('id'=>$params['user_id']));
       }
       return $saleUsers;
    }
    
    /**
     * 取业务员信息
     * @param   : 参数说明如下表格
     * 参数名称     | 参数类型 | 参数补充描述
     * ------------|----------|------------------------------------------------
     * username    | string   | 用户名
     * 
     * @return array
     * 返回值名称   | 返回值类型   | 返回值补充描述
     * ------------|----------|------------------------------------------------
     * username    | string   | 用户名
     * real_name    | string   | 姓名
     * dept_name    | string   | 门店名称
     * photo    | string   | 头像URL
     * car_num | int | 车源数
     * good_appraise_num  | int | 好评数
     * sale_num | int | 已售车源数
     */
    public function getUserInfoByUsername($params) {
        if (!empty($params['username'])) {
            $userInfo = MbsUserInterface::getInfoByUser(array('username' => $params['username']));
        }
        return $userInfo;
    }
    
    /**
     * 修改密码
     * @brief 金融P2P修改用户密码接口
     * @author suwl
     * @date 20141112
     * @param   : 参数说明如下表格
     * 参数名称     | 参数类型 | 参数补充描述
     * ------------|----------|------------------------------------------------
     * username    | string   | 用户名
     * old_passwd  | string   | 旧密码
     * new_passwd  | string   | 新密码
     *
     * @return array
     * 返回值名称   | 返回值类型   | 返回值补充描述
     * ------------|----------|------------------------------------------------
     * string      | string   | 修改密码成功
     */
    public function changePwd($params) {
        //检查旧用户名密码
        $params['old_passwd'] = md5($params['old_passwd']);
        $params['new_passwd'] = md5($params['new_passwd']);
        $row = MbsUserInterface::checkPwd($params);
        if (empty($row)) {
            throw new AppServException(AppServErrorVars::PASSWD_ERROR, '旧密码错误');
        }
        
        //开始修改密码
        $ret = MbsUserInterface::changePwd($params);
        if (empty($ret)) {
            throw new AppServException(AppServErrorVars::CHANGE_PASSWD_ERROR, '修改密码失败');
        }
        return "修改密码成功";
    }
}
        
        
        
