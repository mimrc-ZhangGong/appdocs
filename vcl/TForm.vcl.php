<?php
define('VIEW_SHOW',   1);
define('VIEW_APPEND', 2);
define('VIEW_MODIFY', 3);
define('VIEW_DELETE', 4);
define('POST_APPEND', 5);
define('POST_MODIFY', 6);
define('POST_DELETE', 7);
define('MODE_BAD', 8);

class TForm extends TWinControl
{
	public $Action = 'OnDefault';
	public $BadMainface = array();
	private $ErrorInfo = 'bad request.';
	
	public function __construct($Owner = null){
		parent::__construct($Owner);
		if(isset($_POST['mode'])){
			$mode = htmlspecialchars($_POST['mode']);
			if($mode == 'append'){
				$this->Action = 'OnPostAppend';
			}elseif($mode == 'modify'){
				$this->Action = 'OnPostModify';
			}elseif($mode == 'delete'){
				$this->Action = 'OnPostDelete';
			}else{
				$this->ErrorInfo = '[POST]Mode Error!';
				$this->Action = 'BadRequest';
			}
		}elseif(isset($_GET['mode'])){
			$mode = htmlspecialchars($_GET['mode']);
			if($mode == 'append'){
				$this->Action = 'OnAppend';
			}elseif($mode == 'modify'){
				$this->Action = 'OnModify';
			}elseif($mode == 'delete'){
				$this->Action = 'OnDelete';
			}else{
				$this->ErrorInfo = '[PUT]Mode Error!';
				$this->Action = 'BadRequest';
			}
		}elseif(isset($_GET['a'])){
			$action = htmlspecialchars($_GET['a']);
			if( method_exists( $this , $action ) ){
				$this->Action = $action;
			}else{
				$this->ErrorInfo = '[POST-A]Mode Error!';
				$this->Action = 'BadRequest';
			}
		}elseif(isset($_POST['a'])){
			$action = htmlspecialchars($_POST['a']);
			if( method_exists( $this , $action ) ){
				$this->Action = $action;
			}else{
				$this->ErrorInfo = '[PUT-A]Mode Error!';
				$this->Action = 'BadRequest';
			}
		}
	}
	
	public function Execute(){
		global $Mainface;
		if( !$this->checkLogin() )
		{
			$this->Action = 'BadRequest';
		}else{
			//此处为了满足部分Action不需要默认Mainface的需求，如导出XLS之类
			if(!in_array($this->Action, $this->BadMainface)){
				global $Session;
				$class = 'TScreen' . $Session->ScreenWidth;
				$Mainface = new $class;
			}
			$this->OnCreate();
		}
		if($Mainface){
			$this->Window = $Mainface->mainbox;
			$Mainface->Show(); //它会调用OnShow
		}else{
			$this->Show();
		}
	}
	
	public function checkLogin(){
		if( !isLogin() )
		{
			if(isset($_GET['id']) and isset($_GET['pwd'])){
				global $Session;
				$rst = $Session->Login(htmlspecialchars($_GET['id']), htmlspecialchars($_GET['pwd']), true);
				if(! $rst )
				{
					$this->ErrorInfo = $Session->Message;
				}
				return $rst;
			}else{
				header('Location: '. $this->GetUrl(null, null, 'TFrmLogin'));
				return false;
			}
		}elseif( ulevel() < 0 ){
			header('Location: '. $this->GetUrl(null, null, 'TFrmLogin'));
			return false;
		}else{
			return true;
		}
	}
	
	public function OnShow(){
		/*
			Show 会调用 OnShow;
			OnShow 会调用 Action;
		*/
		call_user_func( array( $this , $this->Action ) );
	} 
	
	public function OnCreate()
	{
		//在此初始化控件
	}

	public function BadRequest(){
		
		header('Content-Type: text/html;charset=utf-8'); 
		echo '<html><head><title>bad request</title></head><body>'
			.$this->ErrorInfo.'</body></html>';
		/*
		echo $this->ErrorInfo;
		*/
	}
	
	function GetUrl($mode = null, $param = '', $form = ''){
		$action = '';
		if(!empty($mode)){
			Switch(htmlspecialchars($mode)){
			case VIEW_APPEND:
				$action = '&a=OnAppend';
				break;
			case VIEW_MODIFY:
				$action = '&a=OnModify';
				break;
			case VIEW_DELETE:
				$action = '&a=OnDelete';
				break;
			default:
				$action = '&a=' . $mode;
				break;
			}
		}
		if($form == '')
			$class_name = get_class($this);
		else
			$class_name = $form;
		if($param == '')
			return 'index.php?m='.$class_name.$action;
		else
			return 'index.php?m='.$class_name.$action.'&'.$param;
	}
	
	public function Helpme(){
		$helpid = isset($_GET['id']) ? htmlspecialchars($_GET['id']) : null;
		if($helpid){
			$url = "http://appdocs.sinaapp.com/getrecord.php?id=$helpid";
			//$url = "http://127.0.0.1/appdocs/1/getrecord.php?id=$helpid";
			echo file_get_contents($url);
		}else{
			$this->BadRequest();
		}
	}
	
	public function __set($name, $value)
	{
		parent::__set($name, $value);
		if($name === 'Caption'){
			global $Mainface;
			if($Mainface) $Mainface->Caption = $value;
		}elseif($name === 'Message'){
			global $Session;
			if($Session){
				$Session->Message = $value;
			}
		}else{
			parent::__set($name, $value);
		}
	}
	
	public function __get($name)
	{
		if($name === 'Caption'){
			global $Mainface;
			return $Mainface ? $Mainface->Caption : null;
		}elseif($name === 'Message'){
			global $Session;
			return $Session->Message;
		}else{
			return parent::__get($name);
		}
	}
}
?>