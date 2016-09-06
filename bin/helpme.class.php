<?php
if( !defined('IN') ) die('bad request');

class helpme extends TDBForm
{
	private $check;
	private $dataset;
	private $id;
	
	public function checkLogin(){
		return true;
	}
	
	public function OnCreate()
	{
		parent::OnCreate();
		$this->Caption = '查看帮助文档';
		//
		if(isset($_GET['id'])){
			$this->id = $_GET["id"];
		}
		elseif(isset($_POST['id'])){
			$this->id = $_POST["id"];
		}
		elseif(isset($_GET['code'])){
			$code = $_GET['code'];
			$funcode = $this->getFuncCode($code);
			if($funcode){
				$this->id = $funcode;
			}
			else{
				$this->check = "<p>找不到 code = $code ！</p>";
			}
		}else{
			$this->id = '100000';
			//$this->check = "<p>未提供文档的关联ID！</p>";
		}
		//
		if(!empty($this->id)){
			//打开数据集
			$ds = new TDataSet();
			$ds->CommandText = "select * from HM_Record where ID_='$this->id'";
			$ds->Open();
			$this->dataset = $ds; 
			if($ds->RecordCount() > 0){
				//搜索是否存在关连记录
				$this->SearchSubItem();
				$ds->Next();
				//显示数据集
				$value = $ds->FieldByName('Subject_');
				$this->Message = "<b>编号</b>：$this->id ，<b>标题</b>：$value";
			}else{
				$this->check = "<p>很抱歉，没有找到相应的文档记录，ID=".$this->id."</p>";
			}
		}
	}

	public function OnDefault()
	{
		if(!empty($this->check)){
			die($this->check);
			exit;
		}
		//显示数据集
		global $Session;
		$ds = $this->dataset;
		echo "<p><b>摘要：</b></p>";
		echo "<p>".$this->rehtml($ds->Body_)."</p>\n";
		echo "<hr/>\n";
		echo "<p><b>建档</b>：".$Session->getUserName($ds->AppUser_)." ";
		echo $ds->AppDate_." ；";
		echo "<b>修改</b>：".$Session->getUserName($ds->UpdateUser_)." ";
		echo $ds->UpdateDate_."</p>\n";
		//显示附件
		$this->ShowFiles();
		//显示文档价值，并允许用户评价
		$this->ShowDocValue();
	}
	
	private function SearchSubItem()
	{ //显示关连文档列表
		global $Session;
		$id = $this->id;
		$pid = DBRead("select RecordID_ from HM_Like where LikeID_=$id", 100000);

		//打开关连记录
		$ds = new TDataSet();
		$ds->CommandText = "SELECT L.LikeID_, R.Subject_ FROM HM_Like L "
			. "INNER JOIN HM_Record R ON L.LikeID_ = R.ID_ "
			. "WHERE L.RecordID_ = '$id' ORDER BY L.It_";
		$ds->Open();
		if($ds->RecordCount() > 0)
			$this->AddMenu('关连文档');
		for($i=0; $i<$ds->RecordCount(); $i++)
		{
			$ds->Next();
			$likeid = $ds->FieldByName('LikeID_');
			$subject = $ds->FieldByName('Subject_');
			$url = array($this->GetUrl(0, "id=$likeid"), $subject);
			$this->AddMenu($url);
		}
		$ds = null;
		if(isLogin()){
			$this->AddMenu('增加资料');
			$this->AddMenu(array("?m=records&mode=append&pid=$id", '增加子文档'));
			$this->AddMenu(array("?m=TFrmFileList&mode=append&pid=$id", '增加说明文件'));
			$this->AddMenu(array("?m=menudict&id=$id", '功能代码维护与查看'));
		}
		//
		if((isLogin()))
			$this->AddMenu('快速选择');
		if(isLogin())
			$this->AddMenu(array("?m=records&mode=modify&id=$id", '修改此文档'));
		if($id != 100000)
			$this->AddMenu(array($this->GetUrl(0, "id=$pid"), '返回父文档'));
	}
	
