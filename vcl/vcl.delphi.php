<?php
require_once 'SAE.vcl.php';

function __autoload($class_name) {
	if(file_exists(VCL.$class_name . '.vcl.php'))
		require_once VCL.$class_name . '.vcl.php';
	elseif(file_exists(BIN.$class_name . '.class.php'))
		require_once BIN.$class_name . '.class.php';
}

class DIV extends TWinControl{
	public $Params;
	public $Text;
	public $DIV = 'div';
	
	public function OnBeforeShow(){
		if($this->Params)
			echo "<$this->DIV $this->Params>\n";
		else
			echo "<$this->DIV>";
	}
	
	public function OnShow(){
		parent::OnShow();
		echo "</$this->DIV>\n";
	}
}

class TABLE extends DIV{
	public function __construct($Owner){
		$this->DIV = 'table';
		parent::__construct($Owner);
	}
}

class TR extends DIV{
	public function __construct($Owner){
		$this->DIV = 'tr';
		parent::__construct($Owner);
	}
}

class TH extends DIV{
	public function __construct($Owner){
		$this->DIV = 'th';
		parent::__construct($Owner);
	}
}

class TD extends DIV{
	public function __construct($Owner){
		$this->DIV = 'td';
		parent::__construct($Owner);
	}
}

//返回数据类型 for delphi
function getVarType($data)
{
	if(is_array($data))
		return '8204';
	elseif(is_string($data))
		return '8';
	elseif(is_integer($data))
		return '3';
	elseif(is_bool($data))
		return '11';
	elseif(is_float($data))
		return '5';
	elseif(preg_match('/^(0[1-9]|[1-2][0-9]|3[0-1])\/(0[1-9]|1[0-2])\/[0-9]{4}$/', $data))
		return '7';
	else
		return '0';
}

function XmlToVar($value, $root = 0){
	$data = null;
	if($root === 0){
		$xml = new DOMDocument();
		$xml->loadXML($value);
		$root = $xml->documentElement;
	}
	$type = $root->getAttribute('type');
	if($type === '8204'){ //array
		$item = $root->firstChild;
		while($item){
			$data[] = XmlToVar(null, $item);
			$item = $item->nextSibling;
		}
	}elseif($type === '8'){ //string
		$data = $root->nodeValue;
	}elseif($type === '3'){ //int
		$data = intval($root->nodeValue);
	}elseif($type === '11'){ //boolean
		$data = $root->nodeValue == '1' ? true : false;
	}elseif($type === '5'){ //float
		$data = (float)$root->nodeValue;
	}elseif($type === '7'){ //
		$data = $root->nodeValue;
	}else{
		echo '未处理的类型：' . $type . '<br/>';
	}
	return $data;
}

//将数组转入xml(for delphi)
function VarToXml($arr, $xml=0, $root=0)
{
    if (!$xml){
        $xml = new DOMDocument('1.0', 'UTF-8');
    }
    if(!$root){
        $root = $xml->createElement("data"); 
		if(!is_array($arr)){
			$root->nodeValue = $arr;
		}
        $type = $xml->createAttribute("type");
		$type->nodeValue = getVarType($arr);
        $root->appendChild($type); 
        $xml->appendChild($root);
    }
	//创建内容
	if(is_array($arr)){
		foreach ($arr as $val){
			$child = $xml->createElement("data");
			$type = $xml->createAttribute("type");
			$type->nodeValue = getVarType($val);
			$child->appendChild($type); 
			$root->appendChild($child);
			if (!is_array($val)){
				if(is_bool($val) and ($val == false))
					$item = $xml->createTextNode('0');
				else
					$item = $xml->createTextNode($val);
				$child->appendChild($item);
				
			}else {
				VarToXml($val,$xml,$child);
			}
		}
	}
    header("Content-Type: text/xml; charset=utf-8");
    return $xml->saveXML();
}

function mssql_ToUTF8($string)
{
	return iconv("gbk","utf-8",$string);
}

function mssql_ToGBK($string)
{
	return iconv("utf-8","gbk",$string);
}

