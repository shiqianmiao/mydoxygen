<?php
/**
 * @brief      卖车App
 * @version    1.0
 * @author     王煜
 * @date       2014-11-3
 */
require_once API_PATH . '/interface/CarSaleAppInterface.class.php';
require_once API_PATH . '/interface/CarSaleInterface.class.php';
require_once APP_SERV . '/app/member/include/MemberHelper.class.php';
require_once dirname(__FILE__) . '/include/SaleAppConfig.class.php';
require_once dirname(__FILE__) . '/include/SaleAppHelper.class.php';
//加载门店API
require_once API_PATH . '/interface/MbsDeptInterface.class.php';
require_once API_PATH . '/interface/MbsUserInterface.class.php';

class SaleappServiceApp {

    /**
     * 发布卖车时默认插入car_sale表的信息
     */
    private $_defaultInsertCarSaleInfo = array(
        'status'        => -1,
        'order_status'  => 99,
        'car_status'    => 1010,
        'source_type'   => 25,
        'info_type'     => 1
    );

     /**
     * 发布卖车
     * @param  : 参数说明如下表格
     * 参数名称              | 参数类型  | 参数补充描述
     * ----------------------|-----------|------------------------------------------------
     * passport              | string    | 认证串，必填
     * car_photo             | array     | 车源照片，必填，不超过20张
     * car_vin               | string    | vin码（车架号），必填
     * brand_id              | int       | 品牌ID，必填
     * series_id             | int       | 车系ID，必填
     * model_id              | int       | 车型ID，必填
     * card_time             | int       | 首次上牌时间戳，必填
     * plate_city_id         | int       | 车牌属地ID，必填
     * deal_province_id      | int       | 交易省份ID，必填
     * deal_city_id          | int       | 交易城市ID，必填
     * kilometer             | int       | 行驶里程（单位：公里），必填
     * price                 | float     | 价格（单位：元），必填
     * price_potential       | float     | 评估价格（单位：元），必填
     * mobile                | string    | 联系方式，必填
     * follow_user           | array     | 顾问用户名，必填
     * car_number            | string    | 车牌号，选填
     * driving_licence_photo | array     | 行驶证照片，选填，不超过3张
     * car_color             | string    | 车源颜色，选填
     * use_type              | int       | 使用性质，选填，1非营运,2营运,3营转非,4租赁车,5特种车,6教练车
     * transfer_num          | int       | 过户次数，选填
     * safe_force_time       | int       | 强交险到期时间，选填
     * year_check_time       | int       | 年险到期时间，选填
     * maintain_address      | int       | 保养地点，选填，1在4S店维修保养,2在一般维修店保养
     * description           | string    | 车主描述，选填
     * @return int
     * 返回值名称   | 返回值类型   | 返回值补充描述
     * -------------|--------------|------------------------------------------------
     *  return     | int           | 发布成功后的自增id
     */
    public function publish($params) {
        $authData = self::_checkPassport($params['passport']);
        //写入卖车app车源表
        $params['member_id'] = $authData['id'];
        $carSaleAppId = CarSaleAppInterface::add($params);
        if (!$carSaleAppId) {
            throw new AppServException(AppServErrorVars::CUSTOM, '发布卖车失败');
        }

        include_once API_PATH . '/interface/CarAttachInterface.class.php';
        include_once API_PATH . '/interface/MbsCarProtectInfoInterface.class.php';
        include_once API_PATH . '/interface/MbsCheckCarPhotoInterface.class.php';

        //统一时间戳
        $time = time();

        //写入客户表
        $customerInfo = array(
            'real_name'    => '卖车app',
            'province'     => $params['deal_province_id'],
            'city'         => $params['deal_city_id'],
            'insert_time'  => $time,
        );
        $customerId = MbsCustomerInterface::customerSave(array('info' => $customerInfo,));

        if (is_array($params['follow_user']) && !empty($params['follow_user'])) {
            //公共信息
            $params = array_merge($params, $this->_defaultInsertCarSaleInfo);
            $extParams = array(
                'plate_province_id' => $params['plate_province_id'],
                'plate_city_id'     => $params['plate_city_id'],
                'car_number'        => $params['car_number'],
                'car_vin'           => $params['car_vin']
            );
            $params['create_time'] = $time;
            $params['update_time'] = $time;
            $params['mobile']      = $authData['mobile'];
            $params['title']       =  VehicleV2Interface::getModelCaption($params);
            $extParams['customer_id'] = $customerId;
            $proParams = array(
                'customer_id'    => $customerId,
                'mobile'         => $authData['mobile'],
                'car_number'     => $params['car_number'],
                //@TODO
                'car_number_pic' => ''
            );

            //分配车源给业务员，分配一个业务员大概9次数据写入
            foreach ($params['follow_user'] as $followUserId) {
                //写入car_sale表
                $params['follow_user_id'] = $followUserId;
                $carSaleId = CarSaleInterface::insertCarInfo(array('car_sale' => $params));

                //car_sale主表写入成功
                if ($carSaleId) {
                    //写入car_sale_ext表
                    $extParams['car_id'] = $carSaleId;
                    $carSaleExtId = CarSaleInterface::insertCarInfo(array('car_sale_ext' => $extParams));

                    //图片信息写入car_attach表
                    if ($params['car_photo'] && is_array($params['car_photo'])) {
                        foreach ($params['car_photo'] as $k => $img) {
                            $imageInfo = array(
                                'object_id'    => $carSaleId,
                                'object_type'  => 1,
                                'file_path'    => $img,
                                'insert_time'  => $time,
                                'status'       => 1,
                                'sort_order'   => $k + 1,
                                'is_cover'     => $k == 0 ? 1 : 0,
                                //@TODO
                                'insert_user_id' => 0,
                            );
                            $attatchId = CarAttachInterface::insertImageInfo(array('images' => $imageInfo));
                        }
                    }

                    //写入车牌图信息表
                    if ($params['driving_licence_photo'] && is_array($params['driving_licence_photo'])) {
                        foreach ($params['driving_licence_photo'] as $k => $img) {
                            $checkParams = array(
                                'car_id'     => $carSaleId,
                                'status'     => 0,
                                'photo'      => $img,
                                'photo_type' => 2,
                            );
                            $checkCarPhotoId = MbsCheckCarPhotoInterface::add($checkParams);
                        }
                    }

                    //写入加密信息表中
                    $proParams = array(
                        'car_id'         => $carSaleId,
                        'customer_id'    => $customerId,
                        'mobile'         => $authData['mobile'],
                        'car_number'     => $params['car_number'],
                        //@TODO
                        'car_number_pic' => '',
                    );
                    $carProtect = MbsCarProtectInfoInterface::add($proParams);
                } else {
                    continue;
                }

                //写入车源顾问表
                CarSaleAppInterface::setFollowUser(array(
                    'car_sale_app_id' => $carSaleAppId,
                    'car_sale_id'     => $carSaleId,
                    'follow_user_id'  => $followUserId
                ));
            }
        }

        if (!$carSaleAppId) {
            throw new AppServException(AppServErrorVars::CUSTOM, '发布卖车失败');
        }

        return $carSaleAppId;
    }

