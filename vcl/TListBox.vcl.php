<?php
if( !defined('IN') ) die('bad request');

class TListBox extends TWinControl
{
    public $ReadOnly = false;
	public $Caption;
	public $Hint;
	public $DataSet;
	public $Value = '';
	private $Items = array();
	private $AllowNull = true;
	
	public function __get($name){
		if($name === 'HtmlText'){
			return $this->GetHtmlText();
		}else{
			return parent::__get($name);
		}
	}
	
	public function GetHtmlText(){
        $readonly = $this->ReadOnly ? " disabled" : "";
		$count = count($this->Items);
		$result = "<select name=\"$this->Name\"$readonly>\n";
		foreach($this->Items as $Val => $Text){
			$selected = '';
			if($this->Value == $Val){
				$selected = " selected";
			}
			$result .= "<option value=\"$Val\"$selected>$Text</option>\n";
		}
		$result .= "</select>\n";
		return $result;
	}

    public function LinkField($FieldCode, $FieldInfo){
		$this->Name = $FieldCode;
        $this->Caption = $FieldInfo->Caption;
        $this->Hint = $FieldInfo->Hint;
		$this->Items = $FieldInfo->Items;
		if($this->DataSet){
			$this->ReadOnly = !$FieldInfo->modify;
			$this->Value = $this->DataSet->FieldByName($FieldCode);
		}else{
			$this->Value = $FieldInfo->Value;
		}
    }
	
	public function checkInput(){
		if($this->Name <> ''){
			if(isset($_POST[$this->Name])){
				$this->Value = $_POST[$this->Name];
			}
		}
		if($this->AllowNull) //允许为空
			return true;
		else
			return $this->Value === '' ? false : true;
	}
}