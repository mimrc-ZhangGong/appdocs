<?php
if( !defined('IN') ) die('bad request');

class TVarRecordSet extends TWinControl{

	public $tablecode;
	public $Data = array();

	public function ReadXML($data){
		return $this->ReadFile(null, $data);
	}
	
	public function ReadFile($xmlfile, $data = ''){
		$xml = new DOMDocument();
		if($xmlfile <> '')
			$xml->load($xmlfile);
		else
			$xml->loadXML($xmlData);
		$root = $xml->documentElement;
		if($root->nodeName === 'data'){
			$item = $root->firstChild;
			while($item){
				if($item->nodeName == 'data'){
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
		$item = $table->firstChild;
		$this->tablecode = $item->nodeValue;
		$item = $item->nextSibling;
		while($item){
			if($item->nodeName == 'data'){ //RecordSet节点
				$this->ReadRecords($item);
			}
			$item = $item->nextSibling;
		}
	}
	
	public function ReadRecords($records){
		$Record = array();
		$item = $records->firstChild;
		while($item){
			if($item->nodeName == 'data'){
				$Record[] = $this->ReadRecord($item);
			}
			$item = $item->nextSibling;
		}
		$this->Data[$this->tablecode] = $Record;
	}
	
	public function ReadRecord($record){
		$Fields = array();
		$item = $record->firstChild;
		while($item){
			if($item->nodeName == 'data'){
				$code = $item->firstChild->nodeValue;
				$value = $item->firstChild->nextSibling->nodeValue;
				$Fields[$code] = $value;
			}
			$item = $item->nextSibling;
		}
		return $Fields;
	}
	
	public function GetMessage(){
		$result = '';
		foreach($rs->Lines as $line){
			$result .= $line . '<br/>';
		}
		return $result;
	}
}
?>