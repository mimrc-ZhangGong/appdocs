<?php
if( !defined('IN') ) die('bad request');

class TScreen480 extends TScreenBase{
	private $colspan = 1;

	public function __construct(){
		parent::__construct();
		$this->mainbox->Window = $this;
	}

	public function OnBeforeShow(){
		parent::OnBeforeShow();
		//主表格
		$val = 800 - 30;
		echo "<table border='0' id='table1' width='100%' "
			. "style='background-color: #3A6DA4' cellspacing='0'>\n";
		//显示主菜单
		$this->ShowMainMenu();
		//显示状态栏
		$query = array();
		if(isset($_GET)){
			foreach($_GET as $key => $value){
				if($key != 'width'){
					$query[] = $key.'='.$value;}
			}
			$url = '?width=768&'.implode('&',$query);
		}else
			$url = '?width=768';
		$url = "【<a href=\"$url\">小屏</a>】";
		global $Session;
		if($Session->Message){
			echo "<tr><td colspan=\"$this->colspan\" class=\"message\">";
			echo '&nbsp;' . $url . ' ' . $Session->Message;
			
			$login_url = isLogin() ? '<a href="index.php?logout">登出</a>' : '<a href="index.php?m=TFrmLogin">登入</a>';
			
			echo "<span style='float:right;'>【".$login_url."】【<a href='?m=TPadMenu'>菜单</a>】</span></td></tr>\n";
		}
		//主功能区, 动态菜单 and 主显示区
		echo "<tr>\n";
		// if($this->colspan > 1){
			// $this->ShowIndexMenu();
		// }
		//主显示区
		echo "<td height=\"100%\" valign=\"middle\" class=\"main\">\n";
	}
	
	public function OnShow(){
		global $copyright, $admin_email, $website, $PV_TOTAL;
		echo "\n</td></tr>\n";
		echo "<tr height=\"25\">\n";
		echo "<td colspan=\"$this->colspan\" class=\"foot\">\n";
		echo "<p>版权所有：$copyright";
		if($PV_TOTAL){
			echo "<span style='float:right;margin-right:15px;'>$PV_TOTAL</span>";
		}
		echo "</p>\n</td>\n";
		echo "</tr>\n";
		echo "</table>\n";
		echo "</body>\n</html>\n";
	}
	
	public function ShowMainMenu(){
		echo "<tr><td class='topmenu' colspan=\"$this->colspan\">\n";
		echo "<center>\n";
		// $i = 0;
		// global $Session;
		global $appname;
		
		$url = '';
		$name = $appname;
		echo "<a href=\"index.php\">$name</a>";

		echo "\n</center>\n";
		echo "</td></tr>\n";
	}
	
	public function ShowIndexMenu(){
		echo "<td width=\"140 px\" bgcolor=\"#FFFFFF\" valign=\"top\" class=\"menu\">\n";
		//动态菜单
		if(count($this->menus) > 0){
			echo "<table border=\"0\" width=\"100%\">\n";
			foreach($this->menus as $item){
				if(!is_array($item)){
					echo "<tr height=\"25\"><td align=\"center\" bgcolor=\"#0000FF\">"
						. "<font color=\"#FFFFFF\">$item</font></td></tr>\n";
				}
				else{
					echo "<tr height=\"25\"><td><a href=\"$item[0]\">$item[1]";
					if(count($item) == 3) echo " $item[2]";
					echo "</a></td></tr>\n";
				}
			}
			echo "</table>\n";
		}
		echo "</td>\n";
	}
}
?>