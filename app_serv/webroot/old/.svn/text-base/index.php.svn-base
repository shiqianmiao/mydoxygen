<?php
$json = array();
if ($_GET['model']) {
    $model = $_GET['model'];
} else {
    $model = substr($_SERVER['REDIRECT_URL'], 1);
}
require_once dirname(__FILE__) . '/../../../conf/config.inc.php';
require_once API_PATH . '/interface/CarSaleInterface.class.php';
require_once API_PATH . '/interface/LocationInterface.class.php';
require_once API_PATH . '/interface/MbsDeptInterface.class.php';
require_once API_PATH . '/interface/VehicleV2Interface.class.php';
require_once API_PATH . '/interface/mbs/MbsContractInterface.class.php';

require_once './include/ModelDd.php';
header('content-Type: text/html; charset=utf-8');


if ($model == 'sale.list') {

    $where = array();
    if ($_GET['max_id']) {
        $where['max_id'] = $_GET['max_id'];
    }
    if ($_GET['last_id']) {
        $where['last_id'] = $_GET['last_id'];
    }
    if ($_GET['id']) {
        $where['id'] = $_GET['id'];
    }
    if ($_GET['offset']) {
        $where['offset'] = intval($_GET['offset']);
    }
    if ($_GET['limit']) {
        $where['limit'] = intval($_GET['limit']);
    }
    
    $result = array();

    $carSaleList = CarSaleInterface::getCarSaleForApi($where);


    if ($_GET['report_type'] == 'phone') {
        $sale_id = $_GET['id'];
        $carSaleList = CarSaleInterface::getCarInfoById(array('id' => $sale_id));
    }

    $modelId2SaleId = array();
    $modelIds = array();
    foreach ((array) $carSaleList as $rs) {
        $item = array();
        $item['id'] = $rs['id'];
        $item['family_code'] = $rs['series_id'];
        if (!empty($rs['model_id'])) {
            $modelId2SaleId[$rs['model_id']] = $rs['id'];
            $modelIds[] = $rs['model_id'];
        }

        if ($_GET['report_type'] == 'phone') {
            $item['info_id'] = $rs['info_id'];
            $item['sale_status'] = $rs['sale_status'];
            $item['contract_info'] = MbsContractInterface::getContractBySaleNumber(array('sale_number' => $item['id']));
        }

        $item['title'] = $rs['title'];
        $item['car_color'] = ModelDd::getColorTow($rs['car_color']);
        
        $item['province'] = LocationInterface::getProvinceById(array('province_id' => $rs['deal_province_id']));
        $item['province'] = $item['province']['name'];
        
        $item['city'] = LocationInterface::getCityById(array('city_id' => $rs['deal_city_id']));
        $item['city'] = $item['city']['name'];

        $item['district'] = LocationInterface::getDistrictById(array('district_id' => $rs['deal_district_id']));
        $item['district'] = $item['district']['name'];
        
        $item['price'] = ($rs['price'] == 99999) ? '面议' : (int) $rs['price'];
        $item['kilometer'] = $rs['kilometer'];
        $item['card_time'] = date('Y-m', $rs['card_time']);
        $item['insert_time'] = date('Y-m-d', $rs['create_time']);

        $item['note'] = $rs['description'] . '编号:' . $item['id'];
        $item['contact'] = '273二手车';
        $item['telephone'] = '4006000273';
        if ($rs['ext_phone']) {
            $deptInfo = MbsDeptInterface::getDeptInfoByDeptId(array('id' => $rs['store_id']));
            $item['contact'] = $deptInfo['dept_name'];
            $item['telephone'] = '4000018273-' . $rs['ext_phone'];
        }

        $imgArray = ModelDd::returnImgAry(intval($rs['id']), 1, 1);
        if ($imgArray) {
            foreach ((array) $imgArray as $key => $value) {
                $item['images'][$key]['file_path'] = $value['file_path'];
                $item['images'][$key]['mini_file_path'] = $value['mini_file_path'];
            }
        }
        $result[$rs['id']] = $item;
    }
    // 去掉最后的逗号
    $modelList = VehicleV2Interface::getModelList(array('ids' => $modelIds));
    foreach ((array) $modelList as $list) {
        $result[$modelId2SaleId[$list['id']]]['make'] = $list['brand_name'];
        $result[$modelId2SaleId[$list['id']]]['family'] = $list['series_name'];
        $result[$modelId2SaleId[$list['id']]]['gear_type'] = $list['gearbox_type'];
        $result[$modelId2SaleId[$list['id']]]['engine'] = $list['displacement'];
    }
    $json['data'] = $result;
    $json['errorCode'] = 0;
    $json['errorMessge'] = '';
} else if ($model == 'buy.list') {

//    $params = array();
//    $params['filters'] = array(
//        array(
//            'sale_status', '=', 0
//        ),
//        array(
//            'status', '=', 1
//        ),
//        array(
//            'create_time', '<', time() - 86400
//        )
//    );
//    if ($params['max_id']) {
//        array_push($params['filters'], array(
//            'id', '>=', $params['max_id']
//        ));
//    }
//
//    if ($params['last_id']) {
//        array_push($params['filters'], array(
//            'id', '<=', $params['last_id']
//        ));
//    }
//    if ($params['id']) {
//        array_push($params['filters'], array(
//            'id', '=', $params['id']
//        ));
//    }
//    if (!$params['offset']) {
//        $params['offset'] = 0;
//    }
//    if (!$params['limit']) {
//        $params['limit'] = 10;
//    }
//    require_once API_PATH . '/interface/mbs/CarBuyInterface.class.php';
//    $CarBuyList = CarBuyInterface::getCarBuyList($params);
//
//    $result = array();
//    foreach ((array) $CarBuyList as $rs) {
//        $item = array();
//        $item['id'] = $rs['id'];
//
//
//        $item['title'] = '求购' . ($rs['title'] ? $rs['title'] : $rs['note']);
//        $item['province'] = LocationInterface::getProvinceById(
//                        array(
//                            'province_id' => $rs['province']
//                ));
//        $item['province'] = $item['province']['name'];
//        $item['city'] = LocationInterface::getCityById(
//                        array(
//                            'city_id' => $rs['city']
//                ));
//        $item['city'] = $item['city']['name'];
//        $item['district'] = LocationInterface::getDistrictById(
//                        array(
//                            'district_id' => $rs['district']
//                ));
//        $item['district'] = $item['district']['name'];
//        $item['min_price'] = ($rs['min_price'] == 99999) ? '面议' : $rs['min_price'];
//        $item['max_price'] = ($rs['max_price'] == 99999) ? '面议' : $rs['max_price'];
//        $item['insert_time'] = $rs['create_time'];
//        $item['note'] = $rs['note'];
//        $result[$rs['id']] = $item;
//    }

    $result = array();
    $json['data'] = $result;
    $json['errorCode'] = 0;
    $json['errorMessge'] = '';
} else
if ($model == 'sale.report') {
    $db = DBMysqli::createDBHandle(DBConfig::$SERVER_MASTER, DBConfig::DB_CAR);
    $date = $_GET['date'] ? $_GET['date'] : date('Y-m-d');
    if ($_GET['type'] == 'month') {
        $format = 'yyyy-mm';
        $startTime = strtotime($date . '-1');
        $endTime = strtotime($date . '-'.date('t',$startTime));
    } else {
        $format = 'yyyy-mm-dd';
        $startTime = strtotime($date);
        $endTime = $startTime + 86400;
    }
    
    
    $group = 'store_id';
    if ($_GET['city']) {
        $group = 'deal_city_id';
    }
    // 车源总数
    $saleNumSql = 'select ' . $group . ', count(*) as cc from car_sale t
                         where ' . $group . '>0 and status=1
                         group by ' . $group;
    
    $saleNum = DBMysqli::queryAll($db, $saleNumSql);

    foreach ($saleNum as $data) {
        $saleNum2[$data[$group]] = $data['cc'];
    }
    
    // 一个月车源总数
    $oneMonthSaleNumSql = 'select ' . $group . ', count(*) as cc from car_sale t
            where ' . $group . '>0 and status=1 and create_time>' . (time() - 86400 * 30) . ' group by ' . $group;

    $oneMonthSaleNum = DBMysqli::queryAll($db, $oneMonthSaleNumSql);

    foreach ($oneMonthSaleNum as $data) {
        $oneMonthSaleNum2[$data[$group]] = $data['cc'];
    }
    // 三个月车源总数
    $threeMonthSaleNumSql = 'select ' . $group . ', count(*) as cc from car_sale t 
        where ' . $group . ' >0 and status=1 and create_time>' . (time() - 86400 * 91)
            . ' group by ' . $group;

    $threeMonthSaleNum = DBMysqli::queryAll($db, $threeMonthSaleNumSql);

    foreach ($threeMonthSaleNum as $data) {
        $threeMonthSaleNum2[$data[$group]] = $data['cc'];
    }
    
    // 有图车源总数
    $salePhotoNumSql = 'select ' . $group . ', count(*) as cc from car_sale t
                   where ' . $group . ' >0 and status=1 and cover_photo != \'\'
                   group by ' . $group;
    
    $salePhotoNum = DBMysqli::queryAll($db, $salePhotoNumSql);
    foreach ($salePhotoNum as $data) {
        $salePhotoNum2[$data[$group]] = $data['cc'];
    }
    
    // 今日新增车源
    $todaySaleNumSql = 'select ' . $group . ', count(*) as cc from car_sale t
                             where ' . $group . '>0 and create_time >= ' . $startTime . ' 
                             and create_time <= ' . $endTime . ' group by ' . $group;
    
    
    $todaySaleNum = DBMysqli::queryAll($db, $todaySaleNumSql);
    foreach ($todaySaleNum as $data) {
        $todaySaleNum2[$data[$group]] = $data['cc'];
    }
    
    
    // 今日新增有图车源
    $salePhotoNumSql = 'select ' . $group . ', count(*) as cc from car_sale t
                   where ' . $group . ' >0 and status=1 and cover_photo != \'\' and
                       create_time >= ' . $startTime . ' 
                             and create_time <= ' . $endTime . '
                   group by ' . $group;
    
    $todayPhotoSaleNum = DBMysqli::queryAll($db, $salePhotoNumSql);
    
    foreach ($todayPhotoSaleNum as $data) {
        $todayPhotoSaleNum2[$data[$group]] = $data['cc'];
    }
    
    // 今日更新车源
    $saleUpdateNumSql = 'select ' . $group . ', count(*) as cc from car_sale t
                             where ' . $group . '>0 and update_time >= ' . $startTime . ' 
                             and update_time <= ' . $endTime . ' group by ' . $group;
    
    $saleUpdateNum = DBMysqli::queryAll($db, $saleUpdateNumSql);
    foreach ($saleUpdateNum as $data) {
        $saleUpdateNum2[$data[$group]] = $data['cc'];
    }
    
    // 终止车源
    $saleStopNumSql = 'select ' . $group . ', count(*)
                       from car_sale_bak t inner join car_sale_ext_bak e on t.id=e.car_id
                       where ' . $group . '>0
                       and stop_time >= ' . $startTime . ' and stop_time <= ' . $endTime . '
                       and status>1 group by ' . $group;
    
    $saleStopNum = DBMysqli::queryAll($db, $saleStopNumSql);
    foreach ($saleStopNum as $data) {
        $saleStopNum2[$data[$group]] = $data['cc'];
    }
    
    $result = array();
    foreach ((array) $saleNum2 as $dept => $num) {
        $result[$dept]['all'] = $num;
        $result[$dept]['all_photo'] = $salePhotoNum2[$dept];
        $result[$dept]['today'] = $todaySaleNum2[$dept];
        $result[$dept]['today_photo'] = $todayPhotoSaleNum2[$dept];
        $result[$dept]['update'] = $saleUpdateNum2[$dept];
        $result[$dept]['stop'] = $saleStopNum2[$dept];
        $result[$dept]['one_month'] = $oneMonthSaleNum2[$dept];
        $result[$dept]['three_month'] = $threeMonthSaleNum2[$dept];
    }
    
    $json['data'] = $result;
    $json['errorCode'] = 0;
    $json['errorMessge'] = '';
    echo json_encode($json);
    exit();
} else {
    $json['errorCode'] = 2;
    $json['errorMessge'] = '此方法后端服务暂时不可用';
    echo json_encode($json);
    exit();
}
if ($_GET['datatype'] == 'array') {
    print_r($json);
    exit();
}
echo json_encode($json);
