<?php
if( !defined('IN') ) die('bad request');

class menudict extends TDBForm{
	private $id;

	public function OnCreate(){
		parent::OnCreate();
		$this->Caption ='文档代码与功能代码关系维护';
		$this->AddMenu(array('?m=TFrmFileList', '云存储文件列表'));
		$id = isset($_GET['id']) ? $_GET['id'] : null;
		if($id){
			$this->AddMenu(array("?m=helpme&id=$id", '返回当前文档'));
			$this->AddMenu(array($this->GetUrl(VIEW_APPEND, "id=$id"), '增加'));
			$this->id = $id;
		}
		global $Session;
		$this->TableName = 'HM_Menus';
		$this->Fields['RecordID_'] = array('Caption' => '文档代码',
			'append' => 'ReadOnly', 'modify' => 'ReadOnly',
			'Value' => $id);
		$this->Fields['Code_'] = array('Caption' => '功能代码');
		$this->Fields['AppUser_'] = array('Caption' => '建档人员',
			'append' => 'ReadOnly', 'modify' => 'ReadOnly',
			'Value' => $Session->UserCode);
		$this->Fields['AppDate_'] = array('Caption' => '建档时间',
			'append' => 'ReadOnly', 'modify' => 'ReadOnly',
			'Value' => date('Y-m-d'));
		$this->Fields['OP'] = array('Caption' => '操作',
			'isData' => false, 'OnGetText' => 'OP_GetText');
	}

	public function OnDefault()
	{
		$id = isset($_GET['id']) ? $_GET['id'] : null;
		if($id){
			//打开数据集
			$DataSet = new TDataSet();
			$DataSet->CommandText = "select * from $this->TableName "
				. "where RecordID_='$id'";
			$DataSet->Open();
			$rec = $DataSet->RecordCount();
			$this->DataSet = $DataSet;

			//显示数据集
			$grid = new TDBGrid($this);
			$grid->DataSet = $DataSet;
			$grid->Fields = $this->Fields;
			$grid->Show();
		}
	}
	
	public function OnModify(){
		$uid = $_GET['uid'];
		//打开数据集
		global $Session;
		$ds = new TDataSet();
		$ds->CommandText = "select * from $this->TableName "
			. "where UpdateKey_='$uid'";
			$ds->Open();
		if($ds->RecordCount() > 0){
			$ds->Next();
			$form = new TEditForm($this);
			$form->AddHidden('mode', 'modify');
			$form->AddHidden('uid', $uid);
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
	/*
	public function OnDefault(){
		$id = $this->id;
		if($id){
			$Captions = array('RecordID_' => '文档代码','Code_' => '功能代码', 
				'AppUser_' => '建档人员', 'AppDate_' => '建档时间');
				
			$remarks = array('RecordID_' => '不可修改','Code_' => '不可为空',
				'AppUser_' => '不可修改', 'AppDate_' => '不可修改');
				
			$ds = new TDataSet();
			$ds->CommandText = "select * from HM_Menus where RecordID_=$id";
			$ds->Open();
			
			$form = new TEditForm($this->GetUrl());
			$form->Begin();
			if($ds->RecordCount() == 0)
			{
				$form->AddHidden('id', $id);
				$form->AddHidden('mode', 'append');
				$form->Add("文档代码: ", "RecordID_", "$id", "不可修改");
				$form->Add("功能代码: ", "Code_", "", "不可为空");
				$form->Add("建档人员: ", "AppUser_", "", "不可修改");
				$form->Add("建档时间: ", "AppDate_", "", "不可修改");
			}
			else
			{
				$form->AddHidden('id', $id);
				$form->AddHidden('mode', 'modify');
				$ds->Next();
				for($i=0; $i < $ds->FieldCount(); $i++)
				{
					$id = $ds->getFieldName($i);
					$value = $ds->FieldByName($id);
					$remark = '';
					if(array_key_exists($id, $Captions))
					{
						$Caption = $Captions[$id];
						$remark = array_key_exists($id, $remarks) ? $remarks[$id] : '';
						$form->Add($Caption.': ', $id, $value, $remark);
					}
				}
			}
			$form->End();
			$form = null;
			$ds = null;
		}else{
			echo "<p>非法的使用方式！</p>";
		}
	}
	
	public function OnSaveAppend()
	{
		$id = $_POST["RecordID_"];
		if($id)
		{
			$this->id = $id;
			$code = $_POST["Code_"];
			$appuser = $mainface->session->usercode;
			$sql = "insert into HM_Menus(RecordID_,Code_,AppUser_,AppDate_,UpdateKey_) values($id,'$code','$appuser',NOW(),UUID())";
			if(!mysql_query($sql))
			{
				echo $sql . '<br/>';
				echo mysql_error() . '<br/>';
			}
		}
		$this->OnDefault();
	}
	
	public function OnSaveModify()
	{
		$id = $_POST["RecordID_"];
		if($id)
		{
			$this->id = $id;
			$code = $_POST["Code_"];
			$appuser = $mainface->session->usercode;
			$sql = "update HM_Menus set Code_='$code' where RecordID_=$id";
			if(!mysql_query($sql))
			{
				echo $sql . '<br/>';
				echo mysql_error() . '<br/>';
			}
		}
		$this->OnDefault();
	}
	*/
}
?>