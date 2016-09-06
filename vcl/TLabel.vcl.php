<?php
if( !defined('IN') ) die('bad request');

/**
 * Created by JetBrains PhpStorm.
 * User: cerc2477
 * Date: 12-1-14
 * Time: 上午8:03
 * To change this template use File | Settings | File Templates.
 */
class TLabel extends TWinControl
{
    public $Caption;

    public function AddText($text){
        $this->Caption .= $text;
    }

    public function AddLine($text){
        $this->Caption[] = "$text<br>\n";
    }
	
	public function OnShow(){
		if(is_array($this->Caption)){
			foreach($this->Caption as $line)
				$this->Lines[] = $line;
		}elseif($this->Caption){
			$this->Lines[] = $this->Caption;
		}
	}
}
