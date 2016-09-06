<?php
if( !defined('IN') ) die('bad request');

class sae_upload
{
	public $domain="files";//域
	public $path= "picture";//上传目录
	public $type="png|jpg|gif";//文件类型
	public $name="userfile";//表单名称
	public $save_name;//保存文件名
	
	public function __construct($save_name="")
	{
		$this->save_name=$save_name;
	}
	public function upload(){
		$result = array();//返回的数据
		$basename = basename($_FILES[$this->name]["name"]);//原始文件名
		$extension = pathinfo($basename, PATHINFO_EXTENSION);//扩展名
		$data = explode("|",trim(strtolower($this->type)));//允许的上传类型转为数组
		if(in_array($extension, $data) || $this->type === '*')
		{
			$upload_path = SAE_TMP_PATH.$this->path;
			move_uploaded_file($_FILES[$this->name]["tmp_name"],$upload_path);
			$content = file_get_contents($upload_path);
			$temp = new SaeStorage();
			if($this->save_name == "")
			{
				$filename = $this->path."/".$basename;
			}//原名称保存
			else
			{
				$filename = $this->path."/".$this->save_name.".".$extension;
			}//按传入的名称保存
			$temp->write($this->domain,$filename,$content);//写入文件
			$url=$temp->getUrl($this->domain,$filename);//获取地址
			$property=$temp->getAttr($this->domain,$filename);//获取文件属性
			$result["url"]=$url;
			$result["property"]=$property;
			$result["success"]="1";
		}
		else
		{
			$result["success"]="0";
            $result["error"] = '不支持的文件类型';
		}
		return $result;
	}
}

function OnSAE(){
	return SAE_MYSQL_HOST_M <> '127.0.0.1';
}
?>