<?php
if( !defined('IN') ) die('bad request');

class TUploadFile extends TWinControl{
	private $Width = 30;
	private $Caption = '';
	private $Hint = '';
	public $AllowNull = false;
	public $Visible = true;
	//与数据库有关
	public $DataSet;
	private $FieldInfo;
	
	public function LinkField($FieldCode, $FieldInfo){
		$this->Name = $FieldCode;
		$this->FieldInfo = $FieldInfo;
		$this->Caption = $FieldInfo->Caption;
		$this->Hint = $FieldInfo->Hint;
	}
	
	public function GetHtmlText(){
		return "<input name=\"".$this->Name."\" type=\"file\" "
			. "size=\"$this->Width\"/>";
	}
	
	public function checkInput(){
		return true;
	}
	
	public function __set($name, $value){
		parent::__set($name, $value);
		if($name === 'Width'){
			$this->Width = $value;
		}elseif($name === 'Caption'){
			$this->Caption = $value;
		}elseif($name === 'Hint'){
			$this->Hint = $value;
		}
	}
	
	public function __get($name){
		if($name === 'Width'){
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
		}else{
			return parent::__get($name);
		}
	}
}
?>