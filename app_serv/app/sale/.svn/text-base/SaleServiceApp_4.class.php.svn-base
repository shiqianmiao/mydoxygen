<?php
/**
 * @brief 手机卖车接口4版本,主要为一些卖车车源相关的一些接口。
 * @author 缪石乾
 * @version 4.0
 * @date 2014-7-31
 * @attention 修改手机业管的童鞋注意下是否影响PC业管哈！
 */

require_once FRAMEWORK_PATH . '/util/common/Util.class.php';
require_once API_PATH . '/interface/MbsDeptInterface.class.php';
require_once APP_SERV . '/config/AppServConfVars.class.php';
require_once API_PATH . '/interface/mbs/MbsSmsEmailInterface.class.php';
require_once API_PATH . '/interface/CarSaleInterface.class.php';
require_once API_PATH . '/interface/CarSaleBakInterface.class.php';
require_once APP_SERV . '/app/sale/include_v2/SaleHelper.class.php';
require_once APP_SERV . '/app/sale/include_v2/SaleVars.class.php';
require_once COM_PATH . '/car/CarVars.class.php';
require_once COM_PATH . '/car/CarStatusVars.class.php';
require_once COM_PATH . '/car/CarAppVars.class.php';
require_once API_PATH . '/interface/mbs/MbsCarSaleInterface.class.php';
require_once API_PATH . '/interface/mbs/MbsCarSaleTmpInterface.class.php';

