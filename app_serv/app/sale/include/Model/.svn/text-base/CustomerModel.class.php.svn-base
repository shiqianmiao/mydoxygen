<?php

class CustomerModel extends Model{
    protected $pk = 'id';
    protected $seqName = 'mbs_customer_0';
    protected $tableName = 'customer';
    protected $tablePrefix='MBS_';
    
    protected $fields = array(
        /* 隐藏字段 */
        'id'=>'number',     //买车id
        'insert_time'=>'date(yYYY-MM-DD hh24:mi:ss)',          //插入时间
        'info_id'=>'number',        //统一编号
        'status'=>'boolean',         //审核状态
        
        /* 主要字段 */
        'province'=>'number(5)',           //省份
        'city'=>'number(5)',               //城市
        'real_name'=>'string(30)',  //真实姓名
        'mobile'=>'string(20)',          //移动电话
        'telephone'=>'string(20)',   //备用电话
        'idcard'=>'string(30)', //身份证号
    );
    
    /**
     * 新客户
     * @param type $data
     * @return type
     */
    public function newCustomer($data = array()){        
        //获取数据
        if(is_array($data) and count($data))$this->data = array_merge($this->data, $data);
        
        //字段验证
        if(!$this->validate()) {
            throw new AppServException(AppServErrorVars::CUSTOM, '客户数据输入格式不正确:' . $this->errField);
        }
        
        //默认数据
        $this->data['insert_time'] = 'to_date(\''.date("Y-m-d H:i:s").'\',\'YYYY-MM-DD hh24:mi:ss\')';
        $this->data['info_id'] = getInfoid(3);
        $this->data['status'] = '1';
        $this->data['telephone2'] = '';
        $this->data['insert_user_id'] = $this->data['follow_user'] = AppServAuth::$userInfo['user']['username'];
        
        //添加
        $customerId=$this->add();
        
        return $customerId;
    }
    
    /**
     * 修改客户资料
     * @param type $data
     * @return type
     */
    public function saveCustomer($data = array()){
        if(empty($data['id']))return false;
        
        //获取数据
        if(is_array($data) and count($data))$this->data = array_merge($this->data, $data);
        
        //字段验证
        if(!$this->validate()) {
            throw new AppServException(AppServErrorVars::CUSTOM, '客户数据输入格式不正确:' . $this->errField);
        }
        
        if(!$this->save())return false;
        
        return true;            
    }

}

?>