    /**
     * 修改车源信息
     * @param  : 参数说明如下表格
     * 参数名称              | 参数类型  | 参数补充描述
     * ----------------------|-----------|------------------------------------------------
     * passport              | string    | 认证串，必填
     * car_id                | int       | 车源ID，必填
     * car_photo             | array     | 车源照片，必填，不超过20张
     * car_vin               | string    | vin（车架号），必填
     * brand_id              | int       | 品牌ID，必填
     * series_id             | int       | 车系ID，必填
     * model_id              | int       | 车型ID，必填
     * card_time             | int       | 首次上牌时间戳，必填
     * plate_city_id         | int       | 车牌属地ID，必填
     * deal_province_id      | int       | 交易省份ID，必填
     * deal_city_id          | int       | 交易城市ID，必填
     * kilometer             | float     | 行驶里程（单位：万公里），必填
     * price                 | float     | 价格（单位：元），必填
     * price_potential       | float     | 评估价格（单位：元），必填
     * mobile                | string    | 联系方式，必填
     * car_number            | string    | 车牌号，选填
     * driving_licence_photo | array     | 行驶证照片，选填
     * car_color             | string    | 车源颜色，选填
     * use_type              | int       | 使用性质，选填，1非营运,2营运,3营转非,4租赁车,5特种车,6教练车
     * transfer_num          | int       | 过户次数，选填
     * safe_force_time       | int       | 强交险到期时间，选填
     * year_check_time       | int       | 年险到期时间，选填
     * maintain_address      | int       | 保养地点，选填，1在4S店维修保养,2在一般维修店保养
     * description           | string    | 车主描述，选填
     * @return bool
     * 返回值名称   | 返回值类型   | 返回值补充描述
     * -------------|--------------|------------------------------------------------
     *  return      | bool         | 是否修改成功
     */
    public function edit($params) {
        $authData = $this->_checkPassport($params['passport']);
        $id = (int) $params['car_id'];
        if (!$id) {
            throw new AppServException(AppServErrorVars::CUSTOM, '参数id不能为空');
        }
        unset($params['car_id']);
        $update = CarSaleAppInterface::updateCarInfoById(array(
            'info'       => $params,
            'id'         => $id,
            'member_id'  => $authData['id'],
        ));
        if (!$update) {
            throw new AppServException(AppServErrorVars::CUSTOM, '修改车源信息失败');
        }

        return $update ? true : fasle;
    }

