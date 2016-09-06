<?php
if( !defined('IN') ) die('bad request');

class TEdit extends TWinControl{
	private $Text = '';
	private $Width = 48;
	private $Caption = '';
	private $Hint = '';
	public $password = false;
	public $AllowNull = false;
	public $Visible = true;
	public $ReadOnly = false;
	//与数据库有关
	public $DataSet;
	private $FieldInfo;
	
	public function LinkField($FieldCode, $FieldInfo){
		$this->Name = $FieldCode;
		$this->FieldInfo = $FieldInfo;
		$this->Caption = $FieldInfo->Caption;
		$this->Width = $FieldInfo->cols ? $FieldInfo->cols : $this->Width;//编辑文本宽度
		$this->Hint = $FieldInfo->Hint;
        $this->Visible = $FieldInfo->Visible;
		if($this->DataSet){
			if(is_string($FieldInfo->modify)){
				$this->ReadOnly = $FieldInfo->modify == 'ReadOnly';
			}else{
				$this->ReadOnly = !$FieldInfo->modify;
			}
			$this->Text = $this->DataSet->FieldByName($FieldCode);
		}else{
			if(is_string($FieldInfo->append)){
				$this->ReadOnly = $FieldInfo->append == 'ReadOnly';
			}else{
				$this->ReadOnly = !$FieldInfo->append;
			}
			$this->Text = $FieldInfo->Value;
		}
	}

	public function GetHtmlText(){
		$fi = $this->FieldInfo;
		$text = '<input';
		if($this->password){
			$text .= ' type="password"';
		}else{
			$text .= ' type="text"';
		}
		$text .= "size=\"$this->Width\" value=\"$this->Text\"";
		//
		$text .= ' name="'.$this->Name.'"';
		if($this->ReadOnly){
			$text .= ' readonly';
		}
		if($fi && $fi->hasParam('FieldType') && $fi->FieldType == 'ftDatetime' && !$this->ReadOnly){//ftDatetime使用jquery日期插件
			$script = '<script language="javascript">$(function(){$(".ftDatetime").datepicker();})</script>';
			$text .= ' class="ftDatetime" ';
		}
		$text .= '/>';
		if(isset($script))$text .= $script;
		if($fi and $fi->hasParam('OnGetHtml')){
			$event = $fi->OnGetHtml;
			return $this->Owner->$event($this, $text);
		}else{
			return $text;
		}
	}
	
	public function checkInput(){
		if($this->Name <> ''){
			if(isset($_POST[$this->Name])){
				$this->Text = $_POST[$this->Name];
			}
		}
		if($this->AllowNull) //允许为空
			return true;
		else
			return $this->Text === '' ? false : true;
	}
	
	public function __set($name, $value){
		parent::__set($name, $value);
		if($name === 'Text'){
			$this->Text = $value;
		}elseif($name === 'Width'){
			$this->Width = $value;
		}elseif($name === 'Caption'){
			$this->Caption = $value;
		}elseif($name === 'Hint'){
			$this->Hint = $value;
		}elseif($name === 'Visible'){
            $this->Visible = $value;
        }
	}
	
	public function __get($name){
		if($name === 'Text'){
			return $this->Text;
		}elseif($name === 'Width'){
			return $this->Width;
		}elseif($name === 'Caption'){
			if($this->Caption === '')
				return $this->Name;
			else
				return $this->Caption;
		}elseif($name === 'Hint'){
			return $this->Hint;
		}elseif($name === 'HtmlText'){
			return $this->GetHtmlText();
		}elseif($name === 'Visible'){
            return $this->Visible;
        }else{
			return parent::__get($name);
		}
	}
}
?>