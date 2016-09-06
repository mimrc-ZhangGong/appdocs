<?php
if( !defined('IN') ) die('bad request');

class records extends TDBForm{
	
	public function OnCreate(){
		parent::OnCreate();
		$this->Caption = '云文档服务中心';
		if(isLogin()){
			Switch($this->Action){
			case VIEW_APPEND:
				$pid = $_GET["pid"];
				if(!$pid) $pid = '100000';
				$this->AddMenu('快捷菜单');
				$this->AddMenu(array('?m=TFrmFileList', '文档附件管理'));
				$this->AddMenu(array("?m=helpme&id=$pid", '返回文档'));
				break;
			case VIEW_MODIFY:
				$id = $_GET["id"];
				$this->AddMenu('快捷菜单');
				$this->AddMenu(array('?m=TFrmFileList', '文档附件管理'));
				$this->AddMenu(array("?m=helpme&id=$id", '返回文档'));
				break;
			case POST_APPEND:
				$this->AddMenu('快捷菜单');
				$this->AddMenu(array('?m=TFrmFileList', '文档附件管理'));
				$this->AddMenu(array('?m=records&mode=append', '增加文档'));
				break;
			case POST_MODIFY:
				$id = $_POST["ID_"];
				$this->AddMenu('快捷菜单');
				$this->AddMenu(array('?m=TFrmFileList', '文档附件管理'));
				$this->AddMenu(array("?m=helpme&id=$id", '返回文档'));
				break;
			case VIEW_DELETE:
				$id = $_GET["id"];
				$pid = $_GET["pid"];
				if(!$pid) $pid = '100000';
				$this->AddMenu('作业检查');
				$this->AddMenu(array("?m=helpme&id=$pid", '返回父文档'));
				$this->AddMenu(array("?m=helpme&id=$id", '查看本文档'));
				break;
			default:
				$this->AddMenu('快捷菜单');
				$this->AddMenu(array('?m=TFrmFileList', '文档附件管理'));
				$this->AddMenu(array('?m=records&mode=append"', '增加文档'));
				break;
			}
		}
		//
		global $Session;
		$this->TableName = 'HM_Record';
		$this->Fields['ParentID_'] = array('Caption' => '父编号',
			'modify' => 'ReadOnly',
			'append' => 'ReadOnly', 'Value' => isset($_GET['pid']) ? $_GET['pid'] : '100000');
		$this->Fields['ID_'] = array('Caption' => '资料ID',
			'OnGetText' => 'ID_GetText', 'modify' => 'ReadOnly', 'append' => true);
		$this->Fields['Subject_'] = array('Caption' => '标题',
			'Hint' => '不可为空');
		$this->Fields['Body_'] = array('Caption' => '摘要',
			'view' => false, 'Hint' => '允许为空', 'Control' => 'TMemo');
		$this->Fields['Type_'] = array('Caption' => '类别');
		$this->Fields['Final_'] = array('Caption' => '确认否',
			'Hint' => '0.待确认；1.确认');
		$this->Fields['UpdateUser_'] = array('Caption' => '更新人员',
			'OnGetText' => 'User_GetText', 'modify' => 'ReadOnly', 'append' => false);
		$this->Fields['UpdateDate_'] = array('Caption' => '更新日期', 'view' => false,
			'modify' => 'ReadOnly', 'append' => false);
		$this->Fields['AppUser_'] = array('Caption' => '建档人员', 'view' => false,
			'modify' => 'ReadOnly', 'append' => false);
		$this->Fields['AppDate_'] = array('Caption' => '建档日期', 'view' => false,
			'modify' => 'ReadOnly', 'append' => false);
		$this->Fields['UpdateKey_'] = array('Caption' => '更新标识', 'view' => false,
			'modify' => false, 'append' => false);
		if(isLogin()){
			$this->Fields['OP'] = array('Caption' => '操作',
				'isData' => false, 'OnGetText' => 'OP_GetText');
		}
	}
	
