<?php
if( !defined('IN') ) die('bad request');

class TRichText extends TWinControl{
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
		$readonly = null;
		$editor = null;
		$param = null;
		if($this->ReadOnly){
			$readonly = ' disabled';
		}else{
			$editor = "<script language='javascript' src='editor/xheditor-1.1.14-zh-cn.min.js'></script>";
			$param = " class=\"xheditor {tools:'Fontface,FontSize,Bold,Italic,Underline,Strikethrough"
				. "FontColor,BackColor,SelectAll,Removeformat,Align,"
				. "List,Outdent,Indent,Link,Unlink,Hr,Table,Source,Fullscreen',width:'80%'}\"";
		}
		/*	完整按钮表：
			|：分隔符 /：强制换行 Cut：剪切 Copy：复制 Paste：粘贴 Pastetext：文本粘贴 Blocktag：段落标签
			Fontface：字体 FontSize：字体大小 Bold：粗体 Italic：斜体 Underline：下划线 Strikethrough：中划线
			FontColor：字体颜色 BackColor：字体背景色 SelectAll：全选 Removeformat：删除文字格式 Align：对齐
			List：列表 Outdent：减少缩进 Indent：增加缩进 Link：超链接 Unlink：删除链接 Anchor：锚点 Img：图片
			Flash：Flash动画 Media：Windows media player视频 Hr：插入水平线 Emot：表情 Table：表格 Source：切换源代码模式
			Preview：预览当前代码 Print：打印 Fullscreen：切换全屏模式
		*/
		return $editor."<textarea".$param." style='overflow-y:scroll' rows=\"$this->rows\" name=\"$this->Name\" "
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