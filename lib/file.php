<?php
class file
{
	public function __construct()
	{
		if(!defined('is_win'))
		{
			define('is_win',stripos(PHP_OS, 'win')===false?false:true);
		}
	}
	/**
	 * 创建文件夹
	 * @param  string  $path 路径
	 * @param  integer $mode 模式
	 * @return bool          创建是否成功
	 */
	public function create_dir($path='',$mode=0)
	{
		if(empty($path)||file_exists($path))
		{
			return false;
		}
		$mode=$mode==0?755:$mode;
		if(is_win)
		{
			if(!mkdir($path)||!chmod($path, $mode)) return false;
		}
		else
		{
			if(!mkdir($path, $mode)) return false;
		}
		return true;
	}
	public function create_file($filename='',$data='')
	{
		$path = dirname($filename);
		if(!file_exists($path)||!is_dir($filename))
		{
			$this->create_dir($path);
		}
		$data = string($data);
		return empty($data)?touch($filename):file_put_contents($filename, $data);
	}
	/**
	 * 删除指定目录下面的所有文件以及文件夹
	 * @param  string $path 目录
	 * @return void
	 */
	public function delete_dir($path='')
	{
		if(!file_exists($path)||!is_dir($path)) return false;
		$this->dir_iterator($path);
	}
	/**
	 * 简单的目录删除方法
	 * 删除指定目录下面的文件
	 * @param  string $path 路径
	 * @return void
	 */
	public function del_dir($path='')
	{
		if(!file_exists($path)||!is_dir($path)) return false;
		foreach(scandir($path) as $file)
		{
			if(is_file($file))
			{
				unlink($path.DIRECTORY_SEPARATOR.$file);
			}
		}
	}
	/**
	 * 读取指定目录下面的所有文件
	 * @param  string $path 目录
	 * @return array        文件以及目录列表
	 */
	public function read_dir($path='')
	{
		if(!file_exists($path)||!is_dir($path)) return false;
		$data = array();
		return $this->dir_iterator($path,false,$data);
	}
	/**
	 * 目录迭代器
	 * @param  string  $path   目录
	 * @param  boolean $is_del 是否要删除文件
	 * @param  array   $data   搜集文件的数组
	 * @return [type]          [description]
	 */
	private function dir_iterator($path='',$is_del=true,&$data=array())
	{
		$dh = opendir($path);
		while(($file = readdir($dh))!==false)
		{
			if($file=='.'||$file=='..') continue;
			$filename = $path.DIRECTORY_SEPARATOR.$file;
			if(is_dir($filename))
			{
				if($is_del)
				{
					$this->dir_iterator($filename);
				}
				else
				{
					$this->dir_iterator($filename,$is_del,$data[$filename]);
				}
			}
			else
			{
				if($is_del)
				{
					unlink($filename);
				}
				else
				{
					$data[$path][] = $file;
					// $data[] = $filename;
				}
			}
		}
		closedir($dh);
		if($is_del)
		{
			rmdir($path); //删除目录
		}
		else
		{
			return $data;
		}
	}
	/**
	 * is_writeable 以前在win上有bug，现在win7上面 ok
	 */
}