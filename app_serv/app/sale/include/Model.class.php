<?php

class Model {

    // 当前数据库操作对象
    protected $db = null;
    // 主键名称
    protected $pk  = 'id';
    // 字段信息
    protected $fields = array(
        //字段验证规则：只能判断数据类型，不能判断是否是具体某种格式，比如电话号码，邮件地址，网址等，待完善
        //number(min,max)   一个参数时为max，min大于0表示必填，但最大数值不能超过max
        //string(min,max)   同number
        //boolean()   自动转为0或1，不存在转为0
        //date(YYYY-MM-DD hh24:mi:ss) date(yYYY-MM-DD hh24:mi:ss) 首字母大写为必填，小写为选填
    );
    // 数据表前缀
    protected $tablePrefix  =   '';
    // 模型名称
    protected $name = '';
    // 数据库名称，oracle中以实例区分，即CONNECT_DATA = (SID = orcl) 部分
    protected $dbName  = '';
    // 数据表名（不包含表前缀） 注意：模型名字和表明不一定一样
    protected $tableName = '';
    // 数据表全名(包括前缀)
    protected $trueTableName = '';
    // 序列号
    protected $seqName = '';
    // 数据信息
    protected $data =   array();
    // 组成sql的元素数组，比如where,limit,group,union,table等
    protected $options = array();
    // 最近错误信息
    protected $error = '';
    // 数据录入时格式错误的字段
    public $errField = '';


    /**
     * 架构函数
     * 取得DB类的实例对象 字段检查
     * @param string $name 模型名称
     * @param string $tablePrefix 表前缀
     * @param mixed $connection 数据库连接信息
     */
    public function __construct($name='',$tablePrefix='') {
        if(!empty($name)) {
            // 获取模型名称
            $this->name   =  $name;

            // 设置表前缀
            if(is_null($tablePrefix)) { // 前缀为Null表示没有前缀
                $this->tablePrefix = '';
            }elseif('' != $tablePrefix) {
                $this->tablePrefix = $tablePrefix;
            }else{
                $this->tablePrefix = $this->tablePrefix?$this->tablePrefix:Cheyou::$dbConfig['gTablePre'];
            }

            // 设置表名
            if($this->tableName=='')$this->tableName = $this->name;
            $this->trueTableName=$this->tablePrefix.$this->tableName;
        }

        $this->db= db();
    }
    public function setTableName($tableName) {
        $this->trueTableName = $tableName;
    }
    /**
     * 字段映射处理，数据必须先存好
     */
    protected function fieldMap(){

        foreach($this->mapData as $k => $v){
            if(isset($this->data[$k])){
                $this->data[$v] = $this->data[$k];
                unset($this->data[$k]);
            }
        }
    }


    /**
     * 字段统一验证，数据必须先存好
     * @param mixed $data 数据地址
     */
    protected function validate(){
        //字段映射
        $this->fieldMap();

        //删除多余的提交数据
        foreach($this->data as $k => $v){
            if(!isset($this->fields[$k]))unset($this->data[$k]);
        }

        //字段验证
        foreach($this->fields as $key => $field){
            list($type,$minlen,$maxlen) = preg_split('/[\(,\)]/', $field);
            if(empty($minlen)){
                $minlen=0;
                $maxlen=2147483647;
            }elseif(empty($maxlen)){
                $maxlen=$minlen;
                $minlen=0;
            }
            $var=$this->data[$key];
            $len=strlen($var);

            /* 开始判断 */
            switch($type){
                case 'number':
                    if($minlen>$len or $maxlen<$len or !preg_match('/^[0-9\.]*$/', $var)){
                        $this->errField = $key;
                        return false;
                    }
                    break;
                case 'date':
                    $dateFormat=ucfirst($maxlen);
                    if($dateFormat==$maxlen and !$len){ //长度验证
                        $this->errField = $key;
                        return false;
                    }
                    if($var!==null and !empty($var)){
                        $phpFormat = str_replace(array('YYYY','MM','DD','hh24','mi','ss'),array('Y','n','j','H','i','s'),$dateFormat); //值验证
                        $phpFormat2 = str_replace(array('YYYY','MM','DD','hh24','mi','ss'),array('Y','m','d','H','i','s'),$dateFormat); //值验证
                        $_timestamp=strtotime($var);
                        if( strtotime(date($phpFormat, $_timestamp)) != $_timestamp and strtotime(date($phpFormat2, $_timestamp)) != $_timestamp ){
                            $this->errField = $key;
                            return false;
                        }
                        //转为oracle数据格式
                        $this->data[$key] = 'to_date(\''.$var.'\',\''.$dateFormat.'\')';
                    }
                    break;
                case 'boolean':
                    $_v=intval($var);
                    if($_v!==0 and $_v!==1)$this->data[$key]=$_v>1?0:$_v;
                    break;
                case 'string':
                    if($minlen>$len or $maxlen<$len){
                        $this->errField = $key;
                        return false;
                    }
                    if($var!==null)$this->data[$key]=addslashes($var);
                    break;
                default:
                    return false;
            }
        }

        return true;
    }

