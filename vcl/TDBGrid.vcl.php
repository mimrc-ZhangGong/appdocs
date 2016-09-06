<?php
if( !defined('IN') ) die('bad request');

class TDBGrid extends TWinControl
{
	public $DataSet;
	public $Fields;
	private $MaxRows = 100; //每页显示最大行数
	private $Page = 1;
	private $LastPage;
	protected $RowCount = 0; //记录总数
	protected $StartRow = 0;
	protected $EndRow = 9;
	private $colspan = 0;
	
	public function Begin()
	{
		if(isset($_GET['Page'])){
			$this->Page = $_GET['Page'];
		}
		$this->StartRow = $this->MaxRows * ($this->Page - 1);
		$this->EndRow = $this->StartRow + $this->MaxRows - 1;
		$this->Lines[] = "\n<table class=\"data\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n";
	}
	
	public function End()
	{
		$this->LastPage = ($this->RowCount - ($this->RowCount % $this->MaxRows)) / $this->MaxRows;
		if($this->RowCount % $this->MaxRows > 0) $this->LastPage++;
		if(($this->StartRow > 0) or ($this->EndRow < $this->RowCount)){
			$this->Lines[] = "<tr class=\"tr_even\">\n";
			$this->Lines[] = "<td colspan=\"$this->colspan\">\n";
			//
			$this->Lines[] = "<table width=\"100%\">\n";
			$this->Lines[] = "<tr>\n<td>\n";
			$this->Lines[] = "总记录数：$this->RowCount 笔, 每页显示 $this->MaxRows 行, 第 $this->Page 页\n";
			$this->Lines[] = "</td>\n";
			$this->Lines[] = "<td align=\"right\">\n";
			$this->Lines[] = $this->GetPageUrl(1, '第一页') . " \n";
			$this->Lines[] = $this->GetPageUrl($this->Page - 1, '上一页') . " \n";
			$this->Lines[] = $this->GetPageUrl($this->Page + 1, '下一页') . " \n";
			$this->Lines[] = $this->GetPageUrl($this->LastPage, '最后一页') . " \n";
			$this->Lines[] = "</td>\n</tr>\n";
			$this->Lines[] = "</table>\n";
			//
			$this->Lines[] = "</td>\n";
			$this->Lines[] = "</tr>\n";
		}
		$this->Lines[] = "</table>\n";
	}
	
	
	public function OnShow()
	{
		if(is_array($this->Fields)){ //有定义字段的输出方式
			$this->ShowByFields();
		}
		elseif($this->DataSet){
			$this->ShowDefault();
		}
		if(count($this->Lines) > 0){
			foreach($this->Lines as $line){
				echo $line;
			}
		}
	}
	
	public function ShowByFields(){ //根据 Fields 定义输出
		$this->Begin();
		$this->Lines[] = "<tr class=\"tr_theme\">\n";
		foreach($this->Fields as $code => $params){
			$this->AddTitle($code, $params);
		}
		$this->Lines[] = "</tr>\n";
		for($i=0; $i<$this->DataSet->RecordCount(); $i++)
		{
			$i%2==0?$css=' td_bg':$css='';
			$this->DataSet->Next();
			if(($i >= $this->StartRow) and ($i <= $this->EndRow)){
				$this->Lines[] = "<tr class=\"tr_even$css\">\n";
				foreach($this->Fields as $code => $params){
					$this->AddField($code, $params);
				}
				$this->Lines[] = "</tr>\n";
			}
		}
		$this->RowCount = $this->DataSet->RecordCount();
		$this->End();
	}
	
	public function ShowDefault(){ // 直接输出所有的字段
		$this->Begin();
		$this->Lines[] = "<tr class=\"tr_theme\">\n";
		for($j=0; $j < $this->DataSet->FieldCount(); $j++)
		{
			$this->AddHead($this->DataSet->getFieldName($j));
		}
		$this->Lines[] = "</tr>\n";
		$row = 0;
		for($i=0; $i<$this->DataSet->RecordCount(); $i++)
		{
			$this->DataSet->Next();
			if(($row >= $this->StartRow) and ($row <= $this->EndRow)){
				$this->Lines[] = "<tr class=\"tr_even\">\n";
				for($j=0; $j < $this->DataSet->FieldCount(); $j++)
				{
					$this->AddItem($this->DataSet->FieldByIndex($j));
				}
				$this->Lines[] = "\n</tr>\n";
			}
			$row++;
		}
		$this->RowCount = $this->DataSet->RecordCount();
		$this->End();
	}
	