	public function OnDefault()
	{
		if($this->TableName === ''){
			$this->BadRequest();
			exit;
		}
		
		//打开数据集
        global $Session;
		$while = '';
		if(isset($_GET['today'])){
			$while = "where AppDate_>='" . date("Y/m/d") . "'";
		}
		if(isset($_GET['mydocs'])){
			$while = "where AppUser_='" . $Session->UserCode . "'";
		}
		
		//打开数据集
		$DataSet = new TDataSet();
		$DataSet->CommandText = "select * from HM_Record $while order by ParentID_,ID_";
		$DataSet->Open();
		$rec = $DataSet->RecordCount();
		$this->DataSet = $DataSet;

		//显示数据集
		$grid = new TDBGrid($this);
		$grid->DataSet = $DataSet;
		$grid->Fields = $this->Fields;
		$grid->Show();
	}
	
	public function ID_GetText($DataSet, $FieldCode, $FieldInfo){
		$id = $DataSet->FieldByName('ID_');
		return "<a href=\"?m=helpme&id=$id\">$id</a>";
	}
	
	public function OP_GetText($DataSet, $FieldCode, $FieldInfo){
		$uid = $DataSet->FieldByName('UpdateKey_');
		$url1 = "<a href=\"".$this->GetUrl(VIEW_MODIFY,"uid=$uid")."\">修改</a>";
		$url2 = "<a href=\"".$this->GetUrl(VIEW_DELETE,"uid=$uid")."\">删除</a>";
		return $url1 . ' ' . $url2;
	}
	
	public function User_GetText($DataSet, $FieldCode, $FieldInfo){	
		global $Session;
		$UserCode = $DataSet->FieldByName($FieldCode);
		return $Session->getUserName($UserCode);
	}

	public function OnModify(){
		if(isset($_GET['uid'])){
			$id = $_GET['uid'];
			$condition = 'UpdateKey_';
		}
		elseif(isset($_GET['id'])){
			$id = $_GET['id'];
			$condition = 'ID_';
		}
		else{
			$this->BadRequest();
			exit;
		}
		//打开数据集
		$ds = new TDataSet();
		$ds->CommandText = "select * from $this->TableName where $condition='$id'";
		$ds->Open();
		if($ds->RecordCount() > 0){
			$ds->Next();
			$form = new TEditForm($this);
			$form->AddHidden('mode', 'modify');
			$form->AddHidden('uid', $ds->UpdateKey_);
			$form->DataSet = $ds;
			foreach($this->Fields as $code => $param){
				$fi = new TFieldInfo($code, $param);
				if($fi->isData and $fi->modify){
					$edit = new $fi->Control($this);
					$edit->Window = $form;
					$edit->DataSet = $ds;
					$edit->LinkField($code, $fi); 
				}
				$fi = null;
			}
			$form->Show();
		}else{
			echo "<p>Bad Request</p>\n";
		}
	}

	public function OnDelete(){
		if(!isset($_GET['uid'])){
			$this->BadRequest();
			exit;
		}
		$uid = $_GET['uid'];
		$ds = new TDataSet();
		$ds->Open("select ParentID_,ID_ from HM_Record where UpdateKey_='$uid'");
		if(!$ds->Next()){
			$this->BadRequest();
			exit;
		}
		$pid = $ds->ParentID_;
		$id = $ds->ID_;
		if($id === '100000'){
			echo "<p>根节点 $id ，不可删除</p>";
			exit;
		}
		if(!DBExists("SELECT RecordID_ FROM HM_Like WHERE RecordID_='$pid' AND LikeID_='$id'"))
		{
			echo "<p>不存在文档记录 $pid 的关联子记录 $id ，请核查！</p>";
		}
		$sql = "DELETE FROM HM_Like WHERE RecordID_='$pid' AND LikeID_='$id'";
		if(!mysql_query($sql))
		{
			echo $sql . '<br/>';
			echo mysql_error() . '<br/>';
		}
		if(!DBExists("SELECT LikeID_ FROM HM_Like WHERE RecordID_='$id'"))
		{
			$sql = "delete from HM_Record where id_='$id'";
			//需要删除相关的files
			if(SAE_MYSQL_HOST_M <> '127.0.0.1')
			{
				$records = mysql_query("select FileName_ from HM_Files where RecordID_='$id'");
				if(!$records){
					echo $sql . '<br/>';
					echo mysql_error() . '<br/>';
				}
				$ss = new SaeStorage();
				while ($row = mysql_fetch_object($records))
				{
					$fn = $id . '/' . $row->FileName_;
					if($ss->fileExists("files", $fn)) $ss->delete('files', $fn);
				}
			}				
		}
		else
			$sql = "UPDATE HM_Record SET ParentID_=ID_ WHERE ID_='$id'";
		if(!mysql_query($sql))
		{
			echo $sql . '<br/>';
			echo mysql_error() . '<br/>';
		}
		else
			echo "<p>文档记录 $pid 的关连子记录 $id 删除成功！</p>";
	}
	
