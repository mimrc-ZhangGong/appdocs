<?php

class TWebSession
{
	private $Logon = false;
	private $LogonInfo = array();
	//public $users;
	private $ScreenWidth = 768;
	private $Message = null;
	//public $menus = array();
	
	public function __construct(){
		$sw_arr = array(0, 768, 480, 240);
		//适应不同的设备
		if(isset($_GET['width'])){
			$wh = $_GET['width'];
			if(array_search($wh, $sw_arr) > 0){
				$_SESSION['width'] = $wh;
				$this->ScreenWidth = $wh;
				//echo "ScreenWidth set $wh ok!";
			}
		}elseif(isset($_SESSION['width'])){
			$this->ScreenWidth = $_SESSION['width'];
		}
		global $appid;
		if(isset($_GET['logout'])){
			if(isset($_SESSION[$appid.'LogonInfo'])){
				$usercode = current(explode('|', $_SESSION[$appid.'LogonInfo']));
				ExecSQL("update WF_UserInfo set Online_=0 where UserCode_='$usercode'");
				$this->LoginLog($usercode, 1);
			}
			//退出处理
			unset($_SESSION[$appid.'LogonInfo']);
			unset($_SESSION['CusCode']);
		}elseif(isset($_SESSION[$appid.'LogonInfo'])){
			//登录处理
			$this->LogonInfo = explode('|', $_SESSION[$appid.'LogonInfo']);
			if(isset($_SESSION['LoginTime'])){
				$time = date('Y-m-d H:i:s');
				$t = (strtotime($time) - strtotime($_SESSION['LoginTime']))/60;
				if($t > 30){
					$this->UPLoginTime($this->UserCode);
					$_SESSION['LoginTime'] = $time;
				}
			}
			$level = array(0 => '<font color="red">超级管理员</font>', 1 => '企业管理员', 2 => '一般用户');
			//print_r($this->LogonInfo);
			$CusName = DBRead("select ShortName_ from WF_CusList where Code_='$this->CorpCode'");
			$JobGroup = $this->LogonInfo[5];
			$JobGroupName = DBRead("select Name_ from US_JobGroup where Code_='$JobGroup'");
			$JobGroupName = $JobGroupName == "" ? null : ", 工作组：[$JobGroupName]";
			if($this->IsServiceUser){
				$this->Message = "您好！[$CusName][$this->UserCode]$this->UserName, 等级：".$level[$this->UserLevel].$JobGroupName;
			}else{
				$this->Message = "欢迎您，[$CusName][$this->UserCode]$this->UserName, 等级：".$level[$this->UserLevel].$JobGroupName;
			}
			$this->Logon = true;
		}else{
			if(isset($_POST['UserCode'])){
				$UserCode = $_POST['UserCode'];
				$password = $_POST['password'];
				if($UserCode and $password){
					if($this->Login($UserCode, $password, 0)){
						header("Location:index.php?m=Welcome");
					}else
						$this->Message = '<font color="red">用户名或密码错误</font>';
				}
				else
					$this->Message = '请登入系统！';
			}
			else
				$this->Message = '请登入系统！';
		}
		$this->menus = isset($_SESSION['menus']) ? $_SESSION['menus'] : null;
	}
	
