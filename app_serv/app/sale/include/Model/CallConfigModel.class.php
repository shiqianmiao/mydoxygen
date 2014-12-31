<?php

class CallConfigModel extends Model {

    public function getMinCountForUserNames($usernames){
        if($usernames){
            if(date('m')>=5 && date('m')<=10){//夏季
                if(date('H:i')>'18:00' || date('H:i')<'08:00'){//下班时间
                    $time = date('Y-m-d', strtotime('+1 day'));
                }else{
                    $time = date('Y-m-d');
                }
            }else{//冬季
                if(date('H:i')>'17:30' || date('H:i')<'08:00'){//下班时间
                    $time = date('Y-m-d', strtotime('+1 day'));
                }else{
                    $time = date('Y-m-d');
                }
            }
            return $this->getOne('select username from mbs_user_call_config where username in ('.$usernames.') and role_id = 25 and status = 0 and insert_time=to_date(\''.$time.'\',\'YYYY-MM-DD\') order by nums');
        } else {
           return $this->getOne('select username from mbs_user where status=1 and role_id = 30');
        }
    }

    public function update($username){
        $this->db->execute('update mbs_user_call_config set nums = nums+1 where username='.intval($username).' and insert_time=to_date(\''.date('Y-m-d').'\',\'YYYY-MM-DD\')');
    }

}



