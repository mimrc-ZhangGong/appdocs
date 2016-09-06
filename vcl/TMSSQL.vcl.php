<?php
if( !defined('IN') ) die('bad request');

class TMSSQL
{
	public function query($sql, $conn)
	{
		return sqlsrv_query($conn, $sql, array(), array( "Scrollable" => SQLSRV_CURSOR_KEYSET ));
	}
	
	public function fetch_array($record)
	{
		return sqlsrv_fetch_array($record, SQLSRV_FETCH_BOTH);
	}
	
	public function fetch_row($record)
	{
		return sqlsrv_fetch_array($record, SQLSRV_FETCH_BOTH);
	}
	
	public function num_rows($record)
	{
		return sqlsrv_num_rows($record);
	}
	
	public function num_fields($record)
	{
		return sqlsrv_num_fields($record);
	}
	
	public function field_name($record, $index)
	{
		return sqlsrv_get_field($record, $index);
	}
	
	public function data_seek($record)
	{
		return sqlsrv_fetch($record, SQLSRV_SCROLL_FIRST);
	}
}