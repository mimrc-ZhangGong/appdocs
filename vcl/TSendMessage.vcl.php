<?php
if( !defined('IN') ) die('bad request');

class TSendMessage{

	public $Users;
	public $Subject;
	public $Body;
	public $TargetID;
	public $Messages = array();
	public $ViewClass = 'viewflow';
	
	public function Execute()
	{
		foreach($this->Users as $User){
			$this->OutputMessage( '通知用户：' . $User );
			$DataSet = new TDataSet();
			$DataSet->CommandText = "select * from WF_UserInfo where UserCode_='$User' and Enabled_=1";
			$DataSet->Open();
			if($DataSet->Next()){
				$smsno = trim($DataSet->SMSNo_);
				if((strlen($smsno) > 0) and $DataSet->SMSUse_){
					$url = $this->getEasyUrl($User);
					if($url <> ''){
						$size = 70 - utf8_strlen($url) - 1;
						$this->OutputMessage(utf8_substr($this->Subject, 0, $size));
						$sms_msg = utf8_substr($this->Subject, 0, $size) . ' ' . $url;
					}
					else{
						$size = 70;
						$sms_msg = utf8_substr($this->Subject, 0, $size);
					}
					$MsgID = $this->AddHistory($User, $sms_msg, 0, 1);
					try{
						$Error = $this->SendSMS($smsno, $sms_msg);
						if($Error <> '') $this->OutputMessage($Error);
						$this->UpdateHistory($MsgID, $Error);
					}catch(Exception $e){
						$Error = $e->getMessage();
						$this->OutputMessage($Error);
						$this->UpdateHistory($MsgID, $Error);
					}
				}
				$email = $DataSet->Email_;
				if(strpos($email, '@') and $DataSet->EmailUse_){
					$url = $this->getEasyUrl($User);
					$MsgID = $this->AddHistory($User, $this->Subject, 1, 1);
					try{
						$Error = $this->SendMail($email, $url);
						if($Error <> '') $this->OutputMessage($Error);
						$this->UpdateHistory($MsgID, $Error);
					}catch(Exception $e){
						$Error = $e->getMessage();
						$this->OutputMessage($Error);
						$this->UpdateHistory($MsgID, $Error);
					}
				}
			}else{
				$this->OutputMessage("用户 $User 找不到或未启用，无法通知！");
			}
		}
	}
	
	public function SendSMS($smsno, $sms_msg){
		$Error = '';
		$this->OutputMessage( '发送简讯：' . $smsno );
		$this->OutputMessage( '简讯内容：' . $sms_msg );
		$sms = new TSendSMS();
		if($sms->Send($smsno, $sms_msg)){
			$this->OutputMessage("简讯发送成功！");
		}
		else{
			$Error = $sms->Message;
			$this->OutputMessage($Error);
		}
		$sms = null;
		return $Error;
	}
	
	public function SendMail($email, $url = ''){
		if(OnSAE()){
			$Error = '';
			$this->OutputMessage( '发送邮件：' . $email );
			$mail = new TSendMail();
			if($mail->Send($email, $this->Subject, $this->Body . "\n" . $url)){
				$this->OutputMessage('邮件发送成功！');
			}else{
				$Error = $mail->Message;
			}
			$mail = null;
			return $Error;
		}else{
			return '非SAE环境，执行失败！';
		}
	}
	
	public function AddHistory($User, $Value, $Type, $Status = 0){
		$ID = NewGuid();
		$Rec = new TPostRecord('WF_Messages');
		$Rec->ID_       = $ID;
		$Rec->Type_     = $Type;
		$Rec->TalkTime_ = 'NOW()';
		$Rec->TalkUser_ = $User;
		$Rec->Subject_  = utf8_substr($Value, 0, 80);
		$Rec->SendOK_   = $Status;
		if($Status === 1)
			$Rec->SendTime_ = 'NOW()';
		$Rec->PostAppend();
		return $ID;
	}
	
	public function UpdateHistory($MsgID, $Error){
		$Rec = new TPostRecord('WF_Messages');
		$Rec->SendOK_ = $Error === '' ? 2 : 3;
		$Rec->SendTime_ = 'NOW()';
		$Rec->SendError_ = $Error;
		$Rec->PostModify("ID_='$MsgID'");
	}

    public function SendMsg($fm, $to, $content, $type){
        global $Session;
        $rec = new TPostRecord('WF_Message');
        $rec->ID_ = NewGuid();
        $rec->CorpCode_ = $Session->CorpCode;
        $rec->SendFm_ = $fm;
        $rec->SendTo_ = $to;
        $rec->Content_ = $content;
        $rec->Type_ = $type;
        $rec->SendTime_ = 'now()';
        $rec->PostAppend();
        $result = $rec->ID_;
        $rec = null;
        return $result;
    }
	
	private function getEasyUrl($User){
		if($this->TargetID){
			return GetSinaUrl("http://knowall.sinaapp.com/"
				."?m=$this->ViewClass&id=$this->TargetID"
				. "&pwd=" . md5($User . $this->TargetID))
				. '&width=240';
		}else{
			return '';
		}
	}
	
	private function OutputMessage($Message){
		$this->Messages[] = $Message;
	}
}
?>