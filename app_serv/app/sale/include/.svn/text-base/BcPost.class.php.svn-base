<?php
class BcPost {

    /*
     * @brief 格式化输出，格式化phoneList，SaleList，以及sale（buy）detail等共同的函数
     * @从oracle到mysql迁移过程中的字段迁移变化
     * 1.格式化price，和kilometer
     * 2.格式化图片url
     * 3.car_id下发后变为id
     * 4.根据status,sale_status 下发status_show,sale_status_show
     * 5.获取follow_user_name
     * 6.getCustomerInfoById获取customer信息
     * 7.从oracle到mysql数据库字段变化
     * 8.获取并格式化质检信息
     * 9.强制质检的三张图片：人车合照图，行驶证图，车牌正面图
     * 10.做优化，将需要ids集合查询，用in来处理，减少数据库访问次数（未完成，时间太急，迁移完成后做）
     * 
     */
    public static function _basicFormat($v ,$query_type = '') {
        
        //格式化图片url
        $ret['photo'] = !empty($v['cover_photo']) ? CarUtil::buildPhoto($v['cover_photo']) : '';
        if (strpos($ret['photo'], '120-90_6_0_0_120-90_6_0_0')) {
            $ret['photo'] = str_replace('120-90_6_0_0_120-90_6_0_0', '120-90_6_0_0', $v['photo']);
        }
        
        //根据status,sale_status 下发status_show,sale_status_show
        switch ($v['status']) {
            case 0 :
                $ret['status_show'] = '未审核';
                break;
            case 1 :
                $ret['status_show'] = '已审核';
                break;
            case 2 :
                $ret['status_show'] = '冻结';
                break;
            case -1:
                $ret['status_show'] = '草稿';
                break;
            default :
                $ret['status_show'] = '终止';
        }
        switch($v['sale_status']) {
            case 0 :
                $ret['sale_status_show'] = '未售出';
                break;
            case 1 :
                $ret['sale_status_show'] = '已售出';
                break;
            default:
                $ret['sale_status_show'] = '';
        }
        
        //获取follow_user_name
        if(!empty($v['follow_user_id'])) {
            $user = MbsUserInterface::getInfoByUser(array('username' => $v['follow_user_id']));
        }
        $ret['follow_user_name'] = empty($user['real_name']) ? '' : $user['real_name'];
        if(!empty($user['mobile'])) {
            $ret['mobile'] = $user['mobile'];
        }
        //获取dept_name
        if(!empty($user['dept_id'])) {
            $dept = MbsDeptInterface::getDeptInfoByDeptId(array('id' => $user['dept_id']));
        }
        $ret['dept_id'] = $user['dept_id'];
        $ret['dept_name'] = $dept['dept_name'] ? $dept['dept_name'] : '';
        
        //getCustomerInfoById获取customer信息
        if(!empty($v['customer_id'])) {
            $customerInfo = self::getCustomerInfoById($v['customer_id']);
        }
        if(!empty($customerInfo)) {
            $ret = array_merge($ret, $customerInfo);
        }
        //获取并格式化质检信息(下期修改)
        $checkInfo = self::getCheckInfo($v['id']);
        if(empty($checkInfo)) {
            $checkInfo = array();
            $checkInfo['check_status'] = '';
            $checkInfo['check_status_show'] = '';
            $checkInfo['check_remark'] = '';
            $checkInfo['check_time'] = '';
        }
        $ret = array_merge($ret, $checkInfo);

        

        return $ret;
    }
    
    
    /*
     * @brief 搜索查询时，若关键字为品牌或车系，将关键字转换为品牌或车系的ID进行查询，列表多返回品牌车系数据
     * 
     * @老的代码没有改动，需要测试
     */
    protected function getInfoByKeyword($params) {
        if (!empty($params['keyword'])) {
            if (is_numeric($params['keyword']) || preg_match('/^[\x81-\xfe][\x40-\xfe]{2,4}[A-Z][A-Z0-9]{4}[A-Z0-9]$/', $params['keyword'])) {
    
            } else {
                $brandInfo = $this->_parseKeywords($params['keyword']);
                if ($brandInfo) {
                    $params['keyword'] = '';
                    if ($brandInfo['series_id']) {
                        $params['series_id'] = $brandInfo['series_id'];
                    } else {
                        $params['brand_id'] = $brandInfo['brand_id'];
                    }
                    $brandInfo['series_id'] = $brandInfo['series_id'] ? $brandInfo['series_id'] : '';
                    $brandInfo['series_name'] = $brandInfo['series_name'] ? $brandInfo['series_name'] : '';
                    $brandInfo['series_path'] = $brandInfo['series_path'] ? $brandInfo['series_path'] : '';
                    return $brandInfo;
                }
            }
        }
    }
    

    
    /**
     * @brief 获取客户信息CustomerInfo
     */
    public static function getCustomerInfoById($customer_id) {
        if(!empty($customer_id)){
            $customer = MbsCustomerInterface::getInfoForId(array('id' => $customer_id));
        }
        $ret['contact_user'] = $customer['real_name'] ? $customer['real_name'] : '';
        $ret['contact_telephone'] = $customer['mobile'] ? $customer['mobile'] : '';
        $ret['contact_telephone2'] = $customer['telephone'] ? $customer['telephone'] : '';
        $ret['idcard'] = $customer['idcard'] ? $customer['idcard'] : '';
        if(!empty($customer['mobile'])) {
            $ret['contact_telephone_addr'] = CarUtil::getNameByTel($customer['mobile']);
        }
    
        return $ret;
    }
    
    
    //根据品牌车系关键词获取对应品牌车系数据
    protected static function _parseKeywords($kw) {
        $kw = trim($kw);
        $kw = preg_replace('/\s+/', " ", $kw);
        $kwOrigin = $kw;
        $kwSeparate = explode(' ', $kwOrigin, 2);
        $kw = str_replace(' ', '', $kw);
        if (empty($kw)) {
            return;
        }
        $searchRet = array();
        $searchParam = array (
                'type'  => 'all',
                'area_id' => 0,
                'limit' => 3,
                'fuzzy' => false
        );
        $data = array();
        do{
            //以用户原始输入为关键字查找
            $searchParam['keyword'] = $kw;
            $searchRet = VehicleV2Interface::getVehicleFromWord($searchParam);
            if (count($searchRet) >= 2) {
                //匹配结果为2个且属于同一个品牌时，跳转第一个匹配结果
                if (count($searchRet) == 2 && $searchRet[0]['brand_id'] == $searchRet[1]['brand_id'] ) {
                    unset($searchRet[0]['import_id']);
                    unset($searchRet[0]['count']);
                    $data = $searchRet[0];
                    break;
                } else {
                    //当有多个匹配结果时，把输入串以关键词直接到检索系统搜索
                    break;
                }
            } else if (!empty($searchRet[0])) {
                unset($searchRet[0]['import_id']);
                unset($searchRet[0]['count']);
                $data = $searchRet[0];
                break;
            }
            //拆分出两个关键字
            if (count($kwSeparate) == 2) {
                $brandName  = $kwSeparate[0];
                $seriesName = $kwSeparate[1];
            } else {
                break;
            }
    
            //以用户输入的第二个参数查找车系
            if (!empty($seriesName)) {
                $searchRet = array();
                $searchParam['keyword'] = $seriesName;
                $searchParam['type'] = 'series';
                $searchParam['limit'] = 10;
    
                $searchRet = VehicleV2Interface::getVehicleFromWord($searchParam);
                if (!empty($searchRet)) {
                    foreach ($searchRet as $item) {
                        if ($item['brand_name'] == $brandName) {
                            //搜索结果中的品牌与用户输入的品牌相同时，跳转
                            unset($item['import_id']);
                            unset($item['count']);
                            $data = $item;
                            break;
                        }
                    }
                    if (!empty($data)) {
                        break;
                    }
                }
    
            }
            //以用户输入的第二个参数查找品牌
            if (!empty($brandName)) {
                $searchRet = array();
                $searchParam['keyword'] = $brandName;
                $searchParam['type'] = 'brand';
                $searchRet = VehicleV2Interface::getVehicleFromWord($searchParam);
                if ($searchRet[0]) {
                    unset($searchRet[0]['import_id']);
                    unset($searchRet[0]['count']);
                    $data = $searchRet[0];
                    break;
                }
            }
            break;
        } while(true);
        return $data;
    }
    
