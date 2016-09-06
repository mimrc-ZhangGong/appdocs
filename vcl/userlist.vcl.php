<?php
if( !defined('IN') ) die('bad request');

class userlist extends TDBForm{
	private $CusCode;

	public function checkLogin(){
		if(uLevel() > 0){
			return false;
		}else{
			return parent::checkLogin();
		}
	}

	public function OnCreate(){
		parent::OnCreate();
		//
		$this->CusCode = isset($_GET['CusCode']) ? $_GET['CusCode'] : null;
		global $Session;
		$this->TableName = 'WF_UserInfo';
		$this->Fields['CorpCode_'] = array('Caption' => '公司别',
			'Value' => $this->CusCode ? $this->CusCode : $Session->CorpCode);
		$this->Fields['DeptName_'] = array('Caption' => '部门名称', 'Value' => '营业部',
			'Hint' => '不允许为空！');
		$this->Fields['UserCode_'] = array('Caption' => '用户帐号',
			'modify' => true,
			'Value' => $this->CusCode . '01');
		$this->Fields['UserName_'] = array('Caption' => '用户姓名');
		$this->Fields['UserPasswd_'] = array('Caption' => '登录密码',
			'view' => false, 'append' => true, 'modify' => false,
			'Value' => '123456');
		$this->Fields['QQ_'] = array('Caption' => 'QQ号码', 'view' => false);
        $this->Fields['EmailUse_'] = array('Caption' => '使用邮件通知',
			'view' => false,
			'Control' => 'TCheckBox', 'Value' => '0');
		$this->Fields['Email_'] = array('Caption' => '邮箱地址', 'view' => false);
        $this->Fields['SMSUse_'] = array('Caption' => '使用手机通知',
			'view' => false,
			'Control' => 'TCheckBox', 'Value' => '0');
		$this->Fields['SMSNo_'] = array('Caption' => '手机号码', 'view' => false);
		$this->Fields['Level_'] = array('Caption' => '用户等级', 'Value' => 2,
			'Items' => array('0' => '超级管理员', '1' => '企业管理员', '2' => '一般用户'),
			'Control' => 'TRadioButtons');
		$this->Fields['Remark_'] = array('Caption' => '备注', 'view' => false);
		$this->Fields['Enabled_'] = array('Caption' => '启用否', 'Value' => 0,
            'Items' => array(0 => '未启用', 1 => '启用', 2 => '已停用'),
			//'OnGetText' => 'Enabled_GetText',
			'Control' => 'TRadioButtons');
		$this->Fields['UpdateUser_'] = array('Caption' => '更新人员', 'modify' => false, 'append' => false);
		$this->Fields['UpdateDate_'] = array('Caption' => '更新日期', 'view' => false, 'modify' => false, 'append' => false);
		$this->Fields['AppUser_'] = array('Caption' => '建档人员', 'view' => false, 'modify' => false, 'append' => false);
		$this->Fields['AppDate_'] = array('Caption' => '建档日期', 'view' => false, 'modify' => false, 'append' => false);
		$this->Fields['UpdateKey_'] = array('Caption' => '更新标识', 'view' => false, 'modify' => false, 'append' => false);
		$this->Fields['OP'] = array('Caption' => '操作', 'isData' => false, 'OnGetText' => 'OP_GetText');
		//
		$this->Caption = '企业用户列表-超级管理员';
		$this->Message = "企业用户列表-超级管理员";
		$this->AddMenu('快捷操作');
		$param = $this->CusCode ? "CusCode=$this->CusCode" : '';
		$this->AddMenu(array($this->GetUrl(0, $param), '用户列表'));
		$this->AddMenu(array($this->GetUrl(VIEW_APPEND, $param), '增加新用户'));
		$this->AddMenu(array($this->GetUrl(0,'a=Check'), '待审核帐号'));
		if(!OnSAE()){
			$this->AddMenu('后台管理');
			$this->AddMenu(array('http://localhost/phpmyadmin/', 'MYSQL数据库'));
		}
	}
	
