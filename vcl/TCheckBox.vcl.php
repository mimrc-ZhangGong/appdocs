<?php
if( !defined('IN') ) die('bad request');

class TCheckBox extends TWinControl{
	public $Caption;
	public $DataSet;
	private $Checked = false;
    public $ReadOnly = false;
	
	public function __set($name, $value){
		parent::__set($name, $value);
		if($name === 'Checked'){
			$this->Checked = $value;
		}elseif($name == 'Text'){
			$this->Checked = $value == 0 ? false : true;
		}else{
			parent::__set($name, $value);
		}
	}
	
	public function __get($name){
		if($name === 'Text'){
			return $this->Checked ? 1 : 0;
		}elseif($name === 'Checked'){
			return $this->Checked;
		}elseif($name === 'HtmlText'){
			return $this->GetHtmlText();
		}else{
			return parent::__get($name);
		}
	}
	
	public function checkInput(){
		if($this->Name <> ''){
			if(isset($_POST[$this->Name])){
				$this->Checked = true;
			}else{
				$this->Checked = false;
			}
		}
		return true;
	}

	public function GetHtmlText(){
		$text = '<input type="checkbox"';
        $text .= $this->Checked ? ' checked' : '';
		if($this->ReadOnly){
			$text .= $this->ReadOnly ? ' name="'.$this->Name.'" disabled' : '';
			if($this->Checked){
				$text .= '><input type="hidden" name="'.$this->Name.'" value="'.$this->Checked.'"';
			}
		}else{
			$text .= ' name="'.$this->Name.'"';
		}
		$text .= '>'.$this->Caption.'</input>';
		return $text;
	}

    public function LinkField($FieldCode, $FieldInfo){
        $this->Name = $FieldCode;
        $this->Caption = $FieldInfo->Caption;
        if($this->DataSet){
			if(is_string($FieldInfo->modify)){
				$this->ReadOnly = $FieldInfo->modify == 'ReadOnly';
			}else{
				$this->ReadOnly = !$FieldInfo->modify;
			}
            if($this->DataSet->FieldByName($FieldCode) == 0)
				$this->Checked = false;
			else
				$this->Checked = true;
		}else{
			if(is_string($FieldInfo->append)){
				$this->ReadOnly = $FieldInfo->append == 'ReadOnly';
			}else{
				$this->ReadOnly = !$FieldInfo->append;
			}
			if($FieldInfo->Value <> 0) $this->Checked = true;
		}
    }
}
?>