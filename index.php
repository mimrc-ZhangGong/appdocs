<?php
define( 'IN' , true );
define( 'ROOT' , dirname( __FILE__ ) . '/' );
define( 'VCL' , ROOT . 'vcl/'  );
define( 'BIN' , ROOT . 'bin/'  );

//define( 'DEBUG' , true  );
date_default_timezone_set('Asia/Shanghai');
Session_start();

require(VCL.'vcl.delphi.php');
require('conf\app.config.php');

global $homepage; //系统首页
$m = isset($_REQUEST['m']) ? $_REQUEST['m'] : $homepage;
//临时修复
if(!isset($_REQUEST['m']) && isset($_REQUEST['app']) && isset($_REQUEST['ver'])) $m='TDloadfile';
$m = htmlspecialchars($m);
$class_name = $m; 
if( ! class_exists( $class_name ) ){
	$mod_file = BIN . $class_name .'.class.php';
	if( !file_exists( $mod_file ) )
		die('Can\'t find controller file - ' . $m . '.class.php');
	require( $mod_file );
}
if( class_exists( $class_name ) ){
	$Session = new TWebSession();
	if($Session->Logon){
		$class_name = Permission($class_name);
	}
	$o = new $class_name;
	if($o){
		$a = 'Execute';
		if( method_exists( $o , $a ) ){
			call_user_func( array( $o , $a ) );
			$a = null;
		}
		else{
			die('Can\'t find method - '   . $a . ' ');
		}
	}
}
else{
	die('Can\'t find class - ' . $class_name);
}

function Permission($code){//菜单权限控制
	global $Session;
	$m = new mainmenu();
	$mainmenu = $m->getItems();
	foreach($mainmenu as $item){//app配置文件中已定义的菜单
		if($item['menucode'] == $code && !empty($item['security'])){
			$secu = DBRead("select UserCode_ from US_Security where Code_='$code' and UserCode_='$Session->UserCode'");
			return $secu ? $code : 'ErrorPage';
		}
	}
	return $code;
}
?>
