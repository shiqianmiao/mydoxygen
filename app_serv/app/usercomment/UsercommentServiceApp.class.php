<?php
/**
 * @brief         用户车源评价 
 * @author        zhuangjx<zhuangjx@273.cn>
 * @date          2013-10-18
 */
require_once API_PATH . '/interface/MbsDeptInterface.class.php';
require_once API_PATH . '/interface/MbsUserCommentInterface.class.php';
require_once API_PATH . '/interface/MbsScoreUserMonthInterface.class.php';
require_once API_PATH . '/interface/DeptPhoneRelationInterface.class.php';
require_once API_PATH . '/interface/ScoreInfoInterface.class.php';
class UsercommentServiceApp {
    
    public function getUserCommentRank($params) {
        if (empty($params['username'])) {
            return null;
        }         
        //取数据  其同城排名和同城总人数 
        //dept_id找到其城市
        $deptInfo = MbsDeptInterface::getDeptInfoByDeptId(array('id'=>$params['dept']));
        //同城总人数 
        $cityTotal = MbsScoreInterface::getUserTotalByCityId(array('city_id'=>$deptInfo['city']));
        //取出其排名
        
        //取车源量
        $carCount = ScoreInfoInterface::getScoreInfoByUsername(array('username'=>$params['username']));
        $scoreUser= MbsScoreUserMonthInterface::getUserRank(array('username'=>$params['username']));
        $scoreUser['city_count'] = $cityTotal[0]['count'];
        $scoreUser['car_num'] = $carCount['car_num'];
        $scoreUser['good_comment'] = $carCount['good_comment'];
        return $scoreUser;
    }
    
    /**
     * 取得用户的转接号码
     */
    public function getUserPhone($params) {
        if (empty($params['username'])) {
            return null;
        }
        $userPhone = DeptPhoneRelationInterface::getPhoneByUser(array('follow_user'=>$params['username']));
        return $userPhone;
    }
    /**
     * @根据出入的username找到其所有的用户车源量和综合排名 
     * 
     */
    public function getRecentCarRankByUserName($params) {
    
        $deptInfo = MbsDeptInterface::getDeptInfoByDeptId(array('id'=>$params['dept']));
        //同城总人数
        $cityTotal = MbsScoreInterface::getUserTotalByCityId(array('city_id'=>$deptInfo['city']));

        $username = explode(',', $params['username']);
       $result =  MbsScoreUserMonthInterface::getrecentCarNum($username);
       $result['city_count'] = $cityTotal[0]['count'];
       return $result;
    }
    public function getPostCount($params) {
        if (empty($params['username'])) {
            return null;
        }
        //取车源量 
        $carCount = ScoreInfoInterface::getScoreInfoByUsername(array('username'=>$params['username']));
        return $carCount['car_num'];
    }
}