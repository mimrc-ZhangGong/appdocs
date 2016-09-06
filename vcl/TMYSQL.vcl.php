<?php
if( !defined('IN') ) die('bad request');

class TMYSQL
{
	public function query($sql, $conn)
	{
		return mysql_query($sql, $conn);
	}
	
	public function num_rows($record)
	{
		return mysql_num_rows($record);
	}
	
	public function fetch_array($record)
	{
		return mysql_fetch_array($record);
	}
	
	public function fetch_row($record)
	{
		return mysql_fetch_row($record);
	}
	
	public function num_fields($record)
	{
		return mysql_num_fields($record);
	}
	
	public function field_name($record, $index)
	{
		return mysql_field_name($record, $index);
	}
	
	public function data_seek($record)
	{
		return mysql_data_seek($record, 0);
	}
}