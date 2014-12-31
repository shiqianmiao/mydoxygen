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
require_once API_PATH . '/interface/ExtPhoneInterface.class.php';
require_once API_PATH . '/interface/MbsUserInterface.class.php';
require_once API_PATH . '/interface/MbsDeptInterface.class.php';

class ExtphoneServiceApp {

    public function updateUserAllBind($params) {
        if (!$params['follow_user'] || !$params['new_mobile'] || !$params['dept_city']) {
            throw new AppServException(AppServErrorVars::CUSTOM, '缺少参数或参数错误');
        }
        return ExtPhoneInterface::updateUserAllBind($params);
    }

    public function updateDeptAllBind($params) {
        if (!$params['dept_id'] || !$params['new_telephone'] || !$params['dept_city']) {
            throw new AppServException(AppServErrorVars::CUSTOM, '缺少参数或参数错误');
        }
        return ExtPhoneInterface::updateDeptAllBind($params);
    }

    public function getInfoByExt($params) {
        if (!$params['phone_ext']) {
            throw new AppServException(AppServErrorVars::CUSTOM, '缺少参数或参数错误');
        }
        $info = ExtPhoneInterface::getInfoByExt($params);
        if (empty($info)) {
            return array();
        }
        $deptInfo = MbsDeptInterface::getDeptInfoByDeptId(array('id' => $info['dept_id']));
        $info['dept_name'] = $deptInfo['dept_name'];
        $info['phone'] = $deptInfo['telephone'];
        $info['real_name'] = '';
        if ($info['follow_user']) {
            $userInfo = MbsUserInterface::getInfoByUser(array('username' => $info['follow_user']));
            $info['real_name'] = $userInfo['real_name'];
            $info['phone'] = $userInfo['mobile'];
            if ($info['mobile']) {
                $info['phone'] = $info['mobile'];
            }
        }
        return $info;
    }

    public function getPhoneByUser($params) {
        $userIds = explode(',', $params['follow_user']);
        if (!$userIds) {
            throw new AppServException(AppServErrorVars::CUSTOM, '缺少参数或参数错误');
        } elseif (count($userIds) < 2) {
            $params['follow_user'] = $userIds[0];
        } else {
            $params['follow_user'] = $userIds;
        }

        return ExtPhoneInterface::getPhonebyUser($params);
    }
    
    public function bindNumber($params) {
        require_once API_PATH . '/interface/CallTransferInterface.class.php';
        return CallTransferInterface::bindNumber($params);
    }
    
    public function getInfoByExtPhone($params) {
        require_once API_PATH . '/interface/CallTransferInterface.class.php';
        return CallTransferInterface::getInfoByExtPhone($params);
    }
    
    /**
     * 绑定结果
     * @param type $params
     *      queue_id   唯一标识ID  
     * @return type
     */
    public function getBindResult($params) {
        require_once API_PATH . '/interface/CallTransferInterface.class.php';
        return CallTransferInterface::getBindResult($params);
    }
    
    /**
     * 解绑
     * @param type $params
     *      unique_id   唯一标识ID  一般为车源ID
     * @return type
     */
    public function delNumber($params) {
        require_once API_PATH . '/interface/CallTransferInterface.class.php';
        return CallTransferInterface::delNumber($params);
    }
    
    public function getPhoneCallLog($params) {
        require_once API_PATH . '/interface/PhoneCallLogInterface.class.php';
        if ($params['ent_exts']) {
            $params['ent_exts'] = explode(',', $params['ent_exts']);
        }
        return PhoneCallLogInterface::getList($params);
    }
    
    /**
     * 根据门店ID 取出转接号
     * @param  $params
     * @return 
     */
    public function getShowExtPhoneByDept($params) {
        require_once API_PATH . '/interface/MbsDeptInterface.class.php';
        if (!$params['dept_id']) {
            return array();
        }
        return MbsDeptInterface::getShowExtPhoneByDept($params);
    }
}
