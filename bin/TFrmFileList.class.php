<?php
if( !defined('IN') ) die('bad request');

class TFrmFileList extends TDBForm{
	
	public function OnCreate(){
		parent::OnCreate();
		$this->Caption = '云存储文件列表';
		$this->Message = '在此可以显示存档的文件：';
		Switch($this->mode){
		case VIEW_APPEND:
			$pid = $_GET['pid'];
			if(!$pid) $pid = $_POST['pid'];
			if(!$pid) $pid = 100000;
			$this->AddMenu('快速选择');
			$this->AddMenu(array("?m=helpme&id=$pid", '查看本文档'));
			break;
		case VIEW_DELETE:
			$pid = $_GET['pid'];
			if(!$pid) $pid = $_POST['pid'];
			if(!$pid) $pid = 100000;
			$this->Caption = '附件删除';
			$this->AddMenu(array($this->GetUrl(), '云存储文件列表'));
			$this->AddMenu(array("?m=helpme&id=$pid", '查看本文档'));
			break;
		}
	}
	
	public function OnDefault(){
		if(OnSAE()){
			echo 'filelist:<br/>';
			$num = 0;
			echo '<table>';
			$ss = new SaeStorage();
			while ( $ret = $ss->getList("files", "*", 100, $num )){
				foreach($ret as $file){
					$url = $ss->getUrl('files', $file);
					echo '<tr>';
					echo "<td><a href=\"$url\">$file</a></td>";
					echo "<td><a href=\"".$_SERVER['PHP_SELF']."?mode=delete&file=$file\">删除</a></td>";
					echo '</tr>';
					$num ++;
				}
			}
			echo '</table>';
			echo "\nTOTAL: {$num} files\n";
			echo '<br/>';
			echo "<a href=\"uploadfile.php\">上传</a>";
		}else{
			echo '本功能只能在SAE平台上运行！';
		}
	}
	
	public function OnAppend()
	{ //增加文件
		$pid = $_GET['pid'];
		if(!$pid) $pid = $_POST['pid'];
		if(!$pid) $pid = 100000;
		$it = DBRead("select count(*) from HM_Files where RecordID_=$pid", 0) + 1;
		$indexflag = $it == 1 ? 1 : 0;
		$form = new TEditForm($this);
		$form->AddHidden('mode', 'append');
		//
		$id = new TEdit($this);
		$id->Caption = '归属编号';
		$id->Name = 'pid';
		$id->Hint = '请勿随意修改';
        $id->Text = $pid;
        $id->ReadOnly = true;
		$id->Window = $form;
		//
		$userfile = new TUploadFile($this);
		$userfile->Name = 'userfile';
		$userfile->Caption = '文件选择';
		$userfile->Window = $form;
		//
		$remark = new TEdit($this);
		$remark->Name = 'remark';
		$remark->Caption = '备注';
		$remark->Window = $form;
		//
		$indexfile = new TEdit($this);
		$indexfile->Name = 'indexfile';
		$indexfile->Caption = '主要文件';
		$indexfile->Text = $indexflag;
		$indexfile->Hint = '0:非主要文件；1.主要文件';
		$indexfile->Window = $form;
		//
		global $Session;
		$appuser = new TEdit($this);
		$appuser->Caption = '建档人员';
		$appuser->Name = 'appuser';
		$appuser->Text = $Session->UserCode;
		$appuser->Window = $form;
		$form->Show();
		/*
		$form->AddItem('归属编号：', 'pid', $pid, '请勿随意修改');
		$form->AddItem('文件选择：', 'userfile', '', '', 'file');
		$form->AddItem('备注：', 'remark', '');
		$form->AddItem('主要文件：', 'indexfile', "$indexflag", '0:非主要文件；1.主要文件');
		$form->AddItem('建档人员：', 'appuser', 'cerc2477');
		*/
	}
	
	public function OnSaveAppend()
	{ //保存文件提交
		if($_FILES['userfile']["name"])
		{
			$pid = $_POST['pid'];
			$tmpfile = $_FILES['userfile']["tmp_name"];
			$filename = $_FILES['userfile']["name"];
			$remark = $_POST['remark'];
			$appuser = $_POST['appuser'];
			$indexfile = $_POST['indexfile'];
			$Caption = "已增加 $filename ，继续增加说明文件";
			//增加文件记录表
			if(!DBExists("select * from HM_Files where RecordID_=$pid and FileName_='$filename'"))
			{
				$sql = "insert into HM_Files (RecordID_,FileName_,Remark_,AppUser_,AppDate_,UpdateKey_) "
					. "values ('$pid', '$filename', '$remark', '$appuser', NOW(), UUID())";
				if(!mysql_query($sql)){
					echo $sql . '<br/>';
					echo mysql_error() . '<br/>';
				}
				//更新到文档记录主表
				if($indexfile == 1)
				{
					$sql = "update HM_Record set IndexFile_='$filename' where ID_=$pid";
					if(!mysql_query($sql)){
						echo $sql . '<br/>';
						echo mysql_error() . '<br/>';
					}
				}
						//存储文件
						$upload = new sae_upload();//参数为空或者不传参数的话将按原始文件名保存
						$upload->domain="files";//域
						$upload->path = $pid;//上传目录[不存在会自己创建]
						$upload->type="png|jpg|gif|txt|xml|htm|html|xsd|doc|ppt|xls";//允许上传的文件类型[注意没有点及用竖线分割]
						$upload->name="userfile";//文件表单名称
						$rs = $upload->upload();//不管成功与否都会返回一个数组
						
						if($rs['success'] == 1)
								$Caption =  "上传 $filename 成功！";
						else
								$Caption =  "上传 $filename 失败！！";
			}
			else{
				$Caption = '已增加文件' . $_FILES['userfile']["name"];
			}
		}
		else{
			$Caption = '提交失败！';
		}
		echo $Caption;
	}
	
	public function OnDelete(){
		$ss = new SaeStorage();
		$fn = $_GET["file"];
		if($fn){
			if($ss->fileExists("files", $fn)){
				$id = substr($fn, 0, stripos($fn, '/'));
				$filename = substr($fn, stripos($fn, '/') + 1);
				$sql = "delete from HM_Files where FileName_='$filename' and RecordID_=$id";
				if(!mysql_query($sql, $this->conn)){
					echo "<p>文件删除失败！</p>\n";
					echo $sql . '<br/>';
					echo mysql_error() . '<br/>';
				}
				else{
					echo "<p>已删除文件记录： $fn </p>\n";
					$ss->delete('files', $fn);
					echo "<p>已删除磁盘文件： $fn </p>\n";
				}
			}
			else{
				echo "<p>磁盘文件 $fn 无法找到，删除失败。</p>\n";
			}
		}
		else{
			echo "<p>文件删除失败！</p>\n";
		}
	}
}
?>