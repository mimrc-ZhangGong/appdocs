<?php
if( !defined('IN') ) die('bad request');

class TDataSet
{
	private $Connection;
	private $Current;

	public $DS;
	public $RecordSet;
	public $CommandText;
	
	//构造函数
	public function __construct()
	{
		global $APP_DB,$DBConnection;
		if(!isset($DBConnection)) $DBConnection = new TMainData();
		$this->Connection = $DBConnection->conn;
		$TDB = 'T'.$APP_DB['TYPE'];
		if(class_exists($TDB))
		{
			$this->DS = new $TDB;//实例化相应数据库操作类
		}
	}
	//求最大行数
	public function RecordCount()
	{
		try {
			if($this->RecordSet){
				$num = $this->DS->num_rows($this->RecordSet);
				return($num);
			}
			else{
				echo '<p>RecordCount Error: ' . $this->CommandText . '</p>';
			}
		} catch (Exception $e){
			echo $e->getMessage();
			exit;
		}
	}
	//打开数据集
	public function Open($sql = '')
	{
		if($sql <> ''){
			$this->CommandText = $sql;
		}
		if(defined('DEBUG_SQL')) echo $this->CommandText . '</br>';
		$this->RecordSet = $this->DS->query($this->CommandText, $this->Connection);
		if(!$this->RecordSet){
			echo '<p>'.mysql_error().'</p>';
			echo '<p>TDataSet.CommandText 被错误赋值为: ' . $this->CommandText . '，无法执行！</p>';
		}
	}
	//执行SQL指令
	public function Execute()
	{
		if(!$this->DS->query($this->CommandText, $this->Connection)){
			//echo "$this->CommandText \n";
			echo mysql_error();
		}
	}
	//执行SQL指令
	public function ExecSQL($sql)
	{
		if(!$this->DS->query($sql, $this->Connection))
			echo mysql_error();
	}
	//下一条记录
	public function Next()
	{
		$this->Current = $this->DS->fetch_array($this->RecordSet);
		return $this->Current;
	}
    //返回第一条记录
    public function First(){
        if($this->DS->num_rows($this->RecordSet) == 0) return false;
        return $this->DS->data_seek($this->RecordSet);
    }
	//取得字段数目
	public function FieldCount()
	{
		return($this->DS->num_fields($this->RecordSet));
	}
	//根据字段索引取得字段名称
	public function getFieldName($index)
	{
		return($this->DS->field_name($this->RecordSet, $index));
	}
	//根据字段索引取得字段值
	public function FieldByIndex($index)
	{
		global $APP_DB;
		$fd = $APP_DB['TYPE'] == 'MYSQL' ? $this->getFieldName($index) : $index;
		return($this->Current[$fd]);
	}
	//根据字段名称取得字段值
	public function FieldByName($fieldname)
	{
		return($this->Current[$fieldname]);
	}
	//支持以属性方式取得当前值
	public function __get($name){
		if(strpos($name, '_')){
			return($this->Current[$name]);
		}
		if($name == 'conn'){
			return $this->Connection;
		}
	}
}
?>