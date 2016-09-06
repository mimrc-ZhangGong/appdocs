<?php
if( !defined('IN') ) die('bad request');

class TPostRecord {
	private $Table = '';
	private $Fields = array();
	private $SystemFields = array('UpdateKey_');
	private $CommandText;
	
	public function __construct($table){
		$this->Table = $table;
	}

	public function SetField($name, $value){
		$this->Fields[$name] = $value;
	}
	
	public function __get($name){
		if(array_key_exists($name, $this->Fields)){
			return($this->Fields[$name]);
		}elseif($name == 'CommandText'){
			return $this->CommandText;
		}
	}
	
	public function __set($name, $value){
		if($name === 'SystemFields'){
			global $Session,$APP_DB;
			foreach($value as $field){
				$field = trim($field);
				if($field === 'UpdateUser_')
					$this->Fields[$field] = $Session->UserCode;
				elseif($field === 'UpdateDate_')
					$this->Fields[$field] = $APP_DB['TYPE'] == 'MYSQL' ? 'NOW()' : 'GETDATE()';
				elseif($field === 'AppUser_')
					$this->Fields[$field] = $Session->UserCode;
				elseif($field === 'AppDate_' || $field === 'UpdateDate_')
					$this->Fields[$field] = $APP_DB['TYPE'] == 'MYSQL' ? 'NOW()' : 'GETDATE()';
				elseif($field === 'UpdateKey_')
					$this->Fields[$field] = $APP_DB['TYPE'] == 'MYSQL' ? 'UUID()' : 'NEWID()';
				else
					die('Error System-Field: '. $field);
			}
		}else{
			if(is_string($value)){
				$value = trim($value);
			}
			$this->Fields[$name] = $value;
		}
	}

    public function PostAppend(){
		if($this->Table <> ''){
			$s1 = '';
			$s2 = '';
			global $Session;
			foreach($this->Fields as $field => $value){
				$s1 .= $field . ',';
				if(is_array($value) && isset($value['bit']))
					$s2 .= GetEncodeSql($value['bit']) . ',';//bit类型处理
				elseif(substr($value, strlen($value) - 2) == '()')
					$s2 .= GetEncodeSql($value) . ',';
				else
					$s2 .= "'" . GetEncodeSql($value) . "',";
			}
			$s1 = substr($s1, 0, strlen($s1) - 1);
			$s2 = substr($s2, 0, strlen($s2) - 1);
			$sql = "insert into $this->Table ($s1) values ($s2)";
            //echo $sql;
			$this->CommandText = $sql;
			return ExecSQL($sql);
		}else{
			die('Save error: $table name is null');
		}
	}
	
	public function PostModify($where = ''){
		if($this->Table <> ''){
			$s2 = '';
			if($where === ''){
				if(isset($_POST['uid']))
					$s2 = "UpdateKey_='$uid'";
			}else{
				$s2 = $where;
			}
			//
			if($s2 <> ''){
				$s1 = '';
				foreach($this->Fields as $field => $value){
					if(is_array($value) && isset($value['bit']))
						$s1 .= "$field = " . GetEncodeSql($value['bit']) . ",";//bit类型处理
					elseif(substr($value, strlen($value) - 2) == '()')
						$s1 .= "$field = " . GetEncodeSql($value) . ",";
					else
                        $s1 .= "$field = '" . GetEncodeSql($value) . "',";
				}
				$s1 = substr($s1, 0, strlen($s1) - 1);
				$sql = "update $this->Table set $s1 where $s2";
				//echo $sql;
				$this->CommandText = $sql;
				return ExecSQL($sql);
			}else{
				die('Save ' . $this->Table . ' error: $where is null');
			}
		}else{
			die('Save error: $table name is null');
		}
	}
}
?>