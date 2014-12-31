<?php

require_once API_PATH . '/interface/CarSaleInterface.class.php';
require_once API_PATH . '/interface/sync2site/SyncInfoInterface.class.php';
require_once API_PATH . '/interface/sync2site/SyncDeptInfoInterface.class.php';
require_once DATA_PATH . '/sync2site/Sync58CommonData.class.php';
require_once API_PATH . '/interface/sync2site/SyncDeptTokenInfoInterface.class.php';
require_once API_PATH . '/interface/VehicleV2Interface.class.php';
require_once API_PATH . '/interface/CarAttachInterface.class.php';
require_once API_PATH . '/interface/sync2site/Sync58InfoInterface.class.php';
require_once API_PATH . '/interface/sync2site/SyncXcarInterface.class.php';
require_once API_PATH . '/interface/CarBrandOutEsczjInterface.class.php';

class Sync2siteServiceApp {

    /***
     * 查看某门店是否有“已同步车源列表”的查看权限
     * $param $params array(
     *     'dept_id'=>
     * )
     * ***/
    public function checkSyncList( $params ) {
        #$dept_list_58 = SyncDeptInfoInterface::getDeptInfoList(array('sync_site_id'=>1,'is_dept_key'=>1));
        $dept_info = SyncDeptInfoInterface::getDeptInfoById(array('dept_id'=>$params['dept_id']));
        #if( array_key_exists($params['dept_id'], $dept_list_58) ) {
        if( $dept_info ) {
            return array('is_show'=>1);
        }
        return array('is_show'=>0);
    }

    /****
     * 设置同步队列
     * @param array $params array(
     *  'id'=>车源id
     *  's58'=>1
     *  'xcar'=>1
     * )
     * ***/
    public function setQueue($params) {
        $array = array(
            'id'=>$params['id'],
        );
        if( $params['s58'] ) $array['s58'] = 1;
        if( $params['xcar'] ) $array['xcar'] = 1;
        return SyncInfoInterface::setQueue($array);
    }

    public function setEditQueue($params) {
        $array = array(
            'id'=>$params['id'],
            'edittime'=>$params['edittime'],
        );
        return SyncInfoInterface::setEditQueue($array);
    }

    /****
     * 获取某车源已同步的合作站信息列表
     * ***/
    public function getSyncList( $params ) {
        $arr = explode(',', $params['car_sale_id']);
        $arr = array('ids'=>$arr);

        $newArr = array();
        $syncList = SyncInfoInterface::getSyncSaleInfoList($arr);
        if( $syncList ) {
            foreach( $syncList as $k=>$v ) {
                $newArr[$v['car_sale_id']][$v['sync_site_id']] = $v;
            }
        }
        $syncList = $newArr;
        return $syncList;
    }

    /***
     * 编辑一个车源信息
     * $param $params array(
     * 'id'=>同步信息id
     * 'sync_site_id'=>1:58,2:xcar
     * )
     * ***/
    public function editPost( $params ) {
        if( $params['sync_site_id'] == 1 ) {
            $sinfo = SyncInfoInterface::getSyncSaleInfoById(array('id'=>$params['id']));
            $carInfo = CarSaleInterface::getCarDetail(array('id' => $sinfo['car_sale_id']));
            $carBrand = VehicleV2Interface::getBrandById(array('brand_id'=>$carInfo['brand_id']));
            $carSeries = VehicleV2Interface::getSeriesById(array('series_id'=>$carInfo['series_id']));
            $carModel = VehicleV2Interface::getModelById(array('model_id'=>$carInfo['model_id']));
            $carImages = CarAttachInterface::getImageInfoByCar(array('id'=>$carInfo['id']));
            $array = array(
                'infoid'=>$sinfo['sync_site_post_id'],
                'info'=>$carInfo,
                'brand'=>$carBrand,
                'series'=>$carSeries,
                'model'=>$carModel,
                'images'=>$carImages,
            );
            return Sync58InfoInterface::editPost($array);
        }
    }

