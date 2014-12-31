<?php

class dbOracle extends Db
{
    // 执行sql提交方式，适用于oracle
    private $commitMode = OCI_COMMIT_ON_SUCCESS;
    
    //序列号前缀，适用于oracle
    private $seqPre = 'SEQ_';
    
    //把字段名改为小写
    private $caseLower = true;
    
    //把数据从gb码转到utf-8码
    private $gbToUtf8 = true;

    /**
     * 构造函数
     */
    function __construct($config = null) {
        putenv("NLS_LANG=AMERICAN_AMERICA.ZHS16GBK");
        if ( !extension_loaded('oci8') ) {
            exit('oracle extension load error!');
        }
        if(!empty($config))$this->config = $config;

        //设置序列前缀
        $this->seqPre = $this->config['gSeqPre'];
    }
	
    /**
     * 数据库连接
     */
    public function connect() 
    {
        if($this->connected)return false;

        $this->linkID = oci_connect($this->config['gDbUser'], $this->config['gDbPwd'], $this->config['gDbName'], 'ZHS16GBK');
        if (!$this->linkID) {
            $this->Error = oci_error($this->linkID);
            $this->halt('connect(' . $Sid . ',' . $User . ',\$Password) failed.');
            return FALSE;
        }else $this->connected = true;
        
        return TRUE;
    }

    /**
     * 获取最后一次插入的ID
     * @param mixed $options 数据库基本参数
     */
    public function getLastId($options = array()) {
        if(empty($options['seq']))$options['seq']=$this->seqPre.$options->table;
        $sql='select '.$options['seq'].'.nextval currval FROM dual';
        
        $result=$this->query($sql);
        $this->lastInsID = $result[0]['currval'];
        return $this->lastInsID;
    }
    
    
    /**
     * 执行语句
     * @param string $str  sql指令
     */
     public function execute($sql) {
        if($this->gbToUtf8){
            $sql=iconv("utf-8","gbk//IGNORE",$sql);
        }
 
        if ($this->linkID == null) $this->connect();
        if ( $this->debug ) $this->historySql[] = $sql;

        $this->queryID = oci_parse($this->linkID, $sql);
        if (!$this->queryID) {
            $this->error = oci_error($this->queryID);
            return false;
        }

        if ( !oci_execute($this->queryID,$this->commitMode) ) {
            $this->error = oci_error($this->queryID);
            return false;
        }else{
            $this->numRows = oci_num_rows($this->queryID);
            return $this->numRows;
        }
    }
	
    /**
     * 执行sql语句
     * @param string $sql sql语句
     * @return mixed
     */
    public function query($sql){
        if($this->gbToUtf8){
            $sql=iconv("utf-8","gbk//IGNORE",$sql);
        }
 
        if ($this->linkID == null) $this->connect();
        if ( $this->debug ) $this->historySql[] = $sql;

        $this->queryID = oci_parse($this->linkID, $sql);
        if (!$this->queryID) {
            $this->error = oci_error($this->queryID);
            return false;
        }

        if ( !@oci_execute($this->queryID,$this->commitMode) ) {
            $this->error = oci_error($this->queryID);
            return false;
        }
        return $this->getAll();
    }
    
    public function buildSelectSql($options = array()) {
        if(!isset($options['limit']) or $options['limit']===1){
            $sql='select '.$options['field'].' from '.$options['table'].' where '.$options['where'];
        }else{
            list($start,$end)=explode(',', $options['limit']);
            if(is_null($end)){
                $end=$start;
                $start=0;
            }
            $where= empty($options['where']) ? '': ' where '.$options['where'];
            $order= empty($options['order']) ? '': ' order by '.$options['order'];
            $sql='select /*+ordered use_nl(cy_c,cy)*/ '.$options['field'].'
                from (select rd
                        from (select rownum as rn, rd
                                from (select rowid rd from '.$options['table'].$where.$order.') cy_a
                               where rownum <= '.$end.') cy_b
                       where rn >= '.$start.') cy_c,
                     '.$options['table'].' cy
                where cy.rowid = cy_c.rd';
        }
        return $sql;
    }

    /**
     * 获得所有的查询数据
     */
     private function getAll() {
        $result = array();
        $this->numRows = @oci_fetch_all($this->queryID, $result, 0, -1, OCI_FETCHSTATEMENT_BY_ROW);
        if($this->caseLower or $this->gbToUtf8) {
            foreach($result as $k=>$v) {
                if($this->gbToUtf8){
                    foreach($v as $_k => $_v){
                        $result[$k][$_k]=iconv("gbk","utf-8//IGNORE",$_v);
                    }
                }
                if($this->caseLower){
                    $result[$k] = array_change_key_case($result[$k], CASE_LOWER);
                }
            }
        }
        return $result;
    }

    /**
     * 释放查询结果
     */
     public function free() {
        oci_free_statement($this->queryID);
        $this->queryID = null;
    }

    /**
     * 返回错误信息
     */
    function get_error_string(){		
        if ( is_string($this->Error) )
                return $this->Error;
        else if ( !is_array($this->Error) )	
                return null;

        if ( isset($this->Error['code']) )
                $err = 'code: ' . $this->Error['code'] . "\n ";
        if ( isset($this->Error['message']) )
                $err .= 'message: ' . $this->Error['message'] . "\n ";
        if ( isset($this->Error['offset']) )
                $err .= 'offset: ' . $this->Error['offset'] . "\n ";
        if ( isset($this->Error['sqltext']) )
                $err .= 'sqltext: ' . $this->Error['sqltext'];

        return $err;
    }

}
?>
