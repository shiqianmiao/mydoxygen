<?php
/**
 * @package              v3
 * @subpackage           
 * @author               $Author:   weihongfeng$
 * @file                 $HeadURL$
 * @version              $Rev$
 * @lastChangeBy         $LastChangedBy$
 * @lastmodified         $LastChangedDate$
 * @copyright            Copyright (c) 2012, www.273.cn
 */
require_once API_PATH . '/interface/CarSaleInterface.class.php';
require_once API_PATH . '/interface/VehicleAdaptInterface.class.php';
require_once API_PATH . '/interface/VehicleV2Interface.class.php';
require_once API_PATH . '/interface/CarTypeMakeInterface.class.php';
require_once API_PATH . '/interface/CarTypeFamilyInterface.class.php';
require_once API_PATH . '/interface/CarAttachInterface.class.php';
require_once API_PATH . '/interface/MbsUserInterface.class.php';
require_once API_PATH . '/interface/MbsDeptInterface.class.php';
require_once API_PATH . '/interface/LocationInterface.class.php';
require_once APP_SERV . '/app/search/include/SearchVars.class.php';
require_once API_PATH . '/interface/ApnsInterface.class.php';
require_once FRAMEWORK_PATH . '/util/common/Util.class.php';
require_once API_PATH . '/interface/MbsCheckCarPhotoInterface.class.php';
require_once API_PATH . '/interface/ExtPhoneInterface.class.php';
require_once dirname(__FILE__) . '/include/SearchHelper.class.php';

class SearchServiceApp {
    
    //是否关闭电话转接 1为关闭电话转接，0为开启电话转接
    private static $_closeExtPhone = 0;
   
