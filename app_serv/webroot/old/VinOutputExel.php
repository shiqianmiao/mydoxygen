<?php
/**
 * @brief 车源发车vin奖励人员工资表导出
 * @version 1.0
 * @athor 缪石乾
 * @date 2014-10-30
 */
error_reporting(1);
ini_set('display_errors', 1);
ini_set('memory_limit','1024M');
set_time_limit(0);
require_once dirname(__FILE__) . '/../../../conf/config.inc.php';
require_once COM_PATH . '/car/CarAppVars.class.php';
require_once FRAMEWORK_PATH . '/util/db/DBMysqli.class.php';
require_once CONF_PATH . '/db/DBConfig.class.php';
require_once API_PATH . '/interface/mbs/MbsUserInterface2.class.php';
require_once API_PATH . '/interface/mbs/MbsDeptInterface2.class.php';
require_once API_PATH . '/interface/LocationInterface.class.php';

class VinOutputExcel {
    /**
     * @brief 执行
     * @param $year 要统计的年份 例如：2014
     * @param $month 要统计的月份 例如：10
     */
    public function run($year = null, $month = null) {
        if (empty($year) || empty($month)) {
            exit("请传入要统计的起始时间");
        }
        
        //获取某月份所有获得vin奖励的员工的数据
        $dbMbsMaster = DBMysqli::createDBHandle(DBConfig::$SERVER_SLAVE, DBConfig::DB_MBS);
        $rewardSql   = "select * from mbs.pub_reward_user where year={$year} and month={$month} and amount > 0 order by city_id asc";
        $rewardInfo  = DBMysqli::queryAll($dbMbsMaster, $rewardSql);
        if (empty($rewardInfo) || !is_array($rewardInfo)) {
            exit('无数据');
        }
        
        $data = array();
        //数据整理，有些多人用同一帐号的需要合并
        foreach ($rewardInfo as $re) {
            $user = MbsUserInterface2::getInfoByUser(array('username' => $re['username']));
            if (empty($user)) {
                continue;
            }
            $deptInfo = MbsDeptInterface2::getDeptInfoById(array('id' => $re['dept_id']));
            if (empty($deptInfo)) {
                continue;
            }
            $locationInfo = LocationInterface::getCityById(array('city_id' => $re['city_id']));
            /*if (!empty($user['bank_card']) && isset($data[$user['bank_card']])) {
                $data[$user['bank_card']]['amount'] += $re['amount'];
            } else if (!empty($user['bank_card'])) {
                $data[$user['bank_card']] = array(
                    'location' => $locationInfo['name'] . '-' . $deptInfo['dept_name'],
                    'user_name' => $user['real_name'] . '-' . $user['username'],
                    'amount' => $re['amount'],
                    'bank_card' => " " . $user['bank_card'],
                    'bank_id_card' => " " . $user['bank_id_card'],
                    'bank_name' => $user['bank_name'],
                    'dept_id' => $re['dept_id'],
                );
            } else {*/
                $data[] = array(
                    'location' => $locationInfo['name'] . '-' . $deptInfo['dept_name'],
                    'user_name' => $user['real_name'] . '-' . $user['username'],
                    'amount' => $re['amount'],
                    'bank_card' => " " . $user['bank_card'],
                    'bank_id_card' => " " . $user['bank_id_card'],
                    'bank_name' => $user['bank_name'],
                    'dept_id' => $re['dept_id'],
                );
            //}
        }
        //导出exel
        include_once FRAMEWORK_PATH . '/extend/phpexcel/PHPExcel.php';
        $objPHPExcel = new PHPExcel();
        $objPHPExcel2 = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A1', '区域')
        ->setCellValue('B1', '姓名')
        ->setCellValue('C1', '底薪')
        ->setCellValue('D1', '绩效')
        ->setCellValue('E1', '年功')
        ->setCellValue('F1', '个人业绩提成')
        ->setCellValue('G1', '补贴')
        ->setCellValue('H1', '制度补扣款')
        ->setCellValue('I1', '结转上月')
        ->setCellValue('J1', '应付工资')
        ->setCellValue('K1', '养老金')
        ->setCellValue('L1', '失业金')
        ->setCellValue('M1', '医疗保险')
        ->setCellValue('N1', '公积金')
        ->setCellValue('O1', '应税小计')
        ->setCellValue('P1', '所得税')
        ->setCellValue('Q1', '实发合计')
        ->setCellValue('R1', '银行帐号')
        ->setCellValue('S1', '身份证号')
        ->setCellValue('T1', '账户名')
        ->setCellValue('U1', '税额')
        ->setCellValue('V1', '税额');
        
        $objPHPExcel2->setActiveSheetIndex(0)
        ->setCellValue('A1', '区域-店名')
        ->setCellValue('B1', '奖励金额');
        
        $i = 2;
        
        $deptData = array(); //统计各个门店的奖励
        foreach ($data as $row) {
            if (!isset($deptData[$row['dept_id']]['amount'])) {
                $deptData[$row['dept_id']]['amount'] = $row['amount'];
                $deptData[$row['dept_id']]['location'] = $row['location'];
            } else {
                $deptData[$row['dept_id']]['amount'] += $row['amount'];
            }
            
            $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A' . $i, $row['location'])
            ->setCellValue('B' . $i, $row['user_name'])
            ->setCellValue('C' . $i, '')
            ->setCellValue('D' . $i, '')
            ->setCellValue('E' . $i, '')
            ->setCellValue('F' . $i, '')
            ->setCellValue('G' . $i, $row['amount'])
            ->setCellValue('H' . $i, '')
            ->setCellValue('I' . $i, '')
            ->setCellValue('J' . $i, $row['amount'])
            ->setCellValue('K' . $i, '')
            ->setCellValue('L' . $i, '')
            ->setCellValue('M' . $i, '')
            ->setCellValue('N' . $i, '')
            ->setCellValue('O' . $i, $row['amount'])
            ->setCellValue('P' . $i, '')
            ->setCellValue('Q' . $i, '')
            ->setCellValue('R' . $i, $row['bank_card'])
            ->setCellValue('S' . $i, $row['bank_id_card'])
            ->setCellValue('T' . $i, $row['bank_name'])
            ->setCellValue('U' . $i, '')
            ->setCellValue('V' . $i, '');
            $i++;
        }
        
        $filename = $year . '-' . $month . '发车vin奖励工资表.xls';
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        
        $d = 2;
        if (!empty($deptData) && is_array($deptData)) {
            foreach ($deptData as $deptId => $r) {
                $objPHPExcel2->setActiveSheetIndex(0)
                ->setCellValue('A' . $d, $r['location'])
                ->setCellValue('B' . $d, $r['amount']);
                $d++;
            }
        }
        $filename = $year . '-' . $month . '发车vin奖励门店工资表.xls';
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel2, 'Excel5');
        $objWriter->save('php://output');
    }
}

$obj = new VinOutputExcel();
$obj->run(2014, 11);
