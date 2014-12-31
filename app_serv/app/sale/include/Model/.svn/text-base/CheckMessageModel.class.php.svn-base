<?php

class CheckMessageModel extends Model {

    protected $pk = 'id';
    protected $seqName = 'MBS_CHECK_MESSAGE_0';
    protected $tableName = 'check_message';
    protected $tablePrefix='mbs_';

    public $titleCfgArray = array(
        '1' => '%s申请修改编号为%s的联系方式,请审核！',
        '2' => '%s申请修改编号为%s的车牌号,请审核！',
        '3' => '%s申请修改编号为%s的身份证号,请审核！',
    );
    public function saveMessage($info){
        $saveInfo = $this->checkMessageField($info);
        $userInfo = AppServAuth::$userInfo['user'];

        switch($info['reason']){
            case '1':
            case '2':
            case '3':
                $saveInfo['title'] = sprintf($this->titleCfgArray[$info['reason']],$userInfo['real_name'],$saveInfo['info_id']);
                $saveInfo['title'] .= '修改原因：'.$info['update_reason'];
                break;
        }
        $this->data = $saveInfo;
        return $this->add();
    }

    public function checkMessageField($info){
        $checkMessageInfo = array();
        $checkMessageInfo['create_id'] = AppServAuth::$userInfo['user']['username'];
        $checkMessageInfo['accept_id'] = $info['accept_id'];
        $checkMessageInfo['insert_time'] = "to_date('" . date('Y-m-d H:i:s') . "','yyyy-mm-dd hh24:mi:ss')";
        $checkMessageInfo['finish_time'] = '';
        $checkMessageInfo['info_id'] = $info['info_id'];
        $checkMessageInfo['status'] = 0;
        $checkMessageInfo['reason'] = $info['reason'];
        $checkMessageInfo['appraised_price'] = intval($info['appraised_price'])*10000;
        $checkMessageInfo['check_content'] = $info['check_content'];
        $checkMessageInfo['object_id'] = $info['object_id'];
        return $checkMessageInfo;
    }
}
