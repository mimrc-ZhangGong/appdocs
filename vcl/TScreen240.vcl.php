<?php
if( !defined('IN') ) die('bad request');

class TScreen240 extends TScreenBase{

	public function __construct(){
		parent::__construct();
		$this->mainbox->Window = $this;
	}

	public function OnBeforeShow(){
		parent::OnBeforeShow();
		//显示主菜单
		global $appname;
		global $Session;
		$i = 0;
		echo "<p style=\"background-color: #C0C0C0\">\n";
		$this->mainmenu[0][1] = $appname;
		foreach($this->mainmenu as $item){
			$url = $item[0];
			$name = $item[1];
			if($i > 0) echo "|";
			echo "<a href=\"$url\">$name</a>";
			$i++ ;
		}
		echo "\n</p>\n";
		//显示状态栏
		$url = "<a href=\"?width=480\">平板屏</a>";
		if($Session->Message){
			echo "<p class=\"message\">&nbsp;$url $Session->Message</p>\n";
		}
		//主功能区, 动态菜单 and 主显示区
		echo "<p style=\"background-color: #C0C0C0\">\n";
		if(count($this->menus) > 0){
			$i = 0;
			foreach($this->menus as $item){
				if(!is_array($item)){
					if($i > 0) echo "<br/>\n";
					echo $item . '：';
					$i = 0;
				}
				else{
					if($i > 0) echo "|";
					echo "<a href=\"$item[0]\">$item[1]";
					if(count($item) == 3) echo " $item[2]";
					echo "</a>";
					$i++ ;
				}
			}
		}
		echo "</p>\n";
		//主显示区
		echo "<p>\n";
	}
	
	public function OnShow(){
		echo "\n</p>\n";
		global $copyright, $admin_email, $website;
		echo "<p style=\"background-color: #C0C0C0\">$copyright</p>\n";
		parent::OnShow();
	}
}
?>