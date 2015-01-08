<?php
/**
 * 文件上传类
 */
class hkp_upload
{
	private $conf = array(
		'field'=>'field',		// 上传文件表单字段
		'type'=>array('jpg'),	// 允许的文件类型
		'size'=>2048,			// 文件最大尺寸 单位kb
		'rename'=>1, 		// 是否重命名
		'path'=>'./', 			// 文件存储路径
		);
	private $files = array();
	private $extend = '';
	public function __construct($conf=array())
	{
		if(count($this->files = input::get_files())==0)
		{
			trigger_error("无文件上传");
			exit();
		}
		foreach($conf as $k=>$v)
		{
			$this->$k=$v;
		}
	}
	public function do_upload($field='')
	{
		// 初始化上传文件的内容
		$field = empty($field)?$this->conf['field']:$field;
		if (!array_key_exists($field, $this->files)) $this->error("上传文件表单字段不存在");
		$this->files = $this->files[$field];
		foreach($this->files as $file)
		{
			if($file['error']) $this->error("上传的文件有问题");
			$this->extend = strtolower(pathinfo($file['name'],PATHINFO_EXTENSION));
			$this->check_size($file['size']);
			$this->check_type($file['type']);
			if($this->conf['rename']) $this->rename($file['name']);
			if(!move_uploaded_file($file['tmp_name'], realpath($this->conf['path']).DIRECTORY_SEPARATOR.$file['name']))
			{
				$this->error("文件$file[name]上传失败!");
			}
		}
		// 上传	
	}
	protected function error($msg='')
	{
		trigger_error($msg);
	}
	protected function check_size($size=0)
	{
		if($size>1024*$this->conf['size']) $this->error("文件尺寸不能大于".$this->conf['size']);
	}
	private function check_type($head='')
	{
		if(array_key_exists($this->extend, http::$mimes)&&stripos(http::$mimes[$this->extend], $head)!==false)
		{
			if($this->extend!=''&&in_array($this->extend, $this->conf['type']))
			{
				$this->extend = '.'.$this->extend;
				return true;
			}
		}
		$this->error("文件格式不支持");
	}
	protected function rename(&$name='')
	{
		$name = sha1(time().mt_rand(111111,999999)).$this->extend;
	}
}
?>