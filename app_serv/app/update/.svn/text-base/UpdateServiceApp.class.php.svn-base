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

class UpdateServiceApp {
    const CKB_VERSION = '1.82';
    const CKB_APK = 'CKB_v1.82.apk';

    //主站越狱
    const MS_IOS_VERSION = '1.4.8';
    //主站app store
    const MS_IOS_APP_VERSION = '1.4.6.2';
    //主站安卓
    const MS_ARD_VERSION = '1.4.7';

    //业管安卓
    const MBS_ARD_VERSION = '2.6';

    //业管ios越狱
    const MBS_IOS_VERSION = '2.6';

    //业管ios appStore
    const MBS_IOS_APP_VERSION = '2.6';
    
    //车况宝线下版
    const CKB_ARD_VERSION = '100';
    const CKB_ARD_APK = 'CKB_SIMPLE.apk';
    
    // 卖车app
    const SALEAPP_ARD_VERSION = '1.0';
    const SALEAPP_IOS_VERSION = '1.0';
 
    /**
     * 车况宝线下版，升级接口
     * @param  : 参数说明如下表格
     * 参数名称     | 参数类型  | 参数补充描述
     * ------------ |-----------|------------------------------------------------
     * version     | string    | 版本号
     * @return array
     * 返回值名称       | 返回值类型  | 返回值补充描述
     * -----------------|-------------|------------------------------------------------
     *  version         | string      | 最新版本号
     *  msg             | string      | 新版更新内容
     *  url             | string      | 下载URL 
     */
    public function getCKBVersion($params) {
        $ret = array();
        if (!isset($params['version'])) {
            throw new AppServException(AppServErrorVars::CUSTOM, 'version参数错误');
        }
        if ($params['version'] < self::CKB_ARD_VERSION) {
            $ret['version'] = self::CKB_ARD_VERSION;
            $ret['msg'] = "车况宝线下版，升级接口";
            $ret['url'] = 'https://192.168.5.31/dowload/' . self::CKB_ARD_APK;
        }
        return $ret;
    }

    public function getVersion($params) {
        $ret = array();
        if (!isset($params['version'])) {
            throw new AppServException(AppServErrorVars::CUSTOM, 'version参数错误');
        }
        if ($params['version'] < self::CKB_VERSION) {
            $ret['version'] = self::CKB_VERSION;
            $ret['msg'] = "1、适配小米平板\n";
            $ret['msg'] .= "2、修正了部分显示问题\n";

            //$ret['url'] = 'https://192.168.5.31/dowload/' . self::CKB_APK;
            $ret['url'] = 'https://appserv.273.cn/dowload/' . self::CKB_APK;
        }
        return $ret;
    }
    
