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
class AppServAuthVars {
    /**
     * @brief 273主站
     *
     */
    const API_KEY_MS = 'fa1f58046f169f08d3ebf086a11399e4';
    /**
     * @brief 273业管
     *
     */
    const API_KEY_MBS = 'fe005b553f24b5665447bd5403132ec0';
    /**
     * @brief 273手机客户端
     *
     */
    const API_KEY_PHONE = '8a7f95774b1ce149fda1025298c310d9'; 
    /**
     * @brief 273wap
     *
     */
    const API_KEY_WAP = 'ec52f6006e6adeb1cfd7c15a09666d0c'; 
    /**
     * @brief 平安保险
     *
     */
    const API_KEY_PINAN = '2768005bae28396c02e2b5dd84c16b2c'; 
    /**
     * @brief 检测平台
     */
    const API_KEY_CHECK = 'a498aab9eef02cf6c07a68d549facdc3';
    /**
     * @brief 爱卡汽车
     */
    const API_KEY_XCAR = '5dd24a00c096d9a92bbf4d9e07ed764f';
    
    /**
     * @brief 招商
     */
    const API_KEY_ZS = 'e3293126764dfef477e824bdea7f43b2';
    
    /**
     * @brief 车多少
     */
    const API_KEY_CDS = 'b1da7839edd4969a1aaa4435721a149c';
    
    /**
     * @brief 二手车之家
     */
    const API_KEY_CHE168 = '2f4af11e0568bb5fc610b070e545906f';

    /**
     * @brief 门店合作
     */
    const API_KEY_DEPT_OPEN = '794cda310b9a02ad271019917b005353';
    
    /**
     * @brief 卖车app
     */
    const API_KEY_SALE_APP = 'ec5738f30ab32c3fec8b404fd8fa669c';
    
    /**
     * @brief P2P金融外包
     */
    const API_KEY_P2P_FINANCE = '6a2caee9d65fd664134c62f7c5a8cea2';

    /**
     * @brief P2P金融外包
     */
    const API_KEY_WX_SEARCH_CAR = '8a7f95774b1c2222da1025298c310d92';

    /**
     * @brief _api_key配置
     * id:编号，api_secret:秘钥，is_inside:是否内部调用,idetity:此_api_key表示出的身份
     * authorization:授权的方法但是is_inside=1时不受限制，limit_range_ip：受限的ip段*表示此段任意is_inside=1时不受限制
     */
    public static $API_KEY_CONFIG = array(
        self::API_KEY_MS => array(
            'id'            => 1,
            'api_secret'    => '0f6a57a943273460f9712ee79b0a8dfb',     
            'is_inside'     => 1,
            'identity'      => 'api_key_ms',
            //'authorization' => array('dept.getDeptInfo','car.getList'),
            //'limit_range_ip' => array('192.168.*.*', '211.111.56.201'),
        ),
        self::API_KEY_MBS => array(
            'id'            => 2,
            'api_secret'    => 'd60826497bf75b771e3634fb38ca2774',     
            'is_inside'     => 1,
            'identity'      => 'api_key_mbs',
        ),
        self::API_KEY_PHONE => array(
            'id'            => 3,
            'api_secret'    => '84805ac06fba5fd3a46185a944e616ee',     
            'is_inside'     => 1,
            'identity'      => 'api_key_phone',
        ),
        self::API_KEY_WAP => array(
            'id'            => 4,
            'api_secret'    => 'a0bdae01b086bd62974bd276c3e5d911',     
            'is_inside'     => 1,
            'identity'      => 'api_key_wap',
        ),
        self::API_KEY_PINAN => array(
            'id'            => 5,
            'api_secret'    => 'cbada4d759a362b3934f12a45cc1ede2',     
            'identity'      => 'api_key_pinan',
            'authorization' => array('evaluate.getHistoryEvaluate', 'evaluate.getResult', 'evaluate.publish'),
        ),
        self::API_KEY_CHECK => array(
            'id'            => 6,
            'api_secret'    => 'f506428ac1e31a49b4521f507d29a952',     
            'is_inside'     => 1,
            'identity'      => 'api_key_check',
        ),
        self::API_KEY_XCAR 	=> array(
            'id'            => 7,
            'api_secret'    => '40651018a95eb0a796f0cc4cc024d97b',     
            'is_inside'     => 1,
            'identity'      => 'api_key_xcar',
        ),
        self::API_KEY_ZS => array(
            'id'            => 8,
            'api_secret'    => 'a925cc85f672bb3597f684828b2b5be8',     
            'identity'      => 'api_key_zs',
            'authorization' => array('extphone.bindNumber', 'extphone.getBindResult', 'extphone.delNumber','extphone.getPhoneCallLog'),
        ),
        //车多少配置
        self::API_KEY_CDS => array(
            'id'            => 9,
            'api_secret'    => '99002bc1a465bdcc45ec64fa90b53c6f',
            'identity'      => 'api_key_cds',
            'authorization' => array('priceevaluate.getEvaluate'),
        ),
        
        self::API_KEY_CHE168 => array(
            'id'            => 10,
            'api_secret'    => '7cca67e8ffddad34a047ad524e152b6a',
            'identity'      => 'api_key_che168',
            'authorization' => array('car.getSaleList'),
        ),

        self::API_KEY_DEPT_OPEN => array(
            'id'            => 11,
            'api_secret'    => 'ab3370bdf5c3ace511f2e91c4ac8101b',
            'identity'      => 'api_key_dept_open',
            'is_inside'     => 1,
        ),
        self::API_KEY_SALE_APP => array(
            'id'            => 12,
            'api_secret'    => 'e5d285e0f75221f3ac77d6fd33ca5e7d',
            'identity'      => 'api_key_sale_app',
            'is_inside'     => 1,
        ),
        self::API_KEY_P2P_FINANCE => array(
            'id'            => 13,
            'api_secret'    => 'f48ad820107dfa0aa452e9a073bfbea7',
            'identity'      => 'api_key_p2p_finance',
            'authorization' => array('user.login','user.getUserInfo','user.getUserInfoById','user.getUserInfoByUsername','user.changePwd'),
        ),
        self::API_KEY_WX_SEARCH_CAR => array(
            'id'            => 14,
            'api_secret'    => '8a7f95774b1c2222da1025298c310d92',
            'identity'      => 'api_key_wx_search_car',
            'is_inside'     => 1,
        ),
    );
    /**
     * @brief 获取_api_key对应的配置                                              
     *                                                          
     */
    public static function getApiKeyConfig($apikey) {
        if (!isset(self::$API_KEY_CONFIG[$apikey])) {                           
            return array();                                                    
        }
        return self::$API_KEY_CONFIG[$apikey];                                  
    }                                                                          
}
