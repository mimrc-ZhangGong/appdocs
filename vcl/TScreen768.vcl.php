<?php
if( !defined('IN') ) die('bad request');

class TScreen768 extends TScreenBase{
	private $colspan = 2;
	private $appTable;
	private $title_tr, $title_td; //标题行
	private $menu_tr, $menu_td; //主菜单
	private $index_tr; //temp
	private $index_td; //索引菜单区
	private $main_tr, $main_td; //主操作区
	private $msg_tr, $msg_td; //提示讯息
	private $foot_tr, $foot_td; //版权区
	
	public function __construct(){
		parent::__construct();
		$this->appTable = new TABLE($this); $this->appTable->Window = $this;
		$this->title_tr = new TR($this); $this->title_tr->Window = $this->appTable;
		$this->menu_tr  = new TR($this); $this->menu_tr->Window  = $this->appTable;
		$this->index_tr = new TR($this); $this->index_tr->Window = $this->appTable;
		//$this->main_tr  = new TR($this); $this->main_tr->Window  = $this->appTable;
		//$this->msg_tr   = new TR($this); $this->msg_tr->Window   = $this->appTable;
		$this->foot_tr  = new TR($this); $this->foot_tr->Window  = $this->appTable;
		{ //归属
			$this->title_td = new TD($this); $this->title_td->Window = $this->title_tr;
			$this->menu_td  = new TD($this); $this->menu_td->Window  = $this->menu_tr;
			
			$this->index_td = new TD($this); $this->index_td->Window = $this->index_tr;
			$this->main_tr  = new TD($this); $this->main_tr->Window  = $this->index_tr;
			$this->msg_td   = new DIV($this); $this->msg_td->Window   = $this->main_tr;
			$this->main_td  = new DIV($this); $this->main_td->Window  = $this->main_tr;
			/* 菜单栏右置
			$this->msg_td  = new TD($this); $this->msg_td->Window  = $this->index_tr;
			$this->index_td = new TD($this); $this->index_td->Window = $this->index_tr;
			$this->main_td   = new TD($this); $this->main_td->Window   = $this->main_tr;
			*/
			$this->foot_td  = new TD($this); $this->foot_td->Window  = $this->foot_tr;
		}
		{ //样式
			$val = 1024 - 30;
			//$this->appTable->Params = "border=\"0\" width=\"" . $val . " px\" id=\"table1\" "
			$this->appTable->Params = "border=\"0\" width=\"100%\" id=\"table1\" "
				. "cellspacing=\"0\"";
			$this->title_td->Params = "colspan=\"$this->colspan\" height=\"100\" class=\"head\"";
			$this->menu_tr->Params ="id=\"menu_tr\"";
			$this->menu_td->Params = "colspan=\"$this->colspan\"";
			//$this->msg_tr->Params ="style=\"background-color: #ECE9D8\"";
			//$this->msg_td->Params = "class=\"message\" colspan=\"2\" style=\"background-color: #FFFFFF\"";
			$this->msg_td->Params = "class=\"message\" ";
			$this->main_td->Params = "class=\"grid\" ";
			$this->index_td->Params = "class=\"menu\" rowspan='2'";
			//$this->main_tr->Params = "height=\"350\"";
			$this->main_tr->Params = "height=\"100%\" width=\"86%\" valign=\"top\" "
				. "class=\"main\"";
			$this->foot_tr->Params = "height=\"80\"";
			$this->foot_td->Params = "colspan=\"$this->colspan\"";
		}
		$this->mainbox->Window = $this->main_td;//print_r($this->main_td);
	}
	
