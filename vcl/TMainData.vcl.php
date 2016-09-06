<?php
if( !defined('IN') ) die('bad request');

class TMainData
{
	public $conn;
	
	public function __construct()
	{
		global $APP_DB;
		if(method_exists($this,'T'.$APP_DB['TYPE']))
		{
			call_user_func(array($this,'T'.$APP_DB['TYPE']));
		}
		else
		{
			die('Haven\'t this database type');
		}
	}
	
	public function TMYSQL()
	{
		global $APP_DB;
		$DBConnection = mysql_connect($APP_DB['HOST'].':'.$APP_DB['PORT'],$APP_DB['USER'],$APP_DB['PASS']);
		if(!$DBConnection)
			die('Could not connect: ' . mysql_error());
			mysql_query("set names utf8");//add by ray
		$db_selected = mysql_select_db($APP_DB['DB'], $DBConnection);
		if (!$db_selected)
		  die ('Can\'t use database : ' . mysql_error());
		$this->conn = $DBConnection;
	}
	
	public function TMSSQL()
	{
		global $APP_DB;
		$connectionInfo = array("UID"=>$APP_DB['USER'], "PWD"=>$APP_DB['PASS'], "Database"=>$APP_DB['DB'], "CharacterSet" => "UTF-8",'ReturnDatesAsStrings'=> true);
		$DBConnection = sqlsrv_connect($APP_DB['HOST'], $connectionInfo);
        if($DBConnection === false)
        {
            die('something went wrong while connecting to MSSQL:' . $APP_DB['HOST'] . ' - ' . $APP_DB['DB']);
            //die( print_r( sqlsrv_errors(), true));
        }
		$this->conn = $DBConnection;
	}
}