<?php
if( !defined('IN') ) die('bad request');

class TComponent{
	private $Name = '';
	public $Owner;
	public $Components = array();

	public function __construct($Owner = null)
	{
		$this->Owner = $Owner;
		if(!empty($Owner)){
			if(method_exists($Owner, 'AddComponent')){
				$Owner->AddComponent($this);
				if($this->Name === ''){
					$name = substr(get_class($this), 1);
					$this->Name = $name . $Owner->ComponentCount;
				}
			}
		}
	}
	
	public function Show(){
		echo 'Name:' . $this->Name;
	}

	public function AddComponent($Item){
		$this->Components[] = $Item;
	}
	
	public function RemoveComponent($Component){
		if(array_key_exists($Component, $this->Components)){
			unset($this->Components[$Component]);
		}
	}

	public function __get($name){
		if($name === 'Name'){
			return $this->Name;
		}elseif($name === 'ComponentCount'){
			return count($this->Components);
		}
	}
	
	public function __set($name, $value){
		if($name === 'Name'){
			$this->Name = $value;
		}
	}
	
	public function getName(){
		return $this->Name;
	}
}
?>