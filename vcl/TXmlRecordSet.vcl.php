<?php
if( !defined('IN') ) die('bad request');

class TXmlRecordSet extends TWinControl{
	public $Data = array();

	public function ReadXML($data){
		return $this->ReadFile(null, $data);
	}

	public function ReadFile($xmlfile, $data = null){
		$xml = new DOMDocument();
		if($xmlfile <> '')
			$xml->load($xmlfile);
		else
			$xml->loadXML($xmlData);
		$root = $xml->documentElement;
		if($root->nodeName === 'RecordSet'){
			$item = $root->firstChild;
			while($item){
				if($item->nodeName == 'table'){
					$this->ReadTable($item);
				}
				$item = $item->nextSibling;
			}
			return true;
		}else{
			$this->Lines[] = '错误的文件格式：' . $xmlfile;
			return false;
		}
		$root = null;
		$xml = null;
	}

	public function ReadTable($table){
		$this->table_code = $table->getAttribute('code');
		$tablecode = $table->getAttribute('code');
		$Records = array();
		$item = $table->firstChild;
		while($item){
			if($item->nodeName == 'record'){
				$Records[] = $this->ReadRecord($item);
			}
			$item = $item->nextSibling;
		}
		$this->Data[$tablecode] = $Records;
	}
	
	public function ReadRecord($record){
		$Fields = array();
		$item = $record->firstChild;
		while($item){
			if($item->nodeName == 'field'){
				$Fields[$item->getAttribute('code')] = $item->nodeValue;
			}
			$item = $item->nextSibling;
		}
		return $Fields;
	}
	
	/* 调用范例
	$xmlfile = ROOT . 'RecordSet.xml';
	$rs = new TXmlRecordSet();
	if($rs->ReadFile($xmlfile)){
		foreach($rs->Data as $table => $Records){
			echo '数据表：' . $table . '<br/>';
			foreach($Records as $RecNo => $Record){
				foreach($Record as $code => $value){
					echo "Field Code: $code, Value=$value <br/>";
				}
			}
		}
	}
	else{
		echo $rs->GetMessage(); //输出错误原因
	}
	*/
}
?>