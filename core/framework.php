<?php
/**
 * 这个是框架的解析执行流程
 * @author fantiq <hack_php@sina.com>
 * @version 0.0.1
 * @see http://www.hackphp.net
 * @param
 * @return void
 * @exception
 */
class framework
{
	public static function run()
	{
		self::init_framework();
		request::run();
		dispatch::run();
		response::run();
	}
	private static function init_framework()
	{
		timer::stop();
		self::check_env();
		// 基本设置
		config::load();// 加载配置文件
		timer::set_timezone();
		// 设置错误 异常
		ini_set('display_errors', config::get('base.display_errors',false)&&is_debug ? E_ALL | E_STRICT : E_ALL & ~E_NOTICE);
		set_error_handler(array(__CLASS__,'error_handler'));
		set_exception_handler(array(__CLASS__,'exception_handler'));
		register_shutdown_function(array(__CLASS__,'shutdown')); // 脚本结束调用函数
		// 设置缓存
		/*ob_start();
		ob_implicit_flush(false);*/
	}
	private static function check_env()
	{
		// 版本检测
		if(version_compare(phpversion(), '5.3.0')<0) exit('php版本不能低于5.3.0');
		define('is_win',stripos(PHP_OS, 'win')!==false?true:false); // 判断服务器系统是否是win
		if(!defined('is_debug')) define('is_debug', false);
		if(!defined('app_dir'))  define('app_dir', dirname(__FILE__));
		define('VERSION', '0.0.1'); //版本
		define('HOST', array_key_exists('HTTP_HOST', $_SERVER)?$_SERVER['HTTP_HOST']:'cli');// 服务器主机
		data::set('sys_loaded_classes',get_declared_classes());
	}
	public static function error_handler($errno,$errmsg,$errfile,$errline,$context)
	{
		$error = new error($errmsg,500,$errno,$errfile,$errline);
		$error->set_contents('自定义错误');
		throw $error;
	}
	public static function exception_handler(Exception $e)
	{	
		// 最终捕获的异常/错误
		//是否写入日志
		// ob_end_clean();
		self::show_except($e);
	}
	private static function show_except(Exception $e)
	{
		// echo $e->getCode().'<br>';
		// echo $e->getMessage().'<br>';
		// exit();
		include core_dir.DIRECTORY_SEPARATOR.'error'.DIRECTORY_SEPARATOR.$e->getCode().'.tpl';
		controller::set_stop(true);
	}
	// 脚本执行结束
	public static function shutdown()
	{
		if(data::get('session_update')) session::update();// session更新
		if(is_debug) echo debug::run();
	}
}
/**
 * 时间处理类
 */
class timer
{
	private static $micro_stamp=0.00;
	public static function stop()
	{
		self::$micro_stamp = microtime(true);
	}
	public static function get_stamp()
	{
		return (int)self::$micro_stamp;
	}
	public static function get_microstamp()
	{
		return self::$micro_stamp;
	}
	public static function get($formate='')
	{
		return date($formate,(int)self::$micro_stamp);
	}
	public static function get_date()
	{
		return date('Y-m-d',(int)self::$micro_stamp);
	}
	public static function get_time()
	{
		return date('H:i:s',(int)self::$micro_stamp);
	}
	public static function get_datetime()
	{
		return date('Y-m-d H:i:s',(int)self::$micro_stamp);
	}
	public static function get_gmt()
	{
		return gmdate('l, d F Y H:i:s').' GMT';
	}
	public static function set_timezone($timezone='')
	{
		empty($timezone)?date_default_timezone_set(config::get('base.timezone','PRC')):date_default_timezone_set($timezoness);
	}
	public static function get_timezone()
	{
		return date_default_timezone_get();
	}
}
/**
 * config 提供对配置文件的读取修改操作
 * @author fantiq <hack_php@sina.com>
 * @version 0.0.1
 * @see http://www.hackphp.net
 * @param
 * @return void
 * @exception
 */
class config extends m_data
{
	public static function load()
	{
		$configs = include(config_dir.DIRECTORY_SEPARATOR.'config.php');
		self::init_data($configs,'configs');
	}
}
/**
 * 加载系统文件以及用户的文件
 * 需要自动加载的文件
 * @author fantiq <hack_php@sina.com>
 * @version 0.0.1
 * @see http://www.hackphp.net
 * @param
 * @return void
 * @exception
 */
class loader
{
	private static $_files  =array();
	private static $_classes=array();
	public static function load_file($path='')
	{
		if(array_key_exists($path, self::$_files))
		{
			return false;
		}
		elseif(file_exists($path))
		{
			include $path;
			return self::$_files[$path]=true;
		}
		trigger_error('要加载的文件<b>'.$path.'</b>不存在!',E_USER_ERROR);
	}
	/**
	 * 加载一个controller类 并且会检测方法是否在这个类中存在
	 * @param  string $path   文件路径
	 * @param  string $method 方法
	 * @return object
	 */
	public static function &check_controller($path='',$method='')
	{
		$info = self::preprocess($path);
		$class_name = $info['filename'];
		$path=controller_dir.DIRECTORY_SEPARATOR.$info['filepath'];
		if(!class_exists($class_name)) self::load_file($path);
		if(method_exists($class_name, $method))
		{
			self::load_class($class_name,$path);
			if(is_subclass_of(self::$_classes[$path], 'controller')) return self::$_classes[$path];
			else trigger_error('未继承controller',E_USER_ERROR);
		}
		else
		{
			throw new dev_except('在类<b>{'.$class_name.'}</b>中不存在方法<b>{'.$method.'}</b>', 404);
		}
	}
	public static function &load_controller($path='',$args=array())
	{
		$info = self::preprocess($path);
		$path = controller_dir.DIRECTORY_SEPARATOR.$info['filepath'];
		return self::load_class($info['filename'],$path,$args);
	}
	public static function &load_model($path='',$args=array())
	{
		$info = self::preprocess($path);
		$path = model_dir.DIRECTORY_SEPARATOR.$info['filepath'];
		return self::load_class($info['filename'],$path,$args);
	}
	// 加载核心库文件
	public static function &load_library($path='',$args=array())
	{
		$info = self::preprocess($path);
		$path = lib_dir.DIRECTORY_SEPARATOR.$info['filepath'];
		return self::load_class($info['filename'],$path,$args);
	}
	// 加载第三方库class文件
	public static function &load_thrid($path='',$args=array())
	{
		$info = self::preprocess($path);
		$path = thrid_party_dir.DIRECTORY_SEPARATOR.$info['filepath'];
		return self::load_class($info['filename'],$path,$args);
	}
	/**
	 * 增加class实例化对象到静态变量中
	 * 这个类主要是为了实现单例对象
	 * @param  string $class_name 类名
	 * @param  string $path       类文件
	 * @return
	 */
	public static function &load_class($class_name='',$path='',$args=null)
	{
		if(!class_exists($class_name)) self::load_file($path);
		array_key_exists($path, self::$_classes)||self::$_classes[$path] = empty($args)?new $class_name():new $class_name($args);
		return self::$_classes[$path];
	}
	private static function preprocess($path='')
	{
		$info = pathinfo($path);
		array_key_exists('extension', $info)||$path.='.php';
		$info['filepath']=trim($path,'\/');
		return $info;
	}
}
class error extends ErrorException
{
	public function set_contents($contents='')
	{
		$this->contents = $contents;
	}
	public function get_contents()
	{
		return $this->contents;
	}
	protected $contents;
}
/**
 * 异常处理类
 */
class dev_except extends RuntimeException{}
class db_except extends RuntimeException{}