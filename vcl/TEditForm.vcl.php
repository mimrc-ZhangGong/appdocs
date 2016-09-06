<?php
if( !defined('IN') ) die('bad request');

class TEditForm extends TWinControl{
	private $line = 0;
    private $Width = 40;
	public $ActionForm;
	public $Caption = '修改记录';
	public $NextForm;
	private $Hidden = array();

	public function AddHidden($id, $value)
	{
		$this->Hidden[$id] = $value;
	}
 
	public function OnBeforeShow()
	{
		foreach($this->Controls as $obj){
			$ClassType = get_class($obj);
			if($ClassType === 'TEdit'){
				if(!$obj->Visible)
					$this->AddHidden($obj->Name, $obj->Text);
				elseif($obj->DataSet and $obj->ReadOnly)
					$this->AddItem($obj->Caption, $obj->Text, $obj->Hint);
				else
					$this->AddItem($obj->Caption, $obj->HtmlText, $obj->Hint);
			}elseif($ClassType === 'TCheckBox'){
				$this->AddItem('', $obj->HtmlText);
			}else{
				$this->AddItem($obj->Caption, $obj->HtmlText, $obj->Hint);
            }
		}
		if(isset($this->ActionForm)){
			$url = $this->ActionForm;
		}else{
			$url = $this->Owner->GetUrl();
		}
		echo "\n";
		echo '<form method="post" action="'.$url.'" enctype="multipart/form-data">';
		echo "\n";
		echo '<input type="hidden" name="goback" value="'.$_SERVER['REQUEST_URI'].'">';
		echo "\n";
		foreach($this->Hidden as $id => $value){
			echo "<input type=\"hidden\" name=\"$id\" value=\"$value\">\n";
		}
		echo "<table class=\"data\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n";
		echo "<tr class=\"tr_theme\">\n";
		echo "<th colspan=\"2\" height=\"25\">\n";
		echo "<p align=\"center\">$this->Caption</p>\n";
		echo "</th>\n";
		echo "</tr>\n";
	}
	
	public function OnShow(){
		parent::OnShow(); //此处会输出 $this->Lines 中的内容
		echo "<tr>\n";
		echo "<td align=\"right\" height=\"30\">　</td>\n<td>";
		if($this->NextForm){
			echo "<input type=\"submit\" value=\"下一步\" name=\"B3\" style='padding:3px 10px;' />\n";
		}else{
			echo "<input type=\"submit\" value=\"提交\" name=\"B1\" style='padding:3px 10px;' />\n";
			echo "<input type=\"reset\" value=\"重置\" name=\"B2\" style='padding:3px 10px;' />\n";
		}
		echo "</td>\n";
		echo "</tr>\n";
		echo "</table>\n";
		echo "</form>\n";
	}

	public function AddItem($name, $text, $remark = '')
	{
		$bg = $this->line%2 != 0 ? ' td_bg' : '';
		$this->line += 1;
		$this->Lines[] = "<tr class=\"tr_even$bg\">\n";
		global $Session;
		if($Session->ScreenWidth > 240)
			$this->Lines[] = "<td align=\"right\" height=\"30\" width=\"150\">";
		else
			$this->Lines[] = "<td align=\"right\" height=\"30\">";
		if($name <> ''){
			$this->Lines[] = $name. '：';
		}
		$this->Lines[] = "</td>\n";
		$this->Lines[] = "<td>&nbsp;$text $remark</td>\n";
		$this->Lines[] = "</tr>\n";
	}
       
	public function setWidth($new)
	{
		$this->Width = $new;
	}
}
?>