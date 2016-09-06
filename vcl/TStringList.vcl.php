<?php
class TStringList{
    var $datas = array();
    public function Add($value)
    {
    	$i = count($this->datas);
    	$this->datas = array_pad($this->datas, $i + 1, $value);
        return($i + 1);
    }
    
	public function __get($name){
		if($name === 'Count'){
			return(count($this->datas));
		}
	}
	
    public function Strings($index)
    {
    	return($this->datas[$index]);
    }
    
    public function Text()
    {
    	$rst = '';
    	foreach($this->datas as $value)
          $rst = $rst . $value;
        return($rst);
    }
    
    public function Debug()
    {
    	print_r($this->datas);
    }
}
?>