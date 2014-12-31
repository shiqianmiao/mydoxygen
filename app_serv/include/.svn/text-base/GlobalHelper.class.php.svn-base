<?php
/**
 * @brief 全局工具类文件
 * @author 缪石乾
 * @date 2014-10-16
 */

require_once dirname(__FILE__) . '/../config/config.inc.php';
require_once API_PATH . '/interface/mbs/MbsUserInterface2.class.php';

class GlobalHelper {
    /**
     * @brief 记录该用户的最后活跃时间
     * @param $userInfo 登陆的用户信息
     */
    public static function setLastLoginTime($userInfo) {
        if (empty($userInfo)) {
            return;
        }
        
        $upParams = array(
            'info' => array(
                'mobile_last_login_time' => time(),
            ),
            'filters' => array(array('username', '=', $userInfo['username'])),
        );
        MbsUserInterface2::updateUserInfo($upParams);
    }
}
?>