<?php

require_once FRAMEWORK_PATH . '/util/db/BaseModel.class.php';
require_once CONF_PATH . '/db/DBConfig.class.php';

class SuperPasswdModel extends BaseModel {

    protected function init($id = '') {
        $this->dbName = DBConfig::DB_MBS;
        $this->tableName = 'super_passwd';
        $this->dbMasterConfig = DBConfig::$SERVER_MASTER;
        $this->dbSlaveConfig = DBConfig::$SERVER_SLAVE;
        $this->fieldTypes = array(
            'id' => 'INT',
            'passwd' => 'VARCHAR',
            'status' => 'INT',
            'create_time' => 'INT',
            'lose_time' => 'INT',
        );
    }
}