	private function ShowFiles()
	{ //显示文档附件
		$id = $this->id;
		if(OnSAE()){
			//显示出附件：
			$records = mysql_query("select count(*) as count from HM_Files where RecordID_=$id"
				. " and (FileName_ like '%.html' or FileName_ like '%.htm')");
			if(mysql_fetch_object($records)->count == 0)
				$records = mysql_query("select FileName_,UpdateKey_ from HM_Files where RecordID_=$id");
			else
				$records = mysql_query("select FileName_,UpdateKey_ from HM_Files where RecordID_=$id"
				. " and (FileName_ like '%.html' or FileName_ like '%.htm')");
			if(!$records){
				echo $sql . '<br/>';
				echo mysql_error() . '<br/>';
			}
			if(mysql_num_rows($records) > 0){
				echo '<hr/>';
			}
			$ss = new SaeStorage();
			while ($row = mysql_fetch_object($records))
			{
				$fn = $id . '/' . $row->FileName_;
				if($ss->fileExists("files", $fn)){
					echo
					"<iframe id=\"file\" align=\"top\" width=\"700\" scrolling=\"auto\" height=\"580\" "
					. "frameborder=\"0\" src=\"http://appdocs-files.stor.sinaapp.com/$fn\"\"></iframe>";
				}
				else{
						$result = mysql_query("delete from HM_Files where UpdateKey_='$row->UpdateKey_'");
						echo "<p>文件 $fn 丢失，自动修复！</p>\n";
				}
			}
		}else{
			echo "<p>非SAE环境，无法显示附件！</p>\n";
		}
	}
	
	private function ShowDocValue()
	{ //显示文档价值，并允许用户评价
		$id = $this->id;
		$rsMsg = null;
		$comment = isset($_GET["value"]) ? $_GET["value"] : null;
		if($comment)
		{
			$IPAddr = $_SERVER["REMOTE_ADDR"];
			if(!DBExists("select RecordID_ from HM_Values where RecordID_=$id and Address_='$IPAddr'"
				. " and TIMEDIFF(AppDate_, NOW())<'01:00:00'"))
			{
				$sql = "INSERT INTO HM_Values(RecordID_,Value_,Remark_,Address_,AppUser_,AppDate_,UpdateKey_) VALUES("
					. "$id, $comment, NULL, '$IPAddr', 'system', NOW(), UUID())";
				if(!mysql_query($sql))
				{
					$rsMsg = mysql_error() . '<br/>';
				}
				if(mysql_affected_rows() == 1){
					$rsMsg = "您已评论成功！\t\n";
				}
			}
			else
			{
				$rsMsg = "您已经评论过该文档，请于1小时后再评论！\n";
			}
		}
		$value1 = DBRead("select count(*) from HM_Values where RecordID_=$id and Value_=1", 0);
		$value2 = DBRead("select count(*) from HM_Values where RecordID_=$id and Value_=2", 0);
		$value3 = DBRead("select count(*) from HM_Values where RecordID_=$id and Value_=3", 0);
		echo '<hr/>';
		echo "<p>以上资料有解决您的问题吗？请告诉我们：</p>";
		echo "<p>\n";
		echo "<a href=\"".$this->GetUrl(0,"id=$id&value=1")."\">没有($value1)</a>\t\n";
		echo "<a href=\"".$this->GetUrl(0,"id=$id&value=2")."\">有一点用($value2)</a>\t\n";
		echo "<a href=\"".$this->GetUrl(0,"id=$id&value=3")."\">有解决问题($value3)</a>\t\n";
		if($rsMsg) echo $rsMsg;
		echo "</p><br/>";
	}
	
	private function rehtml($content)
	{ //将备注字段中的回车符转成html格式
		$content = str_replace("&","&amp;",$content);
		$content = str_replace("<","&lt;",$content);
		$content = str_replace(">","&gt;",$content);
		$content = str_replace(" ","&nbsp;",$content);
		$content = str_replace(chr(13),"<br/>",$content);
		$content = str_replace(chr(34),"&quot;", $content);
		return $content;
	}
	
	private function getFuncCode($code)
	{
		$ds = new TDataSet();
		$ds->CommandText = "select RecordID_ from HM_Menus where Code_='$code'";
		$ds->Open();
		if($ds->RecordCount() == 1)
		{
			$ds->Next();
			return $ds->FieldByName('RecordID_');
		}
		$ds = null;
	}
}
?>