	/*
	public function OnAppend(){
		$pid = isset($_GET['pid']) ? $_GET['pid'] : '100000';
		$form = new TEditForm($this->GetUrl());
		$form->Caption = '增加文档记录';
		$form->AddHidden('mode', 'append');
		$form->Add('归属编号：', 'pid', $pid, '此值不可随意修改，否则会保存失败！');
		$form->Add('文档编号：', 'id', '');
		$form->Add('文档标题：', 'subject', '', '若文档编号已存在，则此栏位不需要输入');
		$form->Add('摘要内容：', 'body', '');
		$form->Add('建档人员：', 'appuser', $this->Session->usercode);
		$form->Show();
	}
	*/

	public function OnPostAppend(){
		$id = $_POST['ID_'];
		$pid = $_POST['ParentID_'];
        global $Session;
		$appuser = $Session->UserCode;
		if(DBExists("select id_ from HM_Record where ID_=$pid")){
			if(!DBExists("select id_ from HM_Record where ID_=$id")){
				$subject = $_POST['Subject_'];
				$remark = $_POST['Body_'];				
				$sql = "INSERT INTO `HM_Record` (`ParentID_`,`ID_`, `Subject_`, `Body_`, `IndexFile_`, `Type_`, `Final_`, "
					. "`UpdateUser_`, `UpdateDate_`, `AppUser_`, `AppDate_`, `UpdateKey_`) VALUES"
					. "($pid, $id, '$subject', '$remark', NULL, 0, 1, '$appuser', NOW(), '$appuser', NOW(), UUID())";
				if(!mysql_query($sql)){
					echo $sql . '<br/>';
					echo mysql_error() . '<br/>';
				}
				if(mysql_affected_rows() == 1){
					echo "<p>增加文档记录 $id 成功！</p>";
					echo "<p>按此<a href=\"?m=helpme&id=$id\">查看本文档</a></p>";
					echo "<p>按此<a href=\"?m=records&mode=append&pid=$pid\">继续增加</a></p>";
				}
			}
			else
			{
				$sql = "update HM_Record set ParentID_=$pid where ID_=$id";
				if(!mysql_query($sql)){
					echo $sql . '<br/>';
					echo mysql_error() . '<br/>';
				}
				if(mysql_affected_rows() == 1){
					echo "<p>更新文档记录 $id 的父文档为 $pid 成功！</p>";
					echo "<p>按此<a href=\"?m=helpme&id=$id\">查看本文档</a></p>";
					echo "<p>按此<a href=\"?m=records&mode=append&pid=$pid\">继续增加</a></p>";
				}
				$it = DBRead("select max(It_) from HM_Like where RecordID_=$pid", 0) + 1;
				$sql = "update HM_Like set RecordID_=$pid,It_=$it where LikeID_=$id";
				if(!mysql_query($sql)){
					echo $sql . '<br/>';
					echo mysql_error() . '<br/>';
				}	
			}
			if(!DBExists("select RecordID_ from HM_Like where RecordID_=$pid and LikeID_=$id"))
			{
				$it = DBRead("select max(It_) from HM_Like where RecordID_=$pid", 0) + 1;
				$sql = "INSERT INTO `HM_Like` (`RecordID_`, `It_`, `LikeID_`, `AppUser_`, `AppDate_`, `UpdateKey_`) VALUES"
					. "($pid, $it, $id, '$appuser', NOW(), UUID())";
				if(!mysql_query($sql)){
					echo $sql . '<br/>';
					echo mysql_error() . '<br/>';
				}
				if(mysql_affected_rows() == 1){
					echo "<p>增加文档记录 $pid 关连子记录 $id 成功！</p>";
				}
			}
			else
			{
				echo "<p>文档记录 $pid 关联子记录  $id 已存在！</p>";
			}
			echo "<p>按此<a href=\"helpme.php?id=$pid\">查看父文档</a></p>";
		}
		else{
			echo "<p>归属 $pid 不存在，无法增加！</p>";
		}
	}
}
?>