class SaleServiceApp_4 {
    /**
     * @brief 手机业管发布卖车接口（包括草稿发布，卖车第一步发布提交，卖车第一步编辑提交）
     * @author 缪石乾
     * @param : 参数说明如下表格：
     * 参数名称     | 参数类型   | 参数补充描述
     * ------------|----------|------------------------------------------------
     * id          | int      | 车源编号。添加车源的时候可以不传或者置空，编辑是必须要
     * is_draft    | int      | 是否草稿。0:非草稿、1:是草稿
     * brand_id    | int      | 车源品牌id
     * series_id   | int      | 车源车系id
     * model_id    | int      | 车源年款id
     * province    | int      | 发车业务员所在省份id。即交易省份id
     * city        | int      | 发车业务员所在城市id。即交易城市id
     * district    | int      | 发车业务员所在的区域id。即交易区域id
     * car_color   | int      | 车身颜色的枚举值。
     * kilometer   | float    | 行驶公里数，注意：单位是 万。
     * price       | float    | 车源价格，注意：单位是 万。
     * description | string   | 车主描述内容。
     * card_time   | date     | 上牌时间，例如：2014-10-3
     * transfer_num | int     | 变更次数
     * maintain_address | int | 保养地点的枚举值。
     * use_quality | int      | 使用性质的枚举值。
     * ad_note     | string   | 一句话广告。
     * safe_time   | date 或 int| 如果交强险过保，safe_time = -1, 否则上发 交强险到期时间
     * year_check_time | date 或 int | 如果年检未检， year_check_time = -1, 否则上发年检到期时间
     * busi_insur_checked | int  | 是否有商业险， 1:有，2:无
     * busi_insur_price |  float | 商业险金额，注意：单位为万。如果有商业险的话，必须要有商业险金额。
     * busi_insur_time  |  date  | 商业险到期时间，如果有商业险的话，必须要有商业险到期时间。
     * scratched   | int      | 是否查看车况。0:无，1:有
     * contact_user| string   | 联系人。即卖主姓名
     * plate_province | int   | 上牌省份的id
     * plate_city  | int      | 上牌城市的id
     * scratched   | int      | 是否有车架事故痕迹，0:没选择，1:用户选择 无，2:用户选择 有
     * soaked      | int      | 是否泡水，0:没选择，1:用户选择 无，2:用户选择 有
     * engine_fixed| int      | 发动机是否拆过，0:没选择，1:用户选择 无，2:用户选择 有
     * odometer_fixed | int   | 行驶里程是否跳过，0:没选择，1:用户选择 无，2:用户选择 有
     * condition_info | object | 车架事故具体痕迹，已json对象的形式上发，如果选择有车架事故痕迹的话，该项必须有值
     * images      | object   | 车源相关的图片对象，目前包括车源图片 和 质检图片
     * car_number  | string   | 车牌号 例如 ： 闽A14856
     * telephone   | string   | 卖主电话
     * telephone2  | string   | 备用电话1
     * telephone3  | string   | 备用电话2
     * model_name  | string   | 用户自定义的车型，通常是用户在车型库这无法找到要的车型的时候，可以选择自定义车型。此时model_id为空。
     * @return json格式数据
     * 返回值名称   | 返回值类型   | 返回值补充描述
     * ------------|----------|------------------------------------------------
     * car_id      | int      | 车源编号。添加车源或编辑车源后，返回该车源的编号
     * errorMessage| string   | 如果发送异常的话，保存异常说明信息
     * @attention 该发车与PC业管使用统一内部发布流程，如有修改请注意是否影响PC发车流程
     * @attention 以上参数内容，如果某个参数没有的话，可以不传 或者 置为空。
     */
    public function newSale($params) {
        $user     =  AppServAuth::$userInfo['user'];
        $deptInfo = MbsDeptInterface::getDeptInfoByDeptId(array('id' => $user['dept_id']));
        //客户端上来的车源图片和质检图片混在一起了
        $images   = Util::getFromArray('image', $params, array());
        $id       = Util::getFromArray('id', $params, 0);
        $smsObj   = MbsSmsEmailInterface::getInstance();
        list($carImages, $qualityImages, $coverImages) = $this->_parseImages($images);
        //如果不是保存草稿，可是又没有车牌质检图片，直接抛出异常。
        /*if (!$params['is_draft'] && empty($qualityImages)) {
            $smsCont = print_r($params, true);
            $smsObj->sendEmail('手机业管编辑车源车牌图片为空', "参数：{$smsCont}");
        }*/
        
        //传给接口的参数
        $insertParams = array(
            'user'           => $user,
            'info'           => array(),
            'car_images'     => $carImages,
            'quality_images' => $qualityImages,
            'is_mobile'      => true,
        );
        
        //特殊字段(因为历史原因，一些特殊字段的处理)
        $isSafeBusiness = Util::getFromArray('busi_insur_checked', $params, 0);
        if (!empty($isSafeBusiness)) {
            //手机客户端传上来的值与Pc业管相反了。
            $isSafeBusiness = $isSafeBusiness == 1 ? 2 : 1;
        }
        $safeTime      = Util::getFromArray('safe_time', $params, 0);
        $yearCheckTime = Util::getFromArray('year_check_time', $params, 0);
        $conditionInfo = !empty($params['condition_info']) ? json_decode(stripslashes($params['condition_info']) , true) 
                         : array();
        //车架事故痕迹的具体内容
        $scratched = (!empty($conditionInfo['scratched']) && !empty($conditionInfo['scratches'])) 
                     ? implode(',', $conditionInfo['scratches']) : '';
        
        $insertParams['info'] = array(
            'id'                   => !empty($id) ? $id : 0,
            'is_caogao'            => $params['is_draft'] ? true : false,
            'brand_id'             => Util::getFromArray('brand_id', $params, 0),
            'series_id'            => Util::getFromArray('series_id', $params, 0),
            'model_id'             => Util::getFromArray('model_id', $params, 0),
            'deal_province_id'     => Util::getFromArray('province', $deptInfo, 0),
            'deal_city_id'         => Util::getFromArray('city', $deptInfo, 0),
            'deal_district_id'     => Util::getFromArray('district', $deptInfo, 0),
            'car_color'            => Util::getFromArray('car_color', $params, 0),
            'kilometer'            => !empty($params['kilometer']) ? $params['kilometer'] * 10000 : 0,
            'price'                => !empty($params['price']) ? $params['price'] * 10000 : 0,
            'description'          => Util::getFromArray('description', $params, ''),
            'card_time'            => !empty($params['card_time']) ? strtotime($params['card_time']) : 0,
            'transfer_num'         => Util::getFromArray('transfer_num', $params, 0),
            'maintain_address'     => Util::getFromArray('maintain_address', $params, 0),
            'use_type'             => Util::getFromArray('use_quality', $params, 0),
            'ad_note'              => Util::getFromArray('ad_note', $params, ''),
            'pass_safe'            => $safeTime == -1 ? 1 : 0, //较强险过保,
            'is_safe_business'     => $isSafeBusiness,
            'safe_business_cash'   => !empty($params['busi_insur_price']) ? $params['busi_insur_price'] * 10000 : 0,
            'safe_business_time'   => !empty($params['busi_insur_time']) ? strtotime($params['busi_insur_time']) : 0,
            'safe_force_time'      => (!empty($safeTime) && $safeTime != -1) ? strtotime($safeTime) : 0,
            'no_year_check'        => $yearCheckTime == -1 ? 1 : 0, //未检,
            'year_check_time'      => (!empty($yearCheckTime) && $yearCheckTime != -1) ? strtotime($yearCheckTime) : 0,
            'is_look_ck'           => !empty($conditionInfo['scratched']) ? 1 : 0,
            'seller_name'          => Util::getFromArray('contact_user', $params, ''),
            'plate_province_id'    => Util::getFromArray('plate_province', $params, 0),
            'plate_city_id'        => Util::getFromArray('plate_city', $params, 0),
            'is_response'          => 1, //手机业管目前没有提交，只有在客户端验证，能提交上来都说明勾选过了。
            'is_frame_problem'     => !empty($conditionInfo['scratched']) ? $conditionInfo['scratched'] : 0,
            'is_water_problem'     => !empty($conditionInfo['soaked']) ? $conditionInfo['soaked'] : 0,
            'is_engine_problem'    => !empty($conditionInfo['engine_fixed']) ? $conditionInfo['engine_fixed'] : 0,
            'is_kilometer_problem' => !empty($conditionInfo['odometer_fixed']) ? $conditionInfo['odometer_fixed'] : 0,
            'ck_detail'            => $scratched,
            'cover_photo'          => !empty($coverImages['file_path']) ? $coverImages['file_path'] : '',
            'car_number'           => Util::getFromArray('car_number', $params, ''),
            'telephone'            => Util::getFromArray('telephone', $params, ''),
            'telephone1'           => Util::getFromArray('telephone2', $params, ''),
            'telephone2'           => Util::getFromArray('telephone3', $params, ''),
            'car_vin'              => Util::getFromArray('vin_code', $params, ''),
            'model_text'           => Util::getFromArray('model_name', $params, ''),
        );
        
        //添加或编辑车源
        include_once API_PATH . '/interface/mbs/mobile/PubSaleInterface.class.php';
        $ret = PubSaleInterface::pubSale($insertParams);
        
        $carId = '';
        if ($ret['errorCode'] == 1) {
            //添加或编辑失败
            $msg = !empty($ret['msg']) ? $ret['msg'] : '发布失败！请重试！';
            throw new AppServException(AppServErrorVars::CUSTOM, $msg);
        }
        $carId = !empty($ret['msg']) ? $ret['msg'] : '';
        
        //应客户端特殊要求，他们需要更新客户端配置文件
        $isCkCity   = AppServConfVars::getCkConfigCity($user['city']); //是否车况承诺城市
        $disclaimer = Util::getFromArray('disclaimer_selected', $params, 0); //客户端特殊参数
        $configOutDate = 0;
        if (($isCkCity && empty($disclaimer)) || (!$isCkCity && !empty($disclaimer))) {
            //车况城市且客户端未上来该参数 或者 非车况城市、客户端确上来该参数。发布成功时抛出异常,让客户端去更新配置文件
            $configOutDate = 1;
        }
        
        //发布或编辑成功后的处理
        if (empty($id) && $carId) {
            return $configOutDate ? array('car_id' => $carId, 'config_outdated' => 1) : array('car_id' => $carId);
        } else if ($carId) {
            //编辑成功,考虑是否需要进同步车源的编辑队列
            $searchArr['filters'][] = array('car_sale_id', '=', $carId);
            $syncList = SyncInfoInterface::getSyncSaleInfoListByWhere($searchArr);
            if($syncList[0]) { //同步成功过，才进入编辑队列
                $eidtQueueArray = array(
                    'id'       => $carId,
                    'edittime' => time(),
                );
                SyncInfoInterface::setEditQueue($eidtQueueArray);
            }
            
            return array('car_id' => $carId);
        }
        throw new AppServException(AppServErrorVars::CUSTOM, '保存车源异常');
    }
    
