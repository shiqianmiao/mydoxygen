<?php
/**
 * @brief         用户车源评价 
 * @author        zhuangjx<zhuangjx@273.cn>
 * @date          2013-10-18
 */
class UserCommentServiceApp {
    
    public function getUserCommentRank($params) {
        if (empty($params)) {
            return null;
        }
        return $params['userID'];
    }
}