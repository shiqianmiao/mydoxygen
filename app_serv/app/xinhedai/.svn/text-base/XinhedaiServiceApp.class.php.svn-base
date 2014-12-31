<?php
/**
 * @package              v3
 * @subpackage           
 * @author               $Author:   weihongfeng$
 * @file                 $HeadURL$
 * @version              $Rev$
 * @lastChangeBy         $LastChangedBy$
 * @lastmodified         $LastChangedDate$
 * @copyright            Copyright (c) 2013, www.273.cn
 */
require_once API_PATH . '/interface/CooperateXinhedaiInterface.class.php';

class XinhedaiServiceApp {

    //双方约定的密钥
    public static $SECRET       = 'Hhidwi2JXC6OP7GEML8jrNFLQ0j2H4nS';
    
    //双方约定的密钥
    public static $GO_TO_URL = 'http://old.xinhedai.com/user-cooperate_rec_url?';
    
    /**
     *
     * @brief 生成接口跳转地址
     */
    public function goToMethod($params) {
        $pushData = trim($_GET['pushData']);
        //必须存在传入数据
        if ($pushData) {
            $pushData = json_decode($pushData, true);
            $userInfo = CooperateXinhedaiInterface::getData($pushData['user_id']);
            //用户信息必须是预先存储好的
            if ($userInfo) {
                $userInfo = json_decode($userInfo, true);
                //内部密码必须相符，这里内部密码是用户登录状态才能写入的，以防止外部提交
                if ($userInfo['interPassword'] == trim($pushData['interPassword'])) {
                    $signatureOg = $userInfo['user_id'] . $userInfo['email'] . $userInfo['mobile'] . $userInfo['time_stamp'] . $userInfo['redirect_url'] . $userInfo['auto_password'] . self::$SECRET;
                    $signature   = md5($signatureOg);
                    
                    $userInfo['signature']  = $signature;
                    $userInfo['creat_time'] = time();
                    
                    //重新存储用户信息
                    CooperateXinhedaiInterface::setData($userInfo['user_id'], json_encode($userInfo));
                    
                    $goToUrl = self::$GO_TO_URL;
                    $goToUrl .= 'user_id=' . $userInfo['user_id'] . '&mobile=' . $userInfo['mobile'];
                    $goToUrl .= '&email=' . $userInfo['email'] . '&time_stamp=' . $userInfo['time_stamp'];
                    $goToUrl .= '&redirect_url=' . $userInfo['redirect_url'] . '&auto_password=' . $userInfo['auto_password'];
                    $goToUrl .= '&signature=' . $signature . '&signatureOg=' . $signatureOg;
                    //echo $goToUrl; exit;
                    echo '<script type="text/javascript" charset="utf-8">' . "\n";
                    echo "top.location='" . $goToUrl . "';\n";
                    echo '</script>';
                    //echo '<meta http-equiv="refresh" content="0; url=' . $goToUrl . '">';
                    //header('location:' . $goToUrl);
                    exit;
                }
            }
        }
        exit;
    }
    
    /**
     *
     * @brief 回调接口，验证传递数据的合法性
     */
    public function verifyMethod($params) {
        $userId   = trim($_GET['user_id']);
        $userInfo = CooperateXinhedaiInterface::getData($userId);
        //用户信息必须是预先存储好的
        if ($userInfo) {
            $userInfo = json_decode($userInfo, true);
            //验证签名是否合法
            if ($userInfo['signature'] == trim($_GET['signature'])) {
                if (intval($_GET['time_stamp']) >= (time() - 30 * 60)) {
                    if ($userInfo['creat_time'] >= (time() - 30 * 60)) {
                        echo json_encode(array('status' => 1, 'err' => ''));
                        exit;
                    }
                    else {
                        echo json_encode(array('status' => 101, 'err' => '系统时间过期！'));
                        exit;
                    }
                }
                else {
                    echo json_encode(array('status' => 102, 'err' => '传入时间过期！'));
                    exit;
                }
            }
            else {
                echo json_encode(array('status' => 103, 'err' => '签名验证失败！'));
                exit;
            }
        }
        else {
            echo json_encode(array('status' => 104, 'err' => '非法用户信息！'));
            exit;
        }
        exit;
    }
    public function creatUrlMethod($params) {
        header('Content-Type:text/html;charset=UTF-8');
        
        $timeStamp    = time();
        $autoPassword = mt_rand(100000, 999999);
        
        echo '<form id="form1" name="form1" action="http://58.83.237.200/1.0/xinhedai.creatUrlMethodSubmit" method="get" target="_blank">
              <table width="100%" border="1" cellpadding="5" cellspacing="0">
                <tr>
                  <td align="right">user_id：</td>
                  <td><label>
                    <input name="user_id" type="text" id="user_id" value="100000001" />
                  </label></td>
                </tr>
                <tr>
                  <td align="right">mobile：</td>
                  <td><input name="mobile" type="text" id="mobile" value="18650361196" /></td>
                </tr>
                <tr>
                  <td align="right">email：</td>
                  <td><input name="email" type="text" id="email" value="77381110@qq.com" /></td>
                </tr>
                <tr>
                  <td align="right">time_stamp：</td>
                  <td><input name="time_stamp" type="text" id="time_stamp" value="' . $timeStamp . '" /></td>
                </tr>
                <tr>
                  <td align="right">redirect_url： </td>
                  <td><input name="redirect_url" type="text" id="redirect_url" value="http://www.xinhedai.com/uc_invest" size="60" /></td>
                </tr>
                <tr>
                  <td align="right">auto_password：</td>
                  <td><input name="auto_password" type="text" id="auto_password" value="' . $autoPassword . '" /></td>
                </tr>
                <tr>
                  <td colspan="2" align="center"><label>
                    <input type="submit" name="Submit" value="提交" />
                  </label></td>
                </tr>
              </table>
            </form>';
        exit;
    }
    
    public function creatUrlMethodSubmit($params) {
        header('Content-Type:text/html;charset=UTF-8');

        $userInfo = array(
                        'user_id'       => trim($_GET['user_id']),
                        'mobile'        => trim($_GET['mobile']),
                        'email'         => trim($_GET['email']),
                        'time_stamp'    => trim($_GET['time_stamp']),
                        'redirect_url'  => trim($_GET['redirect_url']),
                        'auto_password' => trim($_GET['auto_password']),
                    );
        $interPassword = md5($userInfo['user_id'] . $userInfo['time_stamp'] . $userInfo['auto_password']);
        $userInfo['interPassword'] = $interPassword;
        //写入用户数据
        CooperateXinhedaiInterface::setData($userInfo['user_id'], json_encode($userInfo));
        
        $goToUrlArray = array('user_id' => $userInfo['user_id'], 'interPassword' => $interPassword);
        $goToUrl = 'http://58.83.237.200/1.0/xinhedai.goToMethod?pushData=' . json_encode($goToUrlArray);
        //header('location:' . $goToUrl);
        echo '<script type="text/javascript" charset="utf-8">' . "\n";
        echo "top.location='" . $goToUrl . "';\n";
        echo '</script>';
        //echo '<meta http-equiv="refresh" content="0; url=' . $goToUrl . '">';
        exit;
    }
    
    
}