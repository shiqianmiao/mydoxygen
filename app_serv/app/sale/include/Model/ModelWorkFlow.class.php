<?php
require_once API_PATH . '/interface/MbsUserInterface.class.php';

class ModelWorkFlow {

   public function workFlowProcess($type,$info) {
        $userModel = D('User');
        $checkMessageModel = D('CheckMessage');
        $dailyModel = D('Daily');
        $callConfigModel = D('CallConfig');
        $checkMessageInfo = array();
        if (AppServAuth::$userInfo['user']['dept_type'] == 1) {
            $jobUserNames = $dailyModel->getJobDailyUserNameForDept(2);
            $assignUser = $callConfigModel->getMinCountForUserNames($jobUserNames);
            $assignUser = $assignUser['username'];
            $checkMessageInfo['accept_id'] = $assignUser;
            $callConfigModel->update($assignUser);
        } else {
            if (in_array(AppServAuth::$userInfo['user']['role_id'], array(27,28,165)) && ($type != 2)) {
                $checkMessageInfo['accept_id'] = $userModel->getDeptUsers(AppServAuth::$userInfo['user']['dept_id'], 26);
            } elseif (AppServAuth::$userInfo['user']['role_id'] == 26) {
                $checkMessageInfo['accept_id'] = $userModel->getDeptUsers(AppServAuth::$userInfo['user']['dept_id'], 27);
            }
        }
        $checkMessageInfo['reason'] = $type;
        $checkMessageInfo['info_id'] = $info['info_id'];
        $checkMessageInfo['update_reason'] = $info['update_reason'];
        if ($type == 1) {
            $checkMessageInfo['update_value'] = $info['mobile'].','.$info['telephone'].','.$info['telephone2'];
        } elseif ($type == 2) {
            $checkMessageInfo['update_value'] = $info['car_number'];
        } else {
            $checkMessageInfo['update_value'] = $info['idcard'];
        }
        $messageId = $checkMessageModel->saveMessage($checkMessageInfo);
        $updateOperationModel = D('UpdateOperation');
        $operationInfo = '';
        if ($type == 1) {
            $info['mobile']&&$operationInfo['mobile'] = $info['mobile'];
            $info['telephone']&&$operationInfo['telephone'] = $info['telephone'];
            $operationInfo = serialize($operationInfo);
        } elseif ($type == 2) {
            $operationInfo = $info['car_number'];
        } else {
            $operationInfo = $info['idcard'];
        }
        $updateOperationModel->saveUpdateInfo(array(
                'info_id'=>$info['info_id'],
                'update_type'=>$type,
                'update_value'=>$operationInfo,
                'update_reason'=>$info['update_reason'],
                'check_message_id'=>$messageId,)
        );
    }
}
            
