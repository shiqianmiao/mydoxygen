<?php
/**
 * @author 陈朝阳<chency@273.cn>
 * @desc   车友圈客户端接口
 * @since  2014-06-27
 */
 
require_once API_PATH . '/interface/car_friend/CarFriendPostInterface.class.php';
require_once API_PATH . '/interface/car_friend/CarFriendImageInterface.class.php';
require_once FRAMEWORK_PATH . '/util/text/String.class.php';
require_once COM_PATH . '/car_friend/CarFriendConfig.class.php';

class FriendsServiceApp {
    /**
     * @desc 文本信息发布接口
     * @param $params array(
     *                   'text' //消息文本
     *                   'image_count' //消息待上传的图片张数
     *                )
     */
    public function addMessage($params) {
        $params['text'] = trim($params['text']);
        if (empty($params['text']) && !($params['image_count'] >= 0)) {
            throw new AppServException(AppServErrorVars::CUSTOM, '参数错误');
        }
        if (String::strlen_utf8($params['text']) > CarFriendConfig::$MESSAGE_MAX_COUNT) {
            throw new AppServException(AppServErrorVars::CUSTOM, '字数超过限制');
        }
        $user = AppServAuth::$userInfo['user']['username'];
        if (empty($user)) {
            throw new AppServException(AppServErrorVars::CUSTOM, '用户未登陆或不存在');
        }
        $addParam = array(
            'content'      => empty($params['text']) ? '' : $params['text'],
            'username'     => $user,
            'image_count'  => $params['image_count']
        );
        if ($params['image_count'] > 0) {
            //有图片时，帖子状态为0 ，等图片全部入库时，更新状态为1
            $addParam['status'] = 0;
        }
        $ret = CarFriendPostInterface::addPost($addParam);
        return array('id' => $ret);
    }
    
     /**
     * @desc 图片上传入库
     * @param $params array(
     *                   'text' //消息文本
     *                   'image_count' //消息待上传的图片张数
     *                )
     */
    public function addImages($params) {
        if (!empty($params['images'])) {
            $params['images'] = explode(',', $params['images']);
        }
        if (empty($params['images']) || !is_array($params['images']) || !($params['id'] > 0)) {
            throw new AppServException(AppServErrorVars::CUSTOM, '参数错误');
        }
        $user = AppServAuth::$userInfo['user']['username'];
        if (empty($user)) {
            throw new AppServException(AppServErrorVars::CUSTOM, '用户未登陆或不存在');
        }
        $addParam = array(
            'post_id'   => $params['id'],
            'username'  => $user,
        );
        foreach ($params['images'] as $image) {
            $addParam['image_url'] = $image;
            $ret = CarFriendImageInterface::addImage($addParam);
        }
        if ($ret) {
            $updateParam = array(
                'info'      =>array(
                    'status' => 1,
                    'cover_photo' => $params['images'][0],
                ),
                'filters' => array(
                    array('id', '=', $params['id']),
                )
            );
            $ret = CarFriendPostInterface::updatePost($updateParam);
        }
        return $ret;
    }

/**
     * @desc 图片上传入库
     * @param $params array(
     *                   'text' //消息文本
     *                   'image_count' //消息待上传的图片张数
     *                )
     */
    public function deleteMessage($params) {
        if (!($params['id'] > 0)) {
            throw new AppServException(AppServErrorVars::CUSTOM, '参数错误');
        }
        $user = AppServAuth::$userInfo['user']['username'];
        if (empty($user)) {
            throw new AppServException(AppServErrorVars::CUSTOM, '用户未登陆或不存在');
        }
        $updateParam = array(
            'info'      =>array(
                'status' => 2,
            ),
            'filters' => array(
                array('id', '=', $params['id']),
            )
        );
        $ret = CarFriendPostInterface::updatePost($updateParam);
        return $ret;
    }
}
