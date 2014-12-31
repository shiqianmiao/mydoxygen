<?php
require_once COM_PATH . '/check/CheckVars.class.php';
/**
 *
 * @author    陈朝阳<chency@273.cn>
 * @since     2013-11-16
 * @copyright Copyright (c) 2003-2013 273 Inc. (http://www.273.cn)
 * @desc      检测报告辅助函数/插件
 */
class CheckReportHelper {
    
    /**
     * 格式化检测报告
     * @param array $data
     * @return array
     */
    public function formatCheckReport($data) {
        $checkReport = array();
        $checkReport['base_info'] = CheckReportHelper::formatBasicInfo($data);
        $checkReport['complex_check'] = CheckReportHelper::outCheck($data);
        $checkReport['accident_check'] = CheckReportHelper::promiseInfo($data);
        $checkReport['soak_check'] = CheckReportHelper::steepInfo($data);
        $checkReport['check_time'] = $data['create_time_caption'];
        return $checkReport;
    }
    
     /**
     * @desc 格式化基本信息
     */
    public static function formatBasicInfo($data) {
        $basicInfo = array();
        
        $basicInfo[] = array(
            'name'  => '注册地',
            'value' => "{$data['province_caption']}-{$data['city_caption']}",
        );
        
        if (!empty($data['card_time_rep_caption'])) {
            $basicInfo[] = array(
                'name'  => '初登日期',
                'value' => $data['card_time_rep_caption'],
            );
        }
        
        $basicInfo[] = array(
            'name'  => '表显里程',
            'value' => "{$data['kilometer']}万公里",
        );
        
        
        if (!empty($data['configure_info_caption']['import_type'])) {
            $basicInfo[] = array(
                'name'  => '国产/进口',
                'value' => $data['configure_info_caption']['import_type'],
            );
        }
        
        $basicInfo[] = array(
            'name'  => '排量',
            'value' => "{$data['configure_info_caption']['car_engines']}L",
        );
        
        if (!empty($data['configure_info_caption']['trans_type'])) {
            $basicInfo[] = array(
                'name'  => '变速箱',
                'value' => $data['configure_info_caption']['trans_type'],
            );
        }
        
        if (!empty($data['transfer_num_caption'])) {
            $basicInfo[] = array(
                'name'  => '过户次数',
                'value' => $data['transfer_num_caption'],
            );
        }
        
        if (!empty($data['last_transfer_date_rep_caption'])) {
            $basicInfo[] = array(
                'name'  => '最后过户日期',
                'value' => $data['last_transfer_date_rep_caption'],
            );
        }
        
        $color = $data['color'] == 99 ? $data['color_name'] : $data['color_caption'];
        if (!empty($color)) {
            $basicInfo[] = array(
                'name'  => '车身颜色',
                'value' => $color,
            );
        }
        
        if (!empty($data['use_quality_caption'])) {
            $basicInfo[] = array(
                'name'  => '车辆用途',
                'value' => $data['use_quality_caption'],
            );
        }
        
        if (!empty($data['car_owner_type_caption'])) {
            $basicInfo[] = array(
                'name'  => '使用性质',
                'value' => $data['car_owner_type_caption'],
            );
        }
        
        if (!empty($data['maintenance_at_4s_caption'])) {
            $basicInfo[] = array(
                'name'  => '保养情况',
                'value' => $data['maintenance_at_4s_caption'],
            );
        }
        
        if (!empty($data['process_info_caption']['registration'])) {
            $basicInfo[] = array(
                'name'  => '登记证',
                'value' => $data['process_info_caption']['registration'],
            );
        }
        
        if (!empty($data['process_info_caption']['driving_license'])) {
            $basicInfo[] = array(
                'name'  => '行驶证',
                'value' => $data['process_info_caption']['driving_license'],
            );
        }
        
        if (!empty($data['process_info_caption']['year_check_time_rep'])) {
            $basicInfo[] = array(
                'name'  => '年检有效期',
                'value' => $data['process_info_caption']['year_check_time_rep'],
            );
        }
        
        if (!empty($data['process_info_caption']['safe_force_time_rep'])) {
            $basicInfo[] = array(
                'name'  => '交强险有效期',
                'value' => $data['process_info_caption']['safe_force_time_rep'],
            );
        }
        return $basicInfo;
    }

