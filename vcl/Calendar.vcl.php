<?php
class Calendar extends TWinControl
{
    private $weeks  = array('日','一','二','三','四','五','六');
	public $url;
    public $year;
    public $month;
	public $day;
	public $today;
	public $OnGetText;
     
    function __construct($AOwner) {
		parent::__construct($AOwner);
		if(isset($_GET['date'])){
			$this->today = $_GET['date'];
			$this->year = date('Y', strtotime($this->today));
			$this->month = date('m', strtotime($this->today));
			$this->day = date('d', strtotime($this->today));
		}else{
			$this->year = isset($_GET['year']) ? $_GET['year'] : date('Y');
			$this->month = isset($_GET['month']) ? $_GET['month'] : date('m');
			$this->day = isset($_GET['day']) ? $_GET['day'] : date('d');
		}
		/* 给属性批次赋值的范例，勿删除
        $this->url = basename($_SERVER['PHP_SELF']);
        $vars = get_class_vars(get_class($this));
        foreach ($options as $key=>$value) {
            if (array_key_exists($key, $vars)) {
                $this->$key = $value;
            }
        }
		*/
		$url = $_SERVER['PHP_SELF'];
		foreach($_GET as $key => $value){
			if(($key <> 'year') and ($key <> 'month') and ($key <> 'day') and ($key <> 'date')){
				$url .= (strpos($url, '?') ? "&" : "?") . "$key=$value";
			}
		}
		$url .= (strpos($url, '?') ? "&" : "?");
		$this->url = $url;
    }
     
    function OnShow()
    {
        echo '<table class="calendar">';
        $this->showChangeDate();
        $this->showWeeks();
        $this->showDays($this->year,$this->month);
        echo '</table>';
    }
     
    private function showWeeks()
    {
        echo '<tr>';
        foreach($this->weeks as $title)
        {
            echo '<th>'.$title.'</th>';
        }
        echo '</tr>';
    }
     
    private function showDays($year, $month)
    {
        $firstDay = mktime(0, 0, 0, $month, 1, $year);
        $starDay = date('w', $firstDay);
        $days = date('t', $firstDay);
 
        echo "\n<tr>";
        for ($i=0; $i<$starDay; $i++) {
            echo '<td>&nbsp;</td>';
        }
         
        for ($j=1; $j<=$days; $j++) {
            $i++;
            if ($j == $this->day) {
                echo '<td class="today">'.$j.'</td>';
            } else {
				$today = date('Y-m-d',mktime(0,0,0,$month,$j,$year));
                echo '<td>'.$this->Day_GetText($today).'</td>'."\n";
            }
            if ($i % 7 == 0) {
                echo '</tr><tr>';
            }
        }
         
        echo '</tr>';
    }
	
	public function Day_GetText($date){
		$event = $this->OnGetText;
		if($event){
			return $this->Owner->$event($this, $date);
		}else{
			return substr($date, 8, 2);
		}
	}
     
    private function showChangeDate()
    {
        echo "\n<tr>";
		echo '<td><a href="'.$this->preYearUrl($this->year,$this->month).'">'.'<<'.'</a></td>';
		echo '<td><a href="'.$this->preMonthUrl($this->year,$this->month).'">'.'<'.'</a></td>';
        echo '<td colspan="3"><form>';
        $url = $this->url;
        echo '<select name="year" onchange="window.location=\''.$url.'year=\'+this.options[selectedIndex].value+\'&month='.$this->month.'\'">';
        for($ye=2000; $ye<=2030; $ye++) {
            $selected = ($ye == $this->year) ? ' selected' : '';
            echo '<option'.$selected.' value="'.$ye.'">'.$ye.'</option>'."\n";
        }
        echo '</select>';
        echo '<select name="month" onchange="window.location=\''.$url.'year='.$this->year.'&month=\'+this.options[selectedIndex].value+\'\'">';
        for($mo=1; $mo<=12; $mo++) {
            $selected = ($mo == $this->month) ? ' selected' : '';
            echo '<option'.$selected.' value="'.$mo.'">'.$mo.'</option>'."\n";
        }
        echo '</select>';       
        echo '</form></td>';       
		echo '<td><a href="'.$this->nextMonthUrl($this->year,$this->month).'">'.'>'.'</a></td>';
		echo '<td><a href="'.$this->nextYearUrl($this->year,$this->month).'">'.'>>'.'</a></td>';       
        echo '</tr>';
    }
     
    private function preYearUrl($year,$month)
    {
        $year = ($this->year <= 1970) ? 1970 : $year - 1 ;
        return $this->url . 'year='.$year.'&month='.$month;
    }
     
    private function nextYearUrl($year,$month)
    {
        $year = ($year >= 2038)? 2038 : $year + 1;
        return $this->url . 'year='.$year.'&month='.$month;
    }
     
    private function preMonthUrl($year,$month)
    {
        if ($month == 1) {
            $month = 12;
            $year = ($year <= 1970) ? 1970 : $year - 1 ;
        } else {
            $month--;
        }       
        return $this->url . 'year='.$year.'&month='.$month;
    }
     
    private function nextMonthUrl($year,$month)
    {
        if ($month == 12) {
            $month = 1;
            $year = ($year >= 2038) ? 2038 : $year + 1;
        }else{
            $month++;
        }
        return $this->url . 'year='.$year.'&month='.$month;
    }
}