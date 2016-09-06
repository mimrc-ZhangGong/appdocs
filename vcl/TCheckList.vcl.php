<?php
if( !defined('IN') ) die('bad request');

class TCheckList extends TWinControl{
    public $ListCode;
    public $Caption;
    public $DataSet;
    public $ReadOnly = false;
    public $Value = '';
    public $Hint;
    public $Items = array();

    public function __set($name, $value){
        parent::__set($name, $value);
        if($name === 'Checked'){
            $this->Checked = $value;
        }else{
            parent::__set($name, $value);
        }
    }

    public function __get($name){
        if($name === 'HtmlText'){
            return $this->GetHtmlText();
        }else{
            return parent::__get($name);
        }
    }

    public function GetHtmlText(){
		$hidden = null;
		$readonly = null;
		if($this->ReadOnly){
			$hidden = "<input type='hidden' name='$this->Name' value='$this->Value' />";
			$readonly = " disabled";
		}
		// 
		$text = "$hidden<select name=\"$this->Name\" $readonly><option value=''>$this->Caption</option>\n";
		if($this->Items){
			foreach($this->Items as $Val => $Text){
				$selected = '';
				if($this->Value === "$Val"){
					$selected = " selected";
				}
				$text .= "<option value=\"$Val\"$selected>$Text</option>\n";
			}
		}
		$text .= "</select>\n";
		return $text;
    }

    public function LinkField($FieldCode, $FieldInfo){
        $this->Name = $FieldCode;
        $this->Caption = $FieldInfo->Caption;
        $this->Checked = $FieldInfo->Checked;
        $this->Value = $FieldInfo->Value;
        $this->Items = $FieldInfo->Items;
        $this->Hint = $FieldInfo->Hint;
        if($this->DataSet){
            if(is_string($FieldInfo->modify)){
                $this->ReadOnly = $FieldInfo->modify == 'ReadOnly';
            }else{
                $this->ReadOnly = !$FieldInfo->modify;
            }
            if($this->DataSet->FieldByName($FieldCode) == 0)
                $this->Checked = false;
            else{
                $this->Checked = true;
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
}
?>