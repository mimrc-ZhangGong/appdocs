<?
if( !defined('IN') ) die('bad request');

/*--------------------------------
功能:		中国短信网PHP HTTP接口 发送短信
修改日期:	2009-04-08
说明:		http://http.c123.com/tx/?uid=用户账号&pwd=MD5位32密码&mobile=号码&content=内容
状态:
	100 发送成功
	101 验证失败
	102 短信不足
	103 操作失败
	104 非法字符
	105 内容过多
	106 号码过多
	107 频率过快
	108 号码内容空
	109 账号冻结
	110 禁止频繁单条发送
	111 系统暂定发送
	112 号码不正确
	120 系统升级
--------------------------------*/

class TSendSMS{
	//private $http = 'http://http.c123.com/tx/';
	
	private $http = 'http://http.smstong.com/tx/'; //管理平台：http://sms.smstong.com/login.php
	private $uid = '';
	private $pwd_md5 = '';
	private $mid = '';
	
	public $SendTime = '';
	//$SendTime = '2010-05-27 12:11'; //定时发送
	public $Message = '';
	
	public function __construct(){
		global $SmsInfo;
		$this->uid = $SmsInfo['uid'];
		$this->pwd_md5 = $SmsInfo['pwd_md5'];
	}
	
	public function Send($mobile, $content)
	{
		$to = '';
		if(is_array($mobile)){
			foreach($mobile as $user){
				$to .= $user . ',';
			}
			$to = substr($to, 0, strlen($to) - 1);
		}else{
			$to = $mobile;
		}
		$data = array
			(
			'uid'=>$this->uid,					//用户账号
			'pwd'=>strtolower($this->pwd_md5),	//MD5位32密码
			'mobile'=>$to,				        //号码
			'encode'=>'utf8',
			'content'=>$content,			    //内容
			'time'=>$this->SendTime,		    //定时发送
			'mid'=>$this->mid				    //子扩展号
			);
		$re = trim($this->postSMS($this->http, $data));		//POST方式提交
		if( $re == '100' )
		{
			$this->Message = '发送成功!';
			return true;
		}
		else 
		{
			$status = array(
				'100' => '发送成功',
				'101' => '验证失败',
				'102' => '短信不足',
				'103' => '操作失败',
				'104' => '非法字符',
				'105' => '内容过多',
				'106' => '号码过多',
				'107' => '频率过快',
				'108' => '号码内容空',
				'109' => '账号冻结',
				'110' => '禁止频繁单条发送',
				'111' => '系统暂定发送',
				'112' => '号码不正确',
				'120' => '系统升级'
			);
		if(array_key_exists($re, $status))
				$this->Message = '发送失败! 状态：'.$status[$re];
			else
				$this->Message = '发送失败! 状态：'.$re;
			return false;
		}
	}

	private function postSMS($url,$data='')
	{
		$row = parse_url($url);
		$host = $row['host'];
		$port = isset($row['port']) ? $row['port'] : 80;
		$file = $row['path'];
		$post = '';
		while (list($k,$v) = each($data)) 
		{
			$post .= rawurlencode($k)."=".rawurlencode($v)."&";	//转URL标准码
		}
		$post = substr( $post , 0 , -1 );
		$len = strlen($post);
		$fp = @fsockopen( $host ,$port, $errno, $errstr, 10);
		if (!$fp) {
			return "$errstr ($errno)\n";
		} else {
			$receive = '';
			$out = "POST $file HTTP/1.1\r\n";
			$out .= "Host: $host\r\n";
			$out .= "Content-type: application/x-www-form-urlencoded\r\n";
			$out .= "Connection: Close\r\n";
			$out .= "Content-Length: $len\r\n\r\n";
			$out .= $post;		
			fwrite($fp, $out);
			while (!feof($fp)) {
				$receive .= fgets($fp, 128);
			}
			fclose($fp);
			$receive = explode("\r\n\r\n",$receive);
			unset($receive[0]);
			return implode("",$receive);
		}
	}
}
?>