function DBExists($sql)
{
	global $DBConnection;
	if(!isset($DBConnection)) $DBConnection = new TMainData();
	$DB = new TDataSet();
	$result = $DB->DS->query($sql, $DBConnection->conn);
	if($result){
		$count = $DB->DS->num_rows($result);
		$DB = null;
		return $count > 0;
	}else{
		die($sql);
	}
}

function DBRead($sql, $default = null)
{
	//new TMainData();
	global $DBConnection;
	if(!isset($DBConnection)) $DBConnection = new TMainData();
	$DB = new TDataSet();
	$result = $DB->DS->query($sql, $DBConnection->conn);
	if($result){
		if($DB->DS->num_rows($result) > 0){
			$rows = $DB->DS->fetch_row($result);
			$DB = null;
			return $rows[0];
		}
		else
			return $default;
	}else{
		die( $sql );
		return $default;
	}
}

function ExecSQL($sql){
	//new TMainData();
	global $DBConnection;
	if(!isset($DBConnection)) $DBConnection = new TMainData();
	
	$DB = new TDataSet();
	$result = $DB->DS->query($sql, $DBConnection->conn);
	if($result){
		return true;
	}else{
		echo '<p>'.mysql_error()."</p>\n";
		return false;
	}
}

function BuildUrl($url, $Caption){
	return "<a href=\"$url\">$Caption</a>";
}

function NewGuid() {
	$charid = strtoupper(md5(uniqid(mt_rand(), true)));
	$hyphen = chr(45);// "-"
	$uuid = substr($charid, 0, 8).$hyphen
	.substr($charid, 8, 4).$hyphen
	.substr($charid,12, 4).$hyphen
	.substr($charid,16, 4).$hyphen
	.substr($charid,20,12);
	//.chr(125);// "}"
	return $uuid;
}

function utf8_strlen($string = null) {
	// 将字符串分解为单元
	preg_match_all("/./us", $string, $match);
	// 返回单元个数
	return count($match[0]);
}

function utf8_substr($string, $start, $len = 0){
	// 将字符串分解为单元
	preg_match_all("/./us", $string, $match);
	// 返回单元个数
	if($len === 0) $len = count($match[0]);
	$str = '';
	$i = 0;
	foreach($match[0] as $ch){
		if(($i >= $start) and ($i <= $len)){
			$str .= $ch;
		}
		if($i === $len) break;
		$i++;
	}
	return $str;
}

function DateDiff($part, $begin, $end)
{
	$diff = strtotime($end) - strtotime($begin);
	switch($part)
	{
		case "y": $retval = bcdiv($diff, (60 * 60 * 24 * 365)); break;
		case "m": $retval = bcdiv($diff, (60 * 60 * 24 * 30)); break;
		case "w": $retval = bcdiv($diff, (60 * 60 * 24 * 7)); break;
		case "d": $retval = bcdiv($diff, (60 * 60 * 24)); break;
		case "h": $retval = bcdiv($diff, (60 * 60)); break;
		case "n": $retval = bcdiv($diff, 60); break;
		case "s": $retval = $diff; break;
	}
	return $retval;
}

function DateAdd($part, $n, $date)
{
	switch($part)
	{
		case "y": $val = date("Y-m-d", strtotime($date ." +$n year")); break;
		case "m": $val = date("Y-m-d", strtotime($date ." +$n month")); break;
		case "w": $val = date("Y-m-d", strtotime($date ." +$n week")); break;
		case "d": $val = date("Y-m-d", strtotime($date ." +$n day")); break;
		case "h": $val = date("Y-m-d", strtotime($date ." +$n hour")); break;
		case "n": $val = date("Y-m-d", strtotime($date ." +$n minute")); break;
		case "s": $val = date("Y-m-d", strtotime($date ." +$n second")); break;
	}
	return $val;
}

function TimeAdd($part, $n, $date)
{
	switch($part)
	{
		case "y": $val = date("Y-m-d H:i:s", strtotime($date ." +$n year")); break;
		case "m": $val = date("Y-m-d H:i:s", strtotime($date ." +$n month")); break;
		case "w": $val = date("Y-m-d H:i:s", strtotime($date ." +$n week")); break;
		case "d": $val = date("Y-m-d H:i:s", strtotime($date ." +$n day")); break;
		case "h": $val = date("Y-m-d H:i:s", strtotime($date ." +$n hour")); break;
		case "n": $val = date("Y-m-d H:i:s", strtotime($date ." +$n minute")); break;
		case "s": $val = date("Y-m-d H:i:s", strtotime($date ." +$n second")); break;
	}
	return $val;
}

