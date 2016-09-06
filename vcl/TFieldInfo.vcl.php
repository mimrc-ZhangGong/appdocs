<?php
if( !defined('IN') ) die('bad request');

class TFieldInfo{
	private $FieldCode;
	private $params;
	public function __construct($FieldCode, $params){
		$this->FieldCode = $FieldCode;
		$this->params = $params;
	}
	
	public function hasParam($param){
		return array_key_exists($param, $this->params);
	}
	
	public function __get($name){
		if(array_key_exists($name, $this->params))
			return $this->params[$name];
		elseif($name == 'Control'){
			return array_key_exists($name, $this->params) ? $this->params[$name] : 'TEdit';
		}elseif($name == 'Caption')
			return $this->FieldCode;
		elseif($name == 'remark')
			return '';
		elseif($name == 'view')
			return true;
		elseif($name == 'append')
			return true;
		elseif($name == 'modify')
			return true;
		elseif($name == 'isData')
			return true;
        elseif($name == 'Visible')
            return true;
		else
			return null;
	}
}
?>