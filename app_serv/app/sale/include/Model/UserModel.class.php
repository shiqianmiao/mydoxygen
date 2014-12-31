<?php

class UserModel extends Model{
    protected $pk='username';
    protected $tablePrefix='MBS_';
    
    protected $tableRealName = 'mbs_user';
    
    /**
     * 获取门店
     */
    public function getDeptUsers($deptId, $roleId) {
        $sql = 'select username from mbs_user where dept_id='
            . $deptId . ' and role_id='.$roleId.' and status=1';
        $userModel=D("User");
        $usersInfo = $userModel->getOne($sql);
        return $usersInfo['username'] ? $usersInfo['username'] : '';
    }
    
    /**
     * 根据username获得其信息
     */
    public function getInfoByUser($username){
        $sql = 'select * from '.$this->tableRealName. ' where username='. $username;
        return $this->getOne($sql);
    }
}
?>
