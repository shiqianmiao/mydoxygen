<?php 
/**
 * @ brief 消息订阅推送测试
 * @ author guoch
 */
error_reporting(E_ALL ^ E_DEPRECATED ^ E_WARNING);
// error_reporting(0);
require_once dirname(__FILE__) . '/../../config/config.inc.php';
require_once dirname(__FILE__) . '/SubscribeServiceApp.class.php';
require_once dirname(__FILE__) . '/../../common/helper/AppServGlobalHelper.class.php';


class testSubscribeServiceApp {
    /*
     * 测试添加订阅条件
    */

    public static function testaddSubscribeCondition() {
        //订阅参数
        $test = 9;
        $params['brand_id'] = 7;
        $params['series_id'] = 4;
        $params['model_id'] = 4;
        $params['city_id'] = 5;
        $params['price'] = '1-100000';
        $params['car_age'] = '1-10000';
        $params['client_id'] = '540bc9d411122516d2a055dc26dbe1d4'.$test;
        $params['status'] = 2;
        
        $subscribe = new SubscribeServiceApp();
        $ret = $subscribe->addSubscribeCondition($params);
        echo "</br>\n\n".'-------------------------'."\n\n</br>";
        print_r($ret);
        echo "</br>\n\n".'-------------------------'."\n\n</br>";
    }
    
    
    /*
     * 测试根据车源信息推送到手机
    */
    public static function testpushCarMessage() {
        //车源信息参数
        $params['brand_id'] = 2;
        $params['series_id'] =3;
        $params['model_id'] =4;
        $params['city_id'] = 5;
        $params['price'] = 23;
        $params['kilometer'] = 58;
        $params['card_time'] = 58;
        $params['car_id'] = 123243;
        
        $subscribe = new SubscribeServiceApp();
        $ret = $subscribe->pushCarMessage($params);
        print_r($ret);
        echo "</br>\n\n".'-------------------------'."\n\n</br>";
    }
    
    /**
     * 测试更新手机接收推送信息状态（更改status查看返回值）
     */
    public static function testupdateConfigStatus() {
        //更新参数
        $params['status'] = 1;
        $params['client_id'] = '540bc9d411122516d2a055dc26dbe1d41';
        
        $subscribe = new SubscribeServiceApp();
        $ret = $subscribe->updateConfigStatus($params);
        print_r($ret);
        echo "\n\n".'-------------------------'."\n\n";
    }
    /**
     * 测试更新手机接收推送信息状态（更改status查看返回值）
     */
    public static function getConfig() {
        //更新参数
//         $params['status'] = 1;
        $params['client_id'] = '540bc9d411122516d2a055dc26dbe1d41';
    
        $subscribe = new SubscribeServiceApp();
        $ret = $subscribe->getConfig($params);
        print_r($ret);
        echo "\n\n".'-------------------------'."\n\n";
    }

    /**
     * @brief 更新订阅推送条件
     */
    public static function testupdateSubscribeCondition() {
        $test = 2;
        $params['id'] = 18;
        $params['brand_id'] = 70;
        $params['series_id'] = 8;
        $params['model_id'] =9;
        $params['city_id'] = 5;
//         $params['price'] = '1-100000';
//         $params['car_age'] = '1-10000';
        $params['client_id'] = '540bc9d411122516d2a055dc26dbe1d4'.$test;
        
        $subscribe = new SubscribeServiceApp();
        $ret = $subscribe->updateSubscribeCondition($params);
        print_r($ret);
        echo "\n\n".'-------------------------'."\n\n";
    }
    
    /**
     * @brief 根据手机唯一标示查询订阅条件
     */
    public static function testgetSubscribeCondition() {
//         $params['client_id'] = '540bc9d411122516d2a055dc26dbe1d41'; //测试
//         $params['client_id'] = '353327056206329';                    //android
        $params['client_id'] = '6226e581ddef018f5c403a0590fd04b73f6629ab82472ec2790e921e45430ca5';//ios
        
        $subscribe = new SubscribeServiceApp();
        $ret = $subscribe->getSubscribeCondition($params);
        print_r($ret);
        echo "\n\n" . '-------------------------' . "\n\n";
    }
    /**
     * @brief 根据车源条件id，查询相关车源列表
     */
    public static function getCarListByConditionId() {
        $params['condition_id'] = 29;
        $params['sort'] = 'price-desc,create_time-asc,card_time-asc';
        $params['limit'] = 9;
        $params['offset'] = 0;
        $subscribe = new SubscribeServiceApp();
        $ret = $subscribe->getCarListByConditionId($params);
        print_r($ret);
        echo "\n\n" . '-------------------------' . "\n\n";
    }
    