	public function OutArray($Datas){ //输出指定的 $Datas 数组
		if(!is_array($Datas)){
			die('OutArray Error: $Datas 不是一个数组！');
		}
		$this->Begin();
		$i = 0;
		$css = null;
		foreach($Datas as $Line){
			if(($i >= $this->StartRow) and ($i <= $this->EndRow) or $i == 0){
				if($i != 0){
					$css = $i%2 == 0 ? '' : ' td_bg';
				}
				$this->Lines[] = $i === 0 ? "<tr class=\"tr_theme\">" : "<tr class=\"tr_even$css\">";
				foreach($Line as $cell)
					$this->Lines[] = $i === 0 ? $this->AddHead($cell) : $this->AddItem($cell);
				$this->Lines[] = "</tr>\n";
			}
			$i++;
		}
		$this->RowCount = count($Datas);
		$this->End();
	}
	
	public function OutList($Datas){ //输出指定的 $Datas 数组
		if(!is_array($Datas)){
			die('OutArray Error: $Datas 不是一个数组！');
		}
		//$this->MaxRows = 15;
		$this->Begin();
		$i = 0;
		foreach($Datas as $Line){
			if(($i >= $this->StartRow) and ($i <= $this->EndRow) or $i == 0){
				if($i == 0){
					$this->Lines[] = "<tr class=\"tr_theme\"><th>";
					foreach($Line as $cell){
						$this->Lines[] = $cell."&nbsp;";
					}
					$this->Lines[] = "</th></tr>\n";
					if(count($Datas) > 1){
						$this->Lines[] = "<tr class=\"tr_even td_bg\"><td><ul>";
					}
				}else{
					foreach($Line as $cell){
						$this->Lines[] = "<li>$cell</li>";
					}
				}
			}
			$i++;
		}
		if(count($Datas) > 1){
			$this->Lines[] = "</ul></td></tr>\n";
		}
		$this->RowCount = count($Datas);
		$this->End();
	}
	
	public function AddHead($Caption)
	{	
		$this->Lines[] = "<th>$Caption</th>";
		$this->colspan++;
	}
	
	public function AddItem($value)
	{
		$this->Lines[] = "<td>$value</td>";
	}
	
	public function AddTitle($field, $params)
	{
		if(is_numeric($field)){
			$this->AddHead('操作');
		}
		else{
			$fi = new TFieldInfo($field, $params);
			if($fi->view){
				$width = $fi->hasParam('width') ? $fi->width : 1;
				if($width > 0){
					$this->AddHead($fi->Caption);
				}
			}
		}
	}
	
	public function AddField($field, $params)
	{
		$fi = new TFieldInfo($field, $params);
		if($fi->view){
            $width = $fi->hasParam('width') ? $fi->width : 1;
            $align = $fi->hasParam('align') ? ' align="'.$fi->align.'"' : '';
			if($width > 0){
				if($width > 1){
					$this->Lines[] = '<td width="'.$width.'"'.$align.'>';
				}else{
					$this->Lines[] = '<td'.$align.'>';
				}
				if($fi->hasParam('OnGetText')){
					$event = $fi->OnGetText;
					$value = $this->Owner->$event($this->DataSet, $field, $params);
					$this->Lines[] = $value;
				}elseif($fi->Control == 'TRichText'){
					$this->Lines[] = $this->DataSet->FieldByName($field);
				}elseif($fi->hasParam('Items')){
					$Items = $fi->Items;
					$value = $this->DataSet->FieldByName($field);
					if(array_key_exists($value, $Items)){
						$this->Lines[] = $Items[$value];
					}else{
						$this->Lines[] = $value;
					}
				}elseif($fi->hasParam('MaxLength')){//限制显示字符长度
					$value = $this->DataSet->FieldByName($field);
					$this->Lines[] = mb_strlen($value,'utf8') > $fi->MaxLength
					? "<div title='$value'>".mb_substr($value, 0, $fi->MaxLength, 'utf8').'...</div>'
					: $value;
				}else{
					if($fi->hasParam('FieldType')){//字符类型显示处理
						$value = $this->DataSet->FieldByName($field);
						switch ($fi->FieldType){
							case 'ftInteger' :
								$this->Lines[] = number_format($value);break;
							case 'ftBoolean' :
								$this->Lines[] = $value != '' ? 1 : 0;break;
							case 'ftDatetime' :
								$this->Lines[] = $value == '0000-00-00' ? '' : $value;break;
							default :
								$this->Lines[] = $value;
						}
					}else
						$this->Lines[] = $this->DataSet->FieldByName($field);
				}
				$this->Lines[] = '</td>';
			}
		}
	}
	
	private function GetPageUrl($Page, $Title){
		$PageNo = $Page;
		if($Page == 0)
			$PageNo = 1;
		elseif($Page > $this->LastPage)
			$PageNo = $this->LastPage;
		//
		$url = $_SERVER['PHP_SELF'];
		foreach($_GET as $key => $value){
			if($key <> 'Page'){
				$url .= (strpos($url, '?') ? "&" : "?") . "$key=$value";
			}
		}
		$url .= (strpos($url, '?') ? "&" : "?") . "Page=$PageNo";
		return BuildUrl($url, $Title);
	}
}
?>