<?php
class TAppBean{
	public $ParamFile;
	public $CorpCode;
	public $Data = null;
	public $Result = false;
	public $Lines = array();
	
	public function Execute()
	{
		$this->Lines[] = '不可以直接调用 TAppBean.';
		$this->Result = false;
	}
	
	public function AddLine($value){
		if(is_array($value)){
			foreach($value as $line){
				$this->Lines[] = $line;
			}
		}else{
			$this->Lines[] = $value;
		}
	}
	
	public function AddError($value){
		if(!in_array($value, $this->Lines)){
			$this->Lines[] = $value;
		}
		$this->Result = false;
	}
}
?>