    /**
     * 删除车源
     * @param  : 参数说明如下表格
     * 参数名称     | 参数类型  | 参数补充描述
     * ------------ |-----------|------------------------------------------------
     * passport     | string    | 认证串，必填
     * car_id       | int       | 车源ID，必填
     * reason       | int       | 理由，必填，1不卖了，2已出售
     * @return bool
     * 返回值名称       | 返回值类型  | 返回值补充描述
     * -----------------|-------------|------------------------------------------------
     *  return          | bool        | 是否删除成功
     */
    public function delete($params) {
        include_once API_PATH . '/interface/mbs/MbsCarSaleInterface.class.php';
        $authData = $this->_checkPassport($params['passport']);
        $id       = (int) $params['car_id'];
        if (!$id) {
            throw new AppServException(AppServErrorVars::CUSTOM, '参数id不能为空');
        }
        $r = CarSaleAppInterface::delete(array(
            'id' => $id,
            'member_id' => $authData['id']
         ));
        if (!$r) {
            throw new AppServException(AppServErrorVars::CUSTOM, '删除车源失败');
        }

        return $r ? true : fasle;
    }

    /**
     * 首页-获取我的卖车列表（在售车源列表）
     * @param  : 参数说明如下表格
     * 参数名称     | 参数类型  | 参数补充描述
     * ------------ |-----------|------------------------------------------------
     * passport     | string    | 认证串，必填
     * limit        | int       | 数量，选填
     * offset       | int       | 偏移量，选填
     * @return array
     * 返回值名称       | 返回值类型  | 返回值补充描述
     * -----------------|-------------|------------------------------------------------
     *  car_id          |int          | 车源ID
     *  title           |string       | 标题
     *  price           |string       | 价格
     *  car_photo       |string       | 图片，json串
     *  consult_num     |int          | 咨询量
     *  visit_num       |int          | 浏览量
     *  create_time     |int          | 创建时间
     *  update_time     |int          | 更新时间
     */
    public function getOnSaleList($params) {
        $authData = self::_checkPassport($params['passport']);
        $params['member_id'] = $authData['id'];
        return CarSaleAppInterface::getOnSaleCarByMemberId($params);
    }

    /**
     * 管理中心-获取成交记录列表
     * @param  : 参数说明如下表格
     * 参数名称     | 参数类型  | 参数补充描述
     * -------------|-----------|------------------------------------------------
     * passport     | string    | 认证串，必填
     * limit        | int       | 数量，选填
     * offset       | int       | 偏移量，选填
     * @return array
     * 返回值名称       | 返回值类型  | 返回值补充描述
     * -----------------|-------------|------------------------------------------------
     *  car_id          | int         | 车源ID
     *  title           | string      | 标题
     *  price           | string      | 价格
     *  car_photo       | string      | 图片，json串
     *  consult_num     | int         | 咨询量
     *  create_time     | int         | 创建时间
     *  update_time     | int         | 更新时间
     */
    public function getDoneList($params) {
        $authData = self::_checkPassport($params['passport']);
        $params['member_id'] = $authData['id'];
        return CarSaleAppInterface::getSoldCarByMemberId($params);
    }