    private static function getCheckInfo($carId) {
        $checkInfo = MbsCheckCarNumberInterface::getCheckInfoByCarId(array('car_id'=>$carId));
        if (!empty($checkInfo)) {
            $ret['check_status'] = $checkInfo['status'];
            switch ($checkInfo['status']) {
                case 2: $ret['check_status_show'] = '通过'; break;
                case 3: $ret['check_status_show'] = '不通过，下架车源'; break;
                case 4: $ret['check_status_show'] = '不通过，下架车源，3天内不允许发布与刷新车源'; break;
                case 6: $ret['check_status_show'] = '不通过'; break;
                default: $ret['check_status_show'] = ''; break;
            }
            $ret['check_remark'] = $checkInfo['remark'] ? $checkInfo['remark'] : '';
            $ret['check_time'] = $checkInfo['check_time'] ? $checkInfo['check_time'] : '';
        }
        return $ret;
    }
    
    
    /*public static function getCheckCarPhoto($carId) {
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
                    $file_path = $images['file_path'];
                    $imageList[$key]['url'] = CarUtil::buildPhoto($file_path);
                    $imageList[$key]['file_path'] = $file_path;
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
                    $file_path = $imageList[$key]['file_path'];
                    $imageList[$key]['url'] = CarUtil::buildPhoto($file_path);
                    $imageList[$key]['file_path'] = $file_path;
                    $imageList[$key]['remark'] = $carPhotos[$h]['remark'] ? $carPhotos[$h]['remark'] : '';
                    $status[$images['object_type']] = $carPhotos[$h]['status'];
                    $imageList[$key]['check_time'] = $carPhotos[$h]['check_time'] ? $carPhotos[$h]['check_time'] : '' ;
                    $imageList[$key]['cover'] =  $imageList[$key]['is_cover'];
                    $imageList[$key]['type'] =  $imageList[$key]['object_type'];
                    $imageList[$key]['index'] =  $imageList[$key]['sort_order'];
                    unset($imageList[$key]['sort_order']);
                    unset($imageList[$key]['object_type']);
                    unset($imageList[$key]['is_cover']);
                    $ret['image_plate'][] = $imageList[$key];
                }
            }
            $ret['order_status'] = $ret['identified_status'] = $ret['driving_status'] = $ret['advisor_status'] = '';
            if($status[97]['status'] == 2 || $status[98]['status'] == 2 || $status[99]['status'] == 2) {
                $ret['order_status'] = '车源已置顶';
            }
            if($status['98']['status'] == 2) {
                $ret['driving_status'] = '行驶证已审核';
                $ret['identified_status'] = '已标识';
            }
            if($status[97]['status'] == 2 || ($status[99]['status'] ==2 && $status[98]['status']) == 2) {
                $ret['advisor_status'] = '顾问已看车';
                $ret['identified_status'] = '已标识';
            }
            return $ret;
        }
    }*/
    
