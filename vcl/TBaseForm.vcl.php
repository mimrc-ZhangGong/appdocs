<?php
if( !defined('IN') ) die('bad request');

class TBaseForm
{
	public function Begin()
	{
		?>
		<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
		<table align="center">
		<tr>
		<?php
	}
	
	public function Add($Caption, $id, $value = '')
	{
		echo "<td>$Caption</td>";
		echo "<td><input type=\"text\" name=\"$id\" value=\"$value\" size=\"20\"></td>";
	}
	
	public function AddHidden($id, $value)
	{
		echo "<input type=\"hidden\" name=\"$id\" value=\"$value\">";
	}
	
	public function End()
	{
		?>
		</tr>
		<tr>
		<th colspan="2"><input type="submit" value="提交"/><input type="reset" value="重置"/></th>
		</tr>
		</table>
		</form>
		<?php
	}
}
?>