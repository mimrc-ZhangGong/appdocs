<?php
if( !defined('IN') ) die('bad request');

class TWinControl extends TComponent{
	private $Window;
	public $Controls = array();
	public $Lines = array();

	public function AddControl($Control){
		$this->Controls[] = $Control;
	}
	
	public function RemoveControl($Control){
		if(array_key_exists($Control, $this->Controls)){
			unset($this->Controls[$Control]);
		}
	}
	
	public function __get($name){
		if($name === 'Window'){
			return $this->Window;
		}else{
			return parent::__get($name);
		}
	}

	public function __set($name, $value){
		parent::__set($name, $value);
		if($name === 'Window'){
			if($this->Window <> $value){
				//判断必须是TWinControl控件
				if(method_exists($value, 'AddControl')){
					//移除旧的指向
					if($this->Window) $this->Window->RemoveControl($this);
					//注册
					if($value) $value->AddControl($this);
					//记录
					$this->Window = $value;
				}else{
					die( '错误的注册！' );
				}
			}
		}
	}
	
	public function OnBeforeShow(){
		//若是容器类，须覆盖此方法
	}
	
	public function OnShow(){
		if(count($this->Lines) > 0){
			foreach($this->Lines as $line){
				echo $line;
			}
		}else{
			if(defined('DEBUG'))
				echo $this->Name . '.' . get_class($this) . '.' . __FUNCTION__ . "<br/>\n";
		}
	}
	
	final public function Show(){
		$this->OnBeforeShow();
		foreach($this->Controls as $obj){
			//echo get_class($obj);
			$obj->Show();
		}
		$this->OnShow();
	}
}
?>