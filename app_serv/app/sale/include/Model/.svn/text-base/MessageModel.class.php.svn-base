<?php

class MessageModel extends Model {

    protected $pk = 'id';
    protected $seqName = 'MBS_MESSAGE_0';
    protected $tableName = 'message';
    protected $tablePrefix='mbs_';

    public $titleCfgArray = array(
        '1' => '%s申请修改编号为%s的联系方式,请审核！',
        '2' => '%s申请修改编号为%s的车牌号,请审核！',
        '3' => '%s申请修改编号为%s的身份证号,请审核！',
    );
    
    
    public function getMessageCount($tableName, $where) {
        $sql = 'select count(*) as count from '.$tableName.$where;
        $ret = $this->query($sql);
        return $ret[0]['count'];
    }
}
