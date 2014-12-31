<?php

class BuyModel extends Model{
    protected $pk = 'id';
    protected $seqName = 'CAR_SEQ_BUY_ID';
    protected $tableName = 'buy';

    protected $fields = array(
        /* 隐藏字段 */
        'id'=>'number',     //买车id
        'insert_time'=>'date(yYYY-MM-DD hh24:mi:ss)',          //插入时间
        'update_time'=>'date(yYYY-MM-DD hh24:mi:ss)',          //最后修改时间
        'customer_id'=>'number',    //客户id

        /* 主要字段 */
        'province'=>'number(5)',           //交易省份
        'city'=>'number(5)',               //交易城市
        'district'=>'number',           //交易地区
        'plate_province'=>'number(5)',     //买主所在省份
        'plate_city'=>'number(5)',         //买主所在城市
        'min_price'=>'number(10)',              //价格范围最低价
        'max_price'=>'number(10)',              //价格范围最高价
        'air_displacement'=>'string(10)',             //排量范围
        'kilometer'=>'string(4)',          //表显里程范围
        'start_card_time'=>'string(4)',   //上牌起始年份
        'end_card_time'=>'number(4)',   //上牌结束年份
        'card_age' => 'string(10)',         //车龄
        'start_card_time'=>'number(4)',  //开始上牌时间
        'end_card_time'=>'number(4)',  //结束上牌时间
        'note'=>'string(2000)',         //需求简介
        'contact_user'=>'string(2,20)',   //卖主姓名
        'telephone'=>'string(5,20)',          //车主联系方式

        //以下数据会存在多组，存放在另一个表中，会以第1个需求存放在主表中
        //'vehicle_type'=>'string(5)',    //车类型
        //'make_code'=>'string(20)',      //车品牌
        //'family_code'=>'string(20)',    //车系
        //'vehicle_key'=>'string(20)',    //车型
        'car_type' => 'number(10)',     //车类型id
        'brand_id' => 'number(10)',     //品牌id
        'series_id' => 'number(10)',    //车系id
        'model_id' => 'number(10)',     //车型id
        'brind_name'=>'string(100)',    //用户手动填写的车的品牌信息 备注：1.字段拼写错误，正确是brand_name 2.表里还有个brind_note是不用的

        /* 默认字段 */
        'insert_user_id'=>'number(10)',     //添加信息的业务员ID
        'dept_id'=>'number',            //业务员部门ID
        'add_source'=>'number',         //本信息来源类型 0本站,1联盟站,2抓取
        'info_source'=>'number',        //添加信息的用户类型  1网站,2个人,3店秘录入,5来电,6其他,7联盟站,8呼叫中心,9抓取,10营销人员,11外聘营销人员,12评估,13手机客户端,14赶集
        'info_type'=>'number',          //信息来源类型 1网站,3业管
        'follow_user'=>'number',        //跟单业务员id

        /* 生成字段 */
        //'brand_caption'=>'string(100)', //车源完整标题，是根据这个字段和用户选择的品牌信息2选1生成的
        'ip'=>'string(15)',             //ip地址
        'status'=>'boolean',            //审核状态
        'sale_status'=>'boolean',       //出售状态
    );


    /* 插入表单字段映射 */
    protected $mapData = array(
        'user_id'=>'insert_user_id',
        'brand_name'=>'brind_name',
    );