    /**
     * 解析passport返回会员信息
     */
    private static function _checkPassport($passport) {
        $authData = MemberHelper::parsePassport($passport);
        if (empty($authData)) {
            throw new AppServException(AppServErrorVars::CUSTOM, '会员认证失败');
        }

        return $authData;
    }

    /**
     * 修改在售车源的价格
     * @param  : 参数说明如下表格
     * 参数名称     | 参数类型  | 参数补充描述
     * -------------|-----------|------------------------------------------------
     * passport     | string    | 认证串，必填
     * car_id       | int       | 车源ID
     * price        | string    | 价格
     * @return bool
     * 返回值名称         | 返回值类型  | 返回值补充描述
     * -----------------|-----------|------------------------------------------------
     *  return          | bool      | 是否修改成功
     */
    public function changePrice($params) {
        $authData = self::_checkPassport($params['passport']);
        $params['member_id'] = $authData['id'];
        return CarSaleAppInterface::changePrice($params) ? true : false;
    }

    ////////////////////////////////////////////////////////////////////////////

     /**
     * 通过车源ID获取车源详情
     * @param  : 参数说明如下表格
     * 参数名称              | 参数类型  | 参数补充描述
     * ----------------------|-----------|------------------------------------------------
     * passport              | string    | 认证串
     * car_id                | int       | 车源ID
     * @return array
     * 返回值名称   | 返回值类型   | 返回值补充描述
     * -------------|--------------|------------------------------------------------
     * title                 | string    | 标题
     * car_photo             | array     | 车源照片
     * car_vin               | string    | vin码（车架号）
     * brand_id              | int       | 品牌ID
     * brand_name            | string    | 品牌
     * series_id             | int       | 车系ID
     * series_name           | string    | 车系
     * model_id              | int       | 车型ID
     * model_name            | string    | 车型
     * card_time             | string    | 首次上牌时间戳
     * plate_city_id         | int       | 车牌属地ID
     * plate_city            | int       | 车牌属地
     * deal_province_id      | int       | 交易省份ID
     * deal_province         | int       | 交易省份
     * deal_city_id          | int       | 交易城市ID
     * deal_city             | int       | 交易城市
     * kilometer             | int       | 行驶里程（单位：公里）
     * price                 | float     | 价格（单位：元）
     * price_potential       | float     | 评估价格（单位：元）
     * mobile                | string    | 联系方式
     * car_number            | string    | 车牌号
     * driving_licence_photo | array     | 行驶证照片
     * car_color             | int       | 车源颜色id
     * car_color_name        | string    | 车源颜色
     * use_type              | int       | 使用性质,1非营运,2营运,3营转非,4租赁车,5特种车,6教练车
     * transfer_num          | int       | 过户次数
     * safe_force_time       | int       | 强交险到期时间
     * year_check_time       | int       | 年险到期时间
     * maintain_address      | int       | 保养地点,1在4S店维修保养,2在一般维修店保养
     * description           | string    | 车主描述
     * follow_user_num       | int       | 委托的顾问数量
     * consult_num           | int       | 咨询量
     * visit_num             | int       | 浏览量
     */
    public function getCarInfoByCarId($params) {

        $authData = self::_checkPassport($params['passport']);
        $carIdArr = array();
        $carIdArr['id'] = $params['car_id'];
        $carInfo = CarSaleAppInterface::getCarInfoById($carIdArr);

        //@todo 数据格式化
        $carInfo = SaleAppHelper::_formatCarData($carInfo);


        return $carInfo;
    }

