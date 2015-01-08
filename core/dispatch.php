<?php
/**
 * 调度器 通过用户请求的uri分配到规定的文件
 * @author fantiq <hack_php@sina.com>
 * @version 0.0.1
 * @see http://www.hackphp.net
 * @param
 * @return void
 * @exception
 */
class dispatch
{
	private static $controller_dir='';
	private static $segments = array();
	protected static $action='index';
	protected static $method='index';
	protected static $args=array();
	public static function run()
	{
		self::sep_controller_dir(request::get_segments());
		self::parse_uri();
		self::dispatch_to();
	}
	protected static function dispatch_to()
	{
		if(self::$controller_dir!='') self::$controller_dir.=DIRECTORY_SEPARATOR;
		$path = self::$controller_dir.self::$action;
		$controller=&loader::check_controller($path,self::$method);
		// 存在_before()函数则先调用之 然后调用后面的
		if(method_exists($controller, '_before')) call_user_func_array(array($controller,'_before'), self::$args);
		call_user_func_array(array($controller,self::$method), self::$args);
		$controller=null; // 触发析构方法
	}
	protected static function parse_uri()
	{
		if(count(self::$segments)<1||self::$segments[0]=='')
		{
			self::set_default_controller();
		}
		else
		{
			self::set_action(isset(self::$segments[0])?self::$segments[0]:'index');
			self::set_method(isset(self::$segments[1])?self::$segments[1]:'index');
			self::$args = array_slice(self::$segments, 2);
		}
	}
	protected static function sep_controller_dir($segments='')
	{
		if(strpos($segments, '?')===0&&array_key_exists(config::get('tag_name','r'),$_GET))
		{
			$segments=trim($_GET[config::get('tag_name','r')],'/');
		}
		// controller path 在segments中 用/d/进行分割 path/d/segments...
		$parts = explode('/d/', $segments);
		$segments=isset($parts[1])?trim($parts[1]):$segments;
		self::$segments = explode('/', $segments);
		self::$controller_dir=isset($parts[1])?trim($parts[0]):'';
	}
	protected static function set_default_controller()
	{
		$default_controler = explode('/', config::get('router.default_controller','index/index'));
		self::set_action(isset($default_controler[0])?$default_controler[0]:'index');
		self::set_method(isset($default_controler[1])?$default_controler[1]:'index');
	}
	protected static function set_action($action=null)
	{
		self::$action = $action;
	}
	protected static function set_method($method=null)
	{
		self::$method = $method;
	}
	public static function get_action()
	{
		return self::$action;
	}
	public static function get_method()
	{
		return self::$method;
	}
}