    /**
     * 取卖车列表
     * @param  : 参数说明如下表格
     * 参数名称     | 参数类型 | 参数补充描述
     * ------------|----------|------------------------------------------------
     * offset      | int     | 偏移量
     * limit       | int     | 取出的数量
     * sort        | string  | 排序格式field-desc field-asc , field为下面的可排序字段任选一
     * kw          | string  | 关键词
     * id          | int     | 车源id
     * create_time | int     | 车源发布时间 如果为区间查询 值为 最小值-最大值，例如 create_time 1000-2000 下面可区间查询的相同 可排序
     * update_time | int     | 车源更新时间  可区间查询 可排序
     * type_id     | int     | 车类型id
     * brand_id    | int     | 品牌id
     * maker_id    | int     | 制造商id
     * series_id   | int     | 车系id
     * model_id    | int     | 车型id
     * deal_province_id  | int | 交易省份id
     * deal_city_id | int | 交易城市id
     * car_color | int | 车颜色
     * price     | int  | 车的预售价格 可区间查询 可排序
     * kilometer | int  | 表显里程 可区间查询 可排序
     * card_time | int  | 上牌时间 可区间查询 可排序
     * transfer_num | int | 过户次数
     * maintain_address | int | 保养地点(1在4S店维修保养,2在一般维修店保养)
     * store_id |  int  | 业务员部门id
     * follow_user_id | int |跟单业务员id
     * car_body_type | int | 车身结构(1两厢,2三厢,3掀背,4硬顶敞篷,5软顶敞篷)
     * status | int | 审核状态(0未审核，1已审核)
     * ckb_check | int | 是否进行车况宝检测(0不是，1是)
     * displacement | int | 排量
     * gearbox_type| int | 变速箱类型
     * cover_photo| int |是否有图（0无图，1有图）
     * @return array
     * info
     * 返回值名称   | 返回值类型   | 返回值补充描述
     * ------------|----------|------------------------------------------------
     * id          | int      | 车源id
     * create_time | int      | 车源发布时间
     * update_time | int      | 车源更新时间
     * cover_photo | string   | 封面图的url
     * title       | string   | 车源标题
     * description | string   | 车源描述
     * kilometer   | int      | 表显里程
     * card_time   | int      | 上牌时间
     * ckb_check   | int      | 是否车况宝检测
     * price       | int      | 车的预售价格
     * ad_note     | int      | 一句话广告语
     * displacement | int     | 排量
     * gearbox_type | int     | 变速箱类型
     * is_approve   | int     | 是否认证车源0：未认证1：认证
     * 
     * total  查询到记录的总数
     */
    public function getSaleList($params) {
        $searchParams = $this->_getParams($params);
        $keywordBrandId = 0;
        $keywordSeriesId = 0;
        $keywordTypeId = 0;
        $importId = 0;
        $keywordBrandName = '';
        $keywordSeriesName = '';
        $keywordTypeName = '';
        if ($params['kw']) {
            do {
                $kw = preg_replace('/\s+/', " ", $params['kw']);
                $kwOrigin = $kw;
                $kwSeparate = explode(' ', $kwOrigin, 2);
                $kw = preg_replace("/ /", "", $kw);
                if (preg_match('/^\d{7,8}$/', $kw)) {
                    $searchParams['id'] = $kw;
                    break;
                }
                if(count($kwSeparate) == 2) {
                    $brandName = $kwSeparate[0];
                    $seriesName = $kwSeparate[1];
                }
                $searchParam = array (
                    'type'  => 'all',
                    'limit' => 3,
                    'fuzzy' => false
                );
                if (isset($params['city_id']) && $params['city_id']) {
                    $searchParam['area_id'] = 'C' . $params['city_id'];
                }
                $searchParam['keyword'] = $kw;
                $keywordBrandInfo = VehicleV2Interface::getVehicleFromWord($searchParam);
                if (count($keywordBrandInfo) == 2 && $keywordBrandInfo[0]['brand_id'] == $keywordBrandInfo[1]['brand_id'] || count($keywordBrandInfo) == 1) {
                    $keywordBrandId = $keywordBrandInfo[0]['brand_id'];
                    $keywordBrandName = $keywordBrandInfo[0]['brand_name'];
                    $keywordSeriesId = $keywordBrandInfo[0]['series_id'] ? $keywordBrandInfo[0]['series_id'] : 0;
                    $keywordSeriesName = $keywordBrandInfo[0]['series_name'] ? $keywordBrandInfo[0]['series_name'] : 0;
                    $importId = $keywordBrandInfo[0]['import_id'] ? $keywordBrandInfo[0]['import_id'] : 0;
                }
                if(!$keywordBrandInfo && !$keywordBrandId && isset($seriesName)) {
                    $searchParam['keyword'] = $seriesName;
                    $searchParam['type'] = 'series';
                    $searchParam['limit'] = 10;
                    $keywordSeriesInfo  = VehicleV2Interface::getVehicleFromWord($searchParam);
                    if ($keywordSeriesInfo) {
                        foreach ($keywordSeriesInfo as $item) {
                            if ($item['brand_name'] == $brandName) {
                                $keywordBrandId = $item['brand_id'];
                                $keywordBrandName = $item['brand_name'];
                                $keywordSeriesId = $item['series_id'] ? $item['series_id'] : 0;
                                $keywordSeriesName = $item['series_name'] ? $item['series_name'] : 0;
                                $importId = $item['import_id'] ? $item['import_id'] : 0;
                                break;
                            }
                        }
                    }
                }
                if (!$keywordBrandId && isset($brandName)) {
                    $searchParam['keyword'] = $brandName;
                    $searchParam['type'] = 'brand';
                    $keywordBrandInfo  = VehicleV2Interface::getVehicleFromWord($searchParam);
                    if ($keywordBrandInfo) {
                        $keywordBrandId = $keywordBrandInfo[0]['brand_id'];
                        $keywordBrandName = $keywordBrandInfo[0]['brand_name'];
                        $keywordSeriesId = $keywordBrandInfo[0]['series_id'] ? $keywordBrandInfo[0]['series_id'] : 0;
                        $keywordSeriesName = $keywordBrandInfo[0]['series_name'] ? $keywordBrandInfo[0]['series_name'] : 0;
                        $importId = $keywordBrandInfo[0]['import_id'] ? $keywordBrandInfo[0]['import_id'] : 0;
                    }
                }
                if ($keywordBrandId) {
                    $searchParams['brand_id'] = $keywordBrandId;
                    if ($keywordSeriesId) {
                        $searchParams['series_id'] = $keywordSeriesId;
                    }
                    unset($searchParams['kw']);
                }
            } while (false);
        }
        //判断是否为连锁城市，如果是车源为本地车源，否则为爬虫贴
        $isChainCity = LocationInterface::isChainCity(array('id' => $params['deal_city_id']));
        if($params['deal_city_id'] > 0 && (empty($isChainCity) || empty($params['deal_city_id']))) {
            $searchParams['order_source'] = array(10, 40);
        } else {
            $searchParams['order_source'] = 10;
        }
        if (empty($searchParams['deal_city_id'])) {
            unset($searchParams['deal_city_id']);
        }
        // order_status > 100
        $searchParams['order_status'] = array(100, 100000);
        $searchParams['cover_photo'] = 1;
        if (isset($searchParams['id']) && $searchParams['id'] > 0) {
            $ret = CarSaleInterface::getCarDetail(array(
                    'id'   => $searchParams['id'],
                ));
            if ($ret['order_status'] < 100 || $ret['status'] != 1) {
                $ret = array();
            }
            $ret = array(
                'info'   => array($ret),
                'total'  => $ret ? 1 : 0,
            );
        } else {
            $ret = CarSaleInterface::search($searchParams);
        }
        //根据follow_user_id获取对应的follow_user_id
        foreach ($ret['info'] as $key => $value) {
            $ret['info'][$key] = $this->_fomatInfo(SearchHelper::formatSaleListItem($value));
            
            $ret['info'][$key]['driving_status'] = in_array($ret['info'][$key]['mark_type'], array(1, 3)) ? 1 : 0;
            $ret['info'][$key]['advisor_status'] = in_array($ret['info'][$key]['mark_type'], array(2, 3)) ? 1 : 0;
            $user = MbsUserInterface::getInfoByUser(array('username' => $value['follow_user_id']));
            $ret['info'][$key]['follow_user_name'] = $user['real_name'] ? $user['real_name'] : '';
            unset($ret['info'][$key]['follow_user_id']);
            //处理cover_photo
            if (strpos($value['cover_photo'], 'http://') === 0) {
                $path = str_replace('http://img.273.com.cn/', '', $value['cover_photo']);
                $ret['info'][$key]['cover_photo'] = str_replace('_120-90_6_0_0', '', $path);
            }
        }
        $ret['ext']['brand_id'] = $keywordBrandId ? $keywordBrandId : 0;
        $ret['ext']['series_id'] = $keywordSeriesId ? $keywordSeriesId : 0;
        $ret['ext']['brand_name'] = $keywordBrandName ? $keywordBrandName : '';
        $ret['ext']['series_name'] = $keywordSeriesName ? $keywordSeriesName : '';
        if ($ret['ext']['series_name'] && $importId == 1) {
            $ret['ext']['series_name'] .= '（进口）'; 
        }
        $ret['fix_num'] = '400-0018-273';
        $ret['total'] = $ret['total'] ? $ret['total'] : 0;
        //根据客户端开发人员反馈，判断数据是否为空是基于$ret['info']，而并非基于$ret['total']，所以此处根据total为0时，设置info为空
        if (!$ret['total']) {
            $ret['info'] = array();
        }
        return $ret;
    }
    