	public function Login($UserCode, $password, $IsMD5){
		$this->Logon = false;
		global $APP_DB, $appid;
		//new TMainData();
		$ds = new TDataSet();
		switch ($APP_DB['TYPE'])
		{
			case 'MSSQL':
				$Fields = 'CorpCode_,DeptCode_,Code_ as UserCode_,Password_ as UserPasswd_,Name_ as UserName_,*';
				$Table = 'Account';
				break;
			default:
				$Fields = '*';
				$Table = 'WF_UserInfo';
		}
		$ds->CommandText = "select $Fields from $Table where UserCode_='$UserCode' and Enabled_=1";
		$ds->Open();
		if($ds->Next()){
			$TempCode = explode('@', $UserCode);
			if (count($TempCode) === 2){
				$Account = $TempCode[0];
			}else{
				$Account = $UserCode;
			}
			if ($IsMD5 == 1){
				$Signal = $password;
			}else{
				$Signal = md5($Account . $password);
			}
			//兼容新旧密码加密模式
			if($ds->UserPasswd_ === $Signal || $ds->UserPasswd_ === md5($password)){
				$this->LogonInfo = array();
				$this->LogonInfo[] = $UserCode;
				$this->LogonInfo[] = $ds->UserName_;
				$this->LogonInfo[] = $ds->Level_;
				$this->LogonInfo[] = $ds->CorpCode_;
				$this->LogonInfo[] = $ds->DeptCode_;
				$this->LogonInfo[] = $ds->JobGroup_;
				$this->LogonInfo[] = $ds->ServiceUser_;
				$this->LogonInfo[] = $ds->MainCorp_;
				//保存到Session中
				$_SESSION[$appid.'LogonInfo'] = implode('|', $this->LogonInfo);
				$_SESSION['LoginTime'] = date('Y-m-d H:i:s');
				if(isset($_GET['loginlog'])){
					$this->LoginLog($UserCode);
				}
				$this->UPLoginTime($UserCode);
				$CusName = DBRead("select ShortName_ from WF_CusList where Code_='$ds->CorpCode_'");
				$this->Message = "欢迎您：[$CusName][$UserCode]$this->UserName";
				$this->Logon = true;
			}else{
				$this->Message = "用户 $UserCode 密码错误！";
			}
		}else{
			$this->Message = "用户帐号 [$UserCode] 不存在！";
		}
		return $this->Logon;
	}
	
	public function __get($name){
		if($name === 'Logon')
			return $this->Logon;
		elseif($name === 'UserCode')
			return isset($this->LogonInfo[0]) ? $this->LogonInfo[0] : null;
		elseif($name === 'UserName')
			return isset($this->LogonInfo[1]) ? $this->LogonInfo[1] : null;
		elseif($name === 'UserLevel')
			return $this->LogonInfo[2];
		elseif($name === 'MainCorp')
			return $this->LogonInfo[7];
		elseif($name === 'CorpCode')
			return isset($this->LogonInfo[3]) ? $this->LogonInfo[3] : null;
		elseif($name === 'DeptCode')
			return isset($this->LogonInfo[4]) ? $this->LogonInfo[4] : null;
		elseif($name === 'JobGroup')
			return $this->LogonInfo[5];
		elseif($name === 'IsServiceUser')
			return isset($this->LogonInfo[6]) ? $this->LogonInfo[6] : null;
		elseif($name === 'ScreenWidth')
			return $this->ScreenWidth;
		elseif($name === 'Message')
			return $this->Message;
		else{
			die("调用错误，$Session 没有此属性值：$name</br>");
			return null;
		}
	}
	
	public function __set($name, $value){
		if($name === 'Message'){
			$this->Message = $value;
		}
	}
	
    public function  UPLoginTime($UserCode){
		if($UserCode){
			$sql = "update WF_UserInfo set LoginTime_='". date('Y-m-d H:i:s') ."',Online_=1 where UserCode_='$UserCode'";
			ExecSQL($sql);
		}
    }
	
    public function  LoginLog($UserCode, $type = null){
		if($UserCode){
			if($type == null){
				$login = date('Y-m-d H:i:s');
				$_SESSION['login'] = $login;
				$sql = "insert into WF_LoginLog (UserCode_,Login_,Logout_,UpdateKey_) values ('$UserCode','$login','',UUID())";
			}else{
				if(isset($_SESSION['login'])){
					$sql = "update WF_LoginLog set Logout_='".date('Y-m-d H:i:s')."' where Login_='".$_SESSION['login']."' and UserCode_='$UserCode'";
				}
			}
			ExecSQL($sql);
		}
    }
	
	public function UpdateMyCorpCode($NewCorp, $NewLevel, $JobGroup){
		global $appid;
		$this->LogonInfo[3] = $NewCorp;
		$this->LogonInfo[2] = $NewLevel;
		$this->LogonInfo[5] = $JobGroup;
		//保存到Session中
		$_SESSION[$appid.'LogonInfo'] = implode('|', $this->LogonInfo);
	}
}

function isLogin(){
	global $Session;
	if($Session){
		return $Session->Logon;
	}else{
		return false;
	}
}

function uLevel(){
	global $Session;
	return $Session->UserLevel;
}
?>