    public function addPost( $params ) {
        $car_id = $params['car_id'];
        $carInfo = CarSaleInterface::getCarDetail(array('id' => $car_id));
        if( $carInfo['is_sync'] ) {
            echo "this car synced\r\n";
            return false;
        }
        if( !$carInfo['ext_phone'] ) {
            echo "id {".$carInfo['id']."} no ext_phone\r\n";
            return false;
        }

        $carBrand = VehicleV2Interface::getBrandById(array('brand_id'=>$carInfo['brand_id']));
        $carSeries = VehicleV2Interface::getSeriesById(array('series_id'=>$carInfo['series_id']));
        $carModel = array();
        if( $carInfo['model_id'] ) {
            $carModel = VehicleV2Interface::getModelById(array('model_id'=>$carInfo['model_id']));
        }
        $carImages = CarAttachInterface::getImageInfoByCar(array('id'=>$carInfo['id']));
        $carInfo2 = MbsCarSaleInterface::saleDetail(array('carId' => $carInfo['id']));
        //设置跟单人名称
        $carInfo['followUserInfo'] = $carInfo2['followUserInfo'];
        $post_params = array(
            'info'=>$carInfo,
            'brand'=>$carBrand,
            'series'=>$carSeries,
            'model'=>$carModel,
            'images'=>$carImages,
        );
        Sync58InfoInterface::addPost($post_params);
        return true;
    }

    /***
     * 获取门店余额
     * ***/
    public function getSyncDeptInfo( $params ) {
        $dept_info = SyncDeptInfoInterface::getDeptInfoById(array('dept_id'=>$params['dept_id']));
        return $dept_info;
    }

    /*****
     * 设置合作站车源刷新队列
     * ****/
    public function setRefreshQueue( $params ) {
        $ids = explode(',', $params['ids']);

        if( $ids ) {
            foreach( $ids as $id ) {
                $sinfo = SyncInfoInterface::getSyncSaleInfoById(array('id'=>$id));
                $last_refresh_day = date('Ymd', $sinfo['last_refresh_time']);
                $today = date('Ymd');
                if( !$sinfo['last_refresh_time'] || ($today - $last_refresh_day) ) {
                    $info = array(
                        'id'=>$id,
                        'last_refresh_time'=>time(),
                    );
                    $ret = SyncInfoInterface::updatePost($info);
                    if( $ret ) {
                        SyncInfoInterface::setRefreshQueue(array(
                            'id'=>$id,
                        ));
                    }
                    #return array('errorCode' => 0, 'msg' => 'success');
                }else{
                    #return array('errorCode' => 2, 'msg' => '今天已经刷新过了');
                }
            }
        }else{
            #return array('errorCode' => 1, 'msg' => '参数不全');
        }
        return array('errorCode' => 0, 'msg' => 'success');
    }

    /****
     * 设置合作站车源置顶队列
     * ***/
    public function setTopQueue( $params ) {
        $id = intval($params['id']);
        $login_dept_id = $params['dept_id'];
        if( $id ) {
            $sinfo = SyncInfoInterface::getSyncSaleInfoById(array('id'=>$id));
            if( !$sinfo['last_top_time'] ) {
                //获取车源信息
                $sale_info = CarSaleInterface::getCarInfoById(array('id'=>$sinfo['car_sale_id']));
                $dept_info = SyncDeptInfoInterface::getDeptInfoById(array('dept_id'=>$sale_info['store_id']));
                if( $dept_info['balance'] <= 0 )
                    return array('errorCode' => 5, 'msg' => '已没有余额，请续费');

                if( $login_dept_id == $sale_info['store_id'] ) {
                    $info = array(
                        'id'=>$id,
                        'last_top_time'=>time(),
                    );
                    $ret = SyncInfoInterface::updatePost($info);
                    if( $ret !== false ) {
                        $r = SyncInfoInterface::setTopQueue(array(
                            'id'=>$id,
                        ));
                        if( !$r ) return array('errorCode' => 4, 'msg' => '出现错误。');
                        //先扣款，队列后请求http，进行确认扣款，否则还回已扣的款项
                         $amount = 5;    //每次置顶扣款5元
                        SyncDeptInfoInterface::cutBalance(array(
                            'dept_id'=>$login_dept_id,
                            'amount'=>$amount,
                            'loginfo'=>array(
                                'dept_id'=>$login_dept_id,
                                'userid'=>$this->userInfo['user']['id'],
                                'username'=>$this->userInfo['user']['real_name'],
                                'amount'=>$amount,
                                'description'=>'置顶预扣款',
                                'create_time'=>time(),
                            ),
                        ));
                    }
                    return array('errorCode' => 0, 'msg' => '置顶设置成功');
                }else{
                    return array('errorCode' => 3, 'msg' => '非法请求！');
                }
            }else{
                return array('errorCode' => 2, 'msg' => '已置顶');
            }
        }else{
            return array('errorCode' => 1, 'msg' => '参数不全');
        }
        return array('errorCode' => 4, 'msg' => '出现错误。');
    }

