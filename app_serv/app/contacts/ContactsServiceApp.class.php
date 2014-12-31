<?php
/**
 * @package              v3
 * @subpackage           
 * @author               $Author:   guoch
 * @file                 $HeadURL$
 * @version              $Rev$
 * @lastChangeBy         $LastChangedBy$
 * @lastmodified         $LastChangedDate$
 * @copyright            Copyright (c) 2012, www.273.cn
 */
require_once API_PATH . '/interface/ContactsInterface.class.php';
require_once API_PATH . '/interface/UploadInterface.class.php';
require_once API_PATH . '/interface/mbs/MbsUserInterface2.class.php';
require_once FRAMEWORK_PATH . '/util/gearman/LoggerGearman.class.php';
require_once API_PATH . '/interface/MbsDeptInterface.class.php';

class ContactsServiceApp {
    public function addContacts($params) {
        $username = AppservAuth::$userInfo['user']['username'];
        $params['username'] = $username;
        $data['content'] = base64_encode(file_get_contents($_FILES['file']['tmp_name']));
        $data['name'] = ($params['type'] == 1) ? '.vcf' : '.db';
        $data['category'] = 'contacts';
        $data['unique'] = $username.'_'.$params['device_token']; 
        $rs = UploadInterface::imgUpload($data);
        $rs['data'] = $data;
        $params['file_path'] = $rs['url'][0];
        $ret = ContactsInterface::addContacts($params);
        if(empty($ret) || empty($rs)) {
            LoggerGearman::logInfo(array('data'=>array('code' => $params, 'message'=>'通讯录或通话记录保存失败'), 'identity'=>'contacts_appserv'));
            return 0;
        }
        return $ret;
    }

    /*******
     * @desc 搜索通讯录，根据关键字，搜索帐号，姓名，手机号，姓名为模糊搜索
     * @author 李明友
     * @param $params array(
     *  'keyword'=>
     * )
     * ******/
    public function searchUsers( $params ) {
        $list = '';
        $keyword = $params['keyword'];
        //匹配帐号
        if (is_numeric($keyword)) {
            $userinfo = MbsUserInterface2::getInfoByUser(array('username'=>$keyword));
        } else {
            $userinfo = array();
        }
        if( $userinfo ) {
            $list['list'][] = $userinfo;
            $list['total'] = 1;
        }else{
            //不存在则先查找手机号
            $filters = '';
            $filters[] = array('status', '=', 1);
            $filters[] = array('mobile', '=', $keyword);
            $postList = MbsUserInterface2::getUserList(array(
                'field'=>'username,real_name,role_id,mobile,dept_id',
                'filters'=>$filters));
            if( !$postList['total'] ) {
                //最后模糊匹配用户名
                $filters = '';
                $filters[] = array('status', '=', 1);
                $filters[] = array('real_name', 'like', '%' . $keyword. '%');
                $postList = MbsUserInterface2::getUserList(array(
                    'field'=>'username,real_name,role_id,mobile,dept_id',
                    'filters'=>$filters));
            }
            if( $postList['total'] ) {
                $list['total'] = $postList['total'];
                unset($postList['total']);
                $list['list'] = $postList;
            }else{
                unset($postList['total']);
                $list['list'] = $postList;
                $list['total'] = 0;
            }
        }
        $list = self::_formatList($list);

        return $list;
    }

    /**
     * @desc 格式化数据
     */
    public static function _formatList($list) {
        if ($list['total']) {
            foreach ($list['list'] as $key => $value) {
                $deptInfo = MbsDeptInterface::getDeptInfoByDeptId(array('id' => $value['dept_id']));
                $list['list'][$key]['dept_name'] = $deptInfo['dept_name'];
            }
        }
        
        return $list;
    }
}
