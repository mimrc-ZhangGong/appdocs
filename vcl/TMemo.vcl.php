<?php
if( !defined('IN') ) die('bad request');

class TMemo extends TWinControl{
	private $Text = '';
	private $cols = 40;
    private $rows = 10;
	private $Caption = '';
	private $Hint = '';
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
		$this->Hint = $FieldInfo->Hint;
		if(is_string($FieldInfo->modify)){
			$this->ReadOnly = $FieldInfo->modify == 'ReadOnly';
		}else{
			$this->ReadOnly = !$FieldInfo->modify;
		}
        if(is_string($FieldInfo->cols)){
            $this->cols = $FieldInfo->cols;
        }
        if(is_string($FieldInfo->rows)){
            $this->rows = $FieldInfo->rows;
        }
		if($this->DataSet){
			$this->Text = $this->DataSet->FieldByName($FieldCode);
		}else{
			$this->Text = $FieldInfo->Value;
		}
	}
	
	public function GetHtmlText(){
		$readonly = $this->ReadOnly ? ' disabled' : '';
		return "<textarea style='overflow-y:scroll' rows=\"$this->rows\" name=\"$this->Name\" "
			. "cols=\"$this->cols\"$readonly>$this->Text</textarea>\n";
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
		}elseif($name === 'cols'){
			$this->cols = $value;
		}elseif($name === 'rows'){
            $this->rows = $value;
        }elseif($name === 'Caption'){
			$this->Caption = $value;
		}elseif($name === 'Hint'){
			$this->Hint = $value;
		}
	}
	
	public function __get($name){
		if($name === 'Text'){
			return $this->Text;
		}elseif($name === 'cols'){
			return $this->cols;
		}elseif($name === 'rows'){
            return $this->rows;
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