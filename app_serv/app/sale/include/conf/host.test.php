<?php
/** 
 * 数据库
 */
define('CFG_DB_HOST','');   // 主机地址
define('CFG_DB_USER','w273cn');        // 用户名
define('CFG_DB_PASSWORD','w273cn_test');  // 密码
define('CFG_DB_ADAPTER','oracle');    // 数据库类型
define('CFG_DB_NAME','(DESCRIPTION =
    (ADDRESS_LIST =
      (ADDRESS = (PROTOCOL = TCP)(HOST = 192.168.5.203)(PORT = 1521))
    )
    (CONNECT_DATA =
      (SID = orcl)
    )
  )');

define('CFG_DB_PREFIX','car_');      // 表前缀

define('MEMCACHE_HOST', 'idc01-v2-web-01.273hosts');
define('HTTPSQS_HOST', 'idc01-v2-web-01.273hosts');
