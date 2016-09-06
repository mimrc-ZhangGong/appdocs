<?php
//其它配置文件
//require('users.config.php');
//require('mainmenu.config.php');

//以下变量为框架所必须，可修改但不允许删除
{
	//定义应用基本讯息
	global $appid, $appname, $copyright, $website;
	$appid = 'appdocs';
	$appname = '云文档服务中心';
	$copyright = '深圳市华软资讯科技有限公司';
	$website = 'www.mimrc.com';

	//定义系统管理员邮件地址
	global $admin_email; 
	$admin_email = '2477@mimrc.com';

	//数据库配置
	define('SAE_MYSQL_HOST_M', '127.0.0.1');
	define('SAE_MYSQL_PORT', '3306');
	define('SAE_MYSQL_DB', 'app_docs');
	define('SAE_MYSQL_USER', 'mimrcAdmin');
	define('SAE_MYSQL_PASS', 'Lw4733006');
	
	global $APP_DB; 
	$APP_DB['TYPE'] = 'MYSQL';
	$APP_DB['HOST'] = SAE_MYSQL_HOST_M;
	$APP_DB['PORT'] = SAE_MYSQL_PORT;
	$APP_DB['DB'] = SAE_MYSQL_DB;
	$APP_DB['USER'] = SAE_MYSQL_USER;
	$APP_DB['PASS'] = SAE_MYSQL_PASS;

	//定义用户管理 class，此 class 必须从 TCustomUsers 继承
	global $users_class;
	$users_class = 'TWFUsers';
	
	//定义主菜单
	global $mainmenu_class;
	$mainmenu_class = 'mainmenu';

	//定义PV统计工具代码，将显示于首页下方
	global $PV_TOTAL;
	$PV_TOTAL = "<script src=\"http://s13.cnzz.com/stat.php?id=3789556&web_id=3789556\" language=\"JavaScript\"></script>";
}

//可在此定义app专用变量

//主菜单类
class mainmenu
{
	public function getItems(){
		global $Session;
		//定义主菜单
		$mainmenu = array(
			array('section'=>'文档中心', 'menucode'=>'helpme', 'menuname' => '二级菜单', 'default'=>true),
			array('section'=>'文档中心', 'menucode'=>'helpme', 'menuname' => '系统简介'),
			array('section'=>'文档中心', 'menucode'=>'helpme&id=100004', 'menuname' => '合作计划'),
			array('section'=>'后台管理', 'menucode'=>'records', 'menuname' => '二级菜单', 'default'=>true),
			array('section'=>'后台管理', 'menucode'=>'records', 'menuname' => '后台管理'),
			array('section'=>'后台管理', 'menucode'=>'records&mydocs', 'menuname' => '我的文档'),
			array('section'=>'后台管理', 'menucode'=>'records&today', 'menuname' => '今日增加')
		);
		Switch($Session->UserLevel){
		case 0: //系统管理员
			$mainmenu[] = array('section'=>'用户列表', 'menucode'=>'userlist', 'menuname' => '用户列表', 'default'=>true);
			break;
		case 1: //企业管理员
			$mainmenu[] = array('section'=>'用户列表', 'menucode'=>'userview', 'menuname' => '用户列表', 'default'=>true);
			break;
		}
		$mainmenu[] = array('section'=>'我的设置', 'menucode'=>'myset', 'menuname' => '我的设置', 'default'=>true);

		return $mainmenu;
	}
}
?>