<?php
/**
 * @brief sale包的配置文件,其他包的配置请勿配在这里
 * @author 缪石乾
 * @date 2014-10-23
 */
class SaleVars {
    
    /**
     * @brief 我的卖车列表的待办事项类型枚举值配置，该项为草稿的值
     */
    public static $TODO_TYPE_DRAFT = 1;
    
    /**
     * @brief 我的卖车列表的待办事项类型枚举值配置，该项为质检审核未通过的值
     */
    public static $TODO_TYPE_QUALITY = 2;
    
    /**
     * @brief 我的卖车列表的待办事项类型枚举值配置，该项为明日即将下架车源的值
     */
    public static $TODO_TYPE_AUTOSTOP = 3;

    /**
     * @brief 我的卖车列表的待办事项类型枚举值配置，该项为委托车源的值
     */
    public static $TODO_TYPE_DEPUTE = 4;
    
    /**
     * @brief 卖车回访类型配置
     * @author 缪石乾
     * @date 2014-10-27
     */
    public static $SALE_CALL_TYPE = array(
        '1'  => '无人接听',
        '2'  => '无法拨通',
        '3'  => '不方便通话',
        '4'  => '还要卖',
        '5'  => '暂不卖',
        '6'  => '虚假',
        '7'  => '重复',
        '8'  => '不卖',
        '9'  => '已卖',
        '11' => '业务员未联系',
        '12' => '其他',
    );
}
?>