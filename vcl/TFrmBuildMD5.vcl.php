<?php
if( !defined('IN') ) die('bad request');

class TFrmBuildMD5 extends TDBForm{

	private $Edit1;
	
	public function OnCreate(){
		parent::OnCreate();
		$this->Edit1 = new TEdit($this);
	}

	public function OnDefault(){
		$form = new TEditForm($this);
		$form->Caption = '计算MD5值';
		$form->AddHidden('mode', 'append');
		$this->Edit1->Window = $form;
		$this->Edit1->Caption = '请输入字符';
		$this->Edit1->Text = 'hello';
		$form->Show();
	}
	
	public function OnPostAppend(){
		if($this->Edit1->checkInput()){
			echo "您的输入值：". $this->Edit1->Text . "<br/>\n";
			echo "其MD5值为：" . md5($this->Edit1->Text);
		}else{
			echo '您没有输入任何内容！';
		}
	}
}
?>