    /**
     * @brief 手机业管更新卖车接口(发布卖车第一步，第二步在网页版上面执行)
     * @author 缪石乾
     * @param : 参数说明如下表格：
     * 参数名称     | 参数类型   | 参数补充描述
     * ------------|----------|------------------------------------------------
     * id          | int      | 车源编号。添加车源的时候可以不传或者置空，编辑是必须要
     * is_draft    | int      | 是否草稿。0:非草稿、1:是草稿
     * brand_id    | int      | 车源品牌id
     * series_id   | int      | 车源车系id
     * model_id    | int      | 车源年款id
     * province    | int      | 发车业务员所在省份id。即交易省份id
     * city        | int      | 发车业务员所在城市id。即交易城市id
     * district    | int      | 发车业务员所在的区域id。即交易区域id
     * car_color   | int      | 车身颜色的枚举值。
     * kilometer   | float    | 行驶公里数，注意：单位是 万。
     * price       | float    | 车源价格，注意：单位是 万。
     * description | string   | 车主描述内容。
     * card_time   | date     | 上牌时间，例如：2014-10-3
     * transfer_num | int     | 变更次数
     * maintain_address | int | 保养地点的枚举值。
     * use_quality | int      | 使用性质的枚举值。
     * ad_note     | string   | 一句话广告。
     * safe_time   | date 或 int| 如果交强险过保，safe_time = -1, 否则上发 交强险到期时间
     * year_check_time | date 或 int | 如果年检未检， year_check_time = -1, 否则上发年检到期时间
     * busi_insur_checked | int  | 是否有商业险， 1:有，2:无
     * busi_insur_price |  float | 商业险金额，注意：单位为万。如果有商业险的话，必须要有商业险金额。
     * busi_insur_time  |  date  | 商业险到期时间，如果有商业险的话，必须要有商业险到期时间。
     * scratched   | int      | 是否查看车况。0:无，1:有
     * contact_user| string   | 联系人。即卖主姓名
     * plate_province | int   | 上牌省份的id
     * plate_city  | int      | 上牌城市的id
     * scratched   | int      | 是否有车架事故痕迹，0:没选择，1:用户选择 无，2:用户选择 有
     * soaked      | int      | 是否泡水，0:没选择，1:用户选择 无，2:用户选择 有
     * engine_fixed| int      | 发动机是否拆过，0:没选择，1:用户选择 无，2:用户选择 有
     * odometer_fixed | int   | 行驶里程是否跳过，0:没选择，1:用户选择 无，2:用户选择 有
     * condition_info | object | 车架事故具体痕迹，已json对象的形式上发，如果选择有车架事故痕迹的话，该项必须有值
     * images      | object   | 车源相关的图片对象，目前包括车源图片 和 质检图片
     * car_number  | string   | 车牌号 例如 ： 闽A14856
     * telephone   | string   | 卖主电话
     * telephone2  | string   | 备用电话1
     * telephone3  | string   | 备用电话2
     * model_name  | string   | 用户自定义的车型，通常是用户在车型库这无法找到要的车型的时候，可以选择自定义车型。此时model_id为空。
     * @return json格式数据
     * 返回值名称   | 返回值类型   | 返回值补充描述
     * ------------|----------|------------------------------------------------
     * car_id      | int      | 车源编号。添加车源或编辑车源后，返回该车源的编号
     * errorMessage| string   | 如果发送异常的话，保存异常说明信息
     * @attention 该发车与PC业管使用统一内部发布流程，如有修改请注意是否影响PC发车流程
     * @attention 以上参数内容，如果某个参数没有的话，可以不传 或者 置为空。
     */
    public function updateSale($params) {
        //走newSale流程，如果中途有异常直接抛出。
        $this->newSale($params);
        //能到这里说明，更新流程走通
        return array('result' => 1);
    }
    
    /**
     * @brief 根据车源编号获取车源信息
     * @author 缪石乾
     * @param : 参数说明如下表
     * 参数名称   |  参数类型  | 参数补充说明
     * ----------|-----------|---------------
     * car_id    | int       | 车源编号，必须要。
     * @return 返回json对象的车源信息
     * @attention 如果没有car_id参数，将会抛出异常：缺少车源编号
     */
    public function getSaleDetailByCarId($params) {
        $carId = Util::getFromArray('car_id', $params, 0);
        if (empty($carId)) {
            throw new AppServException(AppServErrorVars::CUSTOM, '缺少车源编号！');
        }
        
        $carInfo = CarSaleInterface::getCarDetail(array('id' => $carId));
        if (empty($carInfo)) {
            $carInfo = CarSaleBakInterface::getCarDetail(array('id' => $carId));
        }
        if (empty($carInfo)) {
            throw new AppServException(AppServErrorVars::CUSTOM, '车源不存在！');
        }
        
        //格式化车源信息
        $isReview = !empty($params['is_review']) ? $params['is_review'] : false;
        $isReview = $isReview ? true : false;
        $carInfo = SaleHelper::formatSaleInfo($carInfo, $isReview);
        //车源分享用户名加密
        if ($carInfo['id'] > 0) {
            $encode = $carInfo['id'] . '_' . AppServAuth::$userInfo['user']['username'];
            $encode = base64_encode($encode);
            $url = 'http://273.cn/';
            if ($encode) {
                $url .= 's/' . $encode; 
            } else {
                $url .= 'car/' . $carInfo['id'] . 'html';
            }
            $carInfo['share_url'] = $url;
        }
        return $carInfo;
    }
    