    /**
     * @desc 外观综合检测
     */
    public static function outCheck($data) {
        $outLook = array();
        $checkList = array(
                //漆膜厚度检测
                array(
                        'check_name'     => 'paint',
                        'check_data'     => 'paint_result',
                        'result'         => 'paint_check_result',
                        'check_vars'     => CheckVars::$PAINT,
                        'default_value'  => 1,
                        'is_multi'       => false,
                        'type'           => $data['type_id'],
                ),
                //漆面检测
                array(
                        'check_name'     => 'paint_face',
                        'check_data'     => 'paint_face_check',
                        'result'         => 'paint_face_check_result',
                        'check_vars'     => CheckVars::$PAINT,
                        'default_value'  => 1,
                        'is_multi'       => true,
                ),
                //外观检测
                array(
                        'check_name'     => 'out_hurt',
                        'check_data'     => 'out_hurt',
                        'result'         => 'out_hurt_result',
                        'check_vars'     => CheckVars::$OUT_HURT,
                        'default_value'  => 1,
                        'is_multi'       => true,
                ),
                //内饰检测
                array(
                        'check_name'     => 'in_hurt',
                        'check_data'     => 'in_hurt',
                        'result'         => 'in_hurt_result',
                        'check_vars'     => CheckVars::$IN_HURT,
                        'default_value'  => 1,
                        'is_multi'       => true,
                ),
        );
        foreach ($checkList as $info) {
            $outLook[$info['check_name']] = self::getCheckResult(
                        $data,
                        $info['check_name'],
                        $info['check_data'],
                        $info['result'],
                        $info['check_vars'],
                        $info['default_value'],
                        $info['is_multi'],
                        $info['type']
            );
        }
        return $outLook;
    }
    
    /**
     * @desc 取得外观综合检测的各项数据
     * @param array $data          从接口得到的所有数据
     * @param string $checkName    检测名
     * @param string $checkData    检测数据
     * @param string $result       对应的部位损害变量名
     * @param array $CheckVars     
     * @param int $defValue        正常值id
     * @param boolean $isMulti     是否是多项选择
     * @param int $type            漆膜检测独有,车辆类型(1,3,6)
     */
    public static function getCheckResult($data, $checkName, $checkData, $result, $CheckVars, $defValue = 1, $isMulti = false, $type = 0) {
        $outLook['name'] = CheckVars::$REPORT_RESULT[$checkName]['name'];
        if ($data[$result] != 0) {
            ksort($data[$checkData]);
            foreach ($data[$checkData] as $key => $value) {
                if ($isMulti) {
                    if (in_array($defValue, $value)) {
                        continue;
                    }
                    //值为无时直接跳过
                    if (in_array(9, $value)) {
                        continue;
                    }
                    $value = implode('、', $data[$checkData . '_caption'][$key]);
                    $outLook['value'] .= $CheckVars[$key] . $value . ',';
                } else {
                    if ($value['result'] == $defValue) {
                        continue;
                    }
                    $outLook['value'] .= $CheckVars[$key] . $value['result_caption'] . ',';
                }
            }
            $outLook['value'] = mb_substr($outLook['value'], 0, -1, 'utf-8');
        } else {
            $outLook['value'] = CheckVars::$REPORT_RESULT[$checkName]['result'];
            if ($checkName == 'paint') {
                if ($type == 6) {
                    $outLook['value'] = CheckVars::$REPORT_RESULT[$checkName]['result2'];
                }
            }
        }
        return $outLook;
    }

    /**
     * @desc 格式化承诺项和非承诺项
     * @param unknown $data
     */
    public static function promiseInfo($data) {
        $frame = CheckVars::$FRAME_P2P;
        $rein  = CheckVars::$REINFORCE_P2P;
        
        //结构件检测承诺项目START
        $frameList = array();
        foreach ((array) $frame as $key => $value) {
            $frameList[$key]['name']   = CheckVars::$FRAME[$value];
            if (!empty($data['frame_check'][$value]) && in_array($data['frame_check'][$value], array(2, 3))) {
                $frameList[$key]['value']  = '* ';
            }
            $frameList[$key]['value']  .= $data['frame_check_caption'][$value];
        }
        //结构件检测承诺项目END
        
        //加强件检测非承诺项目 START
        $reinList = array();
        foreach ((array) $rein as $key => $value) {
            $reinList[$key]['name']   = CheckVars::$REINFORCEMENT[$value];
            if (!empty($data['reinforcement_check'][$value]) && in_array($data['reinforcement_check'][$value], array(2, 3))) {
                $reinList[$key]['value']  = '* ';
            }
            $reinList[$key]['value']  .= $data['reinforcement_check_caption'][$value];
        }
        //加强件检测非承诺项目(只显示有问题的项目) END
        
        /* 覆盖件检测-------------------start */
        $panel = CheckVars::$COVER;
        $panelList = array();
        $i = 0;//下面数组的自增ID
        foreach ((array) $panel as $key => $value) {
            $panelList[$i]['name']   = $value;
            if (!empty($data['panel_check'][$key]) && $data['panel_check'][$key] != 1) {
                $panelList[$i]['value']  = '* ';
            }
            $panelList[$i]['value']  .= $data['panel_check_caption'][$key];
            $i++;
        }
        
        return array(
            'frameList' => $frameList,
            'reinList'  => $reinList,
            'coverList' => $panelList,
        );
    }
    
    //浸泡车检测非承诺项目
    public static function steepInfo($data) {
        $steep = CheckVars::$STEEP;
        $steepList = array();
        $i = 0;//下面数组的自增ID
        foreach ((array) $steep as $key => $value) {
            $steepList[$i]['name']   = $value;
            if (!empty($data['steep_check'][$key]) && ($data['steep_check'][$key] != 2) ) {
                $steepList[$i]['value']  = '* ';
            }
            $steepList[$i]['value'] .= $data['steep_check_caption'][$key];
            $i++;
        }
        return $steepList;
    }
}