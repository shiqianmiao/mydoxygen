<?php 
class Cheyou {
    public static $dbConfig = array(
        'gDbHost' => 'localhost',   //mysql
        'gDbType' => 'Oracle',  
        'gDbUser' => CFG_DB_USER,
        'gDbPwd' => CFG_DB_PASSWORD,
        'gDbName' => CFG_DB_NAME,   //oracle
        'gDbPort' => '1521',
        'gTablePre' => 'CAR_',
        'gSeqPre' => 'SEQ_',
    );

    public static $CAR_TYPE = array(
        'PS' => 1,
        'SV' => 2,
        'MB' => 3,
        'LC' => 4,
        'HC' => 5,
        'SC' => 6,
        'PU' => 7,
        'BS' => 8,
    );
}
