<?php
require_once COM_PATH . '/car/CarAppVars.class.php';

/**
 * @package              V3
 * @subpackage           
 * @author               $Author:   guoch$
 * @file                 $HeadURL$
 * @version              $Rev$
 * @lastChangeBy         $LastChangedBy$
 * @lastmodified         $LastChangedDate$
 * @copyright            Copyright (c) 2012, www.273.cn
 */
class AppServConfVars {
    
    /**
     * @强制车源城市配置
     * 列表中的城市，必须上传车源图片才能够发布新的车源
     */
    public static $CONFIG_CITYS = array(
            //福建区域
            1 => 1,
            2 => 1,
            3 => 1,
            4 => 1,
            5 => 1,
            6 => 1,
            8 => 1,
            9 => 1,
            //浙江区域
            10 => 1,
            11 => 1,
            12 => 1,
            13 => 1,
            14 => 1,
            16 => 1,
            19 => 1,
            20 => 1,
            
    );
    /**
     * @brief 城市是否为认证车源城市
     *
     */
    public static function getConfigCity($city) {
        /*if (!isset(self::$CONFIG_CITYS[$city])) {
            return array();
        }
        return self::$CONFIG_CITYS[$city];*/
        return 1; //强制认证推向全国
    }
    
    /**
     * @desc 是否车况承诺城市, 
     */
    public static function getCkConfigCity($city) {
        if (in_array($city, CarAppVars::$CAR_CK_CONFIG)) {
            return 1;
        }
        return 0;
    }
}