    /**
     * @brief 通讯录接口,获取某个门店下面的通讯录信息，如果没有传门店id上来，默认返回的是反问者所在门店的通讯录信息
     * @author 缪石乾
     * @param : 参数说明如下表
     * 参数名称   |  参数类型  | 参数补充说明
     * ----------|-----------|---------------
     * dept_id   | int       | 门店id,用于获取指定门店的通讯录。可以不传，不传的话，默认取自己所在门店的通讯录
     * @date 2014-8-26
     * @return 返回值说明如下：
     * 返回值名称  | 返回值类型  | 返回值补充说明
     * ----------|-----------|----------------------
     * dept_id  | int     | 门店id
     * dept_name| char    | 门店名称
     * telephone | char   | 门店联系电话
     *  address | char    | 门店地址
     * user_info | object  | 该门店内的所有在职业务员的信息
    */
    public function addressBook($params) {
        $userInfo = AppServAuth::$userInfo['user'];
        //如果下面没有传门店id上来的话，就去登陆用户的所在门店的通讯录
        $deptId   = !empty($params['dept_id']) ? $params['dept_id'] : $userInfo['dept_id'];
        $deptInfo = $deptId ? MbsDeptInterface::getDeptInfoByDeptId(array('id' => $deptId)) : array();
        if (empty($deptInfo)) {
            throw new AppServException(AppServErrorVars::CUSTOM, '获取门店信息失败!');
        }
        
        //记录切换别的门店时的日志
        if ($deptId != $userInfo['dept_id']) {
            $info = array(
                'username'    => $userInfo['username'],
                'dept_id'     => $deptId,
                'dept_name'   => $deptInfo['dept_name'],
                'client_type' => 2,
            );
            include_once API_PATH . '/interface/mbs/log/MbsContactLogInterface.class.php';
            MbsContactLogInterface::insertInfo(array('info' => $info));
        }
        
        //给客户端的数据
        $data = array(
            'dept_info' => array(
                'dept_id'   => $deptInfo['id'],
                'dept_name' => $deptInfo['dept_name'],
                'telephone' => empty($deptInfo['telephone']) ? '' : $deptInfo['telephone'],
                'address'   => empty($deptInfo['address']) ? '' : $deptInfo['address'],
                'user_info' => $this->_getContractUser($deptId),
            ),
        );
        return $data;
    }
    
    /**
     * @brief 获取我的卖车列表
     * @author 缪石乾
     * @date 2014-10-10
     * @attention 2.9时发现，该接口新定义的2.9的规范根本不符合搜索和店内卖车列表使用，因此该方法是从2.9当中剥离出来的。
     * @todo 待重构
     */
    public function mySaleList($params) {
        $searchParams = SaleHelper::getSearchParams($params);
        $searchInExt = false;   //为true时表明查电话号码或车牌号或个人录入或系统录入
        $searchId = false;      //为true时表明查车源ID
        $myOrMystore = true;     //为true时表明查我的卖车或店内卖车
        foreach ($searchParams['filters'] as $key => $filter) {
            if ($filter[0] == 'car_status') {
                $carStatus = $filter[2];
                if ($carStatus == 4030) {
                    $searchParams['filters'][$key] = array('status', '=', 3);
                    $searchParams['filters'][] = array('sale_status', '!=', 1);
                }
            } else if ($filter[0] == 'sale_status') {
                $saleStatus = $filter[2];
            } else if ($filter[0] == 'customer_id') {
                $searchInExt = true;
            } else if ($filter[0] == 'car_number') {
                $searchInExt = true;
            } else if ($filter[0] == 'create_user_id') {
                $searchInExt = true;
            } else if ($filter[0] == 'id') {
                $searchId = true;
            } else if ($filter[0] == 'source_type' && $filter[2] == 26) {
                $searchParams['filters'][] = array('car_status', 'in', array(CarStatusVars::DRAFT, CarStatusVars::PUBLISH_MBS));
             }
        }
        
        //按刷新时间排序
        $searchParams['order'] = array('create_time' => 'desc');
        //是否查询bak表
        $isSearchBak = SaleHelper::isSearchBak($carStatus);
        if ($searchInExt) {//查电话号码或车牌号或个人录入或系统录入
            $searchParams['is_mobile'] = 1;
            
            if (!$isSearchBak) {
                $postList = CarSaleInterface::getPostFullFieldsByFilters($searchParams);
                $postList['total'] = CarSaleInterface::getFullFieldsPostCount($searchParams);
            } else {
                $postList = CarSaleBakInterface::getPostFullFieldsByFilters($searchParams);
                $postList['total'] = CarSaleBakInterface::getFullFieldsPostCount($searchParams);
            }
        } else { //在car_sale_bak表里查或查车源ID
             $postList = MbsCarSaleTmpInterface::getCarSaleList($searchParams);
        }

        //格式化车源列表
        $data['total'] = $postList['total'] ? $postList['total'] : 0;
        unset($postList['total']);
        $list = $postList['info'] ? $postList['info'] : $postList;
        $postList = SaleHelper::formatSaleList($list);
        
        //客户端本地草稿，按照时间混排到列表里面。对比的是列表的create_time desc。(目前就筛选草稿 和 默认进入列表页的时候要混排)
        $params['draft_insert_times'] = json_decode($params['draft_insert_times']);
        if ((empty($params['status']) || $params['status'] == CarStatusVars::DRAFT) && empty($params['keyword'])) {
            $data['list'] = saleHelper::sortLocalDraft($postList, $params['draft_insert_times'], $params['start_insert_time']);
        } else {
            //不混排就直接返回服务端获取的车源列表
            $data['list'] = $postList;
        }
        
        //如果是请求第一页的卖车列表的话，返回各个待办事项的数量给客户端
        $data['status_list'] = array();
        if (empty($params['start_insert_time'])) {
            $countParams['filters'] = array(
                array('car_status', '=', 1030),
                array('follow_user_id', '=', AppServAuth::$userInfo['user']['username']),
            );
            //草稿车源量
            $countParams['filters']['0'] = array('car_status', '=', 1010);
            $localDraftCn = !empty($params['draft_insert_times']) ? count($params['draft_insert_times']) : 0;
            $data['status_list'][SaleVars::$TODO_TYPE_DRAFT] = CarSaleInterface::getCountByFilters($countParams) + $localDraftCn;
            
            //质检审核未通过
            $countParams['filters']['0'] = array('car_status', '=', 2020);
            $data['status_list'][SaleVars::$TODO_TYPE_QUALITY] = CarSaleInterface::getCountByFilters($countParams);
            
            //明天即将自动终止车源量(车源发布30天且3天内没有刷新的车源，提前3天提醒他)
            //自动下架脚本的执行时间(凌晨2点执行)
            $autoStopTime = mktime(23,59,59,date("m"),date("d"),date("Y")) + (3600 * 2);
            $conInsertTime = $autoStopTime - (3600 * 24 * 28);
            $conUpdateTime = $autoStopTime - (3600 * 24 * 3);
            $countParams['filters'] = array(
                array('create_time', '<', $conInsertTime),
                array('update_time', '<', $conUpdateTime),
                array('follow_user_id', '=', AppServAuth::$userInfo['user']['username']),
                array('status', '=', 1),
            );
            $data['status_list'][SaleVars::$TODO_TYPE_AUTOSTOP] = CarSaleInterface::getCountByFilters($countParams);
            
            //委托车源的数量
            $countParams['filters'] = array(
                array('car_status', 'in', array(CarStatusVars::DRAFT, CarStatusVars::PUBLISH_MBS)),
                array('source_type', '=', 26),
                array('follow_user_id', '=', AppServAuth::$userInfo['user']['username']),
            );
            $data['status_list'][SaleVars::$TODO_TYPE_DEPUTE] = CarSaleInterface::getCountByFilters($countParams);
        }
        return $data;
    }
    
