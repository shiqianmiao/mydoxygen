<?php
class Db {
    // 数据库类型
    protected $dbType           = null;
    // 是否自动释放查询结果
    protected $autoFree         = false;
    // 当前操作所属的模型名
    protected $model =  '_cheyou_';
    // 是否使用永久连接
    protected $pconnect         = false;
    


    // 是否已经连接数据库
    protected $connected        = false;
    // 当前SQL指令
    protected $queryStr          = '';
    // 数据库连接ID
    protected $linkID          = null;
    // 当前查询ID
    protected $queryID         = null;
    // 最后插入ID，mysql通过返回id获取，oracle通过执行SELECT SEQ_CAR_*.nextval nv FROM dual获取
    protected $lastInsID         = null;
    //影响的行数
    protected $numRows = 0;
    //sql语句记录
    protected $historySql  = array();

    
    //是否显示调试信息 如果启用会在日志文件记录sql语句
    public $debug          = true;
    //查询失败是否退出
    protected $haltOnError = false;
    //最后的错误信息
    protected $error       = '';
    

    /**
     * 构造函数
     * @param array $config 数据库配置数组
     */
    function __construct($config=''){
        return $this->factory($config);
    }

    /**
     * 取得数据库类实例
     */
    public static function getInstance() {
        $args = func_get_args();
        return call_user_func_array(array(__CLASS__, 'factory'), $args);
    }
    
    /**
     * 加载数据库类
     * @param mixed $db_config 数据库配置信息
     */
    static public function factory($db_config=array()) {
        // 数据库类型
        $class = 'db'. $db_config['gDbType'];
       
        // 加载驱动类
        if(require(dirname(__FILE__) . '/' . $class.'.class.php')) {
            $dbObj = new $class($db_config);
            if(APP_DEBUG)$dbObj->debug = true;
        }
        return $dbObj;
    }


    /**
     * 数据库调试 记录当前SQL
     */
    protected function debug() {
    }


    /**
     * 字段名分析
     */
    protected function parseKey(&$key) {
        return $key;
    }
    
    /**
     * 字段value分析
     */
    protected function parseValue($value) {
        if(strpos($value,'to_date')===0 or strpos($value, 'to_char')===0){
            $value = $value;
        }elseif(is_string($value)) {
            $value = '\''.$this->escapeString($value).'\'';
        }elseif(is_null($value)){
            $value   =  'null';
        }
        return $value;
    }

    /**
     * 插入记录
     * @param mixed $data 数据
     * @param array $options 插入参数，比如table等
     */
    public function insert($data, $options) {
        $values  =  $fields    = array();
        $this->model  =   $options['model'];
        foreach ($data as $key=>$val){
            $value   =  $this->parseValue($val);
            if(is_scalar($value)) { // 过滤非标量数据
                $values[]   =  $value;
                $fields[]   =  $this->parseKey($key);
            }
        }
        $sql   =  'INSERT INTO '.$options['table'].' ('.implode(',', $fields).') VALUES ('.implode(',', $values).')';
        return $this->query($sql);
    }
    
    /**
     * 更新记录
     * @param mixed $data 数据
     * @param array $options 表达式
     */
    public function update($data,$options) {
        $this->model  =   $options['model'];
        $sql='UPDATE '.$options['table'].' SET ';
        $comma='';
        foreach($data as $_name=>$_value) {
            $_name=$this->parseKey($_name);
            $_value = $this->parseValue($_value);
            $sql.="$comma $_name=$_value";
            $comma=',';
        }

        $sql.=" WHERE ".$options['where'];
        return $this->execute($sql);
    }

    /**
     * 删除记录
     * @param array $options 表达式
     */
    public function delete($options=array()) {
        $this->model  =   $options['model'];
        return $this->query($sql);
    }

    /**
     * 查找记录
     * @param array $options 表达式
     */
    public function select($options=array()) {
        $this->model  =   $options['model'];
        $sql   = $this->buildSelectSql($options);
        $result   = $this->query($sql);

        return $result;
    }

    /**
     * 生成查询SQL
     * @param array $options 表达式
     */
    public function buildSelectSql($options=array()) {

    }
    
    /**
     * 获取最近一次查询的sql语句 
     * @param string $model  模型名
     */
    public function getLastSql($model='') {
        return $model?$this->modelSql[$model]:$this->queryStr;
    }

    /**
     * 获取最近插入的ID
     */
    public function getLastInsID() {
        return $this->lastInsID;
    }


    /**
     * SQL指令安全过滤
     * @param string $str  SQL字符串
     */
    public function escapeString($str) {
        //return addslashes($str);
        return $str;
    }

    public function setModel($model){
        $this->model  =   $model;
    }

   /**
     * 析构方法
     */
    public function __destruct() {
        // 释放查询
        if ($this->queryID){
            $this->free();
        }
        // 关闭连接
        $this->close();
    }
    
    /**
     * 获取最近的错误信息
     */
    public function getError() {
        return $this->error;
    }
    
    /**
     * 数据库连接错误，退出
     * @param string $msg 错误信息
     */
    function halt($msg){
        if($this->haltOnError){
            trigger_error($this->get_error_string(), E_USER_ERROR);
            return;
        }
        die($this->Error);
    }

    // 关闭数据库 由驱动类定义
    public function close(){}
    
}