    /**
     * 发布新需求
     * @param type $data
     * @return type
     */
    public function newBuy($data = array()){
        //获取数据
        if(is_array($data) and count($data))$this->data = array_merge($this->data, $data);

        if (!empty($this->data['vehicle_type'])) {
            $this->data['car_type'] = Cheyou::$CAR_TYPE[$this->data['vehicle_type']];
        }
        if (!empty($this->data['make_code'])) {
            $brandInfo = VehicleV2Interface::getBrandByCode(array('code' => $this->data['make_code']));
            $this->data['brand_id'] = !empty($brandInfo['id']) ? $brandInfo['id'] : 0;
        }
        if (!empty($this->data['family_code'])) {
            $seriesInfo = VehicleV2Interface::getSeriesByCode(array('code' => $this->data['family_code']));
            $this->data['series_id'] = !empty($seriesInfo['id']) ? $seriesInfo['id'] : 0;
        }
        if (!empty($this->data['vehicle_key'])) {
            $modelInfo = VehicleV2Interface::getModelByCode(array('code' => $this->data['vehicle_key']));
            $this->data['model_id'] = !empty($modelInfo['id']) ? $modelInfo['id'] : 0;
        }
        //字段验证
        if(!$this->validate()) {
            throw new AppServException(AppServErrorVars::CUSTOM, '数据格式输入有误:' . $this->errField);
        }

        if (!$this->data['brind_name']) {
            if (!empty($data['make_code'])) {
                $brind_name = $this->getOne('select description_chinese from car_type_family t where make_code = \''.$data['make_code'].'\' and family_code = \''.$data['family_code'].'\'');
                $this->data['brind_name'] = $brind_name['description_chinese'];
            } else {
                if (!$this->data['model_id'] && (!$this->data['series_id'] || !$this->data['brand_id'])) {
                    $this->data['brind_name'] = '';
                } else {
                    $this->data['brind_name'] = VehicleV2Interface::getModelCaption(array(
                        'brand_id' => $this->data['brand_id'],
                        'series_id' => $this->data['series_id'],
                        'model_id' => $this->data['model_id'],
                    ));
                }
            }

        }
        //默认数据
        $this->data['insert_time'] = 'to_date(\''.date("Y-m-d H:i:s").'\',\'YYYY-MM-DD hh24:mi:ss\')';
        $this->data['update_time'] = $this->data['insert_time'];
        $this->data['follow_time'] = $this->data['insert_time'];
        $this->data['add_source'] = '0';
        $this->data['info_id'] = $_info_id = getInfoid(2);
        $this->data['info_source'] = '13';
        $this->data['info_type'] = '3';
        $this->data['sale_status'] = '0';
        $this->data['status'] = '0';
        $this->data['insert_user_id'] = $this->data['follow_user'] = $this->data['update_user'] = AppServAuth::$userInfo['user']['username'];
        $this->data['dept_id'] = AppServAuth::$userInfo['user']['dept_id'];
        $this->data['min_price']=floatval($this->data['min_price'])*10000;
        $this->data['max_price']=floatval($this->data['max_price'])*10000;
        if (AppServAuth::$userInfo['user']['role_id'] == 26) {
            $this->data['owner_user_id'] = 0;
            $sql = "select username from mbs_user where status=1 and join_auto_assign=1 and dept_id=" . AppServAuth::$userInfo['user']['dept_id'];
            $users = $this->query($sql);
            $userId = $users[array_rand($users)]['username'];
            if ($userId) {
                $this->data['follow_user'] = $userId;
            }
        } else {
            $this->data['owner_user_id'] = AppServAuth::$userInfo['user']['username'];
        }


        //添加
        $buyId=$this->add();
        if(empty($buyId)) {
            throw new AppServException(AppServErrorVars::CUSTOM, '发布买车信息失败');
        }


        //审核日志 27代表店长
        $row=$this->getOne("select mbs_check_message_0.nextval currval FROM dual");
        $_id=$row['currval'];
        $_dianzhang=$this->getOne("select username from mbs_user where role_id=27 and dept_id=".AppServAuth::$userInfo['user']['dept_id']);
        $_accept_id=$_dianzhang['username'];
        $_insert_time = 'to_date(\''.date("Y-m-d H:i:s").'\',\'YYYY-MM-DD hh24:mi:ss\')';
        $sql="insert into mbs_check_message(id,create_id,accept_id,info_id,insert_time,status,reason,title)
            values('".$_id."','".AppServAuth::$userInfo['user']['username']."','".$_accept_id."','".$_info_id."', ".$_insert_time.", '0','26','".AppServAuth::$userInfo['user']['real_name']."录入了条买车信息编号为".$_info_id.",请审核！')";
        $this->query($sql);

        return $buyId;
    }

