<?php
/**
 * 微信相关接口
 * @author limy
 * @since 2014-11-20
 */
require_once API_PATH . '/interface/WeixinMbsInterface.class.php';

class WeixinServiceApp {

    /**
     * 给用户发送消息
     * @param array $params array(
     *      'touser'=>10800003,
     *      'content'=>'这是程序推送的一条消息',
     * )
     */
    public function sendMsg( $params ) {
        if( !isset($params['touser']) || !isset($params['content']) ) return array(
            'errcode'=>2,
            'errmsg'=>'参数不全',
        );
        $array = array(
            'touser'=>$params['touser'],
            'content'=>$params['content'],
        );

        $weixinData = WeixinMbsInterface::getUserInfo(array(
            'userid'=>$params['touser'],
        ));
        if( empty($weixinData) ) {
            return array(
                'errcode' => 3,
                'errmsg' => '用户未绑定',
            );
        }
        $res = WeixinMbsInterface::sendMsg(array(
            'data'=>$array
        ));
        return $res;
    }

    /**
     * 新增一条退订记录
     * @param array $params['data'] array('userid'=>'')
     */
    public function saveUnsub($params) {
        return WeixinMbsInterface::saveUnsub($params);
    }

    /**
     * 获取退订userid列表
     */
    public function getUnsubList($params) {
        $list = WeixinMbsInterface::getUnsubList($params);
        $res = array();
        if( !empty($list) ) {
            foreach($list as $v ) {
                $res[] = $v['userid'];
            }
        }
        return $res;
    }

}