    /***
     * 删除某车源在所有合作站上的记录
     * ***/
    public function deletePost( $params ) {
        //一次只允许删除一条车源信息相关记录
        $car_sale_id = intval($params['car_sale_id']);
        $arr = explode(',', $car_sale_id);
        $arr = array('ids'=>$arr);
        #print_r($params);

        $syncList = SyncInfoInterface::getSyncSaleInfoList($arr);
        print_r($syncList);
        if( $syncList ) {
            foreach( $syncList as $k=>$v ) {
                if( $v['sync_site_id'] == 1 ) { //58
                    Sync58InfoInterface::deletePost(array('infoid'=>$v['sync_site_post_id'], 'dept_id'=>$params['dept_id']));
                }
                if( $v['sync_site_id'] == 2 ) { //xcar
                }
            }
        }
    }

    /***
     * 获取刷新队列
     * ***/
    public function getRefreshQueue( $params ) {
        //http://appserv.273.cn/1.0/sync2site.getRefreshQueue?_api_time=510b60b65b41080750475c832f843f22&_api_token=80af186967ea0a2ad92f0b483ae48471&_api_key=fa1f58046f169f08d3ebf086a11399e4&limit=1
        //https://192.168.5.31/1.0/sync2site.getRefreshQueue?_api_time=510b60b65b41080750475c832f843f22&_api_token=80af186967ea0a2ad92f0b483ae48471&_api_key=fa1f58046f169f08d3ebf086a11399e4&limit=1
        $limit = intval($params['limit']) ? intval($params['limit']) : 2;
        for( $i=0;$i<$limit;$i++ ) {
            #移出队列，并取值
            $res = SyncInfoInterface::popRefreshQueue();
            if( $res ) {
                $res = unserialize($res);
                if( !is_array($res) ) continue;
                $sinfo = SyncInfoInterface::getSyncSaleInfoById(array('id'=>$res['id']));
                $carInfo = CarSaleInterface::getCarDetail(array('id' => $sinfo['car_sale_id']));
                $dept_token_info = SyncInfoInterface::getDeptSyncSiteInfo(array(
                    'dept_id'=>$carInfo['store_id'], 'sync_site_id'=>$sinfo['sync_site_id']));
                $array[] = array(
                    'sinfo'=>$sinfo,
                    'dept_token'=>$dept_token_info[0],
                );
            }
        }
        return $array;
    }

    /****
     * 获取置顶队列
     * ****/
    public function getTopQueue( $params ) {
        //http://appserv.273.cn/1.0/sync2site.getTopQueue?_api_time=510b60b65b41080750475c832f843f22&_api_token=80af186967ea0a2ad92f0b483ae48471&_api_key=fa1f58046f169f08d3ebf086a11399e4&limit=2
        $limit = intval($params['limit']) ? intval($params['limit']) : 2;
        for( $i=0;$i<$limit;$i++ ) {
            #移出队列，并取值
            $res = SyncInfoInterface::popTopQueue();
            if( $res ) {
                $res = unserialize($res);
                if( !is_array($res) ) continue;
                $sinfo = SyncInfoInterface::getSyncSaleInfoById(array('id'=>$res['id']));
                $carInfo = CarSaleInterface::getCarDetail(array('id' => $sinfo['car_sale_id']));
                $dept_token_info = SyncInfoInterface::getDeptSyncSiteInfo(array(
                    'dept_id'=>$carInfo['store_id'], 'sync_site_id'=>$sinfo['sync_site_id']));
                $array[] = array(
                    'sinfo'=>$sinfo,
                    'dept_token'=>$dept_token_info[0],
                );
            }
        }
        return $array;
    }