    /**
     * 设置客户
     */
    public function buyCustomer($cid=0, $id=0){
        if(!$cid or !$id)return;
        $this->setData(array('customer_id'=>$cid,'id'=>$id));
        $this->save();
    }

    /**
     * 设置第1需求
     */
    public function buyDemand($demand){
        $demand['id']=$demand['buy_id'];
        $this->setData($demand);
        $this->save();
    }
    
    ///清空买车需求
    public function clearDemand($buyId) {
        $demand = array(
            'id' => $buyId,
            'brand_name' => '',
        );
        $this->setData($demand);
        $this->save();
    }

    /**
     * 保存买车需求
     * @param type $data
     * @return type
     */
    public function saveBuy($data = array()){
        include_once APP_SERV . '/app/sale/include/Model/ModelWorkFlow.class.php';
        //获取数据
        if(is_array($data) and count($data))$this->data = array_merge($this->data, $data);
        if (!empty($this->data['vehicle_type'])) {
            $this->data['car_type'] = Cheyou::$CAR_TYPE[$this->data['vehicle_type']];
        }
        if (!empty($this->data['make_code'])) {
            $brandInfo = VehicleV2Interface::getBrandByCode(array('code' => $this->data['make_code']));
            $this->data['brand_id'] = !empty($brandInfo['id']) ? $brandInfo['id'] : 0;
        }
        if (!empty($this->data['family_code'])) {
            $seriesInfo = VehicleV2Interface::getSeriesByCode(array('code' => $this->data['family_code']));
            $this->data['series_id'] = !empty($seriesInfo['id']) ? $seriesInfo['id'] : 0;
        }
        if (!empty($this->data['vehicle_key'])) {
            $modelInfo = VehicleV2Interface::getModelByCode(array('code' => $this->data['vehicle_key']));
            $this->data['model_id'] = !empty($modelInfo['id']) ? $modelInfo['id'] : 0;
        }

        //字段验证
        if(!$this->validate()) {
            throw new AppServException(AppServErrorVars::CUSTOM, '数据格式输入有误:' . $this->errField);
        }

        //判断是否是该用户的车源
        $carInfo = $this->buyInfo($this->data['id']);
        if(!$carInfo) { 
            throw new AppServException(AppServErrorVars::CUSTOM, '买车需求不存在');
        }
        if($carInfo['follow_user'] != AppServAuth::$userInfo['user']['username']) {
            throw new AppServException(AppServErrorVars::CUSTOM, '不是本人发布的买车需求');
        }
        if (!$this->data['brind_name']) {
            if ($this->data['make_code']) {
                $brind_name = $this->getOne('select description_chinese from car_type_family t where make_code = \''.$this->data['make_code'].'\' and family_code = \''.$this->data['family_code'].'\'');
                $this->data['brind_name'] = $brind_name['description_chinese'];
            } else {
                if (!$this->data['model_id'] && (!$this->data['series_id'] || !$this->data['brand_id'])) {
                    $this->data['brind_name'] = '';
                } else {
                    $this->data['brind_name'] = VehicleV2Interface::getModelCaption(array(
                        'brand_id' => $this->data['brand_id'],
                        'series_id' => $this->data['series_id'],
                        'model_id' => $this->data['model_id'],
                    ));
                }
            }
        }
        $customer = $this->getOne("select real_name,mobile,idcard,telephone from mbs_customer where id=".intval($carInfo['customer_id']));
        $workFlow = new ModelWorkFlow();
        if ($this->data['telephone'] != $customer['mobile'] || $data['telephone2'] != $customer['telephone']) {
            $info = array(
                'mobile' => $this->data['telephone'],
                'telephone' => $data['telephone2'] ? $data['telephone2'] : '',
                'info_id' => $carInfo['info_id'],
                'update_reason' => '',
            );
            $workFlow->workFlowProcess(1, $info);
        }
        if ($data['idcard'] && $data['idcard'] != $customer['idcard']) {
            $info = array(
                'idcard' => $data['idcard'] ? $data['idcard'] : '',
                'info_id' => $carInfo['info_id'],
                'update_reason' => '',
            );
            $workFlow->workFlowProcess(3, $info);
        }
        //默认数据及防止传入多余参数
        unset($this->data['telephone']);
        unset($this->data['telephone2']);
        unset($this->data['idcard']);
        unset($this->data['dept_id']);
        unset($this->data['insert_time']);
        unset($this->data['sale_status']);
        unset($this->data['status']);
        unset($this->data['follow_user']);
        unset($this->data['insert_user_id']);

        $this->data['update_user'] = AppServAuth::$userInfo['user']['username'];

        //默认数据
        $this->data['min_price']=floatval($this->data['min_price'])*10000;
        $this->data['max_price']=floatval($this->data['max_price'])*10000;

        $carId=$this->data['id'];
        //修改
        if(!$this->save()) {
            throw new AppServException(AppServErrorVars::CUSTOM, '买车需求修改失败');
        }
        return $carId;
    }
    /**
     * 获取车源列表
     * @param number $carid 车源id
     * @param boolen $modify 是否是修改操作
     */
    public function buyInfo($carid, $infoId=0){

        $where = ' id = \''.$carid.'\'';
        if ($infoId) {
            $where = ' info_id = \''.$infoId.'\'';
        }
        
        $this->setOption('where', $where);
        $this->setOption('field', 'id,info_id,status,sale_status,dept_id,vehicle_type,make_code,family_code,vehicle_key,car_type,brand_id,series_id,model_id,province,city,district,customer_id,
            plate_province,plate_city,min_price,max_price,air_displacement,kilometer,card_age,note,brind_name,follow_user,start_card_time,end_card_time,
            to_char(insert_time,\'YYYY-MM-DD hh24:mi:ss\') as insert_time,to_char(update_time,\'YYYY-MM-DD hh24:mi:ss\') as update_time');

        $data = $this->select();
        if(!$data)return false;
        return array_shift($data);
    }

    
    public function buyViewInfo($carid, $infoId=0){
    
        $where = ' id = \''.$carid.'\'';
        if ($infoId) {
            $where = ' info_id = \''.$infoId.'\'';
        }

        $sql = 'select id,info_id,status,sale_status,dept_id,vehicle_type,make_code,family_code,vehicle_key,car_type,brand_id,series_id,model_id,province,city,district,customer_id,
            plate_province,plate_city,min_price,max_price,air_displacement,kilometer,card_age,note,brind_name,follow_user,start_card_time,end_card_time,
            to_char(insert_time,\'YYYY-MM-DD hh24:mi:ss\') as insert_time,to_char(update_time,\'YYYY-MM-DD hh24:mi:ss\') as update_time from mbs_view_buy where ' . $where;
        $data = $this->query($sql);
        if(!$data)return false;
        return array_shift($data);
    }

    /**
     * 获取车源列表
     */
    public function buyList($user=0,$start=0,$end=20,$status=0,$keyword='',$insert_time='',$price='',$cardTime='',$make='',$family='',$vehicle='',$brandId=0,$seriesId=0,$typeId=0,$count=false){
        if (!$count) {
            $this->setOption('limit', intval($start) .','. intval($end));
        } else {    //当$count不为false时，为获取车源总数
            $this->setOption('limit', 1);
        }
        /* 严重注意：判断车源是不是属于这个用户的，要根据follow_user来判断，而不是根据inser_user_id来判断 */
        $where = '';
        if($user == AppServAuth::$userInfo['user']['username']){  //自己的车
            $where.= 'follow_user='.$user;
        }elseif ($user == AppServAuth::$userInfo['user']['dept_id']) {  //店内的车
            $where .= 'dept_id = '. $user;  
        }else{      //搜索车
            $where.=' city=\''.AppServAuth::$userInfo['user']['city'].'\'';
        }
        switch(intval($status)){
            case 1: $where.=' and status=0';break;
            case 2: $where.=' and status=1';break;
            case 3: $where.=' and sale_status=0';break;
            case 4: $where.=' and sale_status=1';
                    $where.=' and status=1';
                    $this->setTableName('car_buy_bak');
                    break;
            case 5: $where.=' and status=2';//冻结
                    $this->setTableName('car_buy_bak');
                    break;
            case 6: $where.=' and status=3';//终止
                    $this->setTableName('car_buy_bak');
                    break;
            default: $where.=' and (status=1 or status=0)';break;
        }
        if(!empty($keyword)){
            if(is_numeric($keyword)){
                if (in_array(strlen($keyword),array(8,10,11,12))) {
                    $where.= " and customer_id in (select id from mbs_customer where (mobile='".$keyword."' or telephone='".$keyword."' or telephone2='".$keyword."'))" 
                          .' and dept_id=' . AppServAuth::$userInfo['user']['dept_id'];
                } else {
                    $where.=' and id=\''.$keyword.'\'';
                }
            }else{
                //$keyList = fenci($keyword);
                //$where.=' and contains(tags,\''.implode (',', $keyList).'\',0)>0';
                $where.=' and contains(tags,\''.$keyword.'\',0)>0';
            }
        }
        if(!empty($insert_time) and preg_match('/^\d{4}-\d{1,2}-\d{1,2} \d{1,2}:\d{1,2}:\d{1,2}$/i', $insert_time)){
            $where .= ' and insert_time < to_date(\''.$insert_time.'\',\'YYYY-MM-DD hh24:mi:ss\')';
        }
        if ($price) {
            $priceArray = explode('-', $price);
            if (isset($priceArray[0])) {
                $where .= (' and (max_price >= ' . $priceArray[0] . ' or max_price = 0)');
                if (isset($priceArray[1])) {
                    $where .= (' and min_price <= ' . $priceArray[1]);
                }
            }
        }
        if ($cardTime) {
            $timeArray = explode('-', $cardTime);
            $cardAge = $timeArray[0];
            switch($cardAge) {
                case '0':
                    $where .= (" and card_age = '1'");
                    break;
                case '1':
                    $where .= (" and card_age = '103'");
                    break;
                case '3':
                    $where .= (" and card_age = '305'");
                    break;
                case '5':
                    $where .= (" and card_age = '508'");
                    break;
                case '8':
                    $where .= (" and card_age = '810'");
                    break;
                case '10':
                    $where .= (" and card_age = '1099'");
                    break;
                default:
                    break;
            }
        }
        if ($make) {
            $brandInfo = VehicleV2Interface::getBrandByCode(array('code' => $make));
            $brandId = !empty($brandInfo['id']) ? $brandInfo['id'] : 0;
        }
        if ($family) {
            $seriesInfo = VehicleV2Interface::getSeriesByCode(array('code' => $family));
            $seriesId = !empty($seriesInfo['id']) ? $seriesInfo['id'] : 0;
        }
        if ($vehicle) {
            $typeId = Cheyou::$CAR_TYPE[$vehicle];
        }
        if ($brandId) {
            $where .= (" and brand_id = " . $brandId);
        }
        if ($seriesId) {
            $where .= (" and series_id = " . $seriesId);
        }
        if ($typeId) {
            $where .= (" and car_type = " . $typeId);
        }
        $this->setOption('where', $where);
        if ($count) {
            $this->setOption('field', 'count(id)');
            $nums = $this->select();
            return $nums;
        }
        $this->setOption('order', 'insert_time desc');
        $this->setOption('field', 'id,info_id,status,sale_status,dept_id,vehicle_type,make_code,family_code,vehicle_key,car_type,brand_id,series_id,model_id,province,city,district,customer_id,
            plate_province,plate_city,min_price,max_price,air_displacement,kilometer,card_age,note,brind_name,follow_user,start_card_time,end_card_time,
            to_char(insert_time,\'YYYY-MM-DD hh24:mi:ss\') as insert_time,to_char(update_time,\'YYYY-MM-DD hh24:mi:ss\') as update_time');

        $data = $this->select();
        if(!$data)return false;
        return $data;
        //return $this->_format($data, $user);
    }
}

?>
