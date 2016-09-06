<?php
	if( !defined('IN') ) define( 'IN' , true );
	require_once("vcl/TDataSet.vcl.php");
	require_once("vcl/TMainData.vcl.php");
	require_once("vcl/vcl.sae.php");
	
	class DsToXml
	{
		var $xml;
		var $root;

		public function DsToXml()
		{
			$this->xml = new DOMDocument('1.0', 'UTF-8');
			$this->xml->formatOutput = true;
			$this->root = $this->xml->createElement("RecordSet"); 
			$type = $this->xml->createAttribute("xmlns:xsi");
			$type->nodeValue = "http://www.w3.org/2001/XMLSchema-instance";
			$this->root->setAttributeNode($type);
			$type = $this->xml->createAttribute("xsi:noNamespaceSchemaLocation");
			$type->nodeValue = "RecordSet.xsd";
			$this->root->setAttributeNode($type);
			$this->xml->appendChild($this->root);
		}
		
		public function EchoXML()
		{
			echo $this->xml->saveXML();
		}
		
		public function AddTable($tableName, $ds, $fields)
		{
			if ($ds->RecordCount() > 0)
			{
				$table = $this->xml->createElement("table");
				$tcode = $this->xml->createAttribute("code");
				$tcode->nodeValue = $tableName;
				$table->setAttributeNode($tcode);
				for($i = 0; $i < $ds->RecordCount(); $i++)
				{
					$ds->Next();
					$record = $this->xml->createElement("record");
					foreach ($fields as $fieldName)
					{
						$field = $this->xml->createElement("field");
						$fcode = $this->xml->createAttribute("code");
						$fcode->nodeValue = $fieldName;
						$field->setAttributeNode($fcode);
						$field->nodeValue = $ds->FieldByName($fieldName);
						$record->appendChild($field);
					}
					$table->appendChild($record);
				}
				$this->root->appendChild($table);
			}
		}
	}
	
	function XmlToDs($xmldoc, $kid)
	{
		$ss = new SaeStorage();
		foreach($xmldoc->table as $table)
		{
			$tablename = $table["code"];
			if($tablename == "HM_Record") $KeyID = "ID_";
			elseif($tablename == "HM_Like") $KeyID = "LikeID_";
			elseif($tablename == "HM_Files") $KeyID = "RecordID_";
			elseif($tablename == "HM_Values") $KeyID = "RecordID_";
			else $KeyID = "ID_";
			$ds = new TDataSet();
			$ds->CommandText = "delete from $tablename where $KeyID='$kid'";
			$ds->Execute();
			foreach($table->record as $record)
			{
				$fieldstr = "";
				$valuestr = "";
				foreach($record->field as $field)
				{
					$fieldstr = $fieldstr . $field["code"] . ",";
					$valuestr = "$valuestr'$field',";
					if($tablename == "HM_Files" && $field["code"] == "FileName_")
					{
						$fn = $kid . '/' . $field;
						if($ss->fileExists("files", $fn))
						$ss->delete('files', $fn);
					}
				}
				if(strlen($fieldstr) > 1)
				{
					$fieldstr = substr($fieldstr, 0, strlen($fieldstr) - 1);
					$valuestr = substr($valuestr, 0, strlen($valuestr) - 1);				
					$ds->CommandText = "insert into $tablename($fieldstr) values($valuestr)";
					$ds->Execute();
				}
			}
			$ds = null;
		}
		if(!mysql_error()) return "success";
	}

	$id = $_GET["id"];
	if(!$id) $id = $_POST["id"];
	$mode = $_GET["mode"];
	if(!$mode) $mode = $_POST["mode"];
	if(!$id || !$mode)
	{
		echo "id and mode can not null!";
		exit;
	}

	//sae mysql
	$dm = new TMainData();
	
	if($mode === "downfile")
	{
		$dsxml = new DsToXml();
		
		$ds = new TDataSet();
		$ds->CommandText = "select * from HM_Record where ID_='$id'";
		$ds->Open();
		$dsxml->AddTable("HM_Record", $ds, Array("ParentID_", "ID_", "Subject_", "Body_", 
			"IndexFile_", "Type_", "Final_", "UpdateUser_", "UpdateDate_", "AppUser_", "AppDate_", "UpdateKey_"));
		$ds = null;
		
		$ds = new TDataSet();
		$ds->CommandText = "select * from HM_Like where LikeID_='$id'";
		$ds->Open();
		$dsxml->AddTable("HM_Like", $ds, Array("RecordID_", "It_", "LikeID_", "AppUser_", "AppDate_", "UpdateKey_"));
		$ds = null;

		$ds = new TDataSet();
		$ds->CommandText = "select * from HM_Files where RecordID_='$id'";
		$ds->Open();
		$dsxml->AddTable("HM_Files", $ds, Array("RecordID_", "FileName_", "Remark_", "AppUser_", "AppDate_", "UpdateKey_"));
		$ds = null;
		
		$ds = new TDataSet();
		$ds->CommandText = "select * from HM_Values where RecordID_='$id'";
		$ds->Open();
		$dsxml->AddTable("HM_Values", $ds, Array("RecordID_", "Value_", "Remark_", "Address_", "AppUser_", "AppDate_", "UpdateKey_"));
		$ds = null;
		
		$dsxml->EchoXML();
	}
	elseif($mode === "uprecord")
	{
		if(isset($_FILES['userfile']) and $_FILES['userfile']["tmp_name"])
		{
			$param = file_get_contents($_FILES['userfile']["tmp_name"]);
			$xmldoc = simplexml_load_string($param);
			echo XmlToDs($xmldoc, $id);
		}
	}
	elseif($mode === "upfile")
	{
		if(isset($_FILES['userfile']) and $_FILES['userfile']["name"])
		{
			$filename = $_FILES['userfile']["name"];
			echo $filename;
			$upload = new sae_upload();
			$upload->domain="files";
			$upload->path = $id;
			$upload->type="png|jpg|gif|txt|html|htm|xml|xsd|doc|ppt|xls";
			$upload->name="userfile";
			$rs = $upload->upload();
			if($rs['success'] == "1")
				echo "success";
			else
				echo "fail";
		}
	}
?>