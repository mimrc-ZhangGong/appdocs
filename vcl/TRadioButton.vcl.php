<?php
if( !defined('IN') ) die('bad request');

class TRadioButton extends TWinControl{
	private $Name = '';
	private $Value = ''; 
	private $Text = ''; 
	private $Checked = False;
	
	public function __set($name, $value){
		parent::__set($name, $value);
		if($name === 'Name'){
			$this->Name = $value;
		}elseif($name === 'Value'){
			$this->Value = $value;
		}elseif($name === 'Text'){
			$this->Text = $value;
		}elseif($name === 'Checked'){
			$this->Checked = $value;
		}
	}
	
	public function __get($name){
		if($name === 'Name'){
			return $this->Name;
		}elseif($name === 'Value'){
			return $this->Value;
		}elseif($name === 'Text'){
			return $this->Text;
		}elseif($name === 'Checked'){
			return $this->Checked;
		}elseif($name === 'HtmlText'){
			return $this->GetHtmlText();
		}
	}
	
	public function GetHtmlText(){
		$checkstr = $this->Checked ? "checked=\"checked\"" : ""; 
		return "<input type=\"radio\" name=\"$this->Name\" value=\"$this->Value\" $checkstr>$this->Text</input>";
	}
}

class TRadioGroup extends TWinControl{
	private $Caption = '';
	private $Hint = '';
	private $Radios = array();
    //与数据库有关
    public $DataSet;

	public function __set($name, $value){
		parent::__set($name, $value);
		if($name === 'Caption'){
			$this->Caption = $value;
		}elseif($name === 'Hint'){
			$this->Hint = $value;
		}
	}
	
	public function __get($name){
		if($name === 'Caption'){
			return $this->Caption;
		}elseif($name === 'Hint'){
			return $this->Hint;
		}elseif($name === 'HtmlText'){
            return $this->GetHtmlText();
        }
	}
	
	private function AddRadio($radio){
		if(get_class($radio) === 'TRadioButton'){
			array_push($this->Radios, $radio);
			return True;
		}else{
			return False;
		}
	}

    public function  GetHtmlText(){
        $result = "<tr class=\"tr_even\">\n" . "\t<td align=\"right\" height=\"30\" width=\"150\">$this->Caption ：</td>\n<td>\t";
        foreach($this->Radios as $radio){
            $result .= $radio->GetHtmlText() . "&nbsp;";
        }
        return $result . " $this->Hint</td>\n</tr>\n";
    }

    public function LinkField($FieldCode, $FieldInfo){
        $this->Caption = $FieldInfo->Caption;
        $this->Hint = $FieldInfo->Hint;
        foreach($FieldInfo->Items as $value => $text){
            $radio = new TRadioButton(self);
            $radio->Name = $FieldCode;
            $radio->Value = $value;
            $radio->Text = $text;
            if($this->DataSet){
                if($value == $this->DataSet->FieldByName($FieldCode)) $radio->Checked = True;
            }else{
                if($FieldInfo->Value === $value) $radio->Checked = True;
            }
            $this->AddRadio($radio);
        }
    }
}
 
?>