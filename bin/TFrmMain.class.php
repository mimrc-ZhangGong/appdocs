<?php
if( !defined('IN') ) die('bad request');

class TFrmMain extends TDBForm
{
	public function checkLogin(){
		return true;
	}

	public function OnCreate()
	{
		parent::OnCreate();
		$this->Caption = '云文档服务中心';
		$this->Message = "此UI界面正在设计中！";

		$this->AddMenu('主索引');
		$this->AddMenu(array('helpme.php?id=100000', '文档中心'));
		$this->AddMenu(array('helpme.php?id=100001', '华软ERP'));
		$this->AddMenu(array('helpme.php?id=100002', '华软SCM'));
		$this->AddMenu(array('helpme.php?id=100003', '百事通'));

		$this->AddMenu('友情链接');
		//$this->AddMenu(array('http://mimrc.sinaapp.com', '云应用试验室'));
		$this->AddMenu(array('http://knowall.sinaapp.com', '企业百事通'));
		$this->AddMenu(array('http://qbook.sinaapp.com', '记帐易'));
		//$this->AddMenu(array('http://upsoft.sinaapp.com', '软件升级服务'));
		//$this->AddMenu(array('http://secpass.sinaapp.com', '通行证服务'));
	}
	
	public function OnDefault()
	{
		global $Session;
		echo "<p>欢迎使用！</p>\n";
		if(isLogin()){
			echo "<p>" . $Session->UserName . '['. $Session->UserCode
				. ']，等级：' . $Session->UserLevel . "</p>\n";
		}
	}
}
?>