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
require_once API_PATH . '/interface/MbsDeptInterface.class.php';
require_once API_PATH . '/interface/MbsUserInterface.class.php';
require_once API_PATH . '/interface/sync2site/SyncDeptInfoInterface.class.php';
class DeptServiceApp {

    public function getDeptByCity($params) {
        if (!empty($params['_app_type']) && $params['_app_type'] == 1) {
            //客户端掉错接口，为了解决兼容问题，临时解觉办法
            if (!isset($params['city_id']) || !$params['city_id']) {
                throw new AppServException(AppServErrorVars::CUSTOM, '城市id参数错误');
            }
            $deptInfoArray = MbsDeptInterface::getDeptsByCity(array('id' => $params['city_id']));
            require_once API_PATH . '/interface/CarSaleInterface.class.php';
            $deptRet = array();
            foreach ($deptInfoArray as $key => $dept) {
                $deptInfo = array();
                if ($dept['id'] == 1354) {
                    continue;
                }
                $deptInfo['id'] = $dept['id'] ? $dept['id'] : '';
                $deptInfo['dept_name'] = $dept['dept_name'] ? $dept['dept_name'] : '';
                $deptInfo['address'] = $dept['address'] ? $dept['address'] : '';
                $deptInfo['telephone'] = $dept['telephone'] ? $dept['telephone'] : '';
                $deptInfo['province'] = $dept['province'] ? $dept['province'] : '';
                $deptInfo['city'] = $dept['city'] ? $dept['city'] : '';
                $deptInfo['photo'] = $dept['photo'] ? $dept['photo'] : '';
                $deptInfo['shop_pic'] = $dept['shop_pic'] ? $dept['shop_pic'] : '';
                $deptInfo['shop_point'] = str_replace('，', ',', $dept['shop_point']);
                $deptInfo['insert_time'] = $dept['insert_time'] ? $dept['insert_time'] : '';
                $deptRet[] = $deptInfo;
            }
            return $deptRet;
        }
        if (!isset($params['city_id']) || !$params['city_id']) {
            throw new AppServException(AppServErrorVars::CUSTOM, '城市id参数错误');
        }
        $deptInfoArray = MbsDeptInterface::getDeptsByCity(array('id' => $params['city_id']));
        require_once API_PATH . '/interface/CarSaleInterface.class.php';
        $deptRet = array();
        foreach ($deptInfoArray as $key => $dept) {
            $deptInfo = array();
            if ($dept['id'] == 1354) {
                continue;
            }
            $deptInfo['id'] = $dept['id'] ? $dept['id'] : '';
            $deptInfo['dept_name'] = $dept['dept_name'] ? $dept['dept_name'] : '';
            $deptInfo['address'] = $dept['address'] ? $dept['address'] : '';
            $deptInfo['telephone'] = $dept['telephone'] ? $dept['telephone'] : '';
            $deptInfo['province'] = $dept['province'] ? $dept['province'] : '';
            $deptInfo['city'] = $dept['city'] ? $dept['city'] : '';
            $deptInfo['photo'] = $dept['photo'] ? $dept['photo'] : '';
            $deptInfo['shop_pic'] = $dept['shop_pic'] ? $dept['shop_pic'] : '';
            $deptInfo['shop_point'] = str_replace('，', ',', $dept['shop_point']);
            $deptInfo['insert_time'] = $dept['insert_time'] ? $dept['insert_time'] : '';
            $manage = MbsUserInterface::getManageUserByDept(array('dept_id' => $dept['id']));
            $deptInfo['manage_name'] = $manage['real_name'] ? $manage['real_name'] : '';
            $postInfo = CarSaleInterface::search(array('status' => 1,
                        'store_id' => $dept['id'],
                        'limit' => 10,
                        'offset' => 0
            ));
            $deptInfo['car_total'] = $postInfo['total'] ? $postInfo['total'] : 0;
            $begin = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
            $end = time();
            $postInfo = CarSaleInterface::search(array('status' => 1,
                        'store_id' => $dept['id'],
                        'create_time' => array($begin, $end),
                        'limit' => 10,
                        'offset' => 0
            ));
            $deptInfo['car_add_total'] = $postInfo['total'] ? $postInfo['total'] : 0;
            $deptRet[] = $deptInfo;
        }
        return $deptRet;
    }
    
    /**
     * 根据城市id取得城市下所有status=1的门店 车况宝用
     * @param array $params(
     *      id => 城市id
     * )
     * @throws AppServException
     * @return array(
     *      id => 门店id,
     *      dept_name => 门店名
     * )
     */
    public function getDeptsByCity($params) {
        if (empty($params['id'])) {
            throw new AppServException(AppServErrorVars::CUSTOM, '城市id参数错误');
        }
        $deptList = MbsDeptInterface::getDeptsByCity(array('id' => $params['id']));
        $deptRet = array();
        foreach ((array) $deptList as $dept) {
            $deptInfo = array();
            if ($dept['id'] == 1354) {
                continue;
            }
            $deptInfo['id'] = $dept['id'];
            $deptInfo['dept_name'] = $dept['dept_name'];
            $deptInfo['domain'] = $dept['domain'];
            $deptRet[] = $deptInfo;
        }

        require COM_PATH . '/check/CheckVars.class.php';
        foreach (CheckVars::$SPECIAL_CHECK_TYPE as $kid => $tname) {
            $deptRet[] = array('id' => $kid, 'dept_name' => $tname);
        }

        return $deptRet;
    }

