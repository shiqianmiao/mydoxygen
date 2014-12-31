<?php
require_once API_PATH . '/interface/PhoneCallLogInterface.class.php';
class SaleModel extends Model{
    protected $pk = 'id';
    protected $seqName = 'CAR_SEQ_SALE_ID';

    protected $fields = array(
        /* 隐藏字段 */
        'id'=>'number',     //车源id
        'insert_time'=>'date(yYYY-MM-DD hh24:mi:ss)',          //插入时间
        'update_time'=>'date(yYYY-MM-DD hh24:mi:ss)',          //最后修改时间
        'info_id'=>'number',        //编号
        'customer_id'=>'number',    //客户id

        /* 主要字段 */
        //'vehicle_type'=>'string(1,5)',    //车类型
        //'make_code'=>'string(2,20)',      //车品牌
        //'family_code'=>'string(2,20)',    //车系
        //'vehicle_key'=>'string(20)',    //车型
        'brand_id' => 'number(1,10)',   //品牌id
        'series_id' => 'number(1,10)',  //车系id
        'model_id' => 'number(1,10)',   //车型id
        'car_type' => 'number(1,10)',   //车类型id
        'photo'=>'string(150)',         //图片集  _min最小图,_266x200中等图片,不带后缀原图 这里图片存放小图
        'province'=>'number(1,5)',           //交易省份
        'city'=>'number(1,5)',               //交易城市
        'district'=>'number',           //交易地区
        'plate_province'=>'number(1,5)',     //上牌省份
        'plate_city'=>'number(1,5)',         //上牌城市
        'brind_name'=>'string(100)',    //用户手动填写的车的品牌信息 备注：1.字段拼写错误，正确是brand_name 2.表里还有个brind_note是不用的
        'car_color'=>'number(1,3)',          //车颜色
        'price'=>'number(1,10)',              //车的预售价格
        'kilometer'=>'number(1,10)',          //表显里程
        'car_number'=>'string(15)',     //车牌号
        'note'=>'string(2000)',         //车况简介
        'title'=>'string(50)',          //一句话广告
        'card_time'=>'date(YYYY-MM-DD)',      //上牌时间
        'safe_time'=>'date(YYYY-MM-DD)',      //交强险到期时间
        'year_check_time'=>'date(YYYY-MM-DD)',      //年检到期时间
        'busi_insur_time'=>'date(yYYY-MM-DD)',      //商业险到期时间
        'transfer_num'=>'number(1,2)',       //过户次数
        'maintain_address'=>'number(2)',   //保养地点 1在4S店维修保养,2在一般维修店保养
        'use_quality'=>'number(1,2)',        //使用性质 1非营运,2营运,3营转非,4租赁车,5特种车,6教练车
        'sale_quality'=>'number(2)',       //车源性质 1个人,2车商,3租赁公司,4修理厂
        'contact_user'=>'string(3,15)',   //卖主姓名
        'telephone'=>'string(5,20)',          //车主联系方式

        /* 默认字段 */
        'insert_user_id'=>'number(10)',     //添加信息的业务员ID
        'dept_id'=>'number',            //业务员部门ID
        'add_source'=>'number',         //本信息来源类型 0本站,1联盟站,2抓取
        'info_source'=>'number',        //添加信息的用户类型  1网站,2个人,3店秘录入,5来电,6其他,7联盟站,8呼叫中心,9抓取,10营销人员,11外聘营销人员,12评估,13手机客户端,14赶集
        'info_type'=>'number',          //信息来源类型 1网站,3业管
        'follow_user'=>'number',        //跟单业务员id

        /* 次要字段 */
        'body_style'=>'number(2)',         //车身结构 1两厢,2三厢,3掀背,4硬顶敞篷,5软顶敞篷
        'safe_business'=>'string(50)',  //商业险项目

        /* 生成字段 */
        'brand_caption'=>'string(100)', //车源完整标题，是根据这个字段和用户选择的品牌信息2选1生成的
        'ip'=>'string(15)',             //ip地址
        'status'=>'boolean',            //审核状态
        'sale_status'=>'boolean',       //出售状态
    );

    /* 插入表单字段映射 */
    protected $mapData = array(
        'user_id'=>'insert_user_id',
        'brand_name'=>'brind_name',
    );

