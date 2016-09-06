<?php
class TWFDiary extends TDBForm
{ //工作日志管理
	private $Edit3;
	private $Edit1;
	private $Edit2;

	public function OnCreate(){
		parent::OnCreate();
		$this->Caption = '工作微博(日志)';
		global $Session;
		$this->TableName = 'WF_Diary';
		$this->Fields['CorpCode_'] = array('Caption' => '公司',
			'view' => false, 'modify' => 'ReadOnly', 'append' => 'ReadOnly',
			'Value' => $Session->CorpCode);
		$this->Fields['TBDate_'] = array('Caption' => '工作日期','align'=>'center',
			'Value' => date('Y-m-d'));
		$this->Fields['AppDate_'] = array('Caption' => '建档日期','align'=>'center',
			'view' => true, 'modify' => 'ReadOnly', 'append' => 'ReadOnly',
			'Value' => date('Y-m-d h:m'));
		$this->Fields['Contents_'] = array('Caption' => '工作内容',
			'Hint' => '<br/>最多输入内容255个汉字，否则会保存失败！',
			'Control' => 'TMemo','OnGetText'=>'GetContent');
        $this->Fields['Review_'] = array('Caption' => '评论结果',
            'view' => true, 'append' => false, 'modify' => false, 'width' => 100);
		$this->Fields['UpdateUser_'] = array('Caption' => '更新人员',
			'view' => false, 'modify' => 'ReadOnly', 'append' => false);
		$this->Fields['UpdateDate_'] = array('Caption' => '更新日期',
			'view' => false, 'modify' => 'ReadOnly', 'append' => false);
		$this->Fields['AppUser_'] = array('Caption' => '建档人员',
			'view' => false, 'modify' => 'ReadOnly', 'append' => 'ReadOnly',
			'Value' => $Session->UserCode);
		$this->Fields['UpdateKey_'] = array('Caption' => '更新标识',
			'view' => false, 'modify' => false, 'append' => false);
		$this->Fields['OP'] = array('Caption' => '操作','align'=>'center', 'isData' => false,
			'OnGetText' => 'OP_GetText', 'width' => 65);
		$this->AddMenu('工作微博');
		$this->AddMenu(array('?m=TWFDiary&a=OnAppend', '写新的微博'));
		$this->AddMenu(array('?m=TWFDiary', '查看我的微博'));
		//
		$ds = new TDataSet();
		$ds->Open("select distinct DeptName_ from WF_UserInfo "
			. "where CorpCode_='$Session->CorpCode' and Enabled_=1 "
			. "order by DeptName_");
		while($ds->Next()){
			$this->AddMenu(array($this->GetUrl('ViewUsers','Dept='.$ds->DeptName_,'TWFDiary'),
				'[' . $ds->DeptName_ . ']在忙什么'));
		}
		//$this->AddMenu(array($this->GetUrl(0, 'op=sendmb'),  '发送到手机上'));
        $this->AddMenu(array('?m=TWFDiary&a=Gather',  '统计报表'));
		$this->AddMenu(array($this->GetUrl('AddressBook'),  '同事通讯录'));
		$this->AddMenu('帮助文档');
		$this->AddMenu(array($this->GetUrl('Helpme', 'id=100006'), '功能说明'));
	}

	public function OP_GetText($DataSet, $FieldCode, $FieldInfo){
		global $Session;
		$uid = $DataSet->UpdateKey_;
		$ids = explode(',',$uid);
		if($DataSet->AppUser_ === $Session->UserCode){
			foreach($ids as $id){
				$url[] = BuildUrl($this->GetUrl(VIEW_MODIFY, "uid=$id"), '修改'). ' '
				.BuildUrl($this->GetUrl(VIEW_DELETE, "uid=$id"), '删除');
			}
		}else{
			foreach($ids as $id){
				$url[] = BuildUrl($this->GetUrl('UserReview', "uid=$id"), '评论');
			}
		}
		return implode('<br/>',$url);
	}

	public function GetContent($DataSet, $FieldCode, $FieldInfo){
		return preg_replace('/[\r\n]+/','<br/>',$DataSet->Contents_);
	}

	public function OnDefault(){
		$this->ViewDiary();
	}

