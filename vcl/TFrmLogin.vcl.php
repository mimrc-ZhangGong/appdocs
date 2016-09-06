<?php
class TFrmLogin extends TDBForm
{
	public function checkLogin(){
		return true;
	}

	public function OnCreate(){
		parent::OnCreate();
		$this->Caption = '系统登入';
	}
	
	public function OnDefault(){
		global $Session;
		if($Session->Logon){
			echo "<p>您好：$Session->UserName <p/>\n";
			echo "<p>当前帐号：$Session->UserCode <p/>\n";
			$url = $_SERVER['PHP_SELF'] . "?logout";
			echo "<p><a href=\"$url\">退出登录！</a></p>\n";
		}else{
			$url = $this->GetUrl('OnDefault','loginlog','TFrmLogin');
			echo "<center>\n";
			echo "<form method=\"POST\" action=\"$url\" style='padding:15px 0'>\n";
			echo "<p style='margin-bottom: 8px;'>用户帐号：<input style='border:1px solid #999;font-weight:bold;padding:4px 5px;width:150px;' type=\"text\" name=\"UserCode\" size=\"10\"></p>\n";
			echo "<p style='margin-bottom: 8px;'>用户密码：<input style='border:1px solid #999;padding:4px 5px;width:150px;font-weight:bold;' type=\"password\" name=\"password\" size=\"10\"></p>\n";
			echo "<p><input style='padding: 2px 5px;' type=\"submit\" value=\"提交\" name=\"B1\">&nbsp;\n";
			echo "<input style='padding: 2px 5px;' type=\"reset\" value=\"重置\" name=\"B2\"></p>\n";
			echo "</form>\n";
			echo "<center>\n";
			//echo "<center>$Session->Message</center>\n";
		}
	}
}
?>