    /**
     * @brief 根据手机唯一标识码，查询相关车源列表
     */
    public static function getCarListByClientId() {
        $subscribe = new SubscribeServiceApp();
        $params['client_id'] = '540bc9d411122516d2a055dc26dbe1d42';
        $params['limit'] = 9;
        $params['offset'] = 0;
        $params['sort'] = 'price-desc,create_time-asc,card_time-asc';
        $ret = $subscribe->getCarListByClientId($params);
        print_r($ret);exit;
        echo "\n\n" . '-------------------------' . "\n\n";
    }
    
    /**
     * @brief 根据关系id删除关系表中数据 
     */
    public static function deleteSubscribeById() {
        $subscribe = new SubscribeServiceApp();
//         $params['id'] = 69;
        $params['ids'] = '70,71';
        $ret = $subscribe->deleteSubscribeById($params);
        print_r($ret);exit;
        echo "\n\n" . '-------------------------' . "\n\n";
    }
    
    /**
     * @brief 根据条件，查询车源数量
     */
    public static function getCarTotalBycondtion($params) {
        
        $test = 1;
        $params['id'] = 18;
        $params['brand_id'] = 7;
        $params['series_id'] = 8;
        $params['model_id'] =9;
        $params['city_id'] = 5;
        $params['price'] = '1-100000';
        $params['car_age'] = '1-10000';
        
        $subscribe = new SubscribeServiceApp();
        $ret = $subscribe->getCarTotalBycondtion($params);
        print_r($ret);exit;
        echo "\n\n" . '-------------------------' . "\n\n";
    }
    
    
    /**
     * @brief 根据client_id更新push_id
     */
    public static function updateClientId() {
        $subscribe = new SubscribeServiceApp();
        $params['client_id'] = '540bc9d411122516d2a055dc26dbe1d42';
        $params['push_id']   = 'abc';
        $ret = $subscribe->updateClientId($params);
        print_r($ret);exit;
        echo "\n\n" . '-------------------------' . "\n\n";
    }
    
    
    /**
     * @brief 测试模板,复制一份直接使用
     */
    public static function test() {
        $subscribe = new SubscribeServiceApp();
        $ret = $subscribe->getSubscribeCondition($params);
        echo "\n\n" . '-------------------------' . "\n\n";
    }
    
    public static function run() {
        //测试添加订阅条件(ok)
//         testSubscribeServiceApp::testaddSubscribeCondition();
        
        //测试根据车源信息 推送到手机(ok)
        // testSubscribeServiceApp::testpushCarMessage();
        
        //测试更新手机接收推送信息状态(ok)
        // testSubscribeServiceApp::testupdateConfigStatus();
        
        //测试更新订阅推送条件
//         testSubscribeServiceApp::testupdateSubscribeCondition();
        
        //测试根据手机唯一标示查询订阅条件(ok)
//         testSubscribeServiceApp::testgetSubscribeCondition();
        
        //测试根据条件id查询车源列表
//         testSubscribeServiceApp::getCarListByConditionId();
        
        //测试根据手机唯一标识码查询车源列表
        testSubscribeServiceApp::getCarListByClientId();
        
        //根据关系id删除关系表中数据 
//         testSubscribeServiceApp::deleteSubscribeById();
        //根据订阅条件获取车源数量
//            testSubscribeServiceApp::getCarTotalBycondtion();

        //根据client_id查询手机状态
//         testSubscribeServiceApp::getConfig();
        
        //根据client_id更新push_id
//         testSubscribeServiceApp::updateClientId();
        
    }
}
testSubscribeServiceApp::run();





