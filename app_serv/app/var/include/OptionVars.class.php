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
class OptionVars {

    public static $PRICE_OPTION = array(
        array(
            'text' => '3万以下',
            'search' => '0-30000',
        ),
        array(
            'text' => '3-5万',
            'search' => '30000-50000',
        ),
        array(
            'text' => '5-10万',
            'search' => '50000-100000',
        ),
        array(
            'text' => '10-15万',
            'search' => '100000-150000',
        ),
        array(
            'text' => '15-20万',
            'search' => '150000-200000',
        ),
        array(
            'text' => '20-30万',
            'search' => '200000-300000',
        ),
        array(
            'text' => '30-40万',
            'search' => '300000-400000',
        ),
        array(
            'text' => '40万以上',
            'search' => '400000-99999999',
        ),
    );

    public static $AGE_OPTION = array(
        array(
            'text' => '1年内',
            'search' => '0-1',
        ),
        array(
            'text' => '1-3年',
            'search' => '1-3',
        ),
        array(
            'text' => '3-5年',
            'search' => '3-5',
        ),
        array(
            'text' => '5-8年',
            'search' => '5-8',
        ),
        array(
            'text' => '8-10年',
            'search' => '8-10',
        ),
        array(
            'text' => '10年以上',
            'search' => '10-100',
        ),
    );

    public static $KILOMETER_OPTION = array(
        array(
            'text' => '1万公里以内',
            'search' => '0-10000',
        ),
        array(
            'text' => '1-3万公里',
            'search' => '10000-30000',
        ),
        array(
            'text' => '3-5万公里',
            'search' => '30000-50000',
        ),
        array(
            'text' => '5-8万公里',
            'search' => '50000-80000',
        ),
        array(
            'text' => '8-10万公里',
            'search' => '80000-100000',
        ),
        array(
            'text' => '10万公里以上',
            'search' => '100000-99999999',
        ),
    );

    public static $TYPE_OPTION = array(
        array(
            'text'  => '轿车',
            'id'    => '1',
        ),
        array(
            'text'  => '越野车/SUV',
            'id'    => 3,
        ),
        array(
            'text'  => '商务车/MPV',
            'id'    => '2',
        ),
        array(
            'text'  => '跑车',
            'id'    => 5,
        ),
        array(
            'text'  => '皮卡',
            'id'    => 6,
        ),
        array(
            'text'  => '面包车',
            'id'    => 4,
        ),
        array(
            'text'  => '客车',
            'id'    => 7,
        ),
        array(
            'text'  => '货车',
            'id'    => 8,
        ),
    );

    public static $TYPE_OPTION_V2 = array(
        array(
            'text'  => '轿车',
            'id'    => 1,
        ),
        array(
            'text'  => '越野车/SUV',
            'id'    => 2,
        ),
        array(
            'text'  => '商务车/MPV',
            'id'    => 4,
        ),
        array(
            'text'  => '跑车',
            'id'    => 6,
        ),
        array(
            'text'  => '皮卡',
            'id'    => 7,
        ),
        array(
            'text'  => '面包车',
            'id'    => 3,
        ),
        array(
            'text'  => '客车',
            'id'    => 8,
        ),
        array(
            'text'  => '货车',
            'id'    => 5,
        ),
    );

    // 福州、昆明、厦门、泉州、漳州、深圳、上海、成都、郑州、武汉

    public static $HOT_CITY_OPTION = array(
        array(
            'name' => '福州',
            'domain' => 'fz',
            'id' => 1,
        ),
        array(
            'name' => '昆明',
            'domain' => 'km',
            'id' => 339,
        ),
        array(
            'name' => '厦门',
            'domain' => 'xm',
            'id' => 2,
        ),
        array(
            'name' => '泉州',
            'domain' => 'qz',
            'id' => 5,
        ),
        array(
            'name' => '漳州',
            'domain' => 'zhangzhou',
            'id' => 6,
        ),
        array(
            'name' => '深圳',
            'domain' => 'sz',
            'id' => 42,
        ),
        array(
            'name' => '上海',
            'domain' => 'sh',
            'id' => 438,
        ),
        array(
            'name' => '成都',
            'domain' => 'cd',
            'id' => 160,
        ),
        array(
            'name' => '郑州',
            'domain' => 'zz',
            'id' => 99,
        ),
        array(
            'name' => '武汉',
            'domain' => 'wh',
            'id' => 116,
        ),
    );
}