    /**
     * 发布卖车-系统推荐顾问
     * @param  : 参数说明如下表格
     * 参数名称     | 参数类型  | 参数补充描述
     * ------------ |-----------|------------------------------------------------
     * passport              | string    | 认证串，必填
     * brand_id              | int       | 品牌ID，必填
     * series_id             | int       | 车系ID，必填
     * model_id              | int       | 车型ID，必填
     * card_time             | int       | 首次上牌时间戳，必填
     * plate_city_id         | int       | 车牌属地ID，必填
     * deal_province_id      | int       | 交易省份ID，必填
     * deal_city_id          | int       | 交易城市ID，必填
     * kilometer             | int       | 行驶里程（单位：公里），必填
     * price                 | float     | 价格（单位：元），必填
     * car_color             | string    | 车源颜色，选填
     * use_type              | int       | 使用性质，选填，1非营运,2营运,3营转非,4租赁车,5特种车,6教练车
     * @return array
     * 返回值名称       | 返回值类型  | 返回值补充描述
     * -----------------|-------------|------------------------------------------------
     *  username        | string       | 用户名
     *  real_name       | string       | 顾问姓名
     *  photo           | string       | 顾问头像
     *  dept_name       | string       | 门店名称
     *  recent_num      | int          | 近期售出车源数量
     *  good_num        | int          | 好评数量
     *  brief           | string       | 顾问简单说明
     */
    public function getRecommendedFollowUser($params) {
        $authData = self::_checkPassport($params['passport']);
        $cityId = $params['deal_city_id'];
        //获取推荐顾问，一个城市取出分数最高的前100个
        $redisKey  = sprintf("sale_app_follow_user_score_%d", $cityId);
        $redis = MemberHelper::_createRedis();
        $allFollowUser = $redis->zRevRange($redisKey, 0, 99);

        //剔除黑名单里的顾问
        $blackParams = array('member_id' => $authData['id']);
        $blackFollowId = $this->getAllBlack($blackParams);
        $allFollowUser = array_diff($allFollowUser, $blackFollowId);

        if (empty($allFollowUser)) {
            return false;
        }
        //打乱数组，并取20个
        shuffle($allFollowUser);
        $followIds = array_rand($allFollowUser,20);

        $users = CarSaleAppInterface::getFollowUsersById($followIds);

        //重组顾问信息
        $followUsers = array();
        foreach ($users as $user) {
            $followUsers[] = $this->_getOneUserAllInfo($user);
        }
        return $followUsers;
    }

    /**
     * 发布卖车-卖车速度最快的顾问
     * @param  : 参数说明如下表格
     * 参数名称     | 参数类型  | 参数补充描述
     * ------------ |-----------|------------------------------------------------
     * car_info     | array     | 车源必填信息
     * @return array
     * 返回值名称       | 返回值类型  | 返回值补充描述
     * -----------------|-------------|------------------------------------------------
     *  username        | string       | 用户名
     *  real_name       | string       | 顾问姓名
     *  photo           | string       | 顾问头像
     *  dept_name       | string       | 门店名称
     *  recent_num      | int          | 近期售出车源数量
     *  good_num        | int          | 好评数量
     *  brief           | string       | 顾问简单说明
     */
    public function getEfficientFollowUser($params) {
        $authData = self::_checkPassport($params['passport']);
        $blackParams = array('member_id' => $authData['id']);
        $blackFollowId = $this->getAllBlack($blackParams);
        $listParams = array('black' => $blackFollowId);
        $users = CarSaleAppInterface::getUsersOrderByTradenum($listParams);
        $total = $users['total'];
        unset($users['total']);
        //重组顾问信息
        $followUsers = array();
        foreach ($users as $user) {
            $followUsers[] = $this->_getOneUserAllInfo($user);
        }
        return $followUsers;
    }

