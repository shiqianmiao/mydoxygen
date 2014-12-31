<?php
/**

 * 接收短信上行
 */
require_once dirname(__FILE__) . '/../../../conf/config.inc.php';
$c = $GLOBALS['HTTP_RAW_POST_DATA'];
$fp = fopen('log.txt', 'a+');
fputs($fp, var_export($c, true));
fclose($fp);
require_once FRAMEWORK_PATH . '/util/redis/RedisQueue.class.php';
RedisQueue::push('sms_up_list01', $c);
echo 'smsup';
