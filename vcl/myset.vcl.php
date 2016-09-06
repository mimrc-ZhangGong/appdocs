<?php
if( !defined('IN') ) die('bad request');

class myset extends TDBForm{

	private $OldPWD;
	private $NewPWD1;
	private $NewPWD2;

	public function OnCreate(){
		parent::OnCreate();
		//
		$this->TableName = 'WF_UserInfo';
		$this->Fields['CorpCode_'] = array('Caption' => '公司别',
			'append' => 'ReadOnly', 'modify' => 'ReadOnly');
		$this->Fields['UserCode_'] = array('Caption' => '用户帐号',
			'append' => 'ReadOnly', 'modify' => 'ReadOnly');
		$this->Fields['UserName_'] = array('Caption' => '用户姓名',
			'append' => 'ReadOnly');
		$this->Fields['Level_'] = array('Caption' => '用户等级',
			'append' => 'ReadOnly', 'modify' => 'ReadOnly',
			'Items' => array(0 => '超级管理员', 1 => '企业管理员', 2 => '普通用户'),
			'Control' => 'TRadioButtons');
		$this->Fields['EmailUse_'] = array('Caption' => '使用邮件通知',
			'Control' => 'TCheckBox');
		$this->Fields['Email_'] = array('Caption' => '邮件地址',
			'append' => 'ReadOnly');
		$this->Fields['SMSUse_'] = array('Caption' => '使用手机通知',
			'Control' => 'TCheckBox');
		$this->Fields['SMSNo_'] = array('Caption' => '手机号码',
			'append' => 'ReadOnly');
		$this->Fields['QQ_'] = array('Caption' => 'QQ号码',
			'append' => 'ReadOnly');
        $this->Fields['Image_'] = array('Caption' => '更换头像',
            'view' => false, 'OnGetHtml'=>'Img_GetHtml');
		//
		$this->Caption = '修改登录密码';
		$this->Message = "请注意密码安全性！";
        if(!OnSAE()){
			$this->AddMenu('辅助工具');
            $this->AddMenu(array('?m=hello', '功能测试'));
        }

		//
		$this->OldPWD = new TEdit($this);
		$this->OldPWD->AllowNull = true;
		$this->OldPWD->Caption = '原有密码';
		$this->OldPWD->password = true;
		//
		$this->NewPWD1 = new TEdit($this);
		$this->NewPWD1->Caption = '新的密码';
		$this->NewPWD1->password = true;
		$this->NewPWD1->AllowNull = false;
		//
		$this->NewPWD2 = new TEdit($this);
		$this->NewPWD2->Caption = '再输入一次';
		$this->NewPWD2->password = true;
		$this->NewPWD2->AllowNull = false;
		//
		$this->AddMenu('操作');
		$this->AddMenu(array($this->GetUrl('UpdatePassword'), '修改我的密码'));
        //$this->AddMenu(array($this->GetUrl('UpdateAll'), '重置所有密码'));
	}

    public function UpdateAll(){
        global $Session;
        $ds = new TDataSet();
        $ds->CommandText = "select UserCode_ from WF_UserInfo "
            . "where CorpCode_='$Session->CorpCode' and Enabled_=1 and UserCode_<>'$Session->UserCode'";
        $ds->Open();
        for($i = 0; $i < $ds->RecordCount(); $i++){
            $ds->Next();
            $ds->ExecSQL("update WF_UserInfo set UserPasswd_='".md5($ds->UserCode_.'123456')."' where UserCode_='$ds->UserCode_'");
        }
    }

	public function OnDefault(){
		//打开数据集
		global $Session;
		$ds = new TDataSet();
		$ds->CommandText = "select * from $this->TableName "
			. "where CorpCode_='$Session->CorpCode' and UserCode_='$Session->UserCode'";
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
	
	public function OnPostModify(){
		if(    isset($_POST['Level_'])
			or isset($_POST['CorpCode_'])
			or isset($_POST['UserCode_'])){ //防止恶意提交
			$this->BadRequest();
		}else{
			parent::OnPostModify();
			echo '您的修改已保存成功！<br/>';
		}
	}

	public function UpdatePassword(){
		$form = new TEditForm($this);
		$form->AddHidden('a', 'OnUpdatePassword');
		$this->OldPWD->AllowNull = false;
		$this->OldPWD->Window = $form;
		$this->NewPWD1->Window = $form;
		$this->NewPWD2->Window = $form;
		$form->Show();
	}
	
	public function OnUpdatePassword(){
		global $Session;
		if($this->OldPWD->checkInput()){
			if($this->NewPWD1->checkInput()){
				if($this->NewPWD1->Text === $this->NewPWD1->Text){
					$oldpwd = DBRead("select UserPasswd_ from WF_UserInfo "
						. "where UserCode_='$Session->UserCode'");
                    $TempCode = explode('@', $Session->UserCode);
                    if (count($TempCode) === 2){
                        $corp = $TempCode[1];
                        if(DBExists("select Code_ from WF_CusList where Code_='$corp'"))
                            $Account = $TempCode[0];
                        else
                            $Account = $Session->UserCode;
                    }else
                        $Account = $Session->UserCode;
					if($oldpwd === md5($Account . $this->OldPWD->Text)){
						$rec = new TPostRecord('WF_UserInfo');
						$rec->UserPasswd_ = md5($Account . $this->NewPWD1->Text);
						$rec->PostModify("UserCode_='$Session->UserCode'");
						echo '您的密码已修改成功！';
					}else{
						echo '您的旧密码输入不正确！<br/>';
					}
				}else{
					echo '您的新密码二次输入不一致！<br/>';
				}
			}else{
				echo '新密码不允许为空！<br/>';
			}
		}else{
			echo '本次未修改密码！<br/>';
		}
	}

    public function Img_GetHtml(TWinControl $Sender, $text){
        $img = $Sender->DataSet->Image_;
        //header("Content-type: image/JPEG",true);
        //return "<img src=\"$img\">";
    }
}

?>
