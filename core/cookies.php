<?php
class cookies
{
	private static $expire=0;
	private static $path='/';
	private static $domain=host;
	public static function set($name=null,$value=null)
	{
		if(is_array($name))
		{
			foreach($name as $key=>$val)
			{
				setcookie($key,$val,self::$expire,self::$path,self::$domain);
			}
		}
		else
		{
			setcookie($name,$value,self::$expire,self::$path,self::$domain);
		}
	}
	/**
	 * 获取cookie数据
	 * @param  string $key 键
	 * @return mixed
	 */
	public static function get($key=null)
	{
		if(is_null($key))
		{
			return $_COOKIE;
		}
		elseif(array_key_exists($key, $_COOKIE))
		{
			return $_COOKIE[$key];
		}
		else
		{
			return null;
		}
		
	}
	/**
	 * 删除cookie文件
	 * @return void
	 */
	public static function del()
	{
		if(!empty($_COOKIE))
		{
			foreach($_COOKIE as $key=>$val)
			{
				self::rm_cookie($key);
			}
		}
	}
	/**
	 * 清空指定cookie项
	 * @param  string $key cookie项
	 * @return void
	 */
	public static function rm($key='')
	{
		if(array_key_exists($key, $_COOKIE))
		{
			unset($_COOKIE[$key]);
			self::$expire=time()-3600;
			self::set($key,null);
		}
	}
	/**
	 * 设置过期时间
	 * @param integer $val 过期时间戳
	 */
	public static function set_expire($val=0)
	{
		self::$expire=is_numeric($val)?$val:0;
	}
	/**
	 * 设置cookie可访问路径
	 * @param string $val 路径
	 */
	public static function set_path($val='')
	{
		self::$path=$val;
	}
	/**
	 * 设置cookie域
	 * @param [type] $val 域
	 */
	public static function set_domain($val=null)
	{
		self::$domain=$val;
	}
	public static function show_cookie()
	{
		echo "<pre>";
		@print_r($_COOKIE);
		echo "</pre>";
	}
}