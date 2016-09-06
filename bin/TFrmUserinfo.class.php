<?php
require_once("vcl.delphi.php");
if( !defined('IN') ) die('bad request');

class TFrmUserinfo extends TForm
{
	private $tablename = 'wf_userinfo';
	private $fields = array();

	public function OnCreate()
	{
		$dm = new TMainData();
		$this->fields['CorpCode_'] = array('Caption' => '公司别');
		$this->fields['UserCode_'] = array('Caption' => '用户帐号');
		$this->fields['UserName_'] = array('Caption' => '用户姓名');
		$this->fields['QQ_'] = array('Caption' => '邮箱地址');
		$this->fields['Type_'] = array('Caption' => '用户等级');
		$this->fields['Remark_'] = array('Caption' => '备注');
		$this->fields['Enabled_'] = array('Caption' => '启用否');
		$this->fields['UpdateUser_'] = array('Caption' => '更新人员');
		$this->fields['UpdateDate_'] = array('Caption' => '更新日期');
		$this->fields['AppUser_'] = array('Caption' => '建档人员');
		$this->fields['AppDate_'] = array('Caption' => '建档日期');
		//
		$this->Caption = '系统用户管理';
		$this->Message = "此UI界面正在设计中！";

		$this->AddMenu('主索引');
		$this->AddMenu(array($this->GetUrl(), '用户列表'));
		$this->AddMenu(array($this->GetUrl(VIEW_APPEND), '添加用户'));
	}
	
	public function OnDefault()
	{
		$Session = $this->Session;
		$self = $_SERVER['PHP_SELF'];
		//打开数据集
		$ds = new TDataSet();
		$ds->CommandText = "select * from $this->tablename";
		$ds->Open();
		$rec = $ds->RecordCount();

		//显示数据集
		$grid = new TDBGrid($ds);
		//$grid->Show();
		echo "<table border=\"1\" width=\"100%\" id=\"table1\" cellpadding=\"2\" "
			."bordercolorlight=\"#0000FF\" bordercolordark=\"#0000FF\" cellspacing=\"0\">";
		echo "<tr>\n";
		foreach($this->fields as $item)
			$grid->AddHead($item['Caption']);
		if(isLogin())	$grid->AddHead('操作');
		echo "</tr>\n";
		for($i=0; $i<$ds->RecordCount(); $i++)
		{
			$ds->Next();
			echo "<tr>";
			foreach($this->fields as $item => $value){
				$grid->AddItem($ds->FieldByName($item));
			}
			if(isLogin()){
				$uid = $ds->FieldByName('UpdateKey_');
				$url1 = '<a href="' . $this->GetUrl(VIEW_MODIFY, "uid=$uid") . '">修改</a>';
				$url2 = '<a href="' . $this->GetUrl(VIEW_DELETE, "uid=$uid") . '">删除</a>';
				$grid->AddItem($url1 . ' ' . $url2);
			}
			echo "</tr>";
		}
		echo "</table>";
		$grid = null;
		$ds = null;
	}
	
	public function OnAppend(){
		$form = new TEditForm();
		$form->Begin('增加记录');
		$form->AddHidden('mode', 'append');
		foreach($this->fields as $code => $item){
			$form->Add($item['Caption'], $code, '');
		}
		$form->End();
	}

	public function OnModify(){
		$uid = $_GET['uid'];
		//打开数据集
		$ds = new TDataSet();
		$ds->CommandText = "select * from $this->tablename where UpdateKey_='$uid'";
		$ds->Open();
		if($ds->RecordCount() > 0){
			$ds->Next();
			$form = new TEditForm();
			$form->Begin('修改记录');
			$form->AddHidden('mode', 'modify');
			foreach($this->fields as $code => $item){
				$form->Add($item['Caption'], $code, $ds->FieldByName($code));
			}
			$form->End();
		}else{
			echo "<p>Bad Request</p>\n";
		}
	}

	public function OnDelete(){
		echo '请在加入显示申请删除数据的代码';
	}

	public function OnSaveAppend(){
		echo '请在加入申请增加数据保存的代码';
	}

	public function OnSaveModify(){
		echo '请在加入申请修改数据保存的代码';
	}

	public function OnSaveDelete(){
		echo '请在加入申请删除数据保存的代码';
	}
}
?>