    /**
     * @desc 主站ios版
     */
    public function getMsIosVersion($params) {
        if (!isset($params['version']) || !isset($params['type'])) {
            throw new AppServException(AppServErrorVars::CUSTOM, 'version或type参数错误');
        }
        if ($params['version'] == '1.0') {
                $params['type'] = 1 - $params['type'];
        }
        //越狱
        if ($params['type'] == 0) {
            if ($params['version'] != self::MS_IOS_VERSION) {
                $ret['version'] = self::MS_IOS_VERSION;
                $ret['msg'] = "1、优化门店功能，新增查看路线功能\n2、新增“我要砍价”功能\n3、新增车源分享功能\n4、修复了一些bug";
                $ret['url'] = 'http://mbsapi.273.com.cn/download/273ForiPhone_Web_v1.0.ipa?version='.self::MS_IOS_VERSION;
                //$ret['url'] = 'itms-services://?action=download-manifest&url=http://mbsapi.273.com.cn/download/web273.plist';
            }
        } elseif ($params['type'] == 1) {
            if ($params['version'] != self::MS_IOS_APP_VERSION) {
                $ret['version'] = self::MS_IOS_APP_VERSION;
                $ret['msg'] = "-1.调整首页样式，简化首页车源展示\n-2.新增地区可选择省份功能\n-3.新增精品推荐功能\n";
                $ret['url'] = 'https://itunes.apple.com/cn/app/273er-shou-che/id684233111?l=zh_CN&mt=8';
            }
        }
        return $ret;
    }
    /**
     * @desc 主站安卓版
     */
    public function getMsArdVersion($params) {
        $ret = array();
        if (!isset($params['version'])) {
            throw new AppServException(AppServErrorVars::CUSTOM, 'version参数错误');
        }
        if ($params['version'] != self::MS_ARD_VERSION) {
            $ret['version'] = self::MS_ARD_VERSION;
            $ret['msg'] = "1、优化门店功能，新增查看路线功能\n2、新增“我要砍价”功能\n3、新增车源分享功能\n4、修复了一些bug";
            $ret['force'] = 0;
            
            $ret['url'] = 'http://mbsapi.273.com.cn/download/273_web_v1.0.apk?version='.self::MS_ARD_VERSION;
        }
        return $ret;
    }
    public function getMbsArdVersion($params) {
        $ret = array();
        if (!isset($params['version'])) {
            throw new AppServException(AppServErrorVars::CUSTOM, 'version参数错误');
        }
        if($params['version'] == 2.8) {
            $ret['version'] = AppServVars::MBS_ARD_VERSION;
            $ret['msg'] = "发现新版本：v" . AppServVars::MBS_ARD_VERSION . "，全网淘车支持抢外网车源委托（福州地区）";
            $ret['url'] = 'http://mbsapi.273.com.cn/download/android.apk?version=2.91';
        } else if ($params['version'] < AppServVars::MBS_ARD_VERSION) {
            $ret['version'] = AppServVars::MBS_ARD_VERSION;
            $ret['msg'] = "发现新版本：v" . AppServVars::MBS_ARD_VERSION . "，全网淘车支持抢外网车源委托（福州地区）";
            $ret['url'] = 'http://mbsapi.273.com.cn/download/android.apk?version=2.91';
        }
        return $ret;
    }
    public function getMbsIosVersion($params) {
        $ret = array();
        if (!isset($params['version']) || !isset($params['type'])) {
            throw new AppServException(AppServErrorVars::CUSTOM, 'version或type参数错误');
        }
        //越狱
        if ($params['type'] == 0) {
            if ($params['version'] < AppServVars::MBS_IOS_VERSION) {
                $ret['version'] = AppServVars::MBS_IOS_VERSION;
                $ret['msg'] = "慧收款升级";
                $ret['url'] = 'itms-services://?action=download-manifest&url=https://dn-cheyou.qbox.me/car273.plist?2.9.1';
//                 $ret['url'] = 'http://www.273.cn/autoinstall.html';
            }
        } elseif ($params['type'] == 1) {
            if ($params['version'] < AppServVars::MBS_IOS_APP_VERSION) {
                $ret['version'] = AppServVars::MBS_IOS_APP_VERSION;
                $ret['msg'] = "慧收款升级";
                $ret['url'] = 'itms-services://?action=download-manifest&url=https://dn-cheyou.qbox.me/car273.plist?2.9.1';
                // $ret['url'] = 'http://www.273.cn/autoinstall.html';    
            }
        }
        return $ret;
    }
    
    /**
     * 卖车appIOS检查更新
     * @param  : 参数说明如下表格
     * 参数名称     | 参数类型  | 参数补充描述
     * ------------ |-----------|------------------------------------------------
     * version     | string    | 版本号
     * @return array
     * 返回值名称       | 返回值类型  | 返回值补充描述
     * -----------------|-------------|------------------------------------------------
     *  version         | string      | 最新版本号
     *  msg             | string      | 新版更新内容
     *  url             | string      | 下载URL 
     */
    public function getSaleAppIOSVersion($params) {
        $ret = array();
        if (!isset($params['version'])) {
            throw new AppServException(AppServErrorVars::CUSTOM, 'version参数错误');
        }
        if ($params['version'] < self::SALEAPP_IOS_VERSION) {
            $ret['version'] = self::SALEAPP_IOS_VERSION;
            $ret['msg'] = '';
            $ret['url'] = '';
        }
        return $ret;
    }
    
    /**
     * 卖车appAndroid检查更新
     * @param  : 参数说明如下表格
     * 参数名称     | 参数类型  | 参数补充描述
     * ------------ |-----------|------------------------------------------------
     * version     | string    | 版本号
     * @return array
     * 返回值名称       | 返回值类型  | 返回值补充描述
     * -----------------|-------------|------------------------------------------------
     *  version         | string      | 最新版本号
     *  msg             | string      | 新版更新内容
     *  url             | string      | 下载URL 
     */
    public function getSaleAppArdVersion($params) {
        $ret = array();
        if (!isset($params['version'])) {
            throw new AppServException(AppServErrorVars::CUSTOM, 'version参数错误');
        }
        if ($params['version'] < self::SALEAPP_ARD_VERSION) {
            $ret['version'] = self::SALEAPP_ARD_VERSION;
            $ret['msg'] = '';
            $ret['url'] = '';
        }
        return $ret;
    }
}