    public function filterZjIdsByIds($params) {
        $info = ApnsInterface::getCarInfoByids($params['ids']);
        foreach ($info as $key => $value) {
            if($value['order_status'] == 200) {
                $ret[] = $value['id'];
            }
        }
        $rs = implode(',', $ret);
        return $rs;
    
    }
    
    /**
     * 取车源详情
     * @param   : 参数说明如下表格
     * 参数名称     | 参数类型 | 参数补充描述
     * ------------|----------|------------------------------------------------
     * id           | int     | 车源ID
     * @return array
     * 返回值名称   | 返回值类型   | 返回值补充描述
     * ------------|----------|------------------------------------------------
     * id          | int      | 车源id
     * create_time | int      | 车源发布时间
     * update_time | int      | 车源更新时间
     * cover_photo | string   | 封面图的url
     * title       | string   | 车源标题
     * description | string   | 车源描述
     * kilometer   | int      | 表显里程
     * card_time   | int      | 上牌时间
     * ckb_check   | int      | 是否车况宝检测
     * price       | int      | 车的预售价格
     * ad_note     | int      | 一句话广告语
     * displacement | int     | 排量
     * gearbox_type | int     | 变速箱类型
     * is_approve   | int     | 是否认证车源0：未认证1：认证
     * car_body_type | int    | 车身结构 1两厢,2三厢,3掀背,4硬顶敞篷,5软顶敞篷
     * year_check_time | int | 年检到期时间
     * safe_force_time | int  |强险到期时间
     * maintain_address | int |保养地点:0未知保养地点,1在4S店维修保养,2在一般维修店保养
     * ext_phone    | string     | 6位转接号
     * brand_name| string | 品牌名称
     * series_name | string  | 车系名称
     * model_name  | string   |车身参数，即车型号
     * plate_province_id | int | 上牌省份id
     * plate_city_id     | int |上牌城市id
     * store_id          | int    | 门店id
     * dept_name | string | 门店名称
     * follow_user_id | string | 业务员ID
     * follow_user_name | string | 业务员名称
     * images | object   | file_path:图片url，没有http://头请组装上http://img.273.com.cn/,有就不用对url做处理 is_cover:是否是封面图 0不是，1是
     * price_suggest_car | object |   价位推荐，search.getSaleList得到的结果info结构一致
     * type_suggest_car  | object |  同车型推荐，search.getSaleList得到的结果info结构一致
     * page_count | int   |浏览数,如果结果是0,表示服务端出现异常，建议随即展示
     * dept_addr | string | 门店地址
     * shop_point | string | 门店坐标
     * dept_telephone | string | 门店电话
     * car_color | int | 车身颜色(之前有给配置表to林光亮)
     * gearbox_name | string | 变速箱类型名称
     * plate_city_name | string | 上牌城市名称
     * @throws AppServException
     */
    public function getSaleDetail($params) {
        if (!$params['id']) {
            throw new AppServException(AppServErrorVars::CUSTOM, '车源id参数错误');
        }
        $info = CarSaleInterface::getCarDetail($params);
        if (empty($info)) {
            return array();
        }
        if (LocationInterface::isChainCity(array(
            'id'    => $info['deal_city_id'],
        )) && $info['follow_user_id'] == 0 && $info['source_type'] != 20) {
            return array();
        }
        $info['driving_status'] = in_array($info['mark_type'], array(1, 3)) ? 1 : 0;
        $info['advisor_status'] = in_array($info['mark_type'], array(2, 3)) ? 1 : 0;
        $brand = VehicleV2Interface::getBrandById(array('brand_id' => $info['brand_id']));
        $info['brand_name'] = $brand['name'] ? $brand['name'] : '';
        $series = VehicleV2Interface::getSeriesById(array('series_id' => $info['series_id']));
        $info['series_name'] = $series['name'] ? $series['name'] : '';
        $model = VehicleV2Interface::getModelById(array('model_id' => $info['model_id']));
        $info['model_name'] = $model['sale_name'] ? $model['sale_name'] : '';
        $info['images'] = CarAttachInterface::getImageInfoByCar(array('id' => $info['id']));
        $dept = MbsDeptInterface::getDeptInfoByDeptId(array('id' => $info['store_id']));
        $info['dept_name'] = $dept['dept_name'] ? $dept['dept_name'] : '';
        $info['dept_addr'] = $dept['address'] ? $dept['address'] : '';
        $info['shop_point'] = $dept['shop_point'] ? $dept['shop_point'] : '';
        $info['dept_telephone'] = $dept['telephone'] ? $dept['telephone'] : '';
        //主站客户端车源分享url
        $info['share_url'] = 'http://273.cn/s/' . $info['id'];

        $info = SearchHelper::formatSaleInfo($info);

        $len = strlen($info['ext_phone']);
        if ($len == 5) {
            $info['ext_phone'] = '0' . $info['ext_phone'];
        }
        if ($info['ext_phone'] == 0) {
            $info['ext_phone'] = '';
        }
        unset($info['telephone2']);
        $user = MbsUserInterface::getInfoByUser(array('username' => $info['follow_user_id']));
        $info['follow_user_name'] = $user['real_name'] ? $user['real_name'] : '';
        $info['gearbox_name'] = '';
        if ($info['gearbox_type']) {
            include_once APP_SERV . '/app/search/include/SearchVars.class.php';
            $info['gearbox_name'] = SearchVars::$GEARBOX_ALL[$info['gearbox_type']];
        }
        $info['plate_city_name'] = '';
        if ($info['plate_city_id']) {
            $plateCity = LocationInterface::getCityById(array('city_id'=>$info['plate_city_id']));
            $info['plate_city_name'] = $plateCity['name'] ? $plateCity['name'] : '';
        }
        $begin = floor($info['price'] * 0.8);
        $end = ceil($info['price'] * 1.2);
        $isChainCity = LocationInterface::isChainCity(array('id' => $info['deal_city_id']));
        if(empty($isChainCity)) {
            $orderSource = array(10, 40);
        } else {
            $orderSource = 10;
        }
        $searchFilter = array(
            'status' => 1,
            'deal_city_id' => $info['deal_city_id'],
            'cover_photo'  => 1,
            'price' => array($begin, $end),
            'offset' => 0,
            'limit' => 4,
            'order_source' => $orderSource,
            'sort' => array('create_time', 'desc'),
        );
        $priceArray = CarSaleInterface::search($searchFilter);
        $info['price_suggest_car'] = $this->_formatSuggest($priceArray['info'], $info['id']);
        $searchFilter = array(
            'status' => 1,
            'deal_city_id' => $info['deal_city_id'],
            'cover_photo'  => 1,
            'series_id' => $info['series_id'],
            'offset' => 0,
            'limit' => 4,
            'order_source' => $orderSource,
            'sort' => array('create_time', 'desc'),
        );
        $typeArray = CarSaleInterface::search($searchFilter);
        $info['type_suggest_car'] = $this->_formatSuggest($typeArray['info'], $info['id']);
        include_once APP_SERV . '/app/page/PageServiceApp.class.php';
        $page = new PageServiceApp();
        $pageCount = $page->getViewCount(array('id' => $info['id']));
        $info['page_count'] = $pageCount+1;
        if ($pageCount >= 0) {
            $page->setViewCount(array(
                'id' => $info['id'],
                'value' => $info['page_count'],
            ));
        }
        self::$_closeExtPhone = ExtPhoneInterface::isExtPhoneClose();
        if(self::$_closeExtPhone) {
            unset($info['ext_phone']);
        } else {
            $info['fix_num'] = '400-0018-273';
        }
        return $info;
    }
    private function _formatSuggest($data, $id) {
        $num = 0;
        $ret = array();
        foreach ($data as $key=>$item) {
            if ($item['id'] != $id && $num<3) {
                //不使用电话转接号
                if(self::$_closeExtPhone) {
                    unset($item['ext_phone']);
                }
                $ret[] = SearchHelper::formatSaleListItem($item);
                $num++;
            }
        }
        return $ret;
    }
    /*
     * @ brief 获取车源质检信息
     * 
     * @ params carId车源id
     * 
     * @ return int order_status,driving_status,identified_status 1，有，0没有
     */
    public function getCheckCarPhoto($carId) {
        $ret['image'] = array();
        $ret['image_plate'] = array();
        $fields = 'id,file_path,sort_order,is_cover,object_type';
        $filters[] = array('object_id' ,'=' ,$carId);
        $filters[] = array('object_type', 'in', array(1, 97, 98, 99));
        $filters[] = array('status', '=', 1);
        $imageList = CarAttachInterface::getImageInfoByPostIds($fields,$filters);
        $carPhotos = MbsCheckCarPhotoInterface::getCheckInfoByCarId(array('car_id' => $carId));
        $status = array();
        if (!empty($imageList)) {
            foreach ($imageList as $key => $images) {
                if ($images['object_type'] == 1) {
                    $imageList[$key]['cover'] =  $imageList[$key]['is_cover'];
                    $imageList[$key]['type'] =  $imageList[$key]['object_type'];
                    $imageList[$key]['index'] =  $imageList[$key]['sort_order'];
                    unset($imageList[$key]['sort_order']);
                    unset($imageList[$key]['object_type']);
                    unset($imageList[$key]['is_cover']);
                    $ret['image'][] = $imageList[$key];
                    unset($imageList[$key]);
                }else {
                    $h = '';
                    foreach($carPhotos as $m => $photo) {
                        if($photo['photo_type'] == (100 -$images['object_type'])) {
                            $h = $m;
                        }
                    }
                    switch(intval($carPhotos[$h]['status'])) {
                        case 0 : $imageList[$key]['image_status_name'] = '待审核';
                        break;
                        case 1 : $imageList[$key]['image_status_name'] = '待审核';
                        break;
                        case 2 : $imageList[$key]['image_status_name'] = '已审核';
                        break;
                        default: $imageList[$key]['image_status_name'] = '审核不通过';
                        break;
                    }
                    $imageList[$key]['remark'] = $carPhotos[$h]['remark'] ? $carPhotos[$h]['remark'] : '';
                    $status[$images['object_type']] = $carPhotos[$h]['status'];
                    $imageList[$key]['check_time'] = $carPhotos[$h]['check_time'] ? $carPhotos[$h]['check_time'] : '' ;
                    $imageList[$key]['cover'] =  $imageList[$key]['is_cover'];
                    $imageList[$key]['type'] =  $imageList[$key]['object_type'];
                    $imageList[$key]['index'] =  $imageList[$key]['sort_order'];
                    unset($imageList[$key]['sort_order']);
                    unset($imageList[$key]['object_type']);
                    unset($imageList[$key]['is_cover']);
//                     $ret['image_plate'][] = $imageList[$key];
                }
            }
            $ret['order_status'] = $ret['identified_status'] = $ret['driving_status'] = $ret['advisor_status'] = '';
            if($status[97]['status'] == 2 || $status[98]['status'] == 2 || $status[99]['status'] == 2) {
                $ret['order_status'] = 1;
            }
            if($status['98']['status'] == 2) {
                $ret['driving_status'] = 1;
                $ret['identified_status'] = 1;
            }
            if($status[97]['status'] == 2 || ($status[99]['status'] ==2 && $status[98]['status']) == 2) {
                $ret['advisor_status'] = 1;
                $ret['identified_status'] = 1;
            }
            return $ret;
        }
    }
    
    
    /*
     *格式化参数
     *将xxxx-xxxx的范围模式改为array(xxxx-xxxx)，将sortby-sortmode的排序模式改为array(sortby, sortmode)
     *将区间查询min-max改为array(min,max)
     */
    private function _getParams($params) {
        if ($params['sort']) {
            $params['order'] = $params['sort'];
        }
        $order = !empty($params['order']) ? explode(',', $params['order']) : array();
        unset($params['order']);
        unset($params['sort']);
        foreach ($params as $field => $item) {
            if (empty($item)) {
                unset($params[$field]);
                continue;
            }
            //-能找到且位置大于0,且检索字段不是kw
            if (strpos($item, '-') && $field != 'kw') {
                $params[$field] = explode('-', $item);
                if ($field == 'card_time') {
                    $temp = $params[$field];
                    $params[$field][0] = strtotime("-" . ((int) $temp[1]) . " year");
                    $params[$field][1] = strtotime("-" . ((int) $temp[0]) . " year");
                }
            } elseif ($field == 'card_time') {
                $params[$field] = (int) $item;
            } elseif ($field == 'brand_id') {
                if ($params['_api_version'] == '1.0') {
                    $newBrandId = VehicleAdaptInterface::brandIdToNew(array('brand_id' => $item));
                    $params[$field] = $newBrandId;
                }
            } elseif ($field == 'series_id') {
                if ($params['_api_version'] == '1.0') {
                    $newSeriesId = VehicleAdaptInterface::seriesIdToNew(array('series_id' => $item));
                    $params[$field] = $newSeriesId;
                }
            } elseif ($field == 'type_id' && isset(SearchVars::$TYPE_ID_ADAPT[$item])) {
                if ($params['_api_version'] == '1.0') {
                    $params[$field] = SearchVars::$TYPE_ID_ADAPT[$item];
                }
            }
        }
        //$params['order_source'] = 10;//本站车源

        if (empty($params['model_id'])) {
            unset($params['model_id']);
        }
        //
        if ($params['car_age']) {
            $cardTime = explode('-', $params['car_age']);

            $params['card_time'] = array(
                time() - $params['car_age'][1] * 365 * 24 * 3600,
                time() - $params['car_age'][0] * 365 * 24 * 3600
            );
        }
        unset($params['car_age']);

        
        
        $params['sort'] = array(
            array('order_source', 'asc'),
            array('order_status', 'desc'),
            array('stick_time', 'asc'),
            array('update_time', 'desc')
        );
        
        
        if ($order) {
            $params['sort'] = array();
            $params['sort'][] = array('order_source', 'asc');
            foreach ($order as $value) {
                $orderBy = explode('-', $value);
                if (count($orderBy) !== 2) {
                    continue;
                }
                $params['sort'][] = array($orderBy[0], $orderBy[1]);
                
            }
            $params['sort'][] = array('order_status', 'desc');
            $params['sort'][] = array('stick_time', 'asc');
        }
        
        return $params;
    }
    
    /*
     * 过滤CarSaleInterface::search返回的info数据
     */
    private function _fomatInfo($info) {
        $data = array(
            'id'          => $info['id'],
            'create_time' => $info['create_time'],
            'update_time' => $info['update_time'],
            'cover_photo' => $info['cover_photo'],
            'title'       => $info['title'],
            'kilometer'   => $info['kilometer'],
            'card_time'   => $info['card_time'],
            'price'       => $info['price'],
            'follow_user_id' => $info['follow_user_id'],
            'ext_phone'   => $info['ext_phone'],
            'seller_name' => $info['seller_name'],
            'telephone'   => $info['telephone'],
            'ip'          => $info['ip'],    
            'mark_type'   => $info['mark_type'],
            'tags'        => empty($info['tags']) ? array() : $info['tags'],
            'tag_title'   => isset($info['tag_title']) ? $info['tag_title'] : '',
        );
        return $data;
    }
}
