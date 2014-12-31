<?php
require_once APP_SERV . '/app/sale/include/config.php';

class CarUtil {
    
    /**
     * @brief 获取电话号码归属地
     * 1.根据接口获取归属地
     * 2.将电话号码和归属地做memcahe缓存处理，第二次读取的时候从缓存中读取
     *
     */
    public static function getNameByTel($telephone) {
        $name = '';
        $cacheHandle = CacheNamespace::createCache(CacheNamespace::MODE_MEMCACHE, MemcacheConfig::$GROUP_DEFAULT);
        if (strlen($telephone) == 11 && substr($telephone, 0, 1) != '0') {
            $memcacheKey = 'phone_to_city_v7_' . $telephone;
            $addr = $cacheHandle->read($memcacheKey);
            if ($addr === false || $addr === null) {
                $phoneInfo = MobileToCityInterface::mtc(array('tel'=>$telephone));
                if (in_array($phoneInfo[1], array('北京','上海','天津','重庆'))) {
                    $name = $phoneInfo[1];
                } else {
                    $name = $phoneInfo[1] . $phoneInfo[4];
                }
                $cacheHandle->write($memcacheKey, $name, 360000);
            } else {
                $name = $addr;
            }
        } elseif (is_numeric($telephone) && substr($telephone,0,1) != '0') {
            $name = '福建福州';
        } elseif (is_numeric($telephone) && substr($telephone,0,1) == '0') {
            $params['domain'] = substr($telephone, 0, 4);
            $city = LocationInterface::getLocationByDomain($params);
            if(!$city) {
                $params['domain'] = substr($telephone, 0, 3);
                $city = LocationInterface::getLocationByDomain($params);
            }
            if ($city) {
                $params['province_id'] = $city['parent_id'];
                $province = LocationInterface::getProvinceById($params);
                $name = $province['name'].$city['name'];
            } else {
                $name = '';
            }
        } else {
            $name = '';
        }
    
        return $name;
    }
    
    /**
     * @brief 生成不同尺寸的图片，$path 原图url
     *
     */

    public static function buildPhoto($path, $width = 120, $height = 90) {
        $path = str_replace('273.cn', '273.com.cn', $path);
        $imgDomain = IMG_DOMAIN;
        if (strpos($path,'http://')===0) {
            $imgDomain = '';
        }
        //默认jpg
        $mode = '.jpg';
        if (strpos($path, '.png')) $mode = '.png';
        if (strpos($path, '.jpeg')) $mode = '.jpeg';
        $path = str_replace('_min'. $mode, $mode, $path);
        return $imgDomain . str_replace($mode, '_' .$width . '-' . $height . '_6_0_0' . $mode, $path);
    }
        
}



