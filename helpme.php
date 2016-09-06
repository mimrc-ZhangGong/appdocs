<?php
define( 'IN' , true );
define( 'ROOT' , dirname( __FILE__ ) . '/' );
define( 'VCL' , ROOT . 'vcl/'  );
define( 'BIN' , ROOT . 'bin/'  );

session_start();

require_once(VCL.'vcl.delphi.php');
require_once(BIN.'app.config.php');
//require_once(BIN.'helpme.class.php');

global $Session;
if(!$Session) $Session = new TWebSession();
//echo $Session->Message . 'helpme.php';
//$mainface_class = 'TScreen' . $Session->ScreenWidth;
//$mainface = new $mainface_class;

$o = new helpme();
$o->Execute();
?>