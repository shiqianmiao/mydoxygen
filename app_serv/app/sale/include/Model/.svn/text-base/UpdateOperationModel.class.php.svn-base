<?php

class UpdateOperationModel extends Model {

    protected $pk = 'id';
    protected $seqName = 'MBS_UPDATE_OPERATION_0';
    protected $tableName = 'update_operation';
    protected $tablePrefix='mbs_';

    public function saveUpdateInfo($info){
        $saveInfo = $this->updateField($info);

        $carInfo = $this->getCarInfoForInfoId($info['info_id']);
        if($info['update_type'] == 1){
            $carInfo['mobile'] && $oldValues['mobile'] = $carInfo['mobile'];
            $carInfo['telephone'] && $oldValues['telephone'] = $carInfo['telephone'];
            $carInfo['telephone2'] && $oldValues['telephone2'] = $carInfo['telephone2'];
            $saveInfo['old_value'] = serialize($oldValues);
        } else if($info['update_type'] == 2){
            $saveInfo['old_value'] = $carInfo['car_number'];
        } else if($info['update_type'] == 3){
            $saveInfo['old_value'] = $carInfo['idcard'];
        }
        $this->data = $saveInfo;
        $this->add();
    }
    public function updateField($info){
        $updateInfo = array();
        $updateInfo['info_id'] = $info['info_id'];
        $updateInfo['update_type'] = $info['update_type'];
        $updateInfo['update_reason'] = $info['update_reason'];
        $updateInfo['update_value'] = $info['update_value'];
        $updateInfo['check_message_id'] = $info['check_message_id'];
        $updateInfo['insert_time'] = "to_date('" . date('Y-m-d H:i:s') . "','yyyy-mm-dd hh24:mi:ss')";
        $updateInfo['status'] = 0;
        $updateInfo['insert_user_id'] = AppServAuth::$userInfo['user']['username'];
        return $updateInfo;
    }
    protected function getCarInfoForInfoId($infoId){
        if(substr($infoId,0,1) == 'S'){
            return $this->getSaleInfoForInfoId($infoId);
        } else {
            return $this->getBuyInfoForInfoId($infoId);
        }
    }
    public function getBuyInfoForInfoId($infoId){
        $sql = 'select b.id,b.id web_id,b.province,b.city,b.make_code,b.brind_note,b.brind_name,
                b.car_type_check,b.car_type,b.car_color,b.min_price,b.max_price,b.start_card_time min_card_time,b.end_card_time max_card_time,b.note,
                b.update_user,b.info_id,b.customer_id ,b.info_source,b.dept_id,b.follow_user,to_char(b.follow_time,\'yyyy-mm-dd hh24:mi:ss\') follow_time, b.owner_user_id,
                b.re_assign_count ,b.re_assign_time ,b.last_visit_time ,b.last_visit_user ,b.last_cs_visit_time ,
                b.last_cs_visit_user,b.visit_count,b.cs_visit_count,b.stop_time,b.is_share,b.freeze_time,b.status,b.sale_status,
                b.family_code,b.vehicle_key,b.vehicle_type,b.plate_province,b.plate_city,to_char(b.insert_time,\'yyyy-mm-dd hh24:mi:ss\') insert_time,
                b.insert_user_id,b.district,to_char(b.update_time,\'yyyy-mm-dd hh24:mi:ss\') update_time,
                c.info_id customer_info_id, c.real_name,c.mobile,c.telephone ,c.telephone2,c.idcard,b.kilometer,b.card_age,b.air_displacement from mbs_view_buy b inner
                 join mbs_customer c on b.customer_id = c.id where b.info_id=\''.trim($infoId).'\'';
        $model = D('UpdateOperationModel');
        $data = $model->getOne($sql);
        return $data;
    }

    public function getSaleInfoForInfoId($info_id){
        $sql='select s.*,
            to_char(s.card_time,\'yyyy-mm-dd\') card_time_2,
            to_char(s.insert_time,\'yyyy-mm-dd hh24:mi:ss\') insert_time_2,
            to_char(s.check_time,\'yyyy-mm-dd hh24:mi:ss\') check_time_2,
            to_char(s.follow_time,\'yyyy-mm-dd hh24:mi:ss\') follow_time_2,
            to_char(s.safe_time,\'yyyy-mm-dd\') safe_time_2,
            to_char(s.year_check_time,\'yyyy-mm-dd\') year_check_time_2,
            to_char(s.update_time,\'yyyy-mm-dd hh24:mi:ss\') update_time_2,
            to_char(s.busi_insur_time,\'yyyy-mm-dd\') busi_insur_time_2,
            c.real_name,c.mobile ,c.telephone telephone_2,c.telephone2 telephone2_2,c.idcard from
              mbs_view_sale s left join mbs_customer c on s.customer_id = c.id  where s.info_id = \''.trim($info_id).'\'';
        $model = D('UpdateOperationModel');
        $info = $model->getOne($sql);
        $info['telephone'] = $info['telephone_2'];
        $info['telephone2'] = $info['telephone2_2'];
        $info['card_time'] = $info['card_time_2'];
        $info['insert_time'] = $info['insert_time_2'];
        $info['check_time'] = $info['check_time_2'];
        $info['follow_time'] = $info['follow_time_2'];
        $info['safe_time'] = $info['safe_time_2'];
        $info['year_check_time'] = $info['year_check_time_2'];
        $info['update_time'] = $info['update_time_2'];
        $info['busi_insur_time'] = $info['busi_insur_time_2'];
        return $info;
    }
}
