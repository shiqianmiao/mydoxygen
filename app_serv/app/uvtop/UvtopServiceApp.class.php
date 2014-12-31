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
require_once API_PATH . '/model/UvTop100Model.class.php';
class UvtopServiceApp {
    private $_uvModel = null;
    public function __construct(){
        $this->_uvModel = new UvTop100Model();
    }
    public function getTop($params) {
        if (empty($this->_uvModel)) {
            return null;
        }
        return $this->_uvModel->getIdsByType($params);
    }
}