	public function OnDefault()
	{
		//打开数据集
		$DataSet = new TDataSet();
		$DataSet->CommandText = "select * from $this->TableName";
		if($this->CusCode){
			$DataSet->CommandText .= " where CorpCode_='$this->CusCode'";
		}
		$DataSet->CommandText .= " order by CorpCode_,DeptName_,UserCode_";
		$DataSet->Open();
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
		$usercode = $DataSet->FieldByName('UserCode_');
		$url1 = "<a href=\"".$this->GetUrl(VIEW_MODIFY, 'uid='.$uid)."\">修改</a>";
		$url2 = "<a href=\"".$this->GetUrl(VIEW_DELETE, 'uid='.$uid)."\">删除</a>";
		$url3 = "<a href=\"".$this->GetUrl('ResetPassword', 'usercode='.$usercode)."\">重设密码</a>";
		return $url1 . ' ' . $url2 . ' ' . $url3;
	}
	public function OnModify(){
		$uid = $_GET['uid'];
		//打开数据集
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
	
	public function OnDelete(){
		$uid = $_GET['uid'];
		$form = new TEditForm($this);
		$form->Caption = '请确认';
		$form->AddHidden('mode', 'delete');
		$form->AddHidden('uid', $uid);
		
		//显示要删除的内容
		$ds = new TDataSet();
		$ds->Open("select CorpCode_,UserCode_, UserName_ from WF_UserInfo "
			. "where UpdateKey_='$uid'");
		$ds->Next();
		$edt0 = new TEdit($this);
		$edt0->Caption = '公司别';
		$edt0->Text = $ds->CorpCode_;
		$edt0->ReadOnly = true;
		$edt0->Window = $form;
		//
		$edt1 = new TEdit($this);
		$edt1->Caption = '用户帐号';
		$edt1->Text = $ds->UserCode_;
		$edt1->ReadOnly = true;
		$edt1->Window = $form;
		//
		$edt2 = new TEdit($this);
		$edt2->Caption = '用户姓名';
		$edt2->Text = $ds->UserName_;
		$edt2->ReadOnly = true;
		$edt2->Window = $form;
		//再次确认
		$rb = new TRadioButtons($this);
		$rb->Name = 'confirm';
		$rb->Caption = '确认删除';
		$rb->Items['yes'] = '确认';
		$rb->Items['no'] = '取消';
		$rb->Value = 'no';
		$rb->Window = $form;
		$form->Show();
	}
	
	public function OnPostDelete(){
		//todo: 请在加入显示申请删除数据的代码
		$uid = isset($_POST['uid']) ? $_POST['uid'] : null;
		if($uid and ($_POST['confirm'] == 'yes')){
			$sql = "delete from $this->TableName where UpdateKey_='$uid'";
			$DataSet = new TDataSet();
			$DataSet->CommandText = $sql;
			$DataSet->Execute();
			$this->OnDefault();
		}else{
			echo 'bad request';
		}
	}

	public function Check(){
		//todo: 待审核帐号
		//打开数据集
		$ds = new TDataSet();
		$ds->CommandText = "select * from $this->TableName where Enabled_=0";
		$ds->Open();

		//显示数据集
		$grid = new TDBGrid($this);
		$grid->DataSet = $ds;
		$grid->Fields = $this->Fields;
		$grid->Show();
	}

	public function ResetPassword(){ //重设密码
		$usercode = new TEdit($this);
		$usercode->ReadOnly = true;
		$usercode->Caption = '您要重设的用户';
		$password = new TEdit($this);
		$password->Caption = '新的密码';
		$password->Hint = '不允许为空';
		//
		if(isset($_GET['usercode'])){
			$form = new TEditForm($this);
			$form->AddHidden('a', 'OnResetPassword');
			$usercode->Text = $_GET['usercode'];
			$usercode->Window = $form;
			$password->Window = $form;
			$form->Show();
		}else{
			echo 'bad request';
		}
	}
	
	public function OnResetPassword(){ //响应重设密码请求
		$usercode = new TEdit($this);
		$password = new TEdit($this);
		//
		if($usercode->checkInput()){
			$usercode = $usercode->Text;
			if($password->checkInput()){
				$rec = new TPostRecord('WF_UserInfo');
				$rec->UserPasswd_ = md5($password->Text);
				$rec->PostModify("UserCode_='$usercode'");
				echo "用户 $usercode 密码已重新设置为临时密码 $password->Text ，请通知用户及时修改密码！";
				global $appname;
				$this->TalkUser($usercode, '您登录到'.$appname
					.'系统密码已被重置为临时密码'.$password->Text.
					',请速进入系统修改此密码');
			}else{
				echo '密码不允许为空！';
			}
		}else{
			echo 'bad request';
		}
	}
}
/*数据结构
ALTER TABLE `WF_UserInfo` ADD `DeptName_` VARCHAR( 30 ) NOT NULL AFTER `CorpCode_` 
*/
?>
