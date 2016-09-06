<?php
if( !defined('IN') ) die('bad request');

class TSendMail
{
	public $smtpAccount = 'ivws6117@163.com';
	public $smtpPassword = 'qq13877973582';
	public $smtpFrom = 'ivws6117@163.com';
	public $smtpServer = 'smtp.163.com';
	public $smtpPort = 25;
	public $smtpauth = true;
	public $time_out = 30;
	public $host_name = 'localhost';
	public $appname = "";
	public $log_file = "";
	public $sock = false;
	public $debug;
	
	public function __construct()
	{
		$this->debug = false;
		global $SmtpInfo;
		global $appname;
		$this->smtpAccount = $SmtpInfo['Account'];
		$this->smtpPassword = $SmtpInfo['Password'];
		$this->smtpFrom = $SmtpInfo['From'];
		$this->smtpServer = $SmtpInfo['Server'];
		$this->smtpPort = $SmtpInfo['Port'];
		$this->appname = $appname;
	}
	
	public function Send($SendTo, $Subject, $SendInfo){
		
		/*$users = array();
		$users[] = '1019183729@qq.com'; //李一
		$users[] = '1416960@qq.com'; //张弓
		$users[] = '707167666@qq.com'; //唐明
		$users[] = 'dadahacker@126.com'; //李震
		$users[] = '51679092@qq.com'; //梁伟
		if(in_array($SendTo, $users)){
		*/
			$result = false;
			$Subject = "=?UTF-8?B?".base64_encode($Subject)."?=";
			if($this->sendmail($SendTo, $Subject, $SendInfo)){
				$result = true;
			}
			return $result;
		
		/*}else{
			echo '<p>抱歉，邮件功能现正在内测中，暂不开放！</p>';
			return false;
		}*/
		
	}
	
	public function sendmail($to, $subject = "", $body = "", $mailtype = 'HTML', $cc = "", $bcc = "", $additional_headers = "")
	{
		$header = null;
		$mail_from = $this->get_address($this->strip_comment($this->smtpFrom));
		$body = preg_replace("/(^|(\r\n))(\\.)/", "\\1.\\3", $body);
		//$header .= "MIME-Version:1.0\r\n";
		$header .= "Content-Transfer-Encoding:binary\r\n";
		if($mailtype=="HTML")
		{
			$header .= "Content-Type:text/html;charset=utf-8\r\n";
		}
		$header .= "To: ".$to."\r\n";
		if ($cc != "")
		{
			$header .= "Cc: ".$cc."\r\n";
		}
		$header .= "From: =?utf-8?B?".base64_encode($this->appname)."?=<".$this->smtpFrom.">\r\n";
		$header .= "Subject: ".$subject."\r\n";
		$header .= $additional_headers;
		$header .= "Date: ".date("r")."\r\n";
		$header .= "X-Mailer:By Redhat (PHP/".phpversion().")\r\n";
		list($msec, $sec) = explode(" ", microtime());
		$header .= "Message-ID: <".date("YmdHis", $sec).".".($msec*1000000).".".$mail_from.">\r\n";
		$TO = explode(",", $this->strip_comment($to));
		 
		if ($cc != "") {
			$TO = array_merge($TO, explode(",", $this->strip_comment($cc)));
		}
		if ($bcc != "") {
			$TO = array_merge($TO, explode(",", $this->strip_comment($bcc)));
		}
		 
		$sent = TRUE;
		foreach ($TO as $rcpt_to) {
			$rcpt_to = $this->get_address($rcpt_to);
			if (!$this->smtp_sockopen($rcpt_to)) {
				$this->log_write("Error: Cannot send email to ".$rcpt_to."\n");
				$sent = FALSE;
				continue;
			}
			if ($this->smtp_send($this->host_name, $mail_from, $rcpt_to, $header, $body)) {
				$this->log_write("E-mail has been sent to <".$rcpt_to.">\n");
			} else {
				$this->log_write("Error: Cannot send email to <".$rcpt_to.">\n");
				$sent = FALSE;
			}
			fclose($this->sock);
			$this->log_write("Disconnected from remote host\n");
		}
		//echo "<br>";
		//echo $header;
		return $sent;
	}
	 
	/* Private Functions */
	 
	public function smtp_send($helo, $from, $to, $header, $body = "")
	{
		if (!$this->smtp_putcmd("HELO", $helo)) {
			return $this->smtp_error("sending HELO command");
		}
		#auth
		if($this->smtpauth){
			if (!$this->smtp_putcmd("AUTH LOGIN", base64_encode($this->smtpAccount))) {
				return $this->smtp_error("sending HELO command");
			}
			 
			if (!$this->smtp_putcmd("", base64_encode($this->smtpPassword))) {
				return $this->smtp_error("sending HELO command");
			}
		}
		if (!$this->smtp_putcmd("MAIL", "FROM:<".$from.">")) {
			return $this->smtp_error("sending MAIL FROM command");
		}
		 
		if (!$this->smtp_putcmd("RCPT", "TO:<".$to.">")) {
			return $this->smtp_error("sending RCPT TO command");
		}
		 
		if (!$this->smtp_putcmd("DATA")) {
			return $this->smtp_error("sending DATA command");
		}
		 
		if (!$this->smtp_message($header, $body)) {
			return $this->smtp_error("sending message");
		}
		 
		if (!$this->smtp_eom()) {
			return $this->smtp_error("sending <CR><LF>.<CR><LF> [EOM]");
		}
	 
		if (!$this->smtp_putcmd("QUIT")) {
			return $this->smtp_error("sending QUIT command");
		}
		 
		return TRUE;
	}
	 