    public static function getCheckCarPhoto($carId) {
        $ret['image'] = array();
        $ret['image_plate'] = array();
        $fields = 'id,file_path,sort_order,is_cover,object_type';
        $filters[] = array('object_id' ,'=' ,$carId);
        $filters[] = array('object_type', 'in', array(1));
        $filters[] = array('status', '=', 1);
        $imageList = CarAttachInterface::getImageInfoByPostIds($fields,$filters);
        $carPhotos = MbsCheckCarPhotoInterface::getCheckInfoByCarId(array('car_id' => $carId));
        $status = array();
        if (!empty($imageList)) {
            foreach ($imageList as $key => $images) {
                if ($images['object_type'] == 1) {
                    $file_path = $images['file_path'];
                    $imageList[$key]['url'] = CarUtil::buildPhoto($file_path);
                    $imageList[$key]['file_path'] = $file_path;
                    $imageList[$key]['cover'] =  $imageList[$key]['is_cover'];
                    $imageList[$key]['type'] =  $imageList[$key]['object_type'];
                    $imageList[$key]['index'] =  $imageList[$key]['sort_order'];
                    unset($imageList[$key]['sort_order']);
                    unset($imageList[$key]['object_type']);
                    unset($imageList[$key]['is_cover']);
                    $ret['image'][] = $imageList[$key];
                    unset($imageList[$key]);
                }
            }
        }

        $status = array();
        //车牌图片处理
        if (!empty($carPhotos)) {
            foreach ($carPhotos as $info) {
                $photoInfo = array();
                switch(intval($info['status'])) {
                    case 0 : $photoInfo['image_status_name'] = '待审核';
                    break;
                    case 1 : $photoInfo['image_status_name'] = '待审核';
                    break;
                    case 2 : $photoInfo['image_status_name'] = '已审核';
                    break;
                    default: $photoInfo['image_status_name'] = '审核不通过';
                    break;
                }

                $file_path = $info['photo'];

                $photoInfo['url'] = CarUtil::buildPhoto($file_path);
                $photoInfo['file_path'] = $file_path;
                $photoInfo['remark'] = $info['remark'] ? $info['remark'] : '';
                $status[$info['photo_type']]['status'] = $info['status'];
                $photoInfo['check_time'] = $info['check_time'] ? $info['check_time'] : '' ;
                //$photoInfo['cover'] =  $photoInfo['is_cover'];
                $photoInfo['type'] =  100 - $info['photo_type'];
                $photoInfo['id']   = $info['car_id'];
                //$imageList[$key]['index'] =  $imageList[$key]['sort_order'];
                $ret['image_plate'][] = $photoInfo;
            }

            $ret['order_status'] = $ret['identified_status'] = $ret['driving_status'] = $ret['advisor_status'] = '';
            if((!empty($status['3']['status']) && $status['3']['status'] == 2) || (!empty($status['2']['status']) && $status['2']['status'] == 2) || (!empty($status['1']['status']) && $status['1']['status'] == 2)) {
                $ret['order_status'] = '车源已置顶';
            }
            if((!empty($status['2']['status']) && $status['2']['status'] == 2)) {
                $ret['driving_status'] = '行驶证已审核';
                $ret['identified_status'] = '已标识';
            }
            if((!empty($status['3']['status']) && $status['3']['status'] == 2) || (!empty($status['1']['status']) && !empty($status['2']['status']) && $status['1']['status'] ==2 && $status['2']['status']) == 2) {
                $ret['advisor_status'] = '顾问已看车';
                $ret['identified_status'] = '已标识';
            }
        }

        return $ret;
    }
    
}



