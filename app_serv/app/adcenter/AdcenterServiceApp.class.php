<?php
/**
 * @package              v3/推广中心
 * @subpackage           
 * @author               $Author: yangfei $
 * @file                 $HeadURL$
 * @version              $Rev: 8990 $
 * @lastChangeBy         $LastChangedBy: yangfei $
 * @lastmodified         $LastChangedDate: 2013-09-12 16:10:22 +0800 (Thu, 12 Sep 2013) $
 * @copyright            Copyright (c) 2012, www.273.cn
 */
class AdcenterServiceApp {

    /**
     * 车源终止，冻结，已售。同步终止同车源已置顶为终止，如果有置顶的话。
     *
     * @param 'car_id' car_sale id
     * @return string
     */
    public function cancelPayrank($params) {

        include_once(FRAMEWORK_PATH . '/util/gearman/LoggerGearman.class.php');
        if (!isset($params['car_id']) || empty($params['car_id'])) {

            return '缺少必要参数 car_id';
        }
        
        include_once(API_PATH . '/interface/MbsPayrankInterface.class.php');
        $payrankUpdateParams = array(
            'update' => array(
                'status' => 2
            ),
            'cond' => array(
                'car_id' => $params['car_id'],
                'status' => 1
            )
        );
        
        $cancelRs = MbsPayrankInterface::edit($payrankUpdateParams);
        if (!$cancelRs) {

            return '终止置顶失败，或不存在该车源置顶中记录';
        } else {

            return true;
        }
    }
}
        
            
