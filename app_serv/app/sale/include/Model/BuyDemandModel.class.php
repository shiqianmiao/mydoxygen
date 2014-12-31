<?php

class BuyDemandModel extends Model{
    protected $pk = 'demand_id';
    protected $seqName = 'SEQ_CAR_BUY_DEMAND';
    protected $tableName = 'buy_demand';
    
    //买车第1需求，根据传过来的key判断
    protected $mainDemand = '';
    
    protected $demands = array();


    protected $fields = array(
        /* 隐藏字段 */
        'demand_id'=>'number',     //买车id
        'buy_id'=>'number',          //插入时间
        
        /* 主要字段 */
        'vehicle_type'=>'string(5)',    //车类型
        'make_code'=>'string(20)',      //车品牌
        'family_code'=>'string(20)',    //车系
        'vehicle_key'=>'string(20)',    //车型
        'brand_name'=>'string(200)'         //需求简介
    );
    
    /**
     * 保存多个需求并设置主需求
     * @param type $demands
     * @param type $buyId
     */
    public function saveDemands($demands, $buyId){
        //删除旧的数据
        $sql="delete from car_buy_demand where buy_id='".$buyId."'";
        $this->query($sql);
        
        foreach($demands as $key => $data){
            if(!$buyId or (empty($data['brand_name']) and empty($data['make_code'])))continue;
            $caption = makeCaption($data['vehicle_type'],$data['make_code'],$data['family_code'],$data['vehicle_key']);
            $data['brand_name']=empty($data['brand_name']) ? $caption : $data['brand_name'];
            $data['buy_id']=$buyId;

            if($data['demand_id']){
                //设置数据
                $this->setData($data);
                $this->validate();

                $this->save();
            }else{
                //设置数据
                $this->setData($data);
                $this->validate();

                $data['demand_id']=$this->add();
            }
            $this->demands[]=$data;
            
            if(!$key)$this->mainDemand=$data;
        }
        return $this->demands;
    }
    
    /**
     * 获取主需求
     * @return type
     */
    public function getMainDemand(){
        return $this->mainDemand;
    }
    
    /**
     * 获取某个买车需求的需求列表
     * @param type $id
     * @return type
     */
    
    public function getDemands($id){
        $sql="select demand_id,make_code,family_code,vehicle_type,vehicle_key,brand_name from car_buy_demand where buy_id='".$id."' order by demand_id asc";
        $result=$this->query($sql);
        
        return $result;
    }
} 
?>