<?php
if( !defined('IN') ) die('bad request');

class TFrmHello extends TForm
{
	public function OnCreate(){
		$this->Caption = 'test!';
		$this->Message = '欢迎您！';
		$this->AddMenu('菜单');
		$this->AddMenu(array($this->GetUrl(VIEW_APPEND), '增加'));
	}

	public function OnDefault(){
		echo 'Hello!';
	}
	
	public function OnAppend(){
		echo '增加';
	}
}
?>