	public function smtp_sockopen($address)
	{
		if ($this->smtpServer == "") {
			return $this->smtp_sockopen_mx($address);
		} else {
			return $this->smtp_sockopen_relay();
		}
	}

	 
	public function smtp_sockopen_relay()
	{
		$this->log_write("Trying to ".$this->smtpServer.":".$this->smtpPort."\n");
		$this->sock = @fsockopen($this->smtpServer, $this->smtpPort, $errno, $errstr, $this->time_out);
		if (!($this->sock && $this->smtp_ok())) {
			$this->log_write("Error: Cannot connenct to relay host ".$this->smtpServer."\n");
			$this->log_write("Error: ".$errstr." (".$errno.")\n");
			return FALSE;
		}
		$this->log_write("Connected to relay host ".$this->smtpServer."\n");
		return TRUE;;
	}

	 
	public function smtp_sockopen_mx($address)
	{
		$domain = preg_replace("/^.+@([^@]+)$/", "\\1", $address);
		if (!@getmxrr($domain, $MXHOSTS)) {
			$this->log_write("Error: Cannot resolve MX \"".$domain."\"\n");
			return FALSE;
		}
		foreach ($MXHOSTS as $host) {
			$this->log_write("Trying to ".$host.":".$this->smtpPort."\n");
			$this->sock = @fsockopen($host, $this->smtpPort, $errno, $errstr, $this->time_out);
			if (!($this->sock && $this->smtp_ok())) {
				$this->log_write("Warning: Cannot connect to mx host ".$host."\n");
				$this->log_write("Error: ".$errstr." (".$errno.")\n");
				continue;
			}
			$this->log_write("Connected to mx host ".$host."\n");
			return TRUE;
		}
		$this->log_write("Error: Cannot connect to any mx hosts (".implode(", ", $MXHOSTS).")\n");
		return FALSE;
	}
	 
	public function smtp_message($header, $body)
	{
		fputs($this->sock, $header."\r\n".$body);
		$this->smtp_debug("> ".str_replace("\r\n", "\n"."> ", $header."\n> ".$body."\n> "));
		 
		return TRUE;
	}
	 
	public function smtp_eom()
	{
		fputs($this->sock, "\r\n.\r\n");
		$this->smtp_debug(". [EOM]\n");
		 
		return true;//$this->smtp_ok();
	}
	 
	function smtp_ok()
	{
		$response = str_replace("\r\n", "", fgets($this->sock, 512));
		$this->smtp_debug($response."\n");
		 
		if (!preg_match("/^[23]/", $response,$match)) {
			fputs($this->sock, "QUIT\r\n");
			fgets($this->sock, 512);
			$this->log_write("Error: Remote host returned \"".$response."\"\n");
			return FALSE;
		}
		return TRUE;
	}
	 
	public function smtp_putcmd($cmd, $arg = "")
	{
		if ($arg != "") {
			if($cmd=="") $cmd = $arg;
			else $cmd = $cmd." ".$arg;
		}

		 
		fputs($this->sock, $cmd."\r\n");
		$this->smtp_debug("> ".$cmd."\n");
		 
		return $this->smtp_ok();
	}
	 
	public function smtp_error($string)
	{
		$this->log_write("Error: Error occurred while ".$string.".\n");
		return FALSE;
	}
	 
	public function log_write($message)
	{
		$this->smtp_debug($message);
		 
		if ($this->log_file == "") {
			return TRUE;
		}
		 
		$message = date("M d H:i:s ").get_current_user()."[".getmypid()."]: ".$message;
		if (!@file_exists($this->log_file) || !($fp = @fopen($this->log_file, "a"))) {
			$this->smtp_debug("Warning: Cannot open log file \"".$this->log_file."\"\n");
			return FALSE;
		}
		flock($fp, LOCK_EX);
		fputs($fp, $message);
		fclose($fp);
		 
		return TRUE;
	}
	 
	public function strip_comment($address)
	{
		$comment = "/\\([^()]*\\)/";
		while (preg_match($comment, $address,$match)) {
			$address = preg_replace($comment, "", $address);
		}
		 
		return $address;
	}
	 
	public function get_address($address)
	{
		$address = preg_replace("/([ \t\r\n])+/", "", $address);
		$address = preg_replace("/^.*<(.+)>.*$/", "\\1", $address);
		 
		return $address;
	}
	 
	public function smtp_debug($message)
	{
		if ($this->debug) {
			echo $message."<br>";
		}
	}
	 
	public function get_attach_type($image_tag) { //
	 
		$filedata = array();
		 
		$img_file_con=fopen($image_tag,"r");
		unset($image_data);
		while ($tem_buffer=AddSlashes(fread($img_file_con,filesize($image_tag))))
			$image_data.=$tem_buffer;
		fclose($img_file_con);
		 
		$filedata['context'] = $image_data;
		$filedata['filename']= basename($image_tag);
		$extension=substr($image_tag,strrpos($image_tag,"."),strlen($image_tag)-strrpos($image_tag,"."));
		switch($extension){
			case ".gif":
			$filedata['type'] = "image/gif";
			break;
			case ".gz":
			$filedata['type'] = "application/x-gzip";
			break;
			case ".htm":
			$filedata['type'] = "text/html";
			break;
			case ".html":
			$filedata['type'] = "text/html";
			break;
			case ".jpg":
			$filedata['type'] = "image/jpeg";
			break;
			case ".tar":
			$filedata['type'] = "application/x-tar";
			break;
			case ".txt":
			$filedata['type'] = "text/plain";
			break;
			case ".zip":
			$filedata['type'] = "application/zip";
			break;
			default:
			$filedata['type'] = "application/octet-stream";
			break;
		}
		 
		 
		return $filedata;
	}
 }
?>