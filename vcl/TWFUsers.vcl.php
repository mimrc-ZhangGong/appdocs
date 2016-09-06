<?php
class TWFUsers extends TCustomUsers{
	private $users;
	
	public function __construct()
	{
		// 载入默认的
		//parent::__construct();
		global $APP_DB;
		//new TMainData();
		$dm = new TDataSet();
		switch ($APP_DB['TYPE'])
		{
			case 'MSSQL':
				$Fields = 'Code_ as UserCode_,Password_ as UserPasswd_,Name_ as UserName_,*';
				$Table = 'Account';
				break;
			default:
				$Fields = '*';
				$Table = 'WF_UserInfo';
		}
		$dm->CommandText = "select $Fields from $Table where Enabled_=1";
		$dm->Open();

		while($row = $dm->Next()){
			$this->users[$row['UserCode_']] = $row;
		}		
	}
	
	public function checkUser($UserCode){
		return array_key_exists($UserCode, $this->users);
	}
	
	public function checkPassword($UserCode, $password, $IsMD5){
        return true;
		if(array_key_exists($UserCode, $this->users)){
            $TempCode = explode('@', $UserCode);
            if (count($TempCode) === 2){
                $corp = $TempCode[1];
                if(DBExists("select Code_ from WF_CusList where Code_='$corp'"))
                    $Account = $TempCode[0];
                else
                    $Account = $UserCode;
            }
            else $Account = $UserCode;
            if ($IsMD5 == 1) $Signal = $password;
                else $Signal = md5($Account . $password);
			//兼容新旧密码加密模式
			if($this->users[$UserCode]['UserPasswd_'] === $Signal || $this->users[$UserCode]['UserPasswd_'] === md5($password)){
                $this->UPLoginTime($UserCode);
                return true;
			}
		}
	}
	
	public function getUserName($UserCode){
		if(array_key_exists($UserCode, $this->users)){
			return $this->users[$UserCode]['UserName_'];
		}else{
			return $UserCode;
		}
	}
	
	public function getUserLevel($UserCode){
		global $APP_DB;
		if(array_key_exists($UserCode, $this->users)){
			return $APP_DB['TYPE'] == 'MYSQL' ? intval($this->users[$UserCode]['Level_']) : '0';
		}else{
			return 2;
		}
	}
	
	public function ReadValue($UserCode, $field){
		if(array_key_exists($UserCode, $this->users)){
			return $this->users[$UserCode][$field];
		}else{
			return 0;
		}
	}

    public  function  UPLoginTime($UserCode){
        global $APP_DB;
		$query = mysql_query("update WF_UserInfo set LoginTime_='". date('Y-m-d H:i:s') ."' where UpdateKey_='"
            . $this->users[$UserCode]['UpdateKey_'] . "'");
		if(!$query && $APP_DB['TYPE'] == 'MYSQL'){
            die('update LoginTime Error!');
        }
    }
}
?>