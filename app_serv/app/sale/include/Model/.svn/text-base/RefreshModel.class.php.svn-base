<?php

class RefreshModel extends Model{

    protected $pk = 'id';
    protected $seqName = 'SEQ_MBS_REFRESH_STAT';
    protected $tableName = 'refresh_stat';
    protected $tablePrefix='mbs_';


    protected $tableRealName = 'mbs_refresh_stat';

    public function getDetailByUsername($username) {
        $sql = 'select * from '.$this->tableRealName. ' where username='. intval($username). ' and insert_time='. strtotime('today');
        return $this->getOne($sql);
    }

    public function addStat($data){ 
        $this->data = $data;
        return $this->add();
    }
    
    public function updateStat($info_id, $username, $originalData = ''){
        if($originalData){
            if(is_string($originalData['detail'])){

                $originalData['detail'] = unserialize($originalData['detail']);
            }
            //总次数-1
            if ($originalData['mobile_total_number'] > 0) {
                $originalData['mobile_total_number']--;
            } else {
                $originalData['total_number']--;
            }
            //车源次数-1 或初始为2-1
            $originalData['detail'][$info_id] = isset($originalData['detail'][$info_id])? $originalData['detail'][$info_id] - 1: (2 - 1);
        }else{
            //@todo 无原始数据，需要先查出来
        }
        //最后一次刷新时间，2次间隔10分钟
        $today = strtotime('today');
        $data['last_refresh_time'] = time();
        $data['total_number'] = $originalData['total_number'];
        $data['mobile_total_number'] = $originalData['mobile_total_number'];
        $data['detail'] = serialize($originalData['detail']);
        $r = $this->db->update(
            $data,
            array('table' => $this->tableRealName,
                  'where' => ' username=\''. $username. '\' and insert_time='. $today)
        );
        return $r;
    }

    /**
     * 完成操作
     * @param <type> $id
     */
    public function complete($id){
        $this->db->update($this->tableRealName,array('status'=>1,'finish_time' => date('Y-m-d H:i:s')),'id='.intval($id));
    }

    /**
     * 根据合同表的系统编号或合同编号关闭相应的审核事务
     *
     * @param unknown_type $id
     */
    public function completeBySystemNumber($systemNumber){

        $this->db->update(
                $this->tableRealName,
                array('status'=>1,'finish_time' => date('Y-m-d H:i:s')),
                'info_id=\''. $systemNumber. '\''
            );
    }


    /**
     * 更新来电呼出未响应的次数(呼叫中心回访时候使用)
     * @param int $id
     */
    public function updateUnCallNum($id){
        $this->db->execute('update '.$this->tableRealName.' set un_call_num=un_call_num+1 where id='.intval($id));
    }

    /**
     * 返回条数
     * @param string $where
     * @return int
     */
    public function getCount($where){
        return $this->db->getValue('select count(*) from '.$this->tableRealName.$where);
    }

    /**
     * 根据ID得到信息
     * @param array $id
     * @return array
     */
    public function getInfoForId($id){
        $checkMessageInfo = array();
        $checkMessageInfo = $this->db->getValue('select c.*,to_char(c.insert_time,\'yyyy-mm-dd hh24:ii:ss\') insert_time_2 from '.$this->tableRealName.' c where id='.intval($id));
        $checkMessageInfo['insert_time'] = $checkMessageInfo['insert_time_2'];
        $checkMessageInfo['viewUrl'] = $this->getViewUrl($checkMessageInfo['info_id']);
        return $checkMessageInfo;
    }
}
