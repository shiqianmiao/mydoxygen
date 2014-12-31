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
require_once API_PATH . '/interface/FeedbackInterface.class.php';
require_once API_PATH . '/interface/VehicleV2Interface.class.php';
require_once API_PATH . '/interface/LocationInterface.class.php';
require_once API_PATH . '/interface/car/CarEvaluateFeedbackInterface.class.php';

class FeedbackServiceApp {
    public static $REPORT_TYPES = array(
        1   => 3,
        2   => 1,
        3   => 21,
        4   => 17,
        5   => 4,
        6   => 5,
        7   => 22,
    );

    /**
     * 意见反馈
     * @param   : 参数说明如下表格
     * 参数名称     | 参数类型 | 参数补充描述
     * ------------|----------|------------------------------------------------
     * source      | string   | 卖车app传 8
     * content     | string   | 反馈内容
     * member_id   | int      | 会员ID
     * @return array
     * 返回值名称   | 返回值类型 | 返回值补充描述
     * ------------|----------|------------------------------------------------
     * return      | int      | 1 成为 0 失败
     */
    public function add($params) {
        if (!isset($params['source']) || !isset($params['content'])) {
            throw new AppServException(AppServErrorVars::CUSTOM, '城市id参数错误');
        }
        $ret = FeedbackInterface::add($params);
        return $ret ? 1 : 0;
    }

    /**
     * 车价评估反馈
     * @param $params
     *      - exact (1：准确 ；0：不准确)
     *      - model_id
     *      - kilometer
     *      - year
     *      - month
     *      // - province 通过city来取
     *      - city
     *      - price_c 评估成交价
     * @return int
     * @throws
     */
    public function price($params) {
        $requireParams = array(
            'model_id', 'kilometer', 'year', 'month', 'city', 'exact', 'price_c',
        );

        foreach ($requireParams as $keyName) {
            if (!isset($params[$keyName])) {
                throw new AppServException(AppServErrorVars::CUSTOM, '缺少必要参数');
            }
        }

        $modelInfo = VehicleV2Interface::getModelById(array(
            'model_id'  => $params['model_id'],
        ));

        if (empty($modelInfo)) {
            throw new AppServException(AppServErrorVars::CUSTOM, '查无该车型');
        }

        $cityInfo = LocationInterface::getCityById(array(
            'city_id'   => $params['city'],
        ));
        if (empty($cityInfo)) {
            throw new AppServException(AppServErrorVars::CUSTOM, '查无该城市');
        }
        $info = array(
            'brand_id'  => (int) $modelInfo['brand_id'],
            'series_id' => (int) $modelInfo['series_id'],
            'model_id'  => (int) $params['model_id'],
            'province_id'   => (int) $cityInfo['province_id'],
            'city_id'   => (int) $cityInfo['id'],
            'kilometer' => (int) $params['kilometer'],
            'price_c'   => (int) $params['price_c'],
            'year'      => (int) $params['year'],
            'month'     => (int) $params['month'],
            'sale_name' => trim(str_replace(array('AT', 'MT'), array('自动', '手动'), $modelInfo['sale_name'])),
            'exact'     => (int) $params['exact'],
        );

        $insertId = CarEvaluateFeedbackInterface::saveInfo(array(
            'info'  => $info,
        ));
        return $insertId ? 1 : 0;
    }

    /**
     * @param $params
     *      - car_id
     *      - types
     *      - text
     *      - phone
     * @throws AppServException
     * @return int
     */
    public function report($params) {
        if (empty($params['types']) || empty($params['phone']) || $params['car_id'] <= 0) {
            throw new AppServException(AppServErrorVars::CUSTOM, '缺失参数');
        }
        include_once API_PATH . '/interface/CarSaleInterface.class.php';
        $carInfo = CarSaleInterface::getCarInfoById(array(
            'id'    => (int) $params['car_id']
        ));
        if (empty($carInfo)) {
            throw new AppServException(AppServErrorVars::CUSTOM, '车源不存在或者已下架');
        }
        include_once API_PATH . '/interface/CarCommentInterface.class.php';

        $types = explode(',', trim($params['types']));
        $reportTypes = array();
        foreach ($types as $type) {
            $type = (int) $type;
            if (isset(self::$REPORT_TYPES[$type])) {
                $reportTypes[] = self::$REPORT_TYPES[$type];
            }
        }
        $reportTypes = array_unique($reportTypes);
        if (empty($reportTypes)) {
            throw new AppServException(AppServErrorVars::CUSTOM, '投诉类型错误');
        }
        $info = array(
            'report_datetime'   => time(),
            'reporter_mobile'   => $params['phone'],
            'report_type'   => $reportTypes,
            'report_content'    => $params['text'] ? $params['text'] : '',
            'carid' => (int) $params['car_id'],
            'info_status'   => 1,
            'mbs_user_id'   => $carInfo['follow_user_id'],
            'dept_id'   => $carInfo['store_id'],
            'add_source'    => 7,
            'error_page'    => "http://www.273.cn/car/{$params[car_id]}.html",
        );
        $ret = CarCommentInterface::insertCarComment($info);
        return $ret > 0 ? 1 : 0;
    }
}