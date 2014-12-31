<?php
/**
 * @package              v3
 * @subpackage           
 * @author               $Author:   guoch
 * @file                 $HeadURL$
 * @version              $Rev$
 * @lastChangeBy         $LastChangedBy$
 * @lastmodified         $LastChangedDate$
 * @copyright            Copyright (c) 2012, www.273.cn
 * @brief                发给客户端的配置。
 */
require_once APP_SERV . '/config/AppServConfVars.class.php';
class ConfigServiceApp {
    
    //是否为强制认证车源的地区
    public function getConfig() {
        //$is_force = AppServConfVars::getConfigCity(AppServAuth::$userInfo['user']['city']);
        //$ret['is_force'] = $is_force ? $is_force : 0;
        $isCkCity = AppServConfVars::getCkConfigCity(AppServAuth::$userInfo['user']['city']); //是否车况承诺显示城市
        $ret['is_force'] = 1; //强制认证推向全国
        $ret['is_disclaimer_required'] = $isCkCity;
        //是否支持分享朋友圈
        $ret['is_share'] = 0;
        return $ret;
    }
    
    /**
     * @desc 获取车架事故痕迹的数组
     */
    public function getFrameConfig() {
        include_once COM_PATH . '/check/CheckVars.class.php';
        $frameInfo = array();
        if (!empty(CheckVars::$FRAME) && !empty(CheckVars::$FRAME_P2P)) {
            foreach (CheckVars::$FRAME_P2P as $key => $p) {
                $frameInfo[] = array(
                    'code' => $p,
                    'name' => CheckVars::$FRAME[$p],
                );
            }
        }
        
        return $frameInfo;
    }

    public function getTagsConfig() {
        include_once APP_SERV . '/config/AppMsSaleVars.class.php';
        $ret = array();
        foreach (AppMsSaleVars::$TAG_ICONS as $item) {
            $ret[] = $item;
        }
        return $ret;
    }
}