	public function OnBeforeShow(){
		parent::OnBeforeShow();
		global $appname, $Session, $copyright, $website, $admin_email;
		if(!$Session) $Session = new TWebSession();
		//系统标题
		$this->title_td->Lines[] = "<p align=\"center\"><img src=\"images/index_logo.png\"><b><font size=\"6\">&nbsp;$appname</font></b></p>";
		//主菜单区
		//$this->menu_td->Lines[] = "<center>\n";
		$this->menu_td->Lines[] = "<ul class='mainmenu'>";
		$m = isset($_GET['m']) ? $_GET['m'] : 'Welcome';
		$this->menu_td->Lines[] = $m == 'Welcome' ? "<li class='current'><a href='./'>首 页</a></li>" : "<li><a href='./'>首 页</a></li>";
		$section = null;
		$menu2 = array();
		if(isLogin()){
			foreach($this->mainmenu as $item){
				if($m == $item['menucode']){
					$section = $item['section'];
					break;
				}
			}
			foreach($this->mainmenu as $item){
				if($section == $item['section']){
					$menu2[] = $item;
				}
				if(isset($item['default'])){
					$url = '?m='.$item['menucode'];
					$name = $item['section'];
					$css = $section == $item['section'] ? ' class="current"' : '';
					if((isset($item['level']) && $Session->UserLevel <= $item['level']) || !isset($item['level'])){//权限
						$this->menu_td->Lines[] = "<li$css><a href=\"$url\">$name</a></li>";
					}
				}
			}
		}
		$this->menu_td->Lines[] = isLogin() ? "<li><a onclick='if(confirm(\"确定退出吗？\") == true)window.location.href=\"index.php?logout\"' href='#'>退出系统</a></li>" : "<li><a href='?m=TFrmLogin'>登 入</a></li>";
		$this->menu_td->Lines[] = "</ul>\n";
		//$this->menu_td->Lines[] = "</center>\n";index.php?logout
		//提示讯息区
		$query = array();
		if(isset($_GET)){
			foreach($_GET as $key => $value){
				if($key != 'width'){
					$query[] = $key.'='.$value;}
			}
			$url = '?width=480&'.implode('&',$query);
		}else
			$url = '?width=480';
		$this->msg_td->Lines[] = "&nbsp;<a href=\"$url\">大屏</a> $Session->Message";
		//二级菜单
		$li = null;
		foreach($menu2 as $item){
			$url = '?m='.$item['menucode'];
			$name = $item['menuname'];
			if((isset($item['level']) && $Session->UserLevel <= $item['level']) || !isset($item['level'])){//权限
				if($m == $item['menucode']){
					$li.= "<li style='background:#E2E9EA;'><b><a href=\"$url\">$name</a></b></li>";
				}else{
					$li.= $item['menucode'] == null ? "<li><h4>$name</h4></li>" : "<li><a href=\"$url\">$name</a></li>";
				}
			}
		}
		if($li != null)$this->index_td->Lines[] = "<div class='mbox'><ul><li><h4>二级菜单</h4></li>$li</ul></div><br/>";
		//当前路径
		$path1 = isset($path1) ? $path1.' > ' : '首页';
		$path2 = isset($path2) ? $path2 : '';
		//$this->main_td->Lines[] = "&nbsp;当前位置：$path1$path2";
		//动态菜单
		if(count($this->menus) > 0){
			$this->index_td->Lines[] = "<div class='mbox'><ul>\n";
			foreach($this->menus as $item){
				if(is_array($item)){
					if($item[0] <> ''){
						$this->index_td->Lines[] = "<li><a href=\"$item[0]\">$item[1]";
						if(count($item) == 3) $this->index_td->Lines[] = " $item[2]";
						$this->index_td->Lines[] = "</a></li>\n";
					}else{
						$this->index_td->Lines[] = "<li>$item[1]</li>\n";
					}
				}
				else{
					$this->index_td->Lines[] = "<li>"
						. "<h4>$item</h4></li>\n";
				}
			}
			$this->index_td->Lines[] = "</ul></div>\n";
		}
		else{
			$this->index_td->Lines[] = "<div class='mbox'><ul>\n";
			$this->index_td->Lines[] = "<li><h4>当前用户</h4></li>\n";
			$this->index_td->Lines[] = "<li><a href='javascript:'>用户账号：$Session->UserCode</a></li>\n";
			$this->index_td->Lines[] = "<li><a href='javascript:'>用户姓名：$Session->UserName</a></li>\n";
			$this->index_td->Lines[] = "<li><a href='javascript:'>部门代码：$Session->DeptCode</a></li>\n";
			global $APP_DB;
			switch ($APP_DB['TYPE'])
			{
				case 'MSSQL':
					$Table = 'Dept';
					break;
				default:
					$Table = 'WF_Dept';
			}
			$this->index_td->Lines[] = "<li><a href='javascript:'>部门名称：".DBRead("select Name_ from $Table where Code_='$Session->DeptCode'")."</a></li>\n";
			$this->index_td->Lines[] = "</ul></div>\n";
		}
		//版权区
		$this->foot_td->Lines[] = '<p align="center" style="line-height: 150%">'.$copyright.'<br/>';
		$this->foot_td->Lines[] = '主网址：<a href="http://'.$website.'">http://'.$website.'</a><br/>';
		$this->foot_td->Lines[] = '系统管理员：<a href="mailto:'.$admin_email.'">'.$admin_email.'</a>'.'</p>';
	}
}
?>