    /**
     * 发布卖车-委托过的顾问
     * @param  : 参数说明如下表格
     * 参数名称     | 参数类型  | 参数补充描述
     * ------------ |-----------|------------------------------------------------
     * passport     | string    | 认证串
     * @return array
     * 返回值名称       | 返回值类型  | 返回值补充描述
     * -----------------|--------------|------------------------------------------------
     *  username        | string       | 用户名
     *  real_name       | string       | 顾问姓名
     *  photo           | string       | 顾问头像
     *  dept_name       | string       | 门店名称
     *  recent_num      | int          | 近期售出车源数量
     *  good_num        | int          | 好评数量
     *  brief           | string       | 顾问简单说明
     */
    public function getHistoryFollowUser($params) {
        $authData = self::_checkPassport($params['passport']);
        $member['member_id'] = $authData['id'];
        $followIdArr = CarSaleAppInterface::getFollowUserIdByMemberId($member);

        //重组顾问id数组
        $followId = array();
        foreach ($followIdArr as $single) {
            $followId[] = $single['follow_user_id'];
        }

        //剔除黑名单里的顾问
        $blackParams = array('member_id' => $authData['id']);
        $blackFollowId = $this->getAllBlack($blackParams);
        $followId = array_diff($followId, $blackFollowId);

        $users = CarSaleAppInterface::getFollowUsersById($followId);

        //重组顾问信息
        $followUsers = array();
        if (!empty($users)) {
            foreach ($users as $user) {
                $followUsers[] = $this->_getOneUserAllInfo($user);
            }
        }
        return $followUsers;
    }

    /**
     * 搜索顾问
     * @param  : 参数说明如下表格
     * 参数名称     | 参数类型  | 参数补充描述
     * ------------ |-----------|------------------------------------------------
     * keyword      | string    | 关键字
     * @return array
     * 返回值名称       | 返回值类型  | 返回值补充描述
     * -----------------|-------------|------------------------------------------------
     *  username        | string       | 用户名
     *  real_name       | string       | 顾问姓名
     *  photo           | string       | 顾问头像
     *  dept_name       | string       | 门店名称
     *  recent_num      | int          | 近期售出车源数量
     *  good_num        | int          | 好评数量
     *  brief           | string       | 顾问简单说明
     */
    public function searchFollowUser($params) {
        $authData = self::_checkPassport($params['passport']);
        if (empty($params['keyword'])) {
           throw new AppServException(AppServErrorVars::CUSTOM, '缺少参数');
        }
        //获取门店id
        $deptIds = MbsDeptInterface::getDeptIdsBykeyword(array('keyword' => $params['keyword']));
        //重组id数组
        $deptIdArr = array();
        foreach ($deptIds as $deptId) {
            $deptIdArr[] = $deptId['id'];
        }

        //黑名单
        $blackParams = array('member_id' => $authData['id']);
        $blackFollowId = $this->getAllBlack($blackParams);
        $searchParams = array(
            'keyword' => $params['keyword'],
            'dept_id' => $deptIdArr,
            'black'   => $blackFollowId,
        );
        $users = CarSaleAppInterface::getUsersBykeyword($searchParams);

        unset($users['total']);

        //重组顾问信息
        $followUsers = array();
        foreach ($users as $user) {
            $followUsers[] = $this->_getOneUserAllInfo($user);
        }
        return $followUsers;
    }

    /**
     * 查看某个车源委托的顾问
     * @param  : 参数说明如下表格
     * 参数名称     | 参数类型  | 参数补充描述
     * ------------ |-----------|------------------------------------------------
     * passport     | string    | 认证串，必填
     * car_id       | int       | 车源ID，必填
     * @return array
     * 返回值名称       | 返回值类型  | 返回值补充描述
     * -----------------|-------------|------------------------------------------------
     *  username        | string       | 用户名
     *  real_name       | string       | 顾问姓名
     *  photo           | string       | 顾问头像
     *  dept_name       | string       | 门店名称
     *  recent_num      | int          | 近期售出车源数量
     *  good_num        | int          | 好评数量
     *  brief           | string       | 顾问简单说明
     */
    public function getFollowUserByCarId($params) {
        $authData = self::_checkPassport($params['passport']);

        //缓存key
        $followUsers = array();
        $redisKey = SaleAppConfig::$REDIS_CONFIG['car_follow_user']['key'] . $params['car_id'];
        $followUsers = MemberHelper::_getRedis($redisKey);

        //缓存中无数据时执行
        if (empty($followUsers)) {
            $carId = array();
            $carId['id'] = $params['car_id'];
            $followId = CarSaleAppInterface::getFollowUserIdByCarId($carId);

            $users = CarSaleAppInterface::getFollowUsersById($followId);

            //格式化顾问信息
            if (!empty($users)) {
                foreach ($users as $user) {
                    $followUsers[] = $this->_getOneUserAllInfo($user);
                }
            }

            //写入redis
            MemberHelper::_setRedis($redisKey, SaleAppConfig::$REDIS_CONFIG['car_follow_user']['life'], $followUsers);
        }
        return $followUsers;
    }

