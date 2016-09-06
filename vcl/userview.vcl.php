<?php
if( !defined('IN') ) die('bad request');

class userview extends TDBForm{

	public function OnCreate(){
		parent::OnCreate();
		//
		global $Session;
		$this->TableName = 'WF_UserInfo';
		$this->Fields['CorpCode_'] = array('Caption' => '公司别',
			'view' => false, 'modify' => 'ReadOnly', 'append' => 'ReadOnly',
			'Value' => $Session->CorpCode);
		$this->Fields['DeptName_'] = array('Caption' => '部门名称', 'Value' => '营业部',
			'Hint' => '不允许为空！');
		$this->Fields['UserCode_'] = array('Caption' => '用户帐号');
		$this->Fields['UserName_'] = array('Caption' => '用户姓名');
		$this->Fields['UserPasswd_'] = array('Caption' => '用户密码',
			'view' => false, 'modify' => false, 'append' => true,
			'Value' => '123456');
		$this->Fields['QQ_'] = array('Caption' => 'QQ号码', 'view' => false);
		$this->Fields['Email_'] = array('Caption' => '邮箱地址', 'view' => false);
        $this->Fields['EmailUse_'] = array('Caption' => '使用邮件通知',
			'view' => false,
			'Control' => 'TCheckBox', 'Value' => '0');
		$this->Fields['SMSNo_'] = array('Caption' => '手机号码',
			'view' => false);
        $this->Fields['SMSUse_'] = array('Caption' => '使用手机通知', 
			'view' => false,
			'Control' => 'TCheckBox', 'Value' => '0');
		$this->Fields['Level_'] = array('Caption' => '用户等级', 'Value' => 2,
			'modify' => 'ReadOnly', 'append' => 'ReadOnly',
            'Items' => array('0' => '超级管理员', '1' => '企业管理员', '2' => '一般用户'),
			'Control' => 'TRadioButtons');
		/*
		$this->Fields['BankCode_'] = array('Caption' => '银行帐号',
			'view' => false, 'Hint' => '用于薪资发放，请勿随意输入！');
		*/
		$this->Fields['Remark_'] = array('Caption' => '备注', 'view' => false);
		$this->Fields['Enabled_'] = array('Caption' => '启用否', 'Value' => 0,
            'Items' => array(0 => '未启用', 1 => '启用', 2 => '已停用'),
			'modify' => false, 'append' => false,
			'Control' => 'TRadioButtons');
		$this->Fields['UpdateUser_'] = array('Caption' => '更新人员', 'modify' => false, 'append' => false);
		$this->Fields['UpdateDate_'] = array('Caption' => '更新日期', 'modify' => false, 'append' => false);
		$this->Fields['AppUser_'] = array('Caption' => '建档人员', 'view' => false, 'modify' => false, 'append' => false);
		$this->Fields['AppDate_'] = array('Caption' => '建档日期', 'view' => false, 'modify' => false, 'append' => false);
		$this->Fields['UpdateKey_'] = array('Caption' => '更新标识',
			'view' => false, 'modify' => false, 'append' => false);
		$this->Fields['OP'] = array('Caption' => '操作', 'isData' => false, 'OnGetText' => 'OP_GetText');
		//
		$this->Caption = '企业用户列表-企业管理员';
		$this->Message = '企业用户列表-企业管理员';
		$this->AddMenu('快捷操作');
		$this->AddMenu(array($this->GetUrl(), '用户列表'));
		$this->AddMenu(array($this->GetUrl(VIEW_APPEND), '添加用户'));
		$this->AddMenu(array('?m=UploadUsers', '批次上传用户'));
	}

	public function OnDefault()
	{
		global $Session;
		//打开数据集
		$DataSet = new TDataSet();
		$DataSet->CommandText = "select * from $this->TableName "
			. "where CorpCode_='$Session->CorpCode' and Enabled_<2 "
			. "order by DeptName_,UserCode_";
		$DataSet->Open();
		$rec = $DataSet->RecordCount();
		$this->DataSet = $DataSet;

		//显示数据集
		$grid = new TDBGrid($this);
		$grid->DataSet = $DataSet;
		$grid->Fields = $this->Fields;
		$grid->Show();
		
	}

	public function OnPostAppend(){
		//todo: 请在加入申请增加数据保存的代码
		global $Session;
		$rec = new TPostRecord($this->TableName);
		foreach($this->Fields as $field => $params){
			$fi = new TFieldInfo($field, $params);
			if($fi->isData and $fi->append){
				$value = isset($_POST[$field]) ? $_POST[$field] : null;
                if($fi->Control === 'TCheckBox'){
					$value = isset($_POST[$field]) ? 1 : 0;
                }elseif($field === 'UserPasswd_'){
					$value = md5($value);
				}elseif($field === 'Level_'){
					$value = isset($_POST[$field]) ? $value : 2;
				}
				$rec->__set($field, $value);
			}elseif($field === 'UpdateUser_'){
				$rec->__set($field, $Session->UserCode);
			}elseif($field === 'UpdateDate_'){
				$rec->__set($field, 'Now()');
			}elseif($field === 'AppUser_'){
				$rec->__set($field, $Session->UserCode);
			}elseif($field === 'AppDate_'){
				$rec->__set($field, 'Now()');
			}elseif($field === 'UpdateKey_'){
				$rec->__set($field, 'UUID()');
			}
		}
		$rec->PostAppend();
		$this->OnDefault();
	}
	
	public function OP_GetText($DataSet, $field, $params){
		$uid = $DataSet->FieldByName('UpdateKey_');
		$url1 = "<a href=\"".$this->GetUrl(VIEW_MODIFY, 'uid='.$uid)."\">修改</a>";
		$url2 = "<a href=\"".$this->GetUrl(VIEW_DELETE, 'uid='.$uid)."\">删除</a>";
		if($DataSet->FieldByName('Enabled_') == 1)
			return $url1;
		else
			return $url1 . ' ' . $url2;
	}
}
?>