function GetEncodeHtml($html){
    $html = str_replace("&", "&amp;",$html);
    $html = str_replace("<", "&lt;",$html);
    $html = str_replace(">", "&gt;",$html);
	return $html;
}

function GetEncodeSql($sql){
    $sql = str_replace("&amp;", "&", $sql);
    $sql = str_replace("&lt;", "<", $sql);
    $sql = str_replace("&gt;", ">", $sql);
    $sql = str_replace("&quot;", "\"", $sql);
	$sql = str_replace("&nbsp;", " ", $sql);
    return addslashes($sql);
}

function GetParam($key){
	$rst = null;
	if($key){
		if(isset($_GET[$key])){
			$rst = $_GET[$key];
		}
	}
	if($rst){
        $rst = str_replace("'", "", $rst);
        $rst = str_replace(";", "", $rst);
	}
	return $rst;
}

function PostParam($key){
	$rst = null;
	if($key){
		if(isset($_POST[$key])){
			$rst = $_POST[$key];
		}
	}
	if($rst){
        if(is_array($rst)){
			foreach($rst as $item){
				$item = str_replace(array("'", ";"), "", $item);
			}
		}else{
			$rst = str_replace(array("'", ";"), "", $rst);
		}
	}
	return $rst;
}

function GetDateBtw($dater, $field_code = null){
	$g_date = date('Y-m-d');
	$date_fr = $g_date;
	$date_to = $g_date;
	$w = date("w", strtotime($g_date));
	$dn = $w ? $w : 6;
	switch($dater){
		case '0': break;
		case '1':
			$date_fr = date('Y-m-d', strtotime(" -1 day"));
			$date_to = date('Y-m-d', strtotime(" -1 day"));
			break;
		case '2':
			$date_fr = date("Y-m-d", strtotime("$g_date -" . $dn . " days"));
			$date_to = date("Y-m-d", strtotime("$date_fr +6 days"));
			break;
		case '3':
			$st = date("Y-m-d", strtotime("$g_date -" . $dn . " days"));
			$date_fr = date('Y-m-d',strtotime(("$st - 7 days")));
			$date_to = date('Y-m-d',strtotime("$st - 1 days"));
			break;
		case '4':
			$date_fr = date('Y-m-d', mktime(0,0,0,date('m'),1,date('Y')));
			$date_to = date('Y-m-d', mktime(0,0,0,date('m'),date('t'),date('Y')));
			break;
		case '5':
			$date_fr = date('Y-m-d', mktime(0,0,0,date('m')-1,1,date('Y')));
			$t = date('t',strtotime("$date_fr"));
			$date_to = date('Y-m-d', mktime(0,0,0,date('m')-1,$t,date('Y')));
			break;
		case '6':
			$date_fr = date('Y-m-d', mktime(0,0,0,1,1,date('Y')));
			$t = date('t',strtotime("$date_fr"));
			$date_to = date('Y-m-d', mktime(0,0,0,12,$t,date('Y')));
			break;
	}
	if($field_code){
		return " and $field_code between '$date_fr' and '$date_to'";
	}
	else{
		return array('fr' => $date_fr, 'to' => $date_to);
	}
}
/*
	echo "<table border=\"1\" width=\"100%\" id=\"table1\" cellspacing=\"0\">";
	$rs = mysql_query("select * from WF_UserInfo", $conn);
	while($ar = mysql_fetch_assoc($rs))
	{
		echo "<tr>";
		$numberfields = mysql_num_fields($rs);
		for($i=0; $i < $numberfields; $i++){
			echo "<td>";
			$fdname = mysql_field_name($rs,   $i);
			echo $ar[$fdname];
			echo "</td>";
		}
		echo "</tr>";
	}
	echo "</table>";

*/
?>