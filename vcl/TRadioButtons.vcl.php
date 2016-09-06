<?php
if( !defined('IN') ) die('bad request');

class TRadioButtons extends TWinControl
{
    public $ReadOnly = false;
	public $Caption;
	public $Hint;
	public $DataSet;
	public $Value = -1;
	public $Items = array();
	private $AllowNull = true;
	
	public function __get($name){
		if($name === 'HtmlText'){
			return $this->GetHtmlText();
		}else{
			return parent::__get($name);
		}
	}
	
	public function GetHtmlText(){
		$result = '';
		foreach($this->Items as $Val => $Text){
			$result .= '<input type="radio"';
			if($this->ReadOnly){
				$result .= $this->ReadOnly ? ' disabled' : '';
			}else{
				$result .= 'value="'.$Val.'"';
				$result .= ' name="'.$this->Name.'"';
			}
			$result .= $this->Value == $Val ? ' checked' : '';
			$result .= '>'.$Text."</input>\n";
		}
		return $result;
	}

    public function LinkField($FieldCode, $FieldInfo){
        $this->Caption = $FieldInfo->Caption;
        $this->Hint = $FieldInfo->Hint;
		$this->Name = $FieldCode;
        foreach($FieldInfo->Items as $key => $Item){
			//$this->AddItem($Item);
			$this->Items[$key] = $Item;
		}
		if($this->DataSet){
			if(is_string($FieldInfo->modify)){
				$this->ReadOnly = $FieldInfo->modify == 'ReadOnly';
			}else{
				$this->ReadOnly = !$FieldInfo->modify;
			}
			$this->Value = $this->DataSet->FieldByName($FieldCode);
			//echo $this->Value . "<br/>\n";
		}else{
			if(is_string($FieldInfo->append)){
				$this->ReadOnly = $FieldInfo->append == 'ReadOnly';
			}else{
				$this->ReadOnly = !$FieldInfo->append;
			}
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
			return $this->Value === -1 ? false : true;
	}
	
	public function AddItem($Item){
		$this->Items[] = $Item;
	}
}