    public function getVehicleFullTitle($key,$makeCode=null,$familyCode=null,$returnType='full') {
        $title = false;
        if ($title===false) {
            if ($key) {
                $info = $this->getOne('select * from car_type_vehicle where vehicle_key=\''.$key.'\'');
                if ($info['vehicle_type_code']=='HC' || $info['vehicle_type_code']=='BS') {//客货车
                    $rs['vehicle'] = $info['local_vehicle_description'];
                    if ($returnType=='array') {
                        $family = $this->getOne('select description_chinese from car_type_family where family_code=\''.$info['family_code'].'\' and make_code=\''.$info['make_code'].'\'');
                        $rs['family'] = $family['description_chinese'];
                        $make = $this->getOne('select description_chinese from car_type_make where make_code=\''.$info['make_code'].'\'');
                        $rs['make'] = $make['description_chinese'];
                        $title =  $rs;
                    } else {
                        $title =  $rs['vehicle'];
                    }
                } else {//乘用车 系列+徽章+变速形式+排量
                    $family = $this->getOne('select description_chinese from car_type_family where family_code=\''.$info['family_code'].'\' and make_code=\''.$info['make_code'].'\'');
                    $rs['family'] = $family['description_chinese'];
                    $make = $this->getOne('select description_chinese from car_type_make where make_code=\''.$info['make_code'].'\'');
                    $rs['make'] = $make['description_chinese'];

                    $rs['vehicle'] = '';
                    $rs['vehicle'] .= $info['series_cn'];
                    $rs['vehicle'] .= $info['badge_description_cn'];

                    $rs['vehicle'] .= $info['gear_type_description_cn'];
                    $rs['vehicle'] .= $info['engine_description'];
                    if ($info['induction_description']=='Turbo') {
                        $rs['vehicle'] .= 'T';
                    }
                    if ($returnType=='full') {
                        if (strpos($rs['family'],$rs['make'])!==false) {
                            $title = $rs['family'].$rs['vehicle'];
                        } else {
                            $title = $rs['make'].$rs['family'].$rs['vehicle'];
                        }
                    } elseif ($returnType=='familyAndVehicle') {
                        $title =  $rs['family'].$rs['vehicle'];
                    }elseif ($returnType=='make') {
                        $title =  $rs['make'];

                    }elseif ($returnType=='array') {
                        $title =  $rs;
                    }
                }
            } else {
                $family = $this->getOne('select description_chinese from car_type_family where family_code=\''.$familyCode.'\' and make_code=\''.$makeCode.'\'');
                $make = $this->getOne('select description_chinese from car_type_make where make_code=\''.$makeCode.'\'');
                $title = $make['description_chinese'] . $family['description_chinese'];
            }
        }
        return $title;
    }
    /**
     * 发布新车
     * @param type $data
     * @return type
     */
    public function newSale($data = array()){
        //获取数据
        if(is_array($data) and count($data))$this->data = array_merge($this->data, $data);


        //默认数据
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
            LoggerGearman::logInfo(array('data'=>array('params' => $data, 'message'=> $this->errField,), 'identity'=>'appservexception_model_id'));
            throw new AppServException(AppServErrorVars::CUSTOM, '数据格式输入有误:' . $this->errField);
        }

        $this->data['insert_time'] = 'to_date(\''.date("Y-m-d H:i:s").'\',\'YYYY-MM-DD hh24:mi:ss\')';
        $this->data['update_time'] = $this->data['insert_time'];
        $this->data['follow_time'] = $this->data['insert_time'];
        $this->data['add_source'] = '0';
        $this->data['info_id'] = $_info_id = getInfoid(1);
        $this->data['info_source'] = '13';
        $this->data['info_type'] = '3';
        $this->data['sale_status'] = '0';
        $this->data['status'] = '0';
        $this->data['insert_user_id'] = $this->data['follow_user'] = AppServAuth::$userInfo['user']['username'];
        $this->data['dept_id'] = AppServAuth::$userInfo['user']['dept_id'];
        $this->data['price']=floatval($this->data['price'])*10000;
        $this->data['kilometer']=floatval($this->data['kilometer'])*10000;
        if ($this->data['make_code']) {
            $this->data['brand_caption'] = $this->getVehicleFullTitle($this->data['vehicle_key'],$this->data['make_code'],$this->data['family_code']);
        } else {
            $this->data['brand_caption'] = VehicleV2Interface::getModelCaption(array(
                    'brand_id' => $this->data['brand_id'],
                    'series_id' => $this->data['series_id'],
                    'model_id' => $this->data['model_id'],
            ));
        }
        if (AppServAuth::$userInfo['user']['city'] == 1) {
            $this->data['order_status'] = 99;
        }
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
        $saleId=$this->add();
        if(empty($saleId)) {
            throw new AppServException(AppServErrorVars::CUSTOM, '发布车源失败');
        }