    /***
     * 获取273与对应网站的车系对应关系
     * $param $params array(
     *     'sync_site_id'=>1:58,2:xcar
     * )
     * ***/
    public function getCarsHook( $params ) {
        if( $params['sync_site_id'] == 1 ) {
            $data = Sync58CommonData::getCarsHook();
            if( $data ) {
                foreach($data as $k=>$v ) {
                    $v['name'] = $k;
                    $new[$v['id']] = $v;
                }
            }
            $data = $new;
        }
        return $data;
    }
    
    /*
     * @brief 手机端同步到58同城,爱卡网
     * @author guoch
     * @params array(
     *     brand_id => 273品牌id
     *     series_id => 273车系id
     *     model_id => 273车型id
     *     sync_58 => 同步到58同城
     *     map_bid_58 => 58品牌id
     *     map_sid_58 => 58车系id
     *     map_mid_58 => 58车型id
     *     map_bname_58 => 58品牌name
     *     map_sname_58 => 58车系name
     *     map_mname_58 => 58车型name
     *     sync_xcar => 同步到爱卡网
     *     map_bid_xcar => 爱卡品牌id
     *     map_sid_xcar => 爱卡车系id
     *     map_mid_xcar => 爱卡车型id
     *     map_bname_xcar => 爱卡品牌name
     *     map_sname_xcar => 爱卡车系name
     *     map_mname_xcar => 爱卡车型name
     *     car_id => 车源id
     * )
     * @return int 1 加入同步队列成功，0加入同步队列失败
     */
    public function Sync2OtherSite($params) {
        try {
            //人工匹配外站车系车型功能
            $bid = intval($params['brand_id']);
            $sid = intval($params['series_id']);
            $mid = intval($params['model_id']);
    
            $sync_58 = $params['sync_58'];
            if( $sync_58 ) {
                //假如需要同步至58，并且品牌，车系，车型都选中，则将对应关系入库
                $map_bid_58 = $params['map_bid_58'];
                $map_sid_58 = $params['map_sid_58'];
                $map_mid_58 = $params['map_mid_58'];
                $map_bname_58 = $params['map_bname_58'];
                $map_sname_58 = $params['map_sname_58'];
                $map_mname_58 = $params['map_mname_58'];
                //品牌，车系为必须，车型id非必须
                if( $bid && $sid && $map_bid_58 && $map_sid_58 ) {
                    $map_array = array(
                            'bid'=>$bid,
                            'sid'=>$sid,
                            'mid'=>$mid,
                            'map_bid'=>$map_bid_58,
                            'map_sid'=>$map_sid_58,
                            'map_mid'=>$map_mid_58,
                            'map_bname'=>$map_bname_58,
                            'map_sname'=>$map_sname_58,
                            'map_mname'=>$map_mname_58,
                            'sync_site_id'=>1,
                    );
                    SyncMapInterface::insertModelMap($map_array);
                }
            }
    
            $sync_xcar = $params['sync_xcar'];
            if( $sync_xcar ) {
                $map_bid_xcar = $params['map_bid_xcar'];
                $map_sid_xcar = $params['map_sid_xcar'];
                $map_mid_xcar = $params['map_mid_xcar'];
                $map_bname_xcar = $params['map_bname_xcar'];
                $map_sname_xcar = $params['map_sname_xcar'];
                $map_mname_xcar = $params['map_mname_xcar'];
                //品牌, 车系, 车型均必须
                if( $bid && $sid && $mid && $map_bid_xcar && $map_sid_xcar && $map_mid_xcar ) {
                    $map_array = array(
                            'bid'=>$bid,
                            'sid'=>$sid,
                            'mid'=>$mid,
                            'map_bid'=>$map_bid_xcar,
                            'map_sid'=>$map_sid_xcar,
                            'map_mid'=>$map_mid_xcar,
                            'map_bname'=>$map_bname_xcar,
                            'map_sname'=>$map_sname_xcar,
                            'map_mname'=>$map_mname_xcar,
                            'sync_site_id'=>SyncCommonVars::$SITE_CONFIG['xcar'],
                    );
                    SyncMapInterface::insertModelMap($map_array);
                }
            }
            //人工匹配外站车系车型功能 end
    
            //同步至其他网站，存如队列
            $sync_array = array();
            $sync_array['id'] = $params['car_id'];
    
            //同步车源数据至58同城
            if( $sync_58 ) {
                $sync_array['s58'] = 1;
            }
            if( $sync_xcar ) {
                $sync_array['xcar'] = 1;
            }
            if( $sync_58 || $sync_xcar ) {
                //更新已同步字段
                $up_arr = array(
                        'info'=>array( 'is_sync'=>'1',),
                        'filter'=>array(0=>array( 'id','=',$params['car_id'],),),
                );
                CarSaleInterface::updateCarInfo($up_arr);
                SyncInfoInterface::setQueue($sync_array);
            }
            //同步至其他网站，存如队列 end
            return 1;
        } catch (Exception $e) {
            //加入队列失败，记录日志
            LoggerGearman::logInfo(array('data'=>$params, 'identity'=>'Sync2OtherSite'));
            return 0;
        }
    }
    /*
     * @brief 获取车系
    * @author guoch
    * params type 'xcar':爱卡网；'58':58同城
    * params int bid brand_id
    * return array(
    *   sid => 车系id，
    *   name => 车系名称
    *   
    * )
    */
    public function getSeriesCacheAction($params) {
        $type = $params['type'];
        $bid = $params['brand_id'];
        if(empty($type) || empty($bid)) {
            throw new AppServException(AppServErrorVars::CUSTOM, '参数不完整');
        }
        switch($type) {
            case '58':
                $list = Sync58InfoInterface::get58Sid2SnameCache();
                
                //格式化数据
                if( $list[$bid] ) {
                    $rs = sort($list[$bid]);
                    $ret = array();
                    
                    foreach($list[$bid] as $key => $value) {
                        $ret[$key]['id'] = $value['sid'];
                        $ret[$key]['name'] = $value['name'];
                    }
                    
                    return $ret;
                }
                break;
            case 'xcar':
                $list = SyncXcarInterface::getPsid2PsnameCache();
                
                //格式化数据
                if( $list[$bid] ) {
                    sort($list[$bid]);
                    
                    foreach($list[$bid] as $key => $value) {
                        $ret[$key]['id'] = $value['psid'];
                        $ret[$key]['name'] = $value['psname'];
                    }
                    
                    return $ret;
                }
                break;

            case 'ganji':
                $list = CarSyncInterface::getSiteBrandData('ganji');
                if( $list[$bid]['series'] ) {
                    $list = $this->sortArray($list[$bid]['series'], 'map_sname');
                    
                    foreach($list as $key => $value) {
                        $ret[$key]['id']   = $value['map_sid'];
                        $ret[$key]['name'] = $value['map_sname'];
                    }
                    //print_r($ret); exit;
                    return $ret;
                }
                break;
                
            case 'esc':
                $list = CarBrandOutEsczjInterface::getSeriseName(array('brand_id' => $bid));
                if($list) {
                    //$list = $this->sortArray($list, 'name');
                    foreach($list as $key => $value) {
                        $ret[$key]['id']   = $value['sid'];
                        $ret[$key]['name'] = $value['sname'];
                    }
                    //print_r($ret); exit;
                    return $ret;
                }
                break;
                              
            default:
                return 0;
                break;
        }
    }
   /*
    * @brief 获取车型
    * @author guoch
    * params string type 'xcar':爱卡网；'58':58同城
    * params int sid 车系id(series_id)
    * return array(
    *       mid  => 车型id
    *       text => 车型名称
    *       sid  => 车系id
    *       bid  => 品牌id
    * )
    */
    public function getModelsCacheAction($params) {
        $type = $params['type'];
        $series_id = $params['series_id'];
        switch($type) {
            case '58':
                $list = Sync58InfoInterface::get58Mid2MnameCache();
                if( $list[$series_id] ) {
                    rsort($list[$series_id]);
                    $rs = $list[$series_id];
                    foreach($rs as $k => $v) {
                        $ret[$k]['id'] = $v['mid'] ? $v['mid'] : '';
                        $ret[$k]['model_name'] = $v['name'] ? $v['name'] : '';
                    }
                    
                    return $ret;
                }
                return array(
                            0 =>array('id' => 0,
                                      'model_name' =>'不限',
                        )
                );
                break;
            case 'xcar':
                $list = SyncXcarInterface::getMid2MnameCache();
                if( $list[$series_id] ) {
                    rsort($list[$series_id]);
                    $rs = $list[$series_id];
                    foreach($rs as $k => $v) {
                        $ret[$k]['id'] = $v['mid'] ? $v['mid'] : '';
                        $ret[$k]['model_name'] = $v['name'] ? $v['name'] : '';
                    }
                    return $ret;
                }
                return array(
                        'id' => 0,
                        'model_name' =>'不限',
                );
                break;
                
            case 'esc':
                $list = CarBrandOutEsczjInterface::getModelName(array('sid' => $series_id));
                if($list) {
                    foreach($list as $k => $v) {
                        $ret[$k]['id'] = $v['mid'] ? $v['mid'] : '';
                        $ret[$k]['model_name'] = $v['mname'] ? $v['mname'] : '';
                    }
                    //print_r($ret); exit;
                    return $ret;
                }
                return array(
                        'id' => 0,
                        'model_name' =>'不限',
                );
                break;              
                
            default:
                break;
        }
    }
    /*
     * @brief 获取品牌
     * @author guoch
     * params type 'xcar':爱卡网；'58':58同城
     * return array(
     *      id => 品牌id
     *      name =>品牌名称
     * )
     */
    public function getBrandsCacheAction($params) {
        $type = $params['type'];
        $typeId = $params['type_id'];
        if ($type == 'xcar') {
            $list = SyncXcarInterface::getPbid2PbnameCache();
        } elseif ($type == '58') {
            $list = Sync58InfoInterface::get58Bid2BnameCache();
            asort($list);
        } elseif ($type=='ganji'){
            $list = CarSyncInterface::getSiteBrandData($type);
        } elseif ($type == 'baixing') {
            $list = CarSyncInterface::getSiteBrandData($type);
            $brandBaixingType = $typeId ? $typeId : 1;
            $brandBaixingType = $list['type_match'][$brandBaixingType];
            $list = $list['brand_data'][$brandBaixingType]['brand'];
        } elseif ($type == 'baixing_m') {
            $list = CarSyncInterface::getSiteBrandData('baixing');
            $brandBaixingType = $typeId ? $typeId : 1;
            $brandBaixingType = $list['type_match'][$brandBaixingType];
            $list = $list['brand_data'][$brandBaixingType]['type'];
        } elseif ($type == 'esc') {
            $list = CarBrandOutEsczjInterface::getBrandName();
        }

        if( $list ) {
            foreach( $list as $bid=>$bname ) {
                if($type == 'ganji') {
                    $bname = $bname['name'];
                } else if ($type == 'baixing' || $type == 'baixing_m') {
                    $bid   = str_replace('|*|m', '', '|*|' . $bname['value']);
                    $bname = $bname['label'];
                } else if ($type == 'esc') {
                    $bid   = $bname['brand_id'];
                    $bname = $bname['name'];
                }
                $bname = trim($bname);
                if ($type == 'baixing_m') {
                    $initial = ' ';
                } else {
                    $initial = substr($bname, 0, 1);
                    $bname   = str_replace('|start|' . $initial, '', '|start|' . $bname);
                }
                $arr[] = array(
                            'id' => $bid,
                            'initial' => $initial,
                            'name' => $bname,
                );
            }
            return $arr;
        }
    }
    
    /***
     * @desc 排序
    * **/
    private function sortArray( $data, $sortName='name', $sortType='ksort' ) {
        foreach( $data as $k=>$v ) {
            $tmp[$v[$sortName]] = $v;
        }
        if( $sortType == 'ksort' ) {
            ksort($tmp);
        }else{
            krsort($tmp);
        }
        return array_values($tmp);
    }
}
