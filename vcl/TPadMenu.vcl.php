<?php
if( !defined('IN') ) die('bad request');

class TPadMenu extends TDBForm{
	
	public function __construct($Owner = null){
		date_default_timezone_set('PRC');
		$this->ClearMenus = false;
		parent::__construct($Owner);
	}
	
	public function OnDefault(){
		global $Mainface;
		echo '<center style="font-size:16px;line-height:24px;"><ul class="pad-mainmenu">';
		foreach($Mainface->mainmenu as $item){//print_r($item);
			if(!empty($item['default'])){
				$url = '?m='.$item['menucode'];
				$name = $item['section'];
				echo "<li><a href=\"$url\">$name</a></li>";
			}
		}
		echo '<div style="clear:left;"></div></ul>';
		if($Mainface->menus){
			echo '<ul class="pad-usermenu">';
			foreach($Mainface->menus as $item){
				if(is_array($item)){
					if($item[0] <> ''){
						echo "<li><a href=\"$item[0]\">$item[1]";
						if(count($item) == 3) echo " $item[2]";
						echo "</a></li>\n";
					}else{
						echo "<li>$item[1]</li>\n";
					}
				}
				// else{
					// echo "<li><a href='javascript:'>$item</a></li>\n";
				// }
			}
			echo '<div style="clear:left;"></div></ul>';
		}
		echo '</center>';
	}
}
?>