    /**
     * 新增数据
     * @param mixed $data 数据
     */
    public function add($data=array()){
        if(is_array($data))$this->data = array_merge($this->data, $data);

        $options = $this->_parseOptions();

        //oracle在添加数据的时候自动增加主键字段
        if(Cheyou::$dbConfig['gDbType']=='Oracle' and !empty($this->pk)){
            $this->data[$this->pk] = $this->db->getLastId($options);
        }
        // 写入数据到数据库
        $result = $this->db->insert($this->data, $options);
        if(false !== $result ) {
            $insertId   =   $this->getLastInsID();
            if($insertId) {
                return $insertId;
            }
        }
        return $result;
    }


    /**
     * 查询数据集
     * @param array $options 表达式参数
     */
    public function select($options=array()) {
        $options =  $this->_parseOptions($options);
        $resultSet = $this->db->select($options);
        if(false === $resultSet) {
            return false;
        }
        if(empty($resultSet)) { // 查询结果为空
            return null;
        }
        return $resultSet;
    }

    /**
     * 保存数据
     * @param mixed $data 数据
     * @param array $options 表达式
     */
    public function save($data='',$options=array()) {
        if(empty($data)) {
            // 没有传递数据，获取当前数据对象的值
            if(!empty($this->data)) {
                $data    =   $this->data;
                // 重置数据
                $this->data = array();
            }else{
                return false;
            }
        }
        // 分析表达式
        $options =  $this->_parseOptions($options);

        if(!isset($options['where']) ) {
            // 如果存在主键数据 则自动作为更新条件
            if(isset($data[$this->getPk()])) {
                $pk   =  $this->getPk();
                $options['where']  =  $pk.'=\''.$data[$pk].'\'';
                $pkValue = $data[$pk];
                unset($data[$pk]);
            }else{
                return false;
            }
        }
        $result = $this->db->update($data,$options);

        if(false !== $result) {
            if(isset($pkValue)) $data[$pk]   =  $pkValue;
        }
        return $result;
    }

    /**
     * 分析sql参数表达式。每次执行sql后要置空，否则会影响到下一次执行的sql
     * @param array $options 表达式参数
     */
    protected function _parseOptions($options=array()) {
        $gOption=array(
            'table'=>$this->trueTableName,  //基本每个sql都会用到
            'model'=>$this->name,   //db记录是哪个模型执行了sql
            'pk'=>$this->pk,        //oracle生成序列和在主键上进行的删除修改操作用到
            'seq'=>$this->seqName   //oracle生成新序列用到
        );
        //先取出已经解析的参数
        if(is_array($options)) $options =  array_merge($this->options,$gOption, $options);

        // 查询过后重置sql表达式组装 避免影响下次查询
        $this->options=array();

        return $options;
    }


    /**
     * 获取一条数据
     * @param string $sql sql语句
     */
    public function getOne($sql, $parse=false){
        $data = $this->query($sql,$parse);
        return $data[0];
    }

    /**
     * SQL查询
     * @param mixed $sql  SQL指令
     * @param boolean $parse  是否需要解析SQL
     */
    public function query($sql,$parse=false) {
        if($parse)$sql = $this->parseSql($sql,$parse);
        return $this->db->query($sql);
    }

    /**
     * 解析SQL语句
     * @param string $sql  SQL指令
     */
    protected function parseSql($sql) {
        // 分析表达式
        if(strpos($sql,'__TABLE__')) $sql = str_replace('__TABLE__',$this->trueTableName,$sql);
        return $sql;
    }

    /**
     * 返回最后插入的ID
     */
    public function getLastInsID() {
        return $this->db->getLastInsID();
    }

    /**
     * 返回最后执行的sql语句
     */
    public function getLastSql() {
        return $this->db->getLastSql($this->name);
    }

    /**
     * 获取主键名称
     */
    public function getPk() {
        return $this->pk;
    }

    /**
     * 设置sql查询条件
     * @param type $name
     * @param type $value
     */
    public function setOption($name, $value){
        $this->options[$name]=$value;
    }


    /**
     * 设置模型的数据，先赋值，再进行字段映射
     * @param string $name 名称
     * @param mixed $value 值
     */
    public function setData($value=array()) {
        $this->data = $value;
        //字段映射
        $this->fieldMap();

        //过滤不需要字段
        foreach($this->data as $k => $v){
            if(!isset($this->fields[$k]))unset($this->data[$k]);
        }

        return $this;
    }

}