    /**
     * @brief 卖车待办事项type配置
     * @author 缪石乾
     * @date 2014-10-22
     * @param : 无需参数
     * @return 返回值说明如下：
     * 返回值名称     | 返回值类型 | 返回值补充描述
     * ---------- --|----------|------------------------------------------------------
     * value        | int      | 待办事项的type值,1:草稿、2:质检审核未通过、3:明日即将下架车源
     * text         | string   | 待办事项的名称
     */
    public function todoTypeList($params) {
        $result = array(
            array(
                'text'  => '草稿',
                'value' => SaleVars::$TODO_TYPE_DRAFT,
            ),
            array(
                'text'  => '质检审核未通过',
                'value' => SaleVars::$TODO_TYPE_QUALITY,
            ),
            array(
                'text'  => '明日即将下架车源',
                'value' => SaleVars::$TODO_TYPE_AUTOSTOP,
            ),
            array(
                'text'  => '委托车源',
                'value' => SaleVars::$TODO_TYPE_DEPUTE,
            ),
        );
        return $result;
    }
    
    
    /**
     * @brief 代办事项列表
     * @author 缪石乾
     * @date 2014-10-22
     * @param : 参数说明如下：
     * 参数名称     | 参数类型 | 参数补充描述
     * ----------  |----------|----------------------------
     * type        | int      | 待办事项的type值,1:草稿，2:质检审核未通过，3:明日即将下架车源
     * @return 返回值说明如下：
     * 返回值名称     | 返回值类型 | 返回值补充描述
     * ---------- --|----------|------------------------------------------------
     * list        | object      | 待办事项的车源列表
     */
    public function todoList($params) {
        $type = Util::getFromArray('type', $params, '');
        if (empty($type)) {
            throw new AppServException(AppServErrorVars::CUSTOM, '缺少待办事项类型！');
        }
        $startUpdateTime = Util::getFromArray('start_insert_time', $params, 0);
        $draftUpdateTime = Util::getFromArray('draft_insert_times', $params, 0);
        $limit           = Util::getFromArray('limit', $params, 0);
        
        if ($type == 1) {
            //草稿车源
            $params['status'] = CarStatusVars::DRAFT;
            return $this->mySaleList($params);
        } else if ($type == 2) {
            $params['status'] = CarStatusVars::QUALITY_CHECK_UNPASS;
            return $this->mySaleList($params);
        } else if ($type == 3) {
            //自动下架脚本的执行时间(凌晨2点执行)
            $autoStopTime = mktime(23,59,59,date("m"),date("d"),date("Y")) + (3600 * 2);
            $conInsertTime = $autoStopTime - (3600 * 24 * 28);
            $conUpdateTime = $autoStopTime - (3600 * 24 * 3);
            
            $searchParams = array(
                'isExt' => 1,
                'filters' => array(
                    array('follow_user_id', '=', AppServAuth::$userInfo['user']['username']),
                    array('create_time', '<', $conInsertTime),
                    array('update_time', '<', $conUpdateTime),
                    array('status', '=', 1),
                ),
                'limit'  => $limit,
                'offset' => 0,
                'order'  => array('update_time' => 'asc'),
            );
            if (!empty($startUpdateTime) && ($startUpdateTime <= $conUpdateTime)) {
                $searchParams['filters'][] = array('update_time', '<', $startUpdateTime);
            }
            
            $postList = MbsCarSaleInterface::getCarSaleList($searchParams);
            unset($postList['total']);
            $post = SaleHelper::formatSaleList($postList);
            
            $data['list'] = $post;
            return $data;
        } elseif ($type == 4) {
            $params['source_type'] = 26;
            $params['status'] = '';
            return $this->mySaleList($params);
        }
    }
    
    /**
     * @brief 获取卖车的回访列表，需要车源编号参数
     * @author 缪石乾
     * @param : 参数说明如下：
     * 参数名称   |  参数类型  | 参数补充说明
     * ----------|-----------|---------------
     * car_id   | int       | 调用者传上来的车源编号
     * @return 返回值说明如下：
     * 返回值名称     | 返回值类型   | 返回值补充描述
     * ---------- --|----------|-------------------------------------
     * who          | string   | 回访者的姓名
     * time         | string   | 回访时间。格式如：2014-10-1 18:09:54
     * detail       | string   | 回访详情。回访的详细内容说明
     * operation    | string   | 回访选项，即回访类型。例如：还要卖，不卖。 等等
     * @attention 如果车源编号car_id丢失,抛出异常：'车源编号丢失或不是数字！'
     */
    public function callbackList($params) {
        $carId = Util::getFromArray('car_id', $params, '');
        if (empty($carId) || !is_numeric($carId)) {
            throw new AppServException(AppServErrorVars::CUSTOM, '车源编号丢失或不是数字！');
        }
        
        $params = array(
            'filters' => array(array('car_id', '=', $carId)),
            //'limit'   => 10,
            //'offset'  => 0
        );
        include_once API_PATH . '/interface/mbs/MbsVisitInterface.class.php';
        $data = MbsVisitInterface::getList($params);
        unset($data['total']);
        $visitInfo = $userNameArr = array();
        if (!empty($data) && is_array($data)) {
            foreach ($data as $vInfo) {
                if (!isset($userNameArr[$vInfo['user_id']])) {
                    $name = MbsUserInterface::getRealNameByUser(array('username' => $vInfo['user_id']));
                    if (!empty($name)) {
                        $userNameArr[$vInfo['user_id']] = $name;
                    }
                }
                $visitInfo[] = array(
                    'operation' => isset(SaleVars::$SALE_CALL_TYPE[$vInfo['call_type']]) ? SaleVars::$SALE_CALL_TYPE[$vInfo['call_type']] : '其他',
                    'who'       => isset($userNameArr[$vInfo['user_id']]) ? $userNameArr[$vInfo['user_id']] : '',
                    'time'      => $vInfo['visit_time'] ? date('Y-m-d H:i:s',$vInfo['visit_time']) : '',
                    'detail'    => $vInfo['visit_content'],
                );
            }
            unset($userNameArr);
        }
        
        return $visitInfo;
    }
    