        $GLOBALS['_midTime'] = microtime(TRUE);

        //审核日志 27代表店长
        $row=$this->getOne("select mbs_check_message_0.nextval currval FROM dual");
        $_id=$row['currval'];
        $_dianzhang=$this->getOne("select username from mbs_user where role_id=27 and status=1 and dept_id=".AppServAuth::$userInfo['user']['dept_id']);
        $_accept_id=$_dianzhang['username'];
        if (AppServAuth::$userInfo['user']['role_id'] == 26) {
            $message = '客服分配了';
        } else {
            $message = AppServAuth::$userInfo['user']['real_name']."发布了";
        }
        $_insert_time = 'to_date(\''.date("Y-m-d H:i:s").'\',\'YYYY-MM-DD hh24:mi:ss\')';
        $sql="insert into mbs_check_message(id,create_id,accept_id,info_id,insert_time,status,reason,title)
            values('".$_id."','".AppServAuth::$userInfo['user']['username']."','".$_accept_id."','".$_info_id."',".$_insert_time.",'0','5','".$message."条卖车信息编号为".$_info_id.",请填写评估价并审核同步！')";
        $this->query($sql);


        return $saleId;
    }

    /**
     * 保存车源
     * @param type $data
     * @return type
     */
    public function saveSale($data = array()){
        
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
            $mdoelInfo = VehicleV2Interface::getModelByCode(array('code' => $this->data['vehicle_key']));
            $this->data['model_id'] = !empty($modelInfo['id']) ? $modelInfo['id'] : 0;
        }

        //字段验证
        if(!$this->validate()) {
            throw new AppServException(AppServErrorVars::CUSTOM, '数据格式输入有误:' . $this->errField);
        }

        //判断是否是该用户的车源
        $carInfo = $this->saleInfo($this->data['id']);
        if(!$carInfo) {
            throw new AppServException(AppServErrorVars::CUSTOM, '车源不存在');
        }
        if($carInfo['follow_user'] != AppServAuth::$userInfo['user']['username']) {
            throw new AppServException(AppServErrorVars::CUSTOM, '不是本人车源');
        }
        

        //默认数据及防止传入多余参数
        unset($this->data['info_type']);
        unset($this->data['dept_id']);
        unset($this->data['insert_time']);
        unset($this->data['sale_status']);
        $customer = $this->getOne("select real_name,mobile,idcard,telephone from mbs_customer where id=".intval($carInfo['customer_id']));
        $workFlow = new ModelWorkFlow();
        if ($this->data['telephone'] != $customer['mobile'] || $data['telephone2'] != $customer['telephone']) {
            $info = array(
                'mobile' => $this->data['telephone'],
                'telephone' => $data['telephone2'],
                'info_id' => $carInfo['info_id'],
                'update_reason' => '',
            );
            $workFlow->workFlowProcess(1, $info);
        }
        if ($this->data['car_number'] && $this->data['car_number'] != $carInfo['car_number']) {
            $info = array(
                'car_number' => $this->data['car_number'],
                'info_id' => $carInfo['info_id'],
                'update_reason' => '',
            );
            $workFlow->workFlowProcess(2, $info);
        }
        if ($data['idcard'] && $data['idcard'] != $customer['idcard']) {
            $info = array(
                'idcard' => $data['idcard'],
                'info_id' => $carInfo['info_id'],
                'update_reason' => '',
            );
            $workFlow->workFlowProcess(3, $info);
        }
        unset($this->data['telephone']);
        unset($this->data['telephone2']);
        unset($this->data['idcard']);
        //unset($this->data['car_number']);
        unset($this->data['status']);
        unset($this->data['follow_user']);
        unset($this->data['insert_user_id']);
        $this->data['price']=floatval($this->data['price'])*10000;
        $this->data['kilometer']=floatval($this->data['kilometer'])*10000;
        $this->data['update_time'] = 'to_date(\''.date("Y-m-d H:i:s").'\',\'YYYY-MM-DD hh24:mi:ss\')';
        if ($this->data['make_code']) {
            $this->data['brand_caption'] = $this->getVehicleFullTitle($this->data['vehicle_key'],$this->data['make_code'],$this->data['family_code']);
        } else {
            $this->data['brand_caption'] = VehicleV2Interface::getModelCaption(array(
                    'brand_id' => $this->data['brand_id'],
                    'series_id' => $this->data['series_id'],
                    'model_id' => $this->data['model_id'],
            ));
        }
        $carId=$this->data['id'];
        //修改
        if(!$this->save()) {
            throw new AppServException(AppServErrorVars::CUSTOM, '车源修改失败');
        }

        return $carId;
    }
    
    public function updateTime($id,$infoId=0) {
        $this->data = array();
        $this->data['id'] = $id;
        $this->data['update_time'] = 'to_date(\''.date("Y-m-d H:i:s").'\',\'YYYY-MM-DD hh24:mi:ss\')';
        if(!$this->save()) {
            throw new AppServException(AppServErrorVars::CUSTOM, '车源修改失败');
        }
        $this->query('insert into mbs_refresh_log (log_id,info_id,refresh_date) values (seq_mbs_refresh_log.nextval,\''.$infoId.'\',sysdate)');
        return $id;
    }

    /**
     * 设置缩略图
     */
    public function saleCover($cover='', $id=0){
        if($cover=='' || $cover=='0' || !$id)return;
        $this->setData(array('photo'=>$cover,'id'=>$id));
        $this->save();
    }

    /**
     * 设置客户
     */
    public function saleCustomer($cid=0, $id=0){
        if(!$cid or !$id)return;
        $this->setData(array('customer_id'=>$cid,'id'=>$id));
        $this->save();
    }



    /**
     *获取通话列表
     *
     */
    public function phoneList($query) {
        $info = array();
        if ($query['query_type'] == 'store') {
            if ($query['salesman']) {
                $info['follow_user'] = $query['salesman'];
            } else {
                $deptId = AppServAuth::$userInfo['user']['dept_id'];
                $info['dept_id'] = $deptId;
            }
        } elseif ($query['query_type'] == 'user') {
            $info['follow_user_id'] = AppServAuth::$userInfo['user']['username'];
        }

        if ($query['lasting'] && is_numeric($query['lasting'])) {
            //当$query['lasting']为-1时查询未接通的来电
            if ($query['lasting'] == -1) {
                $sql .= ' and p.calledtotallen = 0';
                $info['calledtotallen'] = 0;
            }else {
                $sql .= ' and p.calledtotallen<=' . $query['lasting'];
                $info['calledtotallen'] = $query['lasting'];
            }
        }
        if ($query['time']) {
            $timeArray = explode('_', $query['time']);
            $info['calling_start'] = strtotime($timeArray[0]);
            $info['calling_end'] = strtotime($timeArray[1]);    
            $sql .= " and p.calling_time>=" . strtotime($timeArray[0]);
            $sql .= " and p.calling_time<=" . strtotime($timeArray[1]);
        }
        if ($query['make_code']) {
            $brandInfo = VehicleV2Interface::getBrandByCode(array('code' => $query['make_code']));
            $brandId = !empty($brandInfo['id']) ? $brandInfo['id'] : 0;
            $info['brand_id'] = $brand_id;
            $sql .= " and s.brand_id=" . $brandId;
        }
        if ($query['family_code']) {
            $seriesInfo = VehicleV2Interface::getSeriesByCode(array('code' => $query['family_code']));
            $seriesId = !empty($seriesInfo['id']) ? $seriesInfo['id'] : 0;
            $info['series_id'] = $seriesId;
            $sql .= " and s.series_id=" . $seriesId;
        }
        if ($query['vehicle_type']) {
            $typeId = Cheyou::$CAR_TYPE[$query['vehicle_type']];
            $info['car_type'] =$typeId;
            $sql .= " and s.car_type=" . $typeId;
        }
        if ($query['brand_id']) {
            $sql .= " and s.brand_id=" . $query['brand_id'];
            $info['brand_id'] =$query['brand_id'];
        }
        if ($query['series_id']) {
            $sql .= " and s.series_id=" . $query['series_id'];
            $info['series_id'] = $query['series_id'];
        }
        if ($query['type_id']) {
            $sql .= " and s.car_type=" . $query['type_id'];
            $info['car_type'] = $query['type_id'];
        }
        $limit['rowFrom'] = $query['start'];
        $limit['rowTo'] = $query['end'];
        $info['order_by'] = ' p.calling_time desc ';
        $phoneCallLogInterface    = new PhoneCallLogInterface();
        $data = $phoneCallLogInterface->getPhoneInfoByCondition($info, $limit);
        foreach($data as $k =>$v) {
            $data[$k]['follow_user'] = $v['follow_user_id'] ? $v['follow_user_id'] : '';
            $data[$k]['call_phone'] = $v['caller'];
            $data[$k]['title'] = $v['title'] ? $v['title'] : '';
            $data[$k]['photo'] = $v['cover_photo'];
            $data[$k]['card_time'] = $v['card_time'] ? $v['card_time'] : '';
            $data[$k]['calling_time'] = date('Y-m-d H:m:s', $v['calling_time']);
            
            unset($data[$k]['cover_photo']);
            unset($data[$k]['caller']);
            unset($data[$k]['follow_user_id']);
            unset($data[$k]['sms_satisfy']);
            unset($data[$k]['satisfy']);
            unset($data[$k]['dept_id']);
            unset($data[$k]['auto_id']);
        }
        return $data;
    }

    /**
     *获取通话列表
     *
     */
    public function phoneListOld($query) {
        $sql = 'select * from (select a.*,rownum as rn from (select 
            p.file_path,
            p.caller as call_phone,
            to_char(TO_DATE(\'19700101\',\'yyyymmdd\') + p.calling_time/86400 +TO_NUMBER(SUBSTR(TZ_OFFSET(sessiontimezone),1,3))/24,\'yyyy-mm-dd HH24:MI:SS\') as call_time,
            p.calledtotallen as call_lasting,
            p.sale_id,
            s.dept_id,
            s.follow_user,
            s.id,
            s.status,
            s.sale_status,
            s.info_id,
            s.vehicle_type,
            s.make_code,
            s.family_code,
            s.car_type,
            s.brand_id,
            s.series_id,
            s.model_id,
            s.vehicle_key,
            s.photo,
            s.province,
            s.city,
            s.district,
            s.customer_id,
            s.plate_province,
            s.plate_city,
            s.car_color,
            s.price,
            s.kilometer,
            s.car_number,
            s.note,
            s.title,
            s.transfer_num,
            s.maintain_address,
            s.use_quality,
            s.sale_quality,
            s.brand_caption,
            to_char(s.safe_time,\'YYYY-mm-dd\') as safe_time,
            to_char(s.year_check_time,\'YYYY-mm-dd\') as year_check_time,
            to_char(s.busi_insur_time,\'YYYY-mm-dd\') as busi_insur_time,
            to_char(s.card_time,\'YYYY-mm-dd\') as card_time,
            to_char(s.insert_time,\'YYYY-MM-DD hh24:mi:ss\') as insert_time,
            to_char(s.update_time,\'YYYY-MM-DD hh24:mi:ss\') as update_time
                from mbs_phone_call_log p inner join mbs_view_sale s on p.sale_id = s.id 
                where 
        ';
        if ($query['query_type'] == 'store') {
            if ($query['salesman']) {
                $sql .= " s.follow_user='" . $query['salesman'] . "'";
            } else {
                $deptId = AppServAuth::$userInfo['user']['dept_id'];
                $sql .= ' s.dept_id=' . $deptId;
            }
        } elseif ($query['query_type'] == 'user') {
            $sql .= " s.follow_user='" . AppServAuth::$userInfo['user']['username'] . "'";
        } //else {
            //$sql .= 'rownum < 50';
        //}
        if ($query['lasting'] && is_numeric($query['lasting'])) {
            //当$query['lasting']为-1时查询未接通的来电
            if ($query['lasting'] == -1) {
                $sql .= ' and p.calledtotallen = 0';
            }else {
                $sql .= ' and p.calledtotallen<=' . $query['lasting'];
            }
        }
        if ($query['time']) {
            $timeArray = explode('_', $query['time']);
            $sql .= " and p.calling_time>=" . strtotime($timeArray[0]);
            $sql .= " and p.calling_time<=" . strtotime($timeArray[1]);
        }
        if ($query['make_code']) {
            $brandInfo = VehicleV2Interface::getBrandByCode(array('code' => $query['make_code']));
            $brandId = !empty($brandInfo['id']) ? $brandInfo['id'] : 0;
            $sql .= " and s.brand_id=" . $brandId;
        }
        if ($query['family_code']) {
            $seriesInfo = VehicleV2Interface::getSeriesByCode(array('code' => $query['family_code']));
            $seriesId = !empty($seriesInfo['id']) ? $seriesInfo['id'] : 0;
            $sql .= " and s.series_id=" . $seriesId;
        }
        if ($query['vehicle_type']) {
            $typeId = Cheyou::$CAR_TYPE[$query['vehicle_type']];
            $sql .= " and s.car_type=" . $typeId;
        }
        if ($query['brand_id']) {
            $sql .= " and s.brand_id=" . $query['brand_id'];
        }
        if ($query['series_id']) {
            $sql .= " and s.series_id=" . $query['series_id'];
        }
        if ($query['type_id']) {
            $sql .= " and s.car_type=" . $query['type_id'];
        }
        $sql .= " order by p.calling_time desc) a where rownum <= ". $query['end'] . ") where rn >= " . $query['start'];
        $data = $this->query($sql);
        if(!$data)return false;
        return $data;
        //return $this->_format($data, 0);
    }
    public function saleInfo($carid, $infoId=0){
        
        $where = ' id = \''.$carid.'\'';
        if ($infoId) {
            $where = ' info_id = \''.$infoId.'\'';
        }

        $this->setOption('where', $where);
        $this->setOption('field', 'id,info_id,status,sale_status,title,dept_id,vehicle_type,make_code,family_code,vehicle_key,car_type,brand_id,series_id,model_id,photo,province,city,district,follow_user,customer_id,
            plate_province,plate_city,car_color,price,kilometer,car_number,note,transfer_num,maintain_address,use_quality,sale_quality,brand_caption,
            to_char(safe_time,\'YYYY-mm-dd\') as safe_time,to_char(year_check_time,\'YYYY-mm-dd\') as year_check_time,
            to_char(busi_insur_time,\'YYYY-mm-dd\') as busi_insur_time,to_char(card_time,\'YYYY-mm-dd\') as card_time,
            to_char(insert_time,\'YYYY-MM-DD hh24:mi:ss\') as insert_time,to_char(update_time,\'YYYY-MM-DD hh24:mi:ss\') as update_time');
        $data = $this->select();
        if(!$data)return false;
        return array_shift($data);
    }
    
    public function saleViewInfo($carid, $infoId=0){
    
        $where = ' id = \''.$carid.'\'';
        if ($infoId) {
            $where = ' info_id = \''.$infoId.'\'';
        }
        $sql =  'select id,info_id,status,sale_status,title,dept_id,vehicle_type,make_code,family_code,vehicle_key,car_type,brand_id,series_id,model_id,photo,province,city,district,follow_user,customer_id,
            plate_province,plate_city,car_color,price,kilometer,car_number,note,transfer_num,maintain_address,use_quality,sale_quality,brand_caption,
            to_char(safe_time,\'YYYY-mm-dd\') as safe_time,to_char(year_check_time,\'YYYY-mm-dd\') as year_check_time,
            to_char(busi_insur_time,\'YYYY-mm-dd\') as busi_insur_time,to_char(card_time,\'YYYY-mm-dd\') as card_time,
            to_char(insert_time,\'YYYY-MM-DD hh24:mi:ss\') as insert_time,to_char(update_time,\'YYYY-MM-DD hh24:mi:ss\') as update_time from mbs_view_sale where '.$where;
         
         $data = $this->query($sql);
        if(!$data)return false;
        return array_shift($data);
    }

    /**
     * 获取车源列表
     */
    public function saleList($user=0,$start=0,$end=20,$status=0,$keyword='',$insert_time='',$price='',$cardTime='',$make='',$family='',$vehicle='',$sale_status=0,$published=0,$originate=0,$follow_user=0,$province=0,$city=0,$color=0,$gear_type=0,$brandId=0,$seriesId=0,$typeId=0,$count=false){

        if (!$count) {
            $this->setOption('limit', intval($start) .','. intval($end));
        } else {    //当$count不为false时，为获取车源总数
            $this->setOption('limit', 1);
        }
        /* 严重注意：判断车源是不是属于这个用户的，要根据follow_user来判断，而不是根据inser_user_id来判断 */
        $where = '';
        $username = AppServAuth::$userInfo['user']['username'];
        if($user == $username){  //自己的车
            $where.= 'follow_user='.$user;
        }elseif ($user == AppServAuth::$userInfo['user']['dept_id']) {  //店内的车
            $where .= 'dept_id = '. $user;
        }elseif (intval($province)) {
            $where .= 'province='. $province;
            if (intval($city)) {
                $where .= ' and city='.$city;
            }
        }else{      //搜索车
            //$where.=' city=\''.AppServAuth::$userInfo['user']['city'].'\'';
            $where .= 'rownum>0';
        }
        switch(intval($status)){
            case 1: $where.=' and status=0';break;
            case 2: $where.=' and status=1';break;
            case 3: $where.=' and sale_status=0';break;
            case 4: $where.=' and sale_status=1';
                    $where.=' and status=1';
                    $this->setTableName('car_sale_bak');
                    break;
            case 5: $where.=' and status=2';//冻结
                    $this->setTableName('car_sale_bak');
                    break;
            case 6: $where.=' and status=3';//终止
                    $this->setTableName('car_sale_bak');
                    break;
            default: $where.=' and (status=1 or status=0)';break;
        }
        switch (intval($sale_status)) {
            case 1: $where .= ' and sale_status = 0'; break;
            case 2: $where .= ' and sale_status = 1';
            $where.=' and status=1';
            $this->setTableName('car_sale_bak');
            break;
            default: $where .= ' and (sale_status = 0 or sale_status = 1)'; break;
        }
        if(!empty($keyword)){
            if(is_numeric($keyword)){
                if (in_array(strlen($keyword),array(8,10,11,12))) {
                    $where.= " and customer_id in (select id from mbs_customer where (mobile='".$keyword."' or telephone='".$keyword."' or telephone2='".$keyword."'))" 
                          .' and dept_id=' . AppServAuth::$userInfo['user']['dept_id'];
                } else {
                    $where.=' and id=\''.$keyword.'\'';
                }
            } elseif (preg_match('/^[\x81-\xfe][\x40-\xfe]{2,4}[A-Z][A-Z0-9]{4}[A-Z0-9]$/', $keyword)) {
                $where .=' and car_number=\''.$keyword.'\'';
            } else {
                //$keyList = fenci($keyword);
                //$where.=' and contains(tags,\''.implode (',', $keyList).'\',0)>0';/
                $where.=' and contains(tags,\''.$keyword.'\',0)>0';
            }
        }
        if(!empty($insert_time) and preg_match('/^\d{4}-\d{1,2}-\d{1,2} \d{1,2}:\d{1,2}:\d{1,2}$/i', $insert_time)){
            $where .= ' and insert_time < to_date(\''.$insert_time.'\',\'YYYY-MM-DD hh24:mi:ss\')';
        }
        if ($price) {
            $priceArray = explode('-', $price);
            if (isset($priceArray[0])) {
                $where .= (' and price >= ' . $priceArray[0]);
                if (isset($priceArray[1])) {
                    $where .= (' and price <= ' . $priceArray[1]);
                }
            }
        }
        if ($cardTime) {
            $timeArray = explode('-', $cardTime);
            $now = date('m-d H:i:s');
            $year = date('Y');
            if (isset($timeArray[0])) {
                $maxYear = $year - $timeArray[0];
                $maxTime = ($maxYear . '-' . $now);
                $where .= ' and card_time < to_date(\''.$maxTime.'\',\'YYYY-MM-DD hh24:mi:ss\')';
                if (isset($timeArray[1])) {
                    $minYear = $year - $timeArray[1];
                    $minTime = ($minYear . '-' . $now);
                    $where .= ' and card_time >= to_date(\''.$minTime.'\',\'YYYY-MM-DD hh24:mi:ss\')';
                }
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
        
        //按发布时间进行筛选
        switch (intval($published)) {
            case 1: //一周内
                $timeRange = date("Y-m-d H:i:s",strtotime('-1 week'));
                $where .= ' and insert_time > to_date(\''.$timeRange.'\',\'YYYY-MM-DD hh24:mi:ss\')';
                break;
            case 2: //半个月内
                $timeRange = date("Y-m-d H:i:s",strtotime('-15 day'));
                $where .= ' and insert_time > to_date(\''.$timeRange.'\',\'YYYY-MM-DD hh24:mi:ss\')';
                break;
            case 3: //一个月内
                $timeRange = date("Y-m-d H:i:s",strtotime('-1 month'));
                $where .= ' and insert_time > to_date(\''.$timeRange.'\',\'YYYY-MM-DD hh24:mi:ss\')';
                break;
            case 4: //三个月内
                $timeRange = date("Y-m-d H:i:s",strtotime('-3 month'));
                $where .= ' and insert_time > to_date(\''.$timeRange.'\',\'YYYY-MM-DD hh24:mi:ss\')';
                break;
            default: $where .= '';
        }
        //按车源来源筛选
        if($user == $username) {
            switch (intval($originate)) {
                case 1: //个人录入
                    $where .= ' and insert_user_id = follow_user';
                    break;
                case 2: //系统分配
                    $where .= ' and insert_user_id <> follow_user' ;
                    break;
                default: $where .= '';
            }
        }
        //按归属人进行筛选,仅当查询店内卖车列表时可筛选
        if ($user == AppServAuth::$userInfo['user']['dept_id']) {
            if($follow_user) {
                $where .= ' and follow_user =' . $follow_user;
            }
        }
        //按车的颜色筛选
        if (intval($color)) {
            $where .= ' and car_color=' . $color;
        } 
        //按变速器筛选
        switch (intval($gear_type)) {
            case 1: //手动
                 $where .= (' and vehicle_key in (select vehicle_key from car_type_vehicle where gear_type_description_cn =\'手动\')');
                 break;
            case 2: //自动
                $where .= (' and vehicle_key in (select vehicle_key from car_type_vehicle where gear_type_description_cn =\'自动\')');
                break;
            case 3: //手自一体
                $where .= (' and vehicle_key in (select vehicle_key from car_type_vehicle where gear_type_description_cn =\'手自一体\')');
                break;
            default: $where .= '';
        }
        //只获取本站车源
        $where .= ' and order_source = 10';
        $this->setOption('where', $where);
        if ($count) {
            $this->setOption('field', 'count(id)');
            $nums = $this->select();
            return $nums;
        }
        $this->setOption('order', 'insert_time desc');
        $this->setOption('field', 'id,info_id,status,sale_status,title,dept_id,vehicle_type,make_code,family_code,vehicle_key,car_type,brand_id,series_id,model_id,photo,province,city,district,follow_user,customer_id,
            plate_province,plate_city,car_color,price,kilometer,car_number,note,transfer_num,maintain_address,use_quality,sale_quality,brand_caption,insert_user_id,
            to_char(safe_time,\'YYYY-mm-dd\') as safe_time,to_char(year_check_time,\'YYYY-mm-dd\') as year_check_time,
            to_char(busi_insur_time,\'YYYY-mm-dd\') as busi_insur_time,to_char(card_time,\'YYYY-mm-dd\') as card_time,
            to_char(insert_time,\'YYYY-MM-DD hh24:mi:ss\') as insert_time,to_char(update_time,\'YYYY-MM-DD hh24:mi:ss\') as update_time');
        $data = $this->select();
        if(!$data)return false;
        return $data;
        //return $this->_format($data, $user);
    }
    
    /**
     * 根据车牌号码和录入人角色ID获取有效车源
     */
   public function saleValid($carNumber,$role) {
       $where = 'car_number = \''.$carNumber.'\' and (status=0 or status=2 or (status=1 and sale_status=0) )';
       if ($role == 26) {  //店秘录入时判断店内是否已存在该车牌号有效车源
           $where .= ' and dept_id=\''.AppServAuth::$userInfo['user']['dept_id'].'\'';
       }
       if ($role == 27 || $role == 28 || $role == 165) {  //同一个店长或交易顾问或业务主任录入时
           $where .= ' and follow_user=\''.AppServAuth::$userInfo['user']['username'].'\'';
       }
       $this->setOption('where', $where);
       $this->setOption('field', 'id,info_id,status,sale_status,title,dept_id,vehicle_type,make_code,family_code,vehicle_key,car_type,brand_id,series_id,model_id,photo,province,city,district,follow_user,customer_id,
            plate_province,plate_city,car_color,price,kilometer,car_number,note,transfer_num,maintain_address,use_quality,sale_quality,brand_caption,insert_user_id,
            to_char(safe_time,\'YYYY-mm-dd\') as safe_time,to_char(year_check_time,\'YYYY-mm-dd\') as year_check_time,
            to_char(busi_insur_time,\'YYYY-mm-dd\') as busi_insur_time,to_char(card_time,\'YYYY-mm-dd\') as card_time,
            to_char(insert_time,\'YYYY-MM-DD hh24:mi:ss\') as insert_time,to_char(update_time,\'YYYY-MM-DD hh24:mi:ss\') as update_time');
       $data = $this->select();
       if(!$data)return false;
       return array_shift($data);
   } 
}
?>