	public function ViewUsers(){ //查看所有的用户
		global $Session;
		$args = array();
		//$args[] = array('用户帐号', '用户姓名', '今天的微博', '操作');
		$ds = new TDataSet();
		$ds->CommandText = "select UserCode_,UserName_ from WF_UserInfo "
			. "where CorpCode_='$Session->CorpCode' and Enabled_=1";
		$dept = isset($_GET['Dept']) ? $_GET['Dept'] : null;
		if(($dept) and ($dept <> '')){
			$ds->CommandText .= " and DeptName_='$dept'";
		}
		$ds->Open();
		while($ds->Next()){
			$args[$ds->UserCode_] = array($ds->UserCode_, $ds->UserName_, '');
		}
		//搜索当天的微博
		$ds = null;
		//
		$today = isset($_GET['Day']) ? $_GET['Day'] : date('Y-m-d');
		echo $today.' 的微博('.BuildUrl($this->GetUrl('ViewUsers',
			'Day='.DateAdd('d', -1, $today)), '前一天').')：<hr/>';
		$ds = new TDataSet();
		$ds->CommandText = 'select AppUser_,Contents_ from WF_Diary';
		$ds->CommandText .= " where TBDate_='$today'";
		$ds->Open();
		while($ds->Next()){
			if(array_key_exists($ds->AppUser_, $args)){
				$args[$ds->AppUser_][2] .= $ds->Contents_.'<br/>';
			}
		}
		//显示
		foreach($args as $Lines){
			echo "<p>";
			$url = $this->GetUrl('ViewDiary', "user=$Lines[0]");
			$url = BuildUrl($url, $Lines[1]);
			echo "<b>$url-$Lines[0]：</b><br/>";
			echo $Lines[2] <> '' ? preg_replace("/[\r\n]+/","<br/>",$Lines[2]) : '<font color="red">(无)</font>';
			echo "</p>";
		}
	}
	
	public function ViewDiary(){ //查看一个人所有人的工作微博
		global $Session;
		$user = isset($_GET['user']) ? $_GET['user'] : $Session->UserCode;

		//打开数据集
		$DataSet = new TDataSet();
		$DataSet->CommandText = "select group_concat(Contents_ separator '<br/>') as Contents_,CorpCode_,"
			."TBDate_,AppDate_,Review_,UpdateUser_,UpdateDate_,AppUser_,group_concat(UpdateKey_) as UpdateKey_ "
			. "from $this->TableName where AppUser_='$user' group by TBDate_ order by TBDate_ DESC";
		$DataSet->Open();
		$rec = $DataSet->RecordCount();
		$this->DataSet = $DataSet;

		//显示数据集
		$grid = new TDBGrid($this);
		$grid->DataSet = $DataSet;
		$grid->Fields = $this->Fields;
		$grid->Show();
	}
	
	public function AddressBook(){ //同事通讯录
		global $Session;
		$ds = new TDataSet();
		$ds->Open("select DeptName_,UserName_,SMSNo_,Email_,QQ_ from WF_UserInfo "
			. "where CorpCode_='$Session->CorpCode' and Enabled_=1 "
			. "order by DeptName_,UserCode_");
		$grid = new TDBGrid($this);
		$grid->DataSet = $ds;
		$grid->Fields['DeptName_'] = array('Caption' => '所属组别', 'align'=>'center');
		$grid->Fields['UserName_'] = array('Caption' => '姓名', 'align'=>'center');
		$grid->Fields['SMSNo_'] = array('Caption' => '手机', 'align'=>'center');
		$grid->Fields['Email_'] = array('Caption' => '邮件');
		$grid->Fields['QQ_'] = array('Caption' => 'QQ', 'align'=>'center');
		$grid->Show();
	}
	public function UserReview(){
		$uid = $_GET['uid'];
		global $Session;
		$ds = new TDataSet();
		$ds->CommandText = "select * from $this->TableName where CorpCode_='$Session->CorpCode' "
			."and UpdateKey_='$uid'";
		$ds->Open();
		if ($ds->RecordCount() > 0){
			$ds->Next();
			$form = new TEditForm($this);
			$form->AddHidden('a', 'SaveReview');
			$form->AddHidden('uid', $uid);
			$lb = new TLabel($this);
			$lb->Window = $form;
			$lb->Caption = '请输入评论内容：';
			$lb->Show();
			$Memo = new TMemo($this);
			$Memo->Name = 'Memo';
			$Memo->Caption = '评论';
			$Memo->Window = $form;
			$form->Show();
		}
	}