    /**
     * @brief 根据车源编号获取意向客户列表（意向来电 + 降价通知)，二者按照通话时间或者提交时间混排
     * @author 缪石乾
     * @param : 参数说明如下表:
     * 参数名称 | 参数类型 | 参数补充描述
     * --------|--------|-----------
     * id      | int    | 车源编号。
     * @date 2014-09-29
     * @return 返回数据说明如下表：
     * 值名称   | 值类型  | 值补充说明
     * --------|--------|-----------
     * type    | int    | 类型。1:意向来电、2:降价通知
     * phone   | char   | 电话或者固话号码。
     * attribution | char | 号码归属地。如果查不到就返回空字符串''
     * duration | int     | 通话时长。单位是分钟，只有意向来电才有，降价通知没有即为空字符串''
     * expect_price | char | 期望价格。返回例如：1.8万 这样的格式。
     * time     |  char   | 时间。意向来电的来电时间，或者降价通知的提交时间。格式：2014:10:13 09:30:53
     * @attention 目前是没有分页的，考虑到一辆车的来电数据量不大，并且需要意向来电的数据和降价通知的数据混排。
     * @attention 混排是依据意向来电的通话时间，和降价通知的提交时间混排。
     * @note 接口参考的是PC详情页v3/mbs_yg/mbs_post/app/PubPostDetailPage.class.php 里面的意向来电和降价通知。
     */
    public function potentialUserList($params) {
        $carId  = Util::getFromArray('car_id', $params, 0);
        if (empty($carId) || !is_numeric($params['car_id'])) {
            return array();
        }
        
        //获取意向来电的数据
        $params = array(
            'sale_id' => $carId,
        );
        include_once API_PATH . '/interface/PhoneCallLogInterface.class.php';
        $phoneCall = PhoneCallLogInterface::getPhoneInfoBySaleId($params);
        //获取降价通知的数据
        $deParams = array(
            'filters' => array(array('car_id', '=', $carId)),
            'order'   => array('update_time' => 'desc'),
        );
        include_once API_PATH . '/interface/car/CarDepreciateNoticeInterface.class.php';
        $dePrice = CarDepreciateNoticeInterface::getList($deParams);
        
        $result = array();
        if (!empty($phoneCall) && is_array($phoneCall)) {
            foreach ($phoneCall as $info) {
                $file = str_replace('d:\recwav', '', $info['file_path']);
                $file = str_replace('e:\recwav', '', $file);
                $file = str_replace('\\', '/', $file);
                $file = strpos($file, '.wav') ? "http://fzct.273.cn:90" . $file : '';
                $result[] = array(
                    'type'         => 1,
                    'phone'        => $info['caller'],
                    'attribution'  => !empty($info['caller']) ? SaleHelper::mobileToCity($info['caller']) : '',
                    'duration'     => $info['calledtotallen'],
                    'expect_price' => '',
                    'time'         => !empty($info['calling_time']) ? date('Y-m-d H:i:s', $info['calling_time']) : '',
                    'audio_URL'    => $file,
                );
            }
        }
        
        if (!empty($dePrice) && is_array($dePrice)) {
            foreach ($dePrice as $info) {
                $result[] = array(
                    'type'         => 2,
                    'phone'        => $info['mobile'],
                    'attribution'  => !empty($info['mobile']) ? SaleHelper::mobileToCity($info['mobile']) : '',
                    'duration'     => '',
                    'expect_price' => !empty($info['price']) ? (float) sprintf('%.2f', $info['price'] / 10000) . '万' : '',
                    'time'         => !empty($info['update_time']) ? date('Y-m-d H:i:s', $info['update_time']) : '',
                );
            }
        }
        
        if (!empty($result) && is_array($result)) {
            //排序
            $sortInfo = array();
            foreach ($result as $key => $value) {
                $sortInfo[$key] = $value['time'];
            }
            arsort($sortInfo);
            
            $sortResult = array();
            foreach ($sortInfo as $key => $value) {
                $sortResult[] = $result[$key];
            }
            $result = $sortResult;
            unset($sortResult);
        }
        return $result;
    }
    
    /**
     * @brief 获取车源标签
     * @author 缪石乾
     * @date 2014-11-12
     * @param : 参数说明
     * 参数名称      | 参数类型 | 参数补充描述
     * ---------- --|----------|------------------------------------------------------
     * car_id       | int      | 要获取标签的车源编号。必须要.
     * @return 返回值说明如下：
     * 返回值名称     | 返回值类型 | 返回值补充描述
     * ---------- --|----------|------------------------------------------------------
     * value        | int      | 待办事项的type值,1:草稿、2:质检审核未通过、3:明日即将下架车源
     * text         | string   | 待办事项的名称
     */
    public function getCarTabs($params) {
        if (empty($params['car_id']) || !is_numeric($params['car_id'])) {
            throw new AppServException(AppServErrorVars::CUSTOM, '车源编号丢失或不是数字！');
        }
        
        $postInfo = CarSaleInterface::getCarDetail(array('id' => $params['car_id']));
        
        //车源标签图片数组
        $result = array();
        if (!empty($postInfo['condition_id'])) { //有提交车况包检测报告
            $result['tags'][] = SaleHelper::$saleTags['5'];
        }
        if ($postInfo['hurry_sale'] == 1) { //是急售车源
            $result['tags'][] = SaleHelper::$saleTags['3'];
        }
        if ($postInfo['storage_sale'] == 1) { //是寄售车源
            $result['tags'][] = SaleHelper::$saleTags['4'];
        }
        if ($postInfo['is_cooperation'] == 1) { //是全国合作车源
            $result['tags'][] = SaleHelper::$saleTags['2'];
        }
        if ($postInfo['is_quality_car'] == 1) { //是优质车源
            $result['tags'][] = SaleHelper::$saleTags['0'];
        }
        if ($postInfo['is_repeat'] == 1) { //是合并车源
            $result['tags'][] = SaleHelper::$saleTags['1'];
        }
        
        return $result;
    }
    
