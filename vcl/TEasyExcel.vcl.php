<?php
class TEasyExcel{
	
	//将当前模式设置为下载xls文件
	public function SetDownload($filename, $data){
		header("Content-type:application/vnd.ms-excel; charset=utf-8");
		header("Content-Disposition:filename=$filename");
		echo $data;
	}
	
	//将 TDataSet 转为 Excel 文件
	public function LoadFromDataSet(TDataSet $DataSet, $Titles){
		$data[] = array(); $i = 0;
		foreach($Titles as $key => $val) $data[$i][] = $val;
		while($DataSet->Next()){
			$data[] = array(); $i++;
			foreach($Titles as $key => $val) $data[$i][] = $DataSet->FieldByName($key);
		}
		return $this->LoadFromArray($data);
	}
	
	//将数组转化为 Excel 文件
	public function LoadFromArray($data){
	$text = '<html xmlns:o="urn:schemas-microsoft-com:office:office"  
xmlns:x="urn:schemas-microsoft-com:office:excel"  
xmlns="http://www.w3.org/TR/REC-html40">  
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">  
<html>  
<head>  
<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />  
<style id="Classeur1_16681_Styles"></style>  
</head>  
<body>  
<div id="Classeur1_16681" align=center x:publishsource="Excel">  
<table x:str border=0 cellpadding=0 cellspacing=0 width=100% style="border-collapse: collapse">';
		foreach($data as $line){
			$text .= '<tr>';
			foreach($line as $cell){
				$text .= '<td class=xl2216681 nowrap>'.$cell.'</td>';
			}
			$text .= '</tr>';
		}
		$text .= '</table></div></body></html>';
		return $text;
	}
}
?>