    public function SaveReview(){
        global $Session;
        $uid = $_POST['uid'];
        $mem = $_POST['Memo'];
        $dr = new TPostRecord($this->TableName);
        $dr->Review_ = $mem;
        $dr->PostModify("UpdateKey_='$uid'");
        echo '评论成功!';
    }

    public function Gather(){
        global $Session;

        echo '<table bordr="0" cellpadding="0" cellspacing="1" class="data td_center">
                <tr class="tr_theme">
                    <th>部门</th><th>姓名</th><th>上周一</th><th>上周二</th>
                    <th>上周三</th><th>上周四</th><th>上周五</th><th>上周六</th>
                    <th>本周一</th><th>本周二</th><th>本周三</th><th>本周四</th>
                    <th>本周五</th><th>本周六</th>
                </tr>';
        $ts=new TDataSet();
        $g_date = date('Y-m-d');
        $w = date("w", strtotime($g_date));
        $dn = $w ? $w : 7;

        $st = date("Y-m-d", strtotime("$g_date -" . $dn . " days"));

        $date_fr = date('Y-m-d',strtotime(("$st - 6 days")));
        $date_end = date('Y-m-d',strtotime(("$st + 7 days")));
        $ts->Open("select UserCode_,DeptName_,UserName_ from WF_UserInfo where CorpCode_='$Session->CorpCode' and Enabled_='1' and DeptName_<>'' order by DeptName_, UserCode_");
        while($ts->Next()){
            $ds=new TDataSet();
            $ds->Open("select TBDate_ from WF_Diary where CorpCode_='$Session->CorpCode' and AppUser_='$ts->UserCode_' and TBDate_ between '$date_fr' and '$date_end'");
            while($ds->Next()){
                $Date[$ts->UserCode_][$ds->TBDate_]=$ds->TBDate_;
            }
            $ds=null;

            $c%2<>0?$css=' d_bg':$css='';
            echo "<tr class='tr_bg$css'>";
            echo "<td>$ts->DeptName_</td><td><a href='?m=TWFDiary&user=$ts->UserCode_'>$ts->UserName_</a></td>";
            for($i=0;$i<13;$i++){
                    if($i<>6){
                        $date=DateAdd('d', $i, $date_fr);
                        if($Date[$ts->UserCode_][$date]){
                            echo "<td><a href='?m=TWFDiary&a=ViewDay&user=$ts->UserCode_&time=$date'>Yes</a></td>";
                        }else{
                            echo "<td><span style='display:block;height:23px;border:2px solid #FFF;'></span></td>";
                        }
                    }
                }
            echo "</tr>";
            $c+=1;
        }
        echo '</table>';
    }

    public function ViewDay(){
        if($_GET['user']<>''):
            isset($_GET['time'])?$time=$_GET['time']:$time=date('Y-m-d');
            $ts=new TDataSet();
            $ts->Open("select D.Contents_,U.UserName_ from WF_UserInfo U inner join WF_Diary D on U.UserCode_=D.AppUser_ and TBDate_='$time' and U.UserCode_='$_GET[user]'");
	        global $Session;
            echo $Session->getUserName($_GET['user']),' ',$_GET['time'],'<br/>';
	        while($ts->Next()):
	           echo preg_replace("/[\r\n]+/","<br/>",$ts->Contents_),'<br/>';
	        endwhile;
        endif;
    }
}
/* 数据结构
CREATE TABLE IF NOT EXISTS `WF_Diary` (
  `CorpCode_` varchar(10) NOT NULL COMMENT '企业代码',
  `TBDate_` date NOT NULL,
  `Contents_` varchar(255) DEFAULT NULL,
  `UpdateUser_` varchar(30) NOT NULL,
  `UpdateDate_` datetime NOT NULL,
  `AppUser_` varchar(30) NOT NULL,
  `AppDate_` datetime NOT NULL,
  `UpdateKey_` varchar(36) NOT NULL,
  UNIQUE KEY `UpdateKey_` (`UpdateKey_`),
  KEY `CorpCode_` (`CorpCode_`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

*/
?>