    /**
     * @brief 车源操作接口，里面包括了车源的终止，刷新等基本操作
     * @author 缪石乾
     * @date 2014-10-11
     * @todo [刷新]目前维持3版本的写法，需要优化重构
     */
    public function execOperator($params) {
        $carId = Util::getFromArray('id', $params, 0);
        $type  = Util::getFromArray('type', $params, 0);
        if (empty($carId) || empty($type)) {
            throw new AppServException(AppServErrorVars::CUSTOM, '缺少操作必须的参数！');
        }
        
        include_once API_PATH . '/interface/mbs/SaleOperationInterface.class.php';
        $userInfo = AppServAuth::$userInfo['user'];
        $opCode = 0; //操作结果状态。1:失败，0:成功
        $opMsg  = ''; //操作结果信息说明
        switch ($type) {
            case 2: //卖车刷新操作,TODO:待优化
                $refreshParams = array(
                    'loginUserInfo' => $userInfo,
                    'carId'         => $carId,
                    'is_mobile'     => 1,
                );
                $ret = MbsCarSaleInterface::refreshCarRank($refreshParams);
                
                if (!empty($ret) && $ret['refreshStatus'] == 9) {
                    $refreshInfo = MbsRefreshStatInterface::getDetailByUsername(array('username' => $userInfo['username']));
                    if (!empty($refreshInfo)) {
                        $webNumber = !empty($refreshInfo['total_number']) ? $refreshInfo['total_number'] : 100;
                        $mobileNumber = !empty($refreshInfo['mobile_total_number']) ? $refreshInfo['mobile_total_number'] : 50;
                        $total = $webNumber + $mobileNumber;
                        $detail = !empty($refreshInfo) ? unserialize($refreshInfo['detail']) : array();
                        $thisCar = isset($detail[$carId]) ? $detail[$carId] : 2;
                    }
                    if ($detail[$carId] <= 0) {
                        $prompt = '<html><head><body><font color=\'#cc0000\' size=\'15px\'>您本条车源今日刷新次数已用完<br></font>'
                                .'<font color=\'#696969\' size=\'15px\'>刷新次数还剩：'.$total.'次</font></body></head></html>';
                    } else {
                        $prompt = '<html><head><body><font color=\'#000000\' size=\'15px\'>本车源剩余刷新次数：'
                                .$thisCar.'次<br>刷新次数还剩：'.$total.'次</font></body></head></html>';
                    }
                    
                    $opCode = 0;
                    $opMsg  = $prompt ? $prompt : '刷新成功！';
                } else {
                    //车源刷新操作接口的返回状态值
                    include_once  APP_SERV . '/app/sale/include/CarPostVars.class.php';
                    $errorMsg = isset(CarPostVars::$REFRESH_STATUS[$ret['refreshStatus']]) ? CarPostVars::$REFRESH_STATUS[$ret['refreshStatus']] : "刷新车源失败";
                    
                    $opCode = 1;
                    $opMsg  = $errorMsg ? $errorMsg : '刷新失败！';
                }
                break;
            case 4: //卖车删除草稿操作
                $ret = SaleOperationInterface::delDrafitById(array('car_id' => $carId, 'user_info' => $userInfo));
                if ($ret) {
                    $opCode = 0;
                    $opMsg  = '删除草稿成功!';
                } else {
                    $opCode = 1;
                    $opMsg  = '删除草稿失败!';
                }
                break;
            case 5: //卖车取消终止
                $interfaceParams = array(
                    'car_id'     => $carId,
                    'user_info'  => $userInfo,
                    'ext_params' => array(
                    ),
                );
                $result = SaleOperationInterface::sponsorCancelStopForMobile($interfaceParams);
                if ($result['errorCode']) {
                    $opCode = 1;
                    $opMsg  = !empty($result['msg']) ? $result['msg'] : '提交取消终止失败';
                } else {
                    $opCode = 0;
                    $opMsg  = '取消成功,已发送店长审核!';
                }
                break;
            case 9: //设为急售,复制PC的逻辑
                $carInfo = CarSaleInterface::getCarInfoById(array('id' => $carId));
                // 只开放福州地区
                if (!in_array($carInfo['deal_city_id'], CarAppVars::$CAR_TAG_SHOW['hurry'])) {
                    $opCode = 1;
                    $opMsg  = '不在允许城市范围内!';
                    break;
                }
                if ($carInfo['hurry_sale'] == 1) {
                    //已经设置过急售了
                    $opCode = 1;
                    $opMsg = '该车源已经设置过急售了！';
                    break;
                }
                // 不存在车源状态，或者车源状态不是草稿 待审 和审核通过，那么不允许操作
                if (!is_numeric($carInfo['status']) || ($carInfo['status'] > 1)) {
                    $opCode = 1;
                    $opMsg  = '车源已下架！';
                    break;
                }
                // 不是店长也不是店秘，或者不是本店的车源
                if (($userInfo['role_id'] != 26 && $userInfo['role_id'] != 27) || $userInfo['dept_id'] != $carInfo['store_id']) {
                    $opCode = 1;
                    $opMsg  = '没有权限!';
                    break;
                }
                
                $ret = MbsCarSaleInterface::setHurry(array('carid' => $carId, 'user' => $userInfo));
                
                $opCode = 0;
                $opMsg  = '设置成功!';
                break;
            case 10: //设为寄售,复制PC的逻辑
                $carInfo = CarSaleInterface::getCarInfoById(array('id' => $carId));
                // 当前车源不存在
                if (empty($carInfo)) {
                    $opCode = 1;
                    $opMsg  = '车源不存在!';
                    break;
                }
                if ($carInfo['storage_sale'] == 1) {
                    $opCode = 1;
                    $opMsg = '该车源已经设置过寄售了！';
                    break;
                }
                // 未开放地区
                if (!in_array($userInfo['city'], CarAppVars::$CAR_TAG_SHOW['storage'])) {
                    $opCode = 1;
                    $opMsg  = '不在允许城市范围内!';
                    break;
                }
                // 不存在车源状态，或者车源状态不是草稿 待审 和审核通过，那么不允许操作
                if (!is_numeric($carInfo['status']) || ($carInfo['status'] > 1)) {
                    $opCode = 1;
                    $opMsg  = '车源已下架！';
                    break;
                }
                // 不是本人车源的话
                if ($userInfo['username'] != $carInfo['follow_user_id']) {
                    // 不是店长也不是店秘，或者不是本店的车源
                    if (($userInfo['role_id'] != 26 && $userInfo['role_id'] != 27) || $userInfo['dept_id'] != $carInfo['store_id']) {
                        $opCode = 1;
                        $opMsg  = '没有权限!';
                        break;
                    }
                }
                
                // 当前车源不是本店车源
                if ($carInfo['store_id'] != $userInfo['dept_id']) {
                    $opCode = 1;
                    $opMsg  = '当前车源不是本门店车源，没有权限！';
                    break;
                }
                
                $params = array(
                    'info'   => array('storage_sale' => 1, 'index_update_time' => time()),
                    'filter' => array(array('id', '=', $carId)),
                );
                $ret = MbsCarSaleInterface::updateCarInfo($params);
                if ($ret !== false) {
                    $opCode = 0;
                    $opMsg  = '设置成功!';
                    break;
                } else {
                    $opCode = 1;
                    $opMsg  = '设置失败!';
                    break;
                }
                break;
            case 11: //设为优质车源
                $carInfo = CarSaleInterface::getCarInfoById(array('id' => $carId));
                if ($carInfo['is_quality_car'] == 1) {
                    $opCode = 1;
                    $opMsg  = '该车源已经设置过优质车源了!';
                    break;
                }
                $saleUpParams = array(
                    'info' => array(
                        'is_quality_car' => 1,
                        'index_update_time' => time(),
                    ),
                    'filter' => array(array('id', '=', $carId)),
                );
                $ret = MbsCarSaleInterface::updateCarInfo($saleUpParams);
                $opCode = 0;
                $opMsg  = '设置成功!';
                break;
            default:
                break;
        }
        
        return array(
            'result_code'    => $opCode,
            'result_message' => $opMsg,
        );
    }
    
