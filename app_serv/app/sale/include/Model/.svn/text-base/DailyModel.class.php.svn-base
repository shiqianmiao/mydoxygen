<?php

class DailyModel extends Model {


    public function getJobDailyUserNameForDept($deptId,$roleArr=null){
        if($roleArr){
            $sql = 'select username from mbs_user_setting s left join mbs_user u on s.username=u.username where u.role_id in ('.implode(',', $roleArr).') and s.dept_id='.intval($deptId).' and s.status=0 and s.setting_time=to_date(\''.date('Y-m-d').'\',\'yyyy-mm-dd\')';
        }else{
            $sql = 'select username from mbs_user_setting where dept_id='.intval($deptId).' and status=0 and setting_time=to_date(\''.date('Y-m-d').'\',\'yyyy-mm-dd\')';
        }
        $dailyModel = D('DailyModel');
        $result = $dailyModel->query($sql);
        $ret = array();
        foreach ($result as $item) {
            $ret[] = $item['username'];
        }
        return implode(',', $ret);
    }

}

