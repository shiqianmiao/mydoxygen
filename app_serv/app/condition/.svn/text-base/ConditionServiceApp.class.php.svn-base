<?php
require_once API_PATH . '/interface/SsoInterface.class.php';

class ConditionServiceApp {
    
    private $_isManager = false;
    
    public function __construct() {
        if (array_key_exists('check.manager', (array)AppServAuth::$userInfo['permisssions'])) {
            $this->_isManager = true;
        }
    }
    
    /**
     * @desc pad端上传数据
     */
    public function publish($params) {
        $params['user'] = AppServAuth::$userInfo['user'];
        
        if ($params['version_tag'] == '1'){//版本号：0旧版，1新版
            require_once API_PATH . '/interface/checkForOffline/CarConditionForOfflineInterface.class.php';
            return  CarConditionForOfflineInterface::publish($params);//新版接口
        } else {
            require_once API_PATH . '/interface/CarConditionInterface.class.php';
            return  CarConditionInterface::publish($params);
        }
        
    }
    
    /**
     * @desc 根据id取得检测数据
     */
    public function getInfoById($params) {
        
        if ($params['version_tag'] == '1'){//版本号：0旧版，1新版
            require_once API_PATH . '/interface/checkForOffline/CarConditionForOfflineInterface.class.php';
            return  CarConditionForOfflineInterface::getInfoById($params);//新版接口
        } else {
            require_once API_PATH . '/interface/CarConditionInterface.class.php';
            return  CarConditionInterface::getInfoById($params);
        }
        
    }
    
    /**
     * @desc 记录检测时间
     */
    public function startCheck($params) {
        
        if ($params['version_tag'] == '1'){//版本号：0旧版，1新版
            require_once API_PATH . '/interface/checkForOffline/CarConditionForOfflineInterface.class.php';
            return  CarConditionForOfflineInterface::startCheck($params);//新版接口
        } else {
            require_once API_PATH . '/interface/CarConditionInterface.class.php';
            return  CarConditionInterface::startCheck($params);
        }
        
    }
    
    /**
     * @desc 取得检测列表
     */
    public function getList($params) {
        $params['request_source'] = 'pad';
        if ($this->_isManager) {
            $params['infoarea'] = 'dept';
        } else {
            $params['infoarea'] = 'user';
        }
        $params['username'] = AppServAuth::$userInfo['user']['username'];
        $params['dept_id'] = AppServAuth::$userInfo['user']['dept_id'];
        
        if ($params['version_tag'] == '1'){//版本号：0旧版，1新版
            require_once API_PATH . '/interface/checkForOffline/CarConditionForOfflineInterface.class.php';
            return  CarConditionForOfflineInterface::getList($params);//新版接口
        } else {
            require_once API_PATH . '/interface/CarConditionInterface.class.php';
            return  CarConditionInterface::getList($params);
        }
        
    }
    
    /**
     * @desc 取得数量
     */
    public function getCount($params) {
        if ($this->_isManager) {
            $params['infoarea'] = 'dept';
        } else {
            $params['infoarea'] = 'user';
        }
        $params['username'] = AppServAuth::$userInfo['user']['username'];
        $params['dept_id'] = AppServAuth::$userInfo['user']['dept_id'];
        
        if ($params['version_tag'] == '1'){//版本号：0旧版，1新版
            require_once API_PATH . '/interface/checkForOffline/CarConditionForOfflineInterface.class.php';
            return  CarConditionForOfflineInterface::getCount($params);//新版接口
        } else {
            require_once API_PATH . '/interface/CarConditionInterface.class.php';
            return CarConditionInterface::getCount($params);
        }
        
    }
    
    /**
     * @desc 检查信息编号是否存在和重复
     */
    public function checkCarId($params) {
        if ($params['version_tag'] == '1'){//版本号：0旧版，1新版
            require_once API_PATH . '/interface/checkForOffline/CarConditionForOfflineInterface.class.php';
            return  CarConditionForOfflineInterface::checkCarId($params);//新版接口
        } else {
            require_once API_PATH . '/interface/CarConditionInterface.class.php';
            return CarConditionInterface::checkCarId($params);
        }
        
    }
    
    /**
     * @desc
     */
    public function getUpdateTime($params) {
        if ($params['version_tag'] == '1'){//版本号：0旧版，1新版
            require_once API_PATH . '/interface/checkForOffline/CarConditionForOfflineInterface.class.php';
            return  CarConditionForOfflineInterface::getList($params);//新版接口
        } else {
            require_once API_PATH . '/interface/CarConditionInterface.class.php';
            return CarConditionInterface::getList($params);
        }
        
    }

    /**
     * 获取某个类型平板的白名单列表
     * @param type $params type_id
     * @return array
     */
    public function getAppLibrary($params) {
        require_once API_PATH . '/interface/CarConditionInterface.class.php';
        $res = CarConditionInterface::getAppList($params);

        $arr = array();
        foreach( $res as $v ) {
            $arr[]['app'] = $v['app'];
        }
        return json_encode($arr);
    }

    /**
     * 上传车况宝平板使用GPS数据
     * @param array $params [data]字段，json字符串
     */
    public function setAppGps($params) {
        require_once API_PATH . '/interface/CarConditionInterface.class.php';
        return CarConditionInterface::setAppGps($params);
    }
}
