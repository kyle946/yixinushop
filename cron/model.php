<?php

/**
 * Description of model
 *
 * @author  Kyle 青竹丹枫 <316686606@qq.com>
 */

include_once 'DbMysql.php';
class model {
    
    protected $db = null;
    public $prefix = null;
    protected $table = null;
    protected $field = null;
    protected $join = null;
    protected $where = null;

    /**
     * 
     * @param string $name  模型名称
     * @param string $prefix   前缀
     */
    public function __construct($name = '',$prefix = '') {
        if(!empty($prefix)){
            $this->prefix = $prefix;
        }else{
            $this->prefix = 'go_';
        }
        if(!empty($name)){
            $this->table = $this->prefix.$name;
        }
        
        $this->db = new DbMysql();
    }

    /**
     * select 查询 ，并返回 数组 
     * @return type
     */
    public function select() {
        $field = !empty($this->field) ? $this->field : '*';
        if(empty($this->table)){
            exit("The table can't be empty ! ");
        }
        
        $join = !empty($this->join) ? $this->join : null;
        $sql = "select $field from ".$this->table.$this->join.$this->where;
        return $this->db->_query($sql);
    }
    
    public function dbversion() {
        return $this->db->version();
    }
    
    /**
     * 保存数据
     * @param type $data
     */
    public function save($data) {
        if(empty($this->table)){
            exit("The table can't be empty ! ");
        }
        
        if(!is_array($data)){
            return false;
        }
        foreach ($data as $k=>$value) {
            $data[$k] = '\''.$value.'\'';
        }
        //extract($data, EXTR_OVERWRITE);
        $val = null;
        foreach ($data as $key => $value) {
            $field .= "`$key`,";
            $val .= "$value,";
        }
        $field = substr($field, 0,-1);
        $val = substr($val, 0,-1);
        $sql = "insert into ".$this->table."($field) values ($val)";
        return $this->db->query($sql);
    }
    
    /**
     * 过滤 
     * @param type $str
     * @return type
     */
    public function escapeString($str) {
        return $this->db->escapeString($str);
    }
    
    public function g($str) {
        return $this->escapeString(trim($str));
    }
    
    /**
     * 设置 字段 
     * @param string $val  字符 串
     * @return \model
     */
    public function field($val) {
        $this->field = null;
        if(is_array($val)){
            $field = null;
            foreach ($array as $v) {
                $field.="$v,";
            }
            $field = substr($field, 0, -1);
        }else{
            $field = $val;
        }
        $this->field = $field;
        return $this;
    }
    
    /**
     * 
     * @param string $table
     * @param string $on
     * @return \model
     */
    public function join($table,$on) {
        $this->join = null;
        if(empty($table) || empty($on)){
            $this->join = null;
        }else{
            $val = " left join ".$table." on ".$on;
            $this->join = $val;
        }
        return $this;
    }
    
    /**
     * 
     * @param string $val
     * @return \model
     */
    public function where($val) {
        $this->where = null;
        if(!empty($val)){
            $this->where = " where ".$val;
        }else{
            $this->where = null;
        }
        return $this;
    }
    
    /**
     * mysql 原生 查询 
     * @param string $sql
     * @return type  mysql 语句  执行结果
     */
    public function query($sql) {
        return $this->db->query($sql);
    }
    
    /**
     * 执行select 查询 ，并返回 数组 
     * @param type $sql
     * @return type
     */
    public function _query($sql) {
        return $this->db->_query($sql);
    }
    
     /**
     * 执行select 查询 ，并返回 数组 
     * @param type $sql
     * @return type
     */
    public function getall($sql) {
        return $this->db->_query($sql);
    }
    
    /**
     * 从一条语句 获取 一行的一个字段 
     * @return string|boolean
     */
    public function getone($sql) {
        $res = $this->query($sql);
         if ($res !== false){
            $row = mysql_fetch_row($res);
            if ($row !== false) {
                return $row[0];
            }else{
                return '';
            }
        }else{
            return false;
        }
    }
    
    /**
     * 获取一行数据 
     * @param type $sql
     * @return boolean
     */
    public function getrow($sql) {
        $res = $this->query($sql);
        if ($res !== false){
            return mysql_fetch_assoc($res);
        } else{
            return false;
        }
    }
    
    /**
     * 更新或插入数据
     * @param array $data  数据
     * @param string $tableName  表名,不要加前缀
     * @param type $act  操作类型，i为插入，u为更新
     * @param type $where  更新数据($act等于u)的where的条件, 前面不要加 where 字符
     */
    public function sData($data = null,$tableName = null,$act='i',$where = null) {
        if( !empty($data) and !empty($tableName) ){
            if($act=='i'){
                $field = null; $val = null;
                foreach ($data as $key => $value) {
                    $field.="`$key`,";
                    if($value=='now()'){
                        $val.="$value,";
                    }elseif(substr($value,-3)=='---'){
                        $val.=substr($value,0,-3).",";
                    }else{
                        $val.="'$value',";
                    }
                }
                $field = substr($field, 0,-1);
                $val = substr($val, 0,-1);
                $sql = "insert into `".$this->prefix."$tableName` ($field) values ($val)";
                $result = $this->query($sql);
                if($result){
                    return mysql_insert_id();
                }
            }elseif ($act=='u') {
                $val = null;
                foreach ($data as $key => $value) {
                    if($value=='now()'){
                        $val.="`$key`=$value,";
                    }elseif(substr($value,-3)=='---'){  //尾部加3个-符号，表示这个字段不要加单引号
                        $val.="`$key`=".substr($value,0,-3).",";
                    }else{
                        $val.="`$key`='$value',";
                    }
                }
                $val = substr($val, 0,-1);
                $sql = "update `".$this->prefix."$tableName` set $val where $where";
                $result = $this->query($sql);
                if($result){
                    return true;
                }
            }
        }else{
            return false;
        }
    }
    
    /**
     * 删除数据  
     * @param type $tablename  表名 ,不要加前缀
     * @param type $where  where条件，不要加 where 关键字
     * @return boolean
     */
    public function sDelete($tablename=null,$where = null) {
        $sql = "delete from `".$this->prefix."$tablename` where $where";
        $result = $this->query($sql);
        if($result){
            return true;
        }
    }
    
}