    public function getDeptCarNum($params) {
        require_once API_PATH . '/interface/CarSaleInterface.class.php';
        if (!isset($params['store_id']) || !$params['store_id']) {
            throw new AppServException(AppServErrorVars::CUSTOM, '门店id参数错误');
        }
        $ret = array();
        $postInfo = CarSaleInterface::search(array('status' => 1,
                    'store_id' => $params['store_id'],
                    'limit' => 10,
                    'offset' => 0
        ));
        $ret['car_total'] = $postInfo['total'] ? $postInfo['total'] : 0;
        $begin = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        $end = time();
        $postInfo = CarSaleInterface::search(array('status' => 1,
                    'store_id' => $params['store_id'],
                    'create_time' => array($begin, $end),
                    'limit' => 10,
                    'offset' => 0
        ));
        $ret['car_add_total'] = $postInfo['total'] ? $postInfo['total'] : 0;
        return $ret;
    }

    public function getAllDeptPoint($params) {
        return MbsDeptInterface::getAllDeptPoint();
    }

    public function getPhoneBanlance($params) {
        
    }

    public function isExtPhone($params) {
        
    }

    public function getDeptClauseStatus($params) {
        try {
            include_once API_PATH . '/interface/MbsDeptClauseInterface.class.php';            
            $clauseParams = array(
                'fields' => 'mbs_fee_overdraft,loan_overdraft,affiliate_fee_overdraft,debt_overdraft,
                interest_overdraft,tech_service_overdraft,tech_use_overdraft,status,overdraft_time',
                'cond' => array(
                    'dept_id' => $params['dept_id']
                )
            );
            $clauseInfo = MbsDeptClauseInterface::get($clauseParams);            
            return !empty($clauseInfo) ? $clauseInfo : array();
        } catch (AppServException $e) {
            throw new AppServException($e->getMessage());
        }
    }
    public function getDeptRuleDetail($params) {
    	try {
    		include_once API_PATH . '/interface/MbsDeptPaymentRuleDetailInterface.class.php';
    		$clauseParams = array(
    				'fields' => '*',
    				'cond' => array(
    						'dept_id' => $params['dept_id'],
    						'day'     => $params['day'],
    						'end_time>=' => $params['end_time'],
    						'pay_time<=' => $params['pay_time'],
    						'flag'    => 1,
    				)
    		);
    		$ruleDetailInfo = MbsDeptPaymentRuleDetailInterface::getList($clauseParams);
    		return !empty($ruleDetailInfo) ? $ruleDetailInfo : array();
    	} catch (AppServException $e) {
    		throw new AppServException($e->getMessage());
    	}
    }

    /**
     * @desc 通过id获取门店信息
     * @author chenchaoyang
     */
    public function getDeptById($param) {
        if ($param['dept_id']) {
            return MbsDeptInterface::getDeptInfoByDeptId(array('id'=>$param['dept_id']));
        } else {
            return null;
        }
        
    }
    
    /**
     * @author guoch
     * @desc 通过门店id获取
     * @params array('dept_id' =>门店id)
     * @return array(
     *          published_count=>已发布条数
     *          publish_able => 本季度还可发布条数
     *          sync_source => 客户端展示
     *          is_empty => 本季度是否有同步条数，没有同步条数时为1，有同步条数为0
     * )
     * 
     */
    public function getSyncCount($params) {
        //检查参数是否为空
        if(empty($params['dept_id'])) {
            return false;
        }
        
        //构造查询参数
        $searchParams['dept_id'] = $params['dept_id'];
        $searchParams['sync_site_id'] = 1;
        
        //查询可同步到58同城的参数
        $dept_info = SyncDeptInfoInterface::getDeptInfoById($searchParams);
        
        //58同城
        $published = $dept_info['published_count'] ? $dept_info['published_count'] : 0;
        $total = $dept_info['publish_count_total'] ? $dept_info['publish_count_total'] : 0;
        $publishAble = $total - $published;

        $ret[0]['name'] = '58同城';
        $ret[0]['type'] = '58';
        $ret[0]['state'] = '本季度还可以发布'.$publishAble . '，已发布' . $published;
        //本季度是否有同步条数，没有同步条数时为1，有同步条数为0
        $ret[0]['disable'] = empty($ret[0]['publish_able']) ? 0 : 1;
        
        //爱卡汽车
        $ret[1]['name'] = '爱卡汽车';
        $ret[1]['type'] = 'xcar';
        $ret[1]['state'] = '无发布数量限制';
        $ret[1]['disable'] = 0;
        return $ret;
    }
    
    /**
     * 取门店列表
     * @param   : 参数说明如下表格
     * 参数名称     | 参数类型 | 参数补充描述
     * ------------|----------|------------------------------------------------
     * city_id    | string   | 城市ID
     * 
     * @return array
     * 返回值名称   | 返回值类型   | 返回值补充描述
     * ------------|----------|------------------------------------------------
     * id    | string   | 用户名
     * dept_name    | string   | 门店名称
     */
    public function getDeptList($params) {
        if (empty($params['city_id'])) {
            throw new AppServException(AppServErrorVars::CUSTOM, '城市id参数错误');
        }
        $deptLists = MbsDeptInterface::getDeptsByCity(array('id' => $params['id']));
        $deptList = array();
        foreach ((array) $deptLists as $dept) {
            $deptInfo = array();
            if ($dept['id'] == 1354) {
                continue;
            }
            $deptInfo['id'] = $dept['id'];
            $deptInfo['dept_name'] = $dept['dept_name'];
            $deptInfo['domain'] = $dept['domain'];
            $deptList[] = $deptInfo;
        }

        return $deptList;
    }
}