    /**
     * 拉黑顾问
     * @param  : 参数说明如下表格
     * 参数名称     | 参数类型  | 参数补充描述
     * ------------ |-----------|------------------------------------------------
     * passport     | string    | 认证串，必填
     * follow_user  | int       | 顾问用户名，必填
     * @return bool
     * 返回值名称       | 返回值类型  | 返回值补充描述
     * -----------------|-------------|------------------------------------------------
     *  return          | bool        | 成功则返回true
     */
    public function setBlack($params) {

        $authData = self::_checkPassport($params['passport']);
        //通过用户名获取顾问id
        if (empty($params['follow_user'])) {
           throw new AppServException(AppServErrorVars::CUSTOM, '缺少参数');
        }

        $deleteParams = array('member_id' => $authData['id'],'follow_user_id' => $params['follow_user']);
        //搜索被顾问关联的车源id
        $carIds = array();
        $carIdArr = CarSaleAppInterface::getCarIdByLink($deleteParams);
        foreach ($carIdArr as $carId) {
            $carIds[] = $carId['car_sale_id'];
        }
        if (!empty($carIds)) {
            include_once API_PATH . '/interface/mbs/MbsCarSaleInterface.class.php';
            $data = array(
                'info'   => array(
                    'status'            => 3,
                    'stop_time'         => time(),
                    'index_update_time' => time(),
                ),
                'filter' => array(
                    array('id', 'in', $carIds)
                )
            );
            $deleteCarSale = MbsCarSaleInterface::updateCarInfo($data);
        }
        //删除车源——用户——顾问关联记录
        $carIdArr = CarSaleAppInterface::deleteFollowUser($deleteParams);

        //设置黑名单
        return CarSaleAppInterface::setBlack(array(
                                'member_id'          => $deleteParams['member_id'],
                                'follow_user_id'     => $deleteParams['follow_user_id'],
                            ));
    }

    /**
     * 开放城市配置
     * @param  : 无
     * @return array
     * 返回值名称       | 返回值类型  | 返回值补充描述
     * -----------------|-------------|------------------------------------------------
     *  id              | int        | 城市ID
     *  name            | varchar    | 城市名
     */
    public function cityConfig($params = array()) {
        include CONF_PATH . '/../common/sale_app/CarSaleAppConfig.class.php';
        return CarSaleAppConfig::$CITY_CONFIG;
    }

    /**
     * 重组顾问信息
     * @params array $user 成员基本信息数组
     * @return array 返回完整的信息数组
     **/
    protected function _getOneUserAllInfo($user) {
        //顾问id
        //$info['user_id'] = $user['id'];

        //username
        $info['username'] = $user['username'];

        //顾问真名
        $info['real_name'] = $user['real_name'];

        //格式化照片URL
        //$info['photo'] = Util::formatImageUrl( $user['photo'],  SaleAppConfig::$ADVISER_PHOTO_CONFIG);
        $info['photo'] = $user['photo'];

        //门店名称
        $MbsDept = MbsDeptInterface::getDeptInfoByDeptId(array('id' => $user['dept_id']));
        $info['dept_name'] = $MbsDept['dept_name'];

        //近期售出数量
        $info['recent_num'] = $user['trade_num'];

        //好评数量
        $commentInfo = MemberHelper::_getRedis('user_car_comment:' . $user['username']);
        $info['good_num'] = !empty($commentInfo['good_comment']) ? $commentInfo['good_comment'] : 0;

        //顾问简单说明
        $info['brief'] = $info['brief'];

        return $info;
    }

    /**
     * 获取会员设置的顾问黑名单
     *
     */
    private static function getAllBlack($params) {
        $defriend = CarSaleAppInterface::getBlack($params);
        $idArr = array();
        if (!empty($defriend)) {
            foreach ($defriend as $single) {
                $idArr[] = $single['follow_user_id'];
            }
        }
        return $idArr;
    }

}

