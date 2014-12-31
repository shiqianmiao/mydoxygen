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
class UploadServiceApp {

    /**
     * 上传图片到服务器
     * @param   : 参数说明如下表格
     * 参数名称     | 参数类型 | 参数补充描述
     * ------------|----------|------------------------------------------------
     * content     | string   | base64_encode后的图片内容
     * name        | string   | 原始图片名
     * @return array
     * 返回值名称   | 返回值类型 | 返回值补充描述
     * ------------|----------|------------------------------------------------
     * url         | string      | 图片URL
     */
    public function imgUpload($params) {
        include_once API_PATH . '/interface/UploadInterface.class.php';
        if (!$params['content'] || !$params['name']) {
            throw new AppServException(AppServErrorVars::CUSTOM, '缺少图片内容或图片类型');
        }
        return UploadInterface::imgUpload($params);
    }
}


