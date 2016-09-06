<?php
define( 'IN' , true );
define( 'ROOT' , dirname( __FILE__ ) . '/' );
define( 'VCL' , ROOT . 'vcl/'  );
define( 'BIN' , ROOT . 'bin/'  );

session_start();

require_once(VCL.'vcl.delphi.php');
require_once(VCL.'TMainData.vcl.php');
require(BIN.'app.config.php');
include_once(BIN.'menudict.class.php');

header("Content-type:text/html; charset=utf-8");

function rehtml($content)
{
	$content = str_replace("&","&amp;",$content);
	$content = str_replace("<","&lt;",$content);
	$content = str_replace(">","&gt;",$content);
	$content = str_replace(" ","&nbsp;",$content);
	$content = str_replace(chr(13),"<br/>",$content);
	$content = str_replace(chr(34),"&quot;", $content);
	return $content;
}

$dm = new TMainData();

$id = null;
if(!isset($_GET['id'])){
	if(isset($_GET['code'])){
		$code = $_GET['code'];
		if(array_key_exists($code, $menudict)){
			$id = $menudict[$code];
		}
		else{
			echo "找不到 code = $code ！";
			exit;
		}
	}
}
else{
	$id = $_GET["id"];
}
if(!$id)
{
	echo "非法的使用方式！";
	exit;
}

//打开数据集
$ds = new TDataSet();
$ds->CommandText = "select Body_ from HM_Record where ID_=$id";
$ds->Open();

$rec = $ds->RecordCount();
if($rec > 0)
{
	$ds->Next();
	echo rehtml($ds->FieldByName('Body_'));
}
else{
	echo "很抱歉，没有找到相应的文档记录！";
}

/*
调用范例
<?php
$helpid = 100000;
$url = "http://appdocs.sinaapp.com/getrecord.php?id=$helpid";
//$url = "http://127.0.0.1/appdocs/1/getrecord.php?id=$helpid";
echo file_get_contents($url);
?>
*/
?>