    /**
     * @desc 拆分车源图片和质检图片
     * @date 2014-8-26
     * @param $images Array(Array()) 图片数组
     * @return array()
     */
    private function _parseImages($images = array()) {
        $carImages = $qualityImages = $coverImages = array();
        if (empty($images)) {
            return array($carImages, $qualityImages, $coverImages);
        }
        //由于ios、android传上来的key不是按照顺序的，所以排序下
        ksort($images);
        
        foreach ($images as $objType => $img) {
            if ($img['is_plate'] && empty($img['file_path'])) {
                //车源质检图片出现file_path为空的情况，就直接抛出异常
                throw new AppServException(AppServErrorVars::CUSTOM, '因为系统问题，你的质检图片没能上传成功！请重试！');
            } else if (empty($img['file_path'])) {
                //车源图片如果url空掉，跳过这张图片
                continue;
            }
            //车源封面图
            if ($img['cover'] == 1) {
                $coverImages = $img;
                continue;
            }
            //99、98、97没有实际意义，目前只是客户端表示图片类型的一个数值
            switch ($objType) {
                case 99 : //车牌号图片
                    $qualityImages['car_number_images'][] = $img;
                    break;
                case 98 : //行驶证图
                    $qualityImages['images_drive'][] = $img;
                    break;
                case 97 : //人车和合照图
                    $qualityImages['images_car_people'][] = $img;
                    break;
                default : //车源图片
                    $carImages[] = $img;
                    break;
            }
        }
        
        if (!empty($coverImages)) {
            $carImages = array_merge(array($coverImages), $carImages);
        }
        
        return array($carImages, $qualityImages, $coverImages);
    }
    
    /**
     * @desc 获取通讯录用户信息
     * @param $deptId 要获取通讯录的门店id
     * @since 2014-8-26
     * @return array()
    */
    private function _getContractUser($deptId) {
        if (empty($deptId) || !is_numeric($deptId)) {
            return array();
        }
        
        //获取当前店内所有员工通迅录信息
        $userInfoArr = MbsUserInterface::getUsersByDept(array('dept_id' => $deptId));
        if (empty($userInfoArr) || !is_array($userInfoArr)) {
            return array();
        }
        foreach ($userInfoArr as $value) {
            $roleIdArr[] = $value['role_id'];
        }
        //去重
        $roleIds  = array_unique($roleIdArr);
        include_once API_PATH . '/interface/MbsRoleInterface.class.php';
        $roleInfo = MbsRoleInterface::getRoleInfoByRoleIds(array('role_ids' => $roleIds));
        foreach ($roleInfo as $roleInfoValue) {
            $roleIdAndName[$roleInfoValue['role_id']] = $roleInfoValue['role_name'];
        }
        
        //格式化员工通迅录信息
        $uInfo = array();
        foreach ($userInfoArr as $k => $v) {
            $uInfo[$k] = array(
                'username'  => $v['username'],
                'real_name' => $v['real_name'],
                'role_id'   => $v['role_id'],
                'role_name' => $roleIdAndName[$v['role_id']],
                'mobile'    => empty($v['mobile']) ? '' : $v['mobile'],
                'telephone' => empty($v['telephone']) ? '' : $v['telephone'],
                'address'   => empty($v['address']) ? '' : $v['address'],
                'email'     => empty($v['email']) ? '' : $v['email'],
            );
        }
        
        return $uInfo;
    }
}
?>