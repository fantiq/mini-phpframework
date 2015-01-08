<?php
abstract class controller
{
	private $reqType = '';
	private $tpl = null;
	protected $output='';
	protected static $stop_parse=false;
	public function __construct()
	{
		if(($this->reqType = strtolower(request::method())) == 'get')
		{
			$this->tpl = new template();
		}
	}
	public function assign($key='',$val='')
	{
		$this->tpl->assign($key,$val);
	}
	public function set_skin($skin='')
	{
		$this->tpl->skin($skin);
	}
	/**
	 * 控制控制器的析构函数的执行
	 * @param boolean $bool 开关值
	 */
	public static function set_stop($bool=true)
	{
		self::$stop_parse=$bool;
	}
	public function __destruct()
	{
		if(!self::$stop_parse) $this->parse_request();
	}
	private function parse_request()
	{
		if(!is_string($this->output)) trigger_error('控制器指定page错误');
		$this->output = str_replace(' ', '', $this->output);
		// 根据请求形式进行解析
		if($this->reqType=='get')
		{
			if($this->output==''||preg_match('/^[\\/]+$/i', $this->output)) $this->output = dispatch::get_action().DIRECTORY_SEPARATOR.dispatch::get_method();
			$this->tpl->show($this->output);
		}
		elseif($this->reqType=='ajax')
		{
			is_array($this->output)?response::add_contents(json_encode($this->output)):response::add_contents($this->output);
		}
		else
		{
			return null;
		}
	}
	/**
	 * 用于controller层开发中对数据的调试
	 * @param  [type]  $var   要调试的变量
	 * @param  string  $tag   debug的tag标签 方便识别
	 * @param  boolean $is_bp 是否开启断点
	 * @return
	 */
	protected function debug($var=null,$tag='',$is_bp=false)
	{
		$tag = empty($tag)?'':'<b>------'.$tag.':------</b>';
		$text=$tag.'<pre>';
		ob_start();
		var_dump($var);
		$text.=ob_get_clean().'</pre>';
		$this->text($text,$is_bp);
	}
	/**
	 * 显示纯文本
	 * @param  string  $str   要显示的内容
	 * @param  boolean $is_bp 是否开启断点
	 * @return 控制器中输出内容
	 */
	protected function text($str='',$is_bp=false)
	{
		response::add_contents($str);
		self::$stop_parse=true;
		if($is_bp)
		{
			response::run();
			exit();
		}
	}
	protected function verify($function='',$var='',$args=array())
	{
		return verify::main($function,$var,$args);
	}
	/*	
	$opts=array(
		'field1'=>array(
			'func1'=>array(), // args array
			'func2'=>array(),
			...
			),
		'field2'=>array(
			'func1'=>array(), // args array
			'func2'=>array(),
			...
			),
		...
		);
		*/
	protected function verify_opts($opts=array(),$method='')
	{
		$result=array();
		foreach ($opts as $field => $function)
		{
			if(is_array($function))
			{
				foreach ($function as $func => $args)
				{
					if(($result[$field][$func] = verify::main($func,input::input_data($method,$field),$args))===FALSE)
					{
						return $result[$field][$func];
						//exit();
					}
				}
			}
			else
			{
				if(($result[$field][$function] = verify::main($function,input::input_data($method,$field),$args))===FALSE)
				{
					return false;
				}
			}
		}
		return $result;
	}
}class cookies
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
}class data
{
	protected static $datas=array();
	protected static $option='globals';
	/**
	 * 初始化数据
	 * 填充数据到datas
	 * @param  array  $data [description]
	 * @return [type]       [description]
	 */
	public static function init_data(array &$data,$opt=null)
	{
		if(!is_null($opt)) self::set_option($opt);
		self::$datas[self::$option]=self::has(self::$option,self::$datas)?array_merge(self::$datas[self::$option],$data):$data;
	}
	public static function set_option($opt='')
	{
		self::$option = $opt;
	}
	public static function get($key=null)
	{
		if(is_null($key)) return self::$datas[self::$option];
		if(self::has($key)) return self::$datas[self::$option][$key];
		return null;
	}
	public static function set($key=null,$val=null)
	{
		if(is_null($key)) return null;
		if(is_array($key))self::$datas[self::$option] = array_merge(self::$datas[self::$option],$key);
		elseif(strpos($key, '.')===false) self::$datas[self::$option][$key]=$val;
		else return false;
	}
	public static function del($key=null)
	{
		if(is_null($key)) return null;
		if(is_array($key)) foreach($key as $v) if(self::has($v)) unset(self::$datas[self::$option][$v]);
		elseif(self::has($key)) unset(self::$datas[self::$option][$key]);
		else return false;
	}
	public static function has($key=null,$arr=null)
	{
		return is_string($key)||is_integer($key)?array_key_exists($key, is_null($arr)?self::$datas[self::$option]:$arr):false;
	}
}
class m_data extends data
{
	protected static $return = false;
	/**
	 * 获取值
	 * @param  [type] $key 数组键
	 * @return 
	 */
	public static function get($key=null)
	{
		return is_null(self::$return = parent::get($key))?self::iterator($key):self::$return;
	}
	/**
	 * 设置值
	 * @param [type] $key 数组键
	 * @param [type] $val 设置的值
	 */
	public static function set($key=null,$val=null)
	{
		if(parent::set($key,$val)===false)
		{
			$v = &self::iterator($key);
			$v=$val;
			unset($v);
		}
	}
	/**
	 * 删除
	 * @param [type] $key 数组键
	 * @return
	 */
	public static function del($key=null)
	{
		if(parent::del($key)===false)
		{
			self::$return = true;
			return self::iterator($key);
		}
	}
	/**
	 * 数组迭代器
	 * key1.key2.key3.....
	 * 	$array=array(
	 * 		'key1'=>array(
	 * 			'key2'=>array(
	 * 				'key3'=>'val'
	 * 		  	)
	 * 	   	)
	 *   )
	 * @param  string $key 迭代路径
	 * @param  string $arr 要迭代的数组
	 * @return 返回结果
	 */
	protected static function &iterator($key='')
	{
		$data = &self::$datas[self::$option]; // 指针初始化
		$keys = explode('.', $key);
		for($i=0,$len=count($keys);$i<$len;$i++)
		{
			if(!array_key_exists($keys[$i], $data)) return self::$return;
			if($i==$len-1){
				if(self::$return){ // 迭代删除
					self::$return=false;
					unset($data[$keys[$i]]);
					return self::$return;
				}else return $data[$keys[$i]];
			}
			if(is_array($data[$keys[$i]])) $data = &$data[$keys[$i]];
			else return self::$return;
		}
	}
}
/**
 * 测试类
 * 收集整个框架的
 * 加载的文件、存在的class、执行的sql
 * 运行的时间
 * 占用的内存
 */
class debug
{
	private static $debug = array(
		'include_files'=>array(),
		'sql_string'=>array(),
		'loaded_classes'=>array(),
		'runtime'=>'',
		'used_memory'=>''
		);
	private static function init()
	{
		self::$debug['include_files'] = get_included_files();
		self::$debug['loaded_classes'] = array_diff(get_declared_classes(), data::get('sys_loaded_classes'));
		$run_sec = microtime(true)-timer::get_microstamp();
		self::$debug['runtime']=round($run_sec*1000,3).' ms&nbsp;&nbsp;&nbsp;&nbsp;'.round($run_sec,6).' s';
		self::$debug['used_memory']=(memory_get_usage(true)/1024).' kb';
	}
	public static function add_sql_string($sql='')
	{
		if(!empty($sql)) self::$debug['sql_string'][]=$sql;
	}
	public static function run()
	{
		self::init();
		return self::text();
	}
	private static function text()
	{
		$text = "<div style='border:2px dashed #AAA;background-color:#FFA;display:block;padding:10px;'>";
		// 包含的文件
		$text.= "<span style='font-size:14px;font-weight:800;display:block;'>加载的文件</span><ul>";
		foreach(self::$debug['include_files'] as $k=>$v) $text.="<li>".$v."</li>";
		$text.="</ul>";
		// 加载的class
		$text.= "<span style='font-size:14px;font-weight:800;display:block;'>加载的class</span><ul>";
		foreach(self::$debug['loaded_classes'] as $k=>$v) $text.="<li>".$v."</li>";
		$text.="</ul>";
		// 执行的SQL
		$text.= "<span style='font-size:14px;font-weight:800;display:block;'>执行的SQL</span><ul>";
		foreach(self::$debug['sql_string'] as $k=>$v) $text.="<li>".$v."</li>";
		$text.="</ul>";
		// 执行时间
		$text.= "<span style='font-size:14px;font-weight:800;display:inline-block;'>执行时间：</span><b>".self::$debug['runtime']."</b><br>";
		// 占用的内存
		$text.= "<span style='font-size:14px;font-weight:800;display:inline-block;'>占用内存：</span><b>".self::$debug['used_memory']."</b>";
		$text.="</div>";
		return $text;
	}
	/**
	 * 断点调试
	 * 这个断点调试不同与controler提供的调试函数
	 * controller中的调试函数会将整个框架走完
	 * 而这个函数这是直接输出 后面的流程不再进行
	 * @param  [type] $var 调试的变量
	 * @return
	 */
	public static function break_point($var=null)
	{
		// 关闭错误
		error_reporting(0);
		// 输出变量
		ob_end_clean();
		ob_start();
		echo '<pre>';var_dump($var);echo '</pre>';
		ob_end_flush();
		flush();
		exit(); // 断点
	}
}
/**
 * 日志类
 */
class log
{
	public static function write($filename='err.txt',$msg='',$tag='')
	{
		$str='['.timer::get_datetime().']['.$tag.']:'.$msg.PHP_EOL;
		$info = pathinfo($filename);
		$info['extension'] = array_key_exists('extension', $info)&&!empty($info['extension'])?$info['extension']:'log';
		$filename = $info['filename'].'-'.date('Ymd').'.'.$info['extension'];
		$log_dir = config::get('site.log_dir')==''?log_dir:config::get('site.log_dir');
		$log_filepath = $log_dir.DIRECTORY_SEPARATOR.$filename;
		file_put_contents($log_filepath, $str,FILE_APPEND|LOCK_EX);
	}
}
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
}/**
 *过滤用户输入数据
 *
 */
class filter
{
	/**
	 * 对用户输入的数据进行过滤检测
	 * @param  [type]  $arr           要处理的数组
	 * @param  boolean $filter_txt 是否进行xss过滤
	 * @return void
	 */
	public static function input_filter(&$arr,$filter_txt=false)
	{
		foreach($arr as $key=>$val)
		{
			if(is_array($arr[$key]))
			{
				if(preg_match('/^[0-9a-z\-\_\.]+$/i', $key))
				{
					filter::input_filter($arr[$key],$filter_txt);
				}
				else
				{
					unset($arr[$key]);
				}
			}
			else
			{
				if($filter_txt)
				{
					$arr[$key]=xss::clean_text($arr[$key]);
				}
				elseif(preg_match('/^[0-9a-z\-\_\.]+$/i', $val)===0)
				{
					$arr[$key]=null;
				}
			}
		}
	}
	/**
	 * 对文件名称进行过滤
	 * @param  string $filename 文件名
	 * @return string           处理过后的文件名
	 */
	public static function filter_filename($filename='')
	{
		// ><)(&$?;=
		$black_list = array("/","./","../","<!--","-->","<",">","'",'"','&','$','#','{','}','[',']','=',';','?',"%20","%22","%3c","%253c","%3e","%0e","%28","%29","%2528","%26","%24","%3f","%3b","%3d"	);
		$filename = filter::remove_invisible($filename);
		return stripslashes(str_replace($black_list, '', $filename));
	}
	/**
	 * 去除非打印字符
	 * ASCII 0-31 & 127
	 * 9 10 13 分别是 制表 回车 换行 予以保留
	 * @param  string $str 要处理的字符
	 * @return string      处理后的字符
	 */
	public static function remove_invisible($str='')
	{
		$pattern = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S'; // S 加快分析速度
		$count = 0;
		do
		{
			$str = preg_replace($pattern, '', $str, -1, $count);
		}
		while($count>0);
		return $str;
	}
}
/**
 * 对用户的请求进行回应
 * @author fantiq <hack_php@sina.com>
 * @version 0.0.1
 * @see http://www.hackphp.net
 * @param
 * @return void
 * @exception
 */

/**
 * xss过滤
 */
class xss
{
	private static $txt;
	public static function clean_text($txt='')
	{
		// -------------------preprocess--------------------------
		// 过滤不可显示的字符
		self::$txt = filter::remove_invisible($txt);
		// 解析URL
		self::$txt = rawurldecode(self::$txt);
		// 解码HTML实体
		self::decode_html_entity();
		// 过滤不可显示的字符
		self::$txt = filter::remove_invisible(self::$txt);
		// 处理关键字中间空格或者制表符的绕过情况
		foreach(array('javascript', 'expression', 'vbscript', 'script', 'base64','applet', 'alert', 'document', 'write', 'cookie', 'window') as $word)
		{
			//  正则表达式中 \s 代表任意的空白符，包括空格，制表符(Tab),换行符
			$pattern='\s*';
			for($i=0,$len=strlen($word);$i<$len;$i++)
			{
				$pattern.=$word[$i].'\s*';
			}
			self::$txt = preg_replace_callback('/'.$pattern.'/i', array(__CLASS__,'remove_keyword_sapce'), self::$txt);
		}
		// -------------------filter----------------------
		self::never_allowed();
		if(preg_match('/<\s*a[^>]*?(>|$)/i', self::$txt))
		{
			self::$txt = preg_replace_callback('/<\s*a([^>]*?)(>|$)/i', array(__CLASS__,'remove_link_attr'), self::$txt);
		}
		if(preg_match('/<\s*img[^>]*?(>|$)/i', self::$txt))
		{
			self::$txt = preg_replace_callback('/<\s*img([^>]*?)\/*(>|$)/i', array(__CLASS__,'remove_img_attr'), self::$txt);
		}
		if(preg_match('/<\s*script[^>]*?[>|$]/i', self::$txt) || preg_match('/<\s*xss[^>]*?[>|$]/i', self::$txt))
		{
			self::$txt = preg_replace('/<(\/*)(script|xss)(.*?)>/si', '[removed]', self::$txt);
		}
		self::remove_attr();
		// 处理特殊标签
		self::$txt = str_replace(array('<?','?>','<!','-->'), array('&lt;?','?&gt;','&lt;!','--&gt;'), self::$txt);
		// 处理HTML
		$black_list = 'alert|applet|audio|basefont|base|behavior|bgsound|blink|body|embed|expression|form|frameset|frame|head|html|ilayer|iframe|input|isindex|layer|link|meta|object|plaintext|style|script|textarea|title|video|xml|xss';
		self::$txt = preg_replace_callback('#<(/*\s*)('.$black_list.')([^><]*)([><]*)#is', array(__CLASS__, 'remove_eval_html'), self::$txt);
		// 处理PHP
		return preg_replace('#(alert|cmd|passthru|eval|exec|expression|system|fopen|fsockopen|file|file_get_contents|readfile|unlink)(\s*)\((.*?)\)#si', "\\1\\2&#40;\\3&#41;", self::$txt);
	}
	/**
	 * html_entity_decode php自带的解析html标签的函数不能解析实体没分号的情况（&#98 ）
	 * 但是大多的浏览器是可以解析这样的字符串的
	 * 为了防止浏览器解析XSS代码，也为了后面对字符串的安全检测过滤处理，这里对ASCII范围可显示的字符做修正操作
	 * 用户可以通过 &#47; 这种形式代替 引号来绕过检测
	 *
	 * 这没有将所有可能的不带分号结尾实体全部修复转化，只是转换了会引起XSS的字符
	 */
	public static function decode_html_entity()
	{
		self::$txt = html_entity_decode(self::$txt);// 解码HTML实体
		self::$txt = str_replace(array('&quot','&amp','&lt','&gt','&nbsp'), array('"','&','<','>',' '), self::$txt);
		self::$txt = preg_replace('/\&\#0*(3[2-9]|[4-9][0-9]|1[0-1][0-9]|12[0-6])/e', 'chr(\\1)', self::$txt);
		self::$txt = preg_replace('/\&\#x([2-6][0-9a-f]|7[0-9a-e])/ei', 'chr(hexdec("\\1"))', self::$txt);
	}
	private static function remove_keyword_sapce($matches=array())
	{
		return preg_replace('/\s/i', '', $matches[0]);
	}
	private static function remove_link_attr($matches=array())
	{
		// $matches[0] 要替换的内容
		// $matches[1] 属性
		$matches[1] = str_replace(array('<','>','/*','*/'), '', $matches[1]);
		return str_replace($matches[1], 
			preg_replace('/\s*href\s*\=\s*([\042|\047]*)\s*(alert\(|javascript\:|vbscript\:|livescript\:|mocha\:|charset\=|window\.|document\.|\.cookie|<script|<xss|data\s*:).*?\\1($|\;|\s)(.*)/si', " href='###' $4", $matches[1])
			, $matches[0]);
	}
	private static function remove_img_attr($matches=array())
	{
		// $matches[0] 要替换的内容
		// $matches[1] 属性
		$matches[1] = str_replace(array('<','>','/*','*/'), '', $matches[1]);
		return str_replace($matches[1],
			preg_replace('/\s*src\s*\=\s*([\042|\047]*)\s*(alert\(|javascript\:|vbscript\:|livescript\:|mocha\:|charset\=|window\.|document\.|\.cookie|<script|<xss|data\s*:).*?\\1($|\;|\s)(.*)/si', " src='###' $4", $matches[1])
			, $matches[0]);
	}
	private static function remove_attr()
	{
		$attr = array();
		$black_list = array('on\w*', 'xmlns', 'style', 'formaction');
		preg_match_all('/('.implode('|', $black_list).')\s*\=\s*(\042|\047)([^\\2]*?)(\\2)/i', self::$txt, $matches);
		foreach($matches as $match)
		{
			if(count($match)>0) $attr[] = preg_quote($match[0],'/');
		}
		preg_match_all('/('.implode('|', $black_list).')\s*\=\s*([^\s>]*)/i', self::$txt, $matches);
		foreach($matches as $match)
		{
			if(count($match)>0) $attr[] = preg_quote($match[0],'/');
		}
		if(count($attr)>0)
		{
			self::$txt = preg_replace('/(<?)(\/?[^><]+?)([^A-Za-z<>\-])(.*?)('.implode('|', $attr).')(.*?)([\s><]?)([><]*)/i', '$1$2 $4$6$7$8', self::$txt);
		}
	}
	private static function remove_eval_html($matches=array())
	{
		return '&lt;'.$matches[1].$matches[2].$matches[3].str_replace(array('<','>'), array('&lt;','&gt;'), $matches[4]);
	}
	private static function never_allowed()
	{
		self::$txt = preg_replace('/(javascript\s*:|vbscript\s*:|expression\s*\(|Redirect\s+302)/i', '[removed]', self::$txt);
		self::$txt = preg_replace("/([\"'])?data\s*:[^\\1]*?base64[^\\1]*?,[^\\1]*?\\1?/i", '[removed]', self::$txt);
	}
}/**
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
class db_except extends RuntimeException{}/**
 * 对字符串进行xss过滤
 * 一般情况下是不需要用这个过滤函数的
 * 框架会自动将需要过滤的字符串进行过滤
 */
if(!function_exists('xss_clean'))
{
	function xss_clean($str='')
	{
		return xss::clean_text($str);
	}
}
/**
 * 请求url处理
 */
if(!function_exists('base_url'))
{
	function base_url()
	{
		return request::base_url();
	}
}
if(!function_exists('uri_string'))
{
	function uri_string()
	{
		return request::uri_string();
	}
}
if(!function_exists('get_host'))
{
	function get_host()
	{
		return request::host();
	}
}
if(!function_exists('client_ip'))
{
	function client_ip()
	{
		return request::ip();
	}
}
if(!function_exists('is_ie'))
{
	function is_ie()
	{
		return stripos(request::browser(), 'ie')===false?false:true;
	}
}
if(!function_exists('build_url'))
{
	function build_url($str='')
	{
		$str = empty($str)?'':DIRECTORY_SEPARATOR.trim($str,'\/');
		return get_host().$str;
	}
}
if(!function_exists('redirect'))
{
	function redirect($url='')
	{
		if(preg_match('/^\/?([a-zA-Z0-9\_\-]+\/)?[a-zA-Z0-9\_\-]+\/?$/', $url))
		{
			$url=build_url($url);
		}
		elseif(verify::main('url',$url))
		{
			if(stripos($url, 'http://')===false) $url = 'http://'.$url;
		}
		else
		{
			return false;
		}
		header("Location:".$url);
		exit();
	}
}
/*
	controller中经常用到的函数
 */
if(!function_exists('model'))
{
	function model($filename)
	{
		return loader::load_model($filename);
	}
}
if(!function_exists('controller'))
{
	function controller($filename)
	{
		return loader::load_controller($filename);
	}
}
/**
 * 方便获取客户端的输入数据 get post cookie
 */
if(!function_exists('get'))
{
	function get($key=null)
	{
		return input::input_data('get',$key);
	}
}
if(!function_exists('post'))
{
	function post($key=null)
	{
		return input::input_data('post',$key);
	}
}
if(!function_exists('cookie'))
{
	function cookie($key=null)
	{
		return input::input_data('cookie',$key);
	}
}
/**
 * session操作
 */
if(!function_exists('session_init'))
{
	function session_init($user_id=0)
	{
		return session::start($user_id);
	}
}
if(!function_exists('get_session'))
{
	function get_session($key=null)
	{
		return session::start()->get($key);
	}
}
if(!function_exists('set_session'))
{
	function set_session($key=null,$val=null)
	{
		return session::start()->set($key,$val);
	}
}
if(!function_exists('del_session'))
{
	function del_session($key=null)
	{
		return session::start()->del($key);
	}
}
if(!function_exists('session_delete'))
{
	function session_delete()
	{
		return session::destroy();
	}
}
if(!function_exists('is_online'))
{
	function is_online($user_id)
	{
		return session::is_online($user_id);
	}
}
if(!function_exists('num_online'))
{
	function num_online()
	{
		return session::num_online();
	}
}
// 配置函数
if(!function_exists('config_get'))
{
	function config_get($key=null,$default=null)
	{
		return config::get($key,$default);
	}
}
if(!function_exists('config_set'))
{
	function config_set($key=null,$val=null)
	{
		return config::set($key,$val);
	}
}
//日志函数
if(!function_exists('log_write'))
{
	function log_write($filename='',$msg='',$tag='')
	{
		log::write($filename,$msg,$tag);
	}
}
/**
 * 断点调试
 */
if(!function_exists('debug'))
{
	function debug($var=null)
	{
		return debug::break_point($var);
	}
}
/**
 * 过滤函数
 */
/*if(!function_exists(''))
{
	function (){}
}*/

function p($d)
{
	echo "<pre>";
	print_r($d);
	echo "</pre>";
}/**
 * http
 * @author fantiq <hack_php@sina.com>
 * @version 0.0.1
 * @see http://www.hackphp.net
 * @param
 * @return void
 * @exception
 */
class http
{
	public static function init()
	{
		// 请求协议
		self::$request['proto'] = isset($_SERVER['HTTPS'])&&$_SERVER['SERVER_PORT']==='443'?'https':'http';
		// 请求域名
		self::$request['host']=$_SERVER['HTTP_HOST'];
		// 请求字符串
		self::$request['uri']=$_SERVER['REQUEST_URI'];
		// 脚本名称
		self::$request['script']=$_SERVER['SCRIPT_NAME'];
		// 端口
		self::$request['port']=$_SERVER['SERVER_PORT'];
		// 请求方法 命令行的请求不存在 request method 字段
		self::$request['method']=array_key_exists('REQUEST_METHOD',$_SERVER)?strtolower($_SERVER['REQUEST_METHOD']):'CGI';
		// UserAgent
		self::$request['agent']=$_SERVER['HTTP_USER_AGENT'];
		// ajax request
		self::$request['is_ajax']=isset($_SERVER['HTTP_X_REQUESTED_WITH'])&&strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])==='xmlhttprequest';
		// 用户ip
		self::$request['ip']=self::client_ip();
		unset($_SERVER);
	}
	// 用户IP
	private static function client_ip()
	{
		/*存在代理ip 若最后一个ip与remote_addr匹配 请求通过代理过来 取代理中的第一个
											不匹配可能是伪造的x-forwarded-for头信息
		不存在代理ip就是 REMOTE_ADDR*/

		if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
		{
			foreach(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']) as $val)
			{
				if(strtolower($val) !== 'unkonwn')
				{
					$ip = $val;
					break;
				}
			}
		}
		else
		{
			$ip = isset($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:'0.0.0.0';
		}
		$v_flag = strpos(':', $ip)===false?FILTER_FLAG_IPV4:FILTER_FLAG_IPV6;
		return filter_var($ip,FILTER_VALIDATE_IP,array('flags'=>$v_flag))?$ip:'0.0.0.0';
	}
	protected static $request = array(
		'proto'=>'',
		'host'=>'',
		'uri'=>'',
		'script'=>'',
		'port'=>'',
		'method'=>'',
		'agent'=>'',
		'is_ajax'=>'',
		'ip'=>'',
		);
	// 暂时修改为public 便于调试
	public static $response = array(
		'status'=>'HTTP/1.1 200 OK',
		'headers'=>array(),
		'content_type'=>'text/html;charset=utf-8;',
		'contents'=>'',
		);
	public static $status=array(
		200	=> 'OK',
		201	=> 'Created',
		202	=> 'Accepted',
		203	=> 'Non-Authoritative Information',
		204	=> 'No Content',
		205	=> 'Reset Content',
		206	=> 'Partial Content',

		300	=> 'Multiple Choices',
		301	=> 'Moved Permanently',
		302	=> 'Found',
		304	=> 'Not Modified',
		305	=> 'Use Proxy',
		307	=> 'Temporary Redirect',

		400	=> 'Bad Request',
		401	=> 'Unauthorized',
		403	=> 'Forbidden',
		404	=> 'Not Found',
		405	=> 'Method Not Allowed',
		406	=> 'Not Acceptable',
		407	=> 'Proxy Authentication Required',
		408	=> 'Request Timeout',
		409	=> 'Conflict',
		410	=> 'Gone',
		411	=> 'Length Required',
		412	=> 'Precondition Failed',
		413	=> 'Request Entity Too Large',
		414	=> 'Request-URI Too Long',
		415	=> 'Unsupported Media Type',
		416	=> 'Requested Range Not Satisfiable',
		417	=> 'Expectation Failed',

		500	=> 'Internal Server Error',
		501	=> 'Not Implemented',
		502	=> 'Bad Gateway',
		503	=> 'Service Unavailable',
		504	=> 'Gateway Timeout',
		505	=> 'HTTP Version Not Supported'
	);
	public static $mimes = array(
		'hqx'	=>	'application/mac-binhex40',
		'cpt'	=>	'application/mac-compactpro',
		'csv'	=>	'text/x-comma-separated-values,text/comma-separated-values,application/octet-stream,application/vnd.ms-excel,application/x-csv,text/x-csv,text/csv,application/csv,application/excel,application/vnd.msexcel',
		'bin'	=>	'application/macbinary',
		'dms'	=>	'application/octet-stream',
		'lha'	=>	'application/octet-stream',
		'lzh'	=>	'application/octet-stream',
		'exe'	=>	'application/octet-stream,application/x-msdownload',
		'class'	=>	'application/octet-stream',
		'psd'	=>	'application/x-photoshop',
		'so'	=>	'application/octet-stream',
		'sea'	=>	'application/octet-stream',
		'dll'	=>	'application/octet-stream',
		'oda'	=>	'application/oda',
		'pdf'	=>	'application/pdf,application/x-download',
		'ai'	=>	'application/postscript',
		'eps'	=>	'application/postscript',
		'ps'	=>	'application/postscript',
		'smi'	=>	'application/smil',
		'smil'	=>	'application/smil',
		'mif'	=>	'application/vnd.mif',
		'xls'	=>	'application/excel,application/vnd.ms-excel,application/msexcel',
		'ppt'	=>	'application/powerpoint,application/vnd.ms-powerpoint',
		'wbxml'	=>	'application/wbxml',
		'wmlc'	=>	'application/wmlc',
		'dcr'	=>	'application/x-director',
		'dir'	=>	'application/x-director',
		'dxr'	=>	'application/x-director',
		'dvi'	=>	'application/x-dvi',
		'gtar'	=>	'application/x-gtar',
		'gz'	=>	'application/x-gzip',
		'php'	=>	'application/x-httpd-php',
		'php4'	=>	'application/x-httpd-php',
		'php3'	=>	'application/x-httpd-php',
		'phtml'	=>	'application/x-httpd-php',
		'phps'	=>	'application/x-httpd-php-source',
		'js'	=>	'application/x-javascript',
		'swf'	=>	'application/x-shockwave-flash',
		'sit'	=>	'application/x-stuffit',
		'tar'	=>	'application/x-tar',
		'tgz'	=>	'application/x-tar,application/x-gzip-compressed',
		'xhtml'	=>	'application/xhtml+xml',
		'xht'	=>	'application/xhtml+xml',
		'zip'	=>  'application/x-zip,application/zip,application/x-zip-compressed',
		'mid'	=>	'audio/midi',
		'midi'	=>	'audio/midi',
		'mpga'	=>	'audio/mpeg',
		'mp2'	=>	'audio/mpeg',
		'mp3'	=>	'audio/mpeg,audio/mpg,audio/mpeg3,audio/mp3',
		'aif'	=>	'audio/x-aiff',
		'aiff'	=>	'audio/x-aiff',
		'aifc'	=>	'audio/x-aiff',
		'ram'	=>	'audio/x-pn-realaudio',
		'rm'	=>	'audio/x-pn-realaudio',
		'rpm'	=>	'audio/x-pn-realaudio-plugin',
		'ra'	=>	'audio/x-realaudio',
		'rv'	=>	'video/vnd.rn-realvideo',
		'wav'	=>	'audio/x-wav,audio/wave,audio/wav',
		'bmp'	=>	'image/bmp,image/x-windows-bmp',
		'gif'	=>	'image/gif',
		'jpeg'	=>	'image/jpeg,image/pjpeg',
		'jpg'	=>	'image/jpeg,image/pjpeg',
		'jpe'	=>	'image/jpeg,image/pjpeg',
		'png'	=>	'image/png',  'image/x-png',
		'tiff'	=>	'image/tiff',
		'tif'	=>	'image/tiff',
		'css'	=>	'text/css',
		'html'	=>	'text/html',
		'htm'	=>	'text/html',
		'shtml'	=>	'text/html',
		'txt'	=>	'text/plain',
		'text'	=>	'text/plain',
		'log'	=>	'text/plain,text/x-log',
		'rtx'	=>	'text/richtext',
		'rtf'	=>	'text/rtf',
		'xml'	=>	'text/xml',
		'xsl'	=>	'text/xml',
		'mpeg'	=>	'video/mpeg',
		'mpg'	=>	'video/mpeg',
		'mpe'	=>	'video/mpeg',
		'qt'	=>	'video/quicktime',
		'mov'	=>	'video/quicktime',
		'avi'	=>	'video/x-msvideo',
		'movie'	=>	'video/x-sgi-movie',
		'doc'	=>	'application/msword',
		'docx'	=>	'application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/zip',
		'xlsx'	=>	'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/zip',
		'word'	=>	'application/msword,application/octet-stream',
		'xl'	=>	'application/excel',
		'eml'	=>	'message/rfc822',
		'json' => 	'application/json,text/json'
	);
}class model
{
	protected $db=null;
	public function __construct()
	{
		$this->db_adapter();
	}
	private function db_adapter()
	{
		$conf = config::get('db.driver','pdo_mysql');
		$conf = explode('_', $conf);
		if(array_key_exists(1, $conf)) config::set('db.driver',$conf[1]);
		loader::load_file(lib_dir.DIRECTORY_SEPARATOR.'DB'.DIRECTORY_SEPARATOR.$conf[0].'.php');
		$conf[0]=$conf[0].'_engine';
		$this->db = new $conf[0];
	}
	public function __destruct()
	{
		if(is_debug) debug::add_sql_string($this->db->sql());
	}
}
/*class abstractDB
{
	abstract function where();
}
*//**
 * 
 * @author fantiq <hack_php@sina.com>
 * @version 0.0.1
 * @see http://www.hackphp.net
 * @param
 * @return void
 * @exception
 */
class request extends http
{
	public static function run()
	{
		self::init();
		input::init();
	}
	public static function get_segments()
	{
		$script = self::$request['script'];
		$uri = self::$request['uri'];
		$search = stripos($uri,$script)===false?dirname($script):$script;
		$segments = trim(str_replace($search, '', $uri),'/');
		$mapping = config::get('router.route_maps',array());
		return array_key_exists($segments, $mapping)?$mapping[$segments]:$segments;
	}
	public static function method()
	{
		if(self::$request['is_ajax']===true) return 'ajax';
		return self::$request['method'];
	}
	public static function browser()
	{
		if(stripos(self::$request['agent'], 'MSIE 6.0')!==FALSE) return 'IE6';
		if(stripos(self::$request['agent'], 'MSIE 7.0')!==FALSE) return 'IE7';
		if(stripos(self::$request['agent'], 'MSIE 8.0')!==FALSE) return 'IE8';
		if(stripos(self::$request['agent'], 'MSIE 9.0')!==FALSE) return 'IE9';

		if(stripos(self::$request['agent'], 'Firefox')!==FALSE&&stripos(self::$request['agent'], 'Navigator')!==FALSE) return 'Navigator';
		if(stripos(self::$request['agent'], 'Firefox')!==FALSE) return 'Firefox';
		if(stripos(self::$request['agent'], 'Chrome')!==FALSE&&stripos(self::$request['agent'], 'OPR')===FALSE) return 'Chrome';
		if(stripos(self::$request['agent'], 'OPR')!==FALSE||stripos(self::$request['agent'], 'Opera')!==FALSE) return 'Opera';
		if(stripos(self::$request['agent'], 'AppleWebKit')!==FALSE&&stripos(self::$request['agent'], 'Safari')!==FALSE) return 'Safari';
	}
	public static function uri_string()
	{
		return self::$request['uri'];
	}
	public static function base_url()
	{
		$path = stripos(self::$request['uri'], self::$request['script'])===false?dirname(self::$request['script']).'/':self::$request['script'];
		return self::$request['proto'].'://'.self::$request['host'].$path;
	}
	public static function host()
	{
		return self::$request['proto'].'://'.self::$request['host'];
	}
	public static function ip()
	{
		return self::$request['ip'];
	}
}
/**
 * 处理用户的请求 GET POST FILE COOKIE
 * 对用户请求的数组数据要进行 key val 的过滤 
 * @author fantiq <hack_php@sina.com>
 * @version 0.0.1
 * @see http://www.hackphp.net
 * @param
 * @return void
 * @exception
 */
class input
{
	private static $get=array();
	private static $post=array();
	private static $cookie=array();
	private static $files=array();
	public static function init()
	{
		// 初始化过滤input数据数组的 key val
		foreach(array('get','post','cookie') as $k) self::gpc_init($k);
		self::files_init();
	}
	public static function get_files()
	{
		return self::$files;
	}
	public static function gpc_init($name='')
	{
		if(!in_array($name, array('get','post','cookie'))) return null;
		$data = strtoupper(ltrim($name,'_'));
		$filter_text = $data=='GET'?FALSE:TRUE;
		$data='_'.$data;
		global $$data;
		filter::input_filter($$data,$filter_text);
		self::$$name=$$data;
		unset($$data);
	}
	private static function files_init()
	{
		if(!empty($_FILES))
		{
			foreach($_FILES as $file=>$info)
			{
				if(preg_match('/^[0-9a-z\_\-\.]+$/i', $file))
				{
					if(is_array($info['name']))
					{
						for($i=0,$len=count($info['name']);$i<$len;$i++)
						{
							self::$files[$file][$i]['name']=filter::filter_filename($info['name'][$i]);
							self::$files[$file][$i]['type']=$info['type'][$i];
							self::$files[$file][$i]['tmp_name']=$info['tmp_name'][$i];
							self::$files[$file][$i]['error']=$info['error'][$i];
							self::$files[$file][$i]['size']=$info['size'][$i];
						}
					}
					else
					{
						$info['name'] = filter::filter_filename($info['name']);
						self::$files[$file][0]=$info;
					}
				}
				else
				{
					unset($_FILES[$file]);
				}
			}
		}
		else
		{
			self::$files=array();
		}
		unset($_FILES);
	}
	// 返回用户请求数据
	public static function input_data($input='',$key=null)
	{
		$input=strtolower($input);
		if(!in_array($input, array('get','post','cookie')))
		{
			return null;
		}
		$data = input::$$input;
		if(is_null($key))
		{
			return $data;
		}
		if(stripos($key, '.')!==FALSE)
		{
			$d = &$data;
			$keys = explode('.', $key);
			foreach($keys as $k)
			{
				if(isset($d[$k])&&!is_array($d[$k]))
				{
					return $d[$k];
				}
				$d=&$d[$k];
			}
		}
		if(isset($data[$key]))
		{
			return $data[$key];
		}
		return null;
	}
}class response extends http
{
	/**
	 * 响应请求数据
	 * @return void
	 */
	public static function run()
	{
		//ob_end_clean(); // 清除用户无意的输出
		echo self::$response['contents']; //输出内容
		
		// self::send_response();
	}
	private static function send_response()
	{
		header(self::$response['status']);							// 发送状态行
		header('Content-Type: '.self::$response['content_type']);	// 发送文件类型
		foreach(self::$response['headers'] as $h)					// 头信息
		{
			header($h);
		}
		// 输出响应内容到客户端
		if(config::get('base.is_gz')&&extension_loaded('zlib')&&!ini_get('zlib.output_compression')){
			ob_start('ob_gzhandler');
		}else{
			ob_start();
		}
		echo self::$response['contents']; //输出内容
		ob_end_flush();
		flush();
	}
	// 设置响应行
	public static function set_status($code=0,$phrase='')
	{
		if(array_key_exists($code, self::$status[$code]))
		{
			self::$response['status']='HTTP/1.1 '.$code.' '.self::$status[$code];
		}
	}
	// 设置响应头信息
	public static function set_headers($key=null,$val=null)
	{
		if(!empty($key)) self::$response['headers'][]=$key.': '.$val;
	}
	// 设置响应文件内容
	public static function set_content_type($content_type='')
	{
		if(array_key_exists(strtolower($content_type), self::$mimes))
		{
			self::$response['content_type']=self::$mimes[$content_type];
		}
	}
	// 设置响应内容
	public static function set_contents($contents='')
	{
		self::$response['contents'] = $contents;
	}
	// 增加响应内容
	public static function add_contents($contents='')
	{
		self::$response['contents'].=$contents;
	}
}class session
{
	private static $session_id=0;
	private static $session_name='';
	private static $session_store=null;
	private static $session_data=null;
	private static $need_update=false;
	public static function start($user_id=0)
	{
		self::session_init();
		data::set('session_update',true);
		if($record=self::$session_store->fetch(self::$session_id)){
			return self::$session_data = new sess_data(self::$session_id,$record['user_id'],$record['expire_time'],$record['last_active'],$record['data']);
		}else{
			return self::create($user_id);
		}
	}
	private static function create($user_id=0)
	{
		// 生成session_id
		self::$session_id = self::new_sid();
		$user_id = (int)$user_id;
		$last_active = timer::get_stamp();
		$alive = config::get('session.alive_time',3600);
		$expire_time = $last_active + $alive;
		// 设置客户端cookie
		setcookie(self::$session_name,self::$session_id,$last_active+config::get('session.cookie_expire',''),config::get('session.cookie_path','/'),config::get('session.cookie_domain',HOST));
		self::$session_store->create(self::$session_id, $user_id, $expire_time, $last_active);
		return self::$session_data = new sess_data(self::$session_id, $user_id, $expire_time, $last_active);
	}
	private static function new_sid()
	{
		return md5(request::ip().timer::get_stamp().timer::get_microstamp().mt_rand(10000,99999));
	}
	public static function update()
	{
		if(!self::$need_update) return null;
		$last_active = timer::get_stamp();
		$expire_time = self::$session_data->get_expire_time()+$last_active-self::$session_data->get_last_active();
		self::$session_store->update(self::$session_id,$expire_time,$last_active,self::$session_data->get());
		self::$session_store=null;
		self::$session_data=null;
		// 清除函数 按一定的几率执行
		// self::clean();
	}
	public static function need_update($is=false)
	{
		self::$need_update=$is;
	}
	public static function clean()
	{
		return self::$session_store->clean(self::$session_id);
	}
	public static function destroy()
	{
		self::$session_store->destroy(self::$session_id);
		return setcookie(self::$session_name,'',timer::get_stamp()-3600,config::get('session.cookie_path','/'),config::get('session.cookie_domain',HOST));
	}
	public static function is_online($user_id=0)
	{
		self::$session_store->is_online($user_id);
	}
	public static function num_online()
	{
		self::$session_store->num_online();
	}
	private static function session_init()
	{
		// session引擎
		loader::load_file(lib_dir.DIRECTORY_SEPARATOR.'session'.DIRECTORY_SEPARATOR.'session_store_adapter.php');
		self::$session_store = session_store_adapter::store_adapter();
		// 关闭php系统session
		ini_set('session.use_cookies', '0');
		// 获取session_name
		self::$session_name = config::get('session.sess_name','HACKPHP_SESSIONID');
		// 获取session_id
		if(!empty($_COOKIE[self::$session_name])){
			self::$session_id = $_COOKIE[self::$session_name];
		}elseif(!empty($_GET['sid'])){
			self::$session_id=$_GET['sid'];
		}else{
			self::$session_id = false;
		}
	}
}/**
 * 模版引擎 需要controller来继承
 */
class template
{
	private $sep_l='<{';
	private $sep_r='}>';
	private $tpl_vars = array();
	private $tpl_ext='';
	private $tpl_dir=''; // 模版文件目录
	private $com_dir=''; // 编译文件目录
	private $cac_dir=''; // 缓存文件目录
	private $skin = ''; // 皮肤
	private $expire = false; //缓存 expire 分钟
	private $loop_elem = null; // 是否有循环语句需要解析
	public function __construct()
	{
		$this->skin = config::get('site.default_skin','');
		$this->ext = config::get('site.tpl_ext','html');
	}
	private function init($path='')
	{
		$this->tpl_dir = empty($path)?view_dir:trim($path);
		if(!empty($this->skin))
		{
			$this->tpl_dir.=DIRECTORY_SEPARATOR.$this->skin;
		}
		$this->com_dir=$this->tpl_dir.DIRECTORY_SEPARATOR.'compile';
		$this->cac_dir=$this->tpl_dir.DIRECTORY_SEPARATOR.'cache';
		// 创建文件夹
		foreach(array($this->tpl_dir, $this->com_dir, $this->cac_dir) as $dir)
		{
			if(!file_exists($dir))
			{
				mkdir($dir, 0755);
				chmod($dir, 0755);
			}
		}
	}
	/**
	 * 显示模版文件
	 * @param  string $path
	 * @return [type]
	 */
	public function show($path='')
	{
		$this->init();
		$tpl_file = $this->tpl_dir.DIRECTORY_SEPARATOR.trim($path).'.'.$this->ext; // 模版文件
		$com_file = $this->com_dir.DIRECTORY_SEPARATOR.md5(trim($path)); // 编译文件
		// 编译模版
		$this->compile($tpl_file, $com_file);
		ob_start();
		// 缓存
		if($this->expire>0 || $this->expire=='static')
		{
			$cac_file = $this->cac_dir.DIRECTORY_SEPARATOR.md5(trim($path));
			// 文件不存在或者过期或者编译文件修改过 生成新缓存文件
			$this->cache_file($com_file,$cac_file);
		}
		else
		{
			include $com_file;
			response::add_contents(ob_get_clean());
		}
	}
	/**
	 * 为模版文件赋值
	 * @param  string $key key
	 * @param  mixed $val  val
	 * @return void
	 */
	public function assign($key=null,$val=null)
	{
		$this->tpl_vars[$key] = $val;
	}
	/**
	 * 设置缓存文件的过期时间
	 * @param  integer $expire 缓存时间 单位 min
	 * @return void
	 */
	public function cache($expire=0)
	{
		if($expire>=0) $this->expire = $expire;
		else $this->expire = 'static'; // 静态化文件
	}
	/**
	 * 当缓存文件不存在、编译文件修改、缓存文件过期则生成缓存文件
	 * @param  string $com_file [description]
	 * @param  string $cac_file [description]
	 * @return [type]           [description]
	 */
	private function cache_file($com_file='',$cac_file='')
	{
		if($this->expire<=0)
		{
			@unlink($cac_file);
			return false;
		}
		$new_cache_file = false;
		// 缓存文件不存在 || 编译文件修改
		if(!file_exists($cac_file)||filemtime($cac_file)<filemtime($com_file))
		{
			$new_cache_file = true;
		}
		else
		{
			// 检测缓存文件是否过期
			include $cac_file;
			$out = ob_get_clean();
			if(preg_match_all('/^cache--(\d+)-->/', $out, $matches))
			{
				if($matches[1][0]<timer::get_stamp()) // 缓存文件过期重新生成
				{
					$new_cache_file = true;
				}
			}
		}
		if($new_cache_file)
		{
			$out = file_get_contents($com_file);
			if($this->expire!='static')
			{
				$out = 'cache--'.($this->expire*3600+timer::get_stamp()).'-->'.$out;
			}
			if(!file_put_contents($cac_file, $out))
			{
				trigger_error('生成缓存文件<b>{'.$cac_file.'}</b>失败!',E_USER_ERROR);
			}
		}
		$out=preg_replace('/^cache--\d+--\>/i', '', $out);
		response::add_contents($out);
		unset($out);
	}
	private function compile($tpl_file='',$com_file='')
	{
		if(!file_exists($tpl_file))
		{
			trigger_error('请求的模版文件<b>{'.$tpl_file.'}</b>不存在!',E_USER_ERROR);
		}
		// 编译文件不存在或者模版文件被修改
		if(is_debug||!file_exists($com_file) || filemtime($com_file)<filemtime($tpl_file))
		{
			$out = file_get_contents($tpl_file);
			//$out = str_replace(array("\r","\n","\t"), '', $out); // 压缩文件
			$pattern = '/'.preg_quote($this->sep_l).'(.*?)'.preg_quote($this->sep_r).'/i';
			$out = preg_replace_callback($pattern, array($this,'parse'), $out);
			if(!file_put_contents($com_file, $out))
			{
				trigger_error('生成编译文件<b>{'.$com_file.'}</b>失败!',E_USER_ERROR);
			}
			unset($out);
		}
	}
	/**
	 * 解析模版文件
	 * @param  array  $matches 匹配到的标签
	 * @return string          处理后的字符串
	 */
	private function parse($matches=array())
	{
		$matches[1]=trim($matches[1]);
		// 解析php代码
		if(stripos($matches[1], 'include')===0)
		{
			return preg_replace('/^include\s+(.+)\s*/i', "<?php include '".$this->tpl_dir.DIRECTORY_SEPARATOR."\\\\1';?>", $matches[1]);
		}
		if(preg_match('/^php.*/i', $matches[1]))
		{
			return str_replace('=', ' echo ', preg_replace('/^php(.*)/i', '<?php \\1;?>', rtrim($matches[1],'; ')));
		}
		if(preg_match('/^\/\w+$/i', $matches[1]))
		{
			return "<?php }?>";
		}
		if(stripos($matches[1], 'else')!==FALSE)
		{
			return "<?php }else{?>";
		}
		if(stripos($matches[1], 'if')!==FALSE) // if条件语句
		{
			$str = str_replace(array('neq','eq','lt','gt','if',' '), array('!=','==','<','>','',''), $matches[1]);
			if(strpos($str, "'")!==FALSE||strpos($str, "\"")!==FALSE)
			{
				if(preg_match('/(\w+)(==|!=|>|<)([\042|\047]+\w+[\042|\047]+)/i', $str))
				{
					$str = preg_replace('/(\w+)(==|!=|>|<)([\042|\047]+\w+[\042|\047]+)/i', '<?php if($this->tpl_vars[\'${1}\']${2}${3}){?>', $str);
				}
				else
				{
					$str = preg_replace('/([\042|\047]+\w+[\042|\047]+)(==|!=|>|<)(\w+)/i', '<?php if(${1}${2}$this->tpl_vars[\'${3}\']){?>', $str);
				}
			}
			else
			{
				if(preg_match('/\w+(==|!=|>|<)\d+/i', $str))
				{
					$str = preg_replace('/(\w+)(==|!=|>|<)(\d+)/i', '<?php if($this->tpl_vars[\'${1}\']${2}${3}){?>', $str);
				}
				else
				{
					$str = preg_replace('/(\w+)(==|!=|>|<)(\w+)/i', '<?php if($this->tpl_vars[\'${1}\']${2}$this->tpl_vars[\'${3}\']){?>', $str);
				}
			}
			return $str;
		}
		if(stripos($matches[1], 'foreach')!==FALSE && stripos($matches[1], '->')!==FALSE) // 循环
		{
			$str = str_replace(array('foreach',' '), '', $matches[1]);
			$this->loop_elem=substr($str, stripos($str, '->')+2);
			if(stripos($str, '=')!==FALSE)
			{
				return preg_replace('/(\w+)=(\w+)->(\w+)/i', '<?php foreach($this->tpl_vars[\'${1}\'] as $${2}=>$${3}){?>', $str);
			}
			else
			{
				return preg_replace('/(\w+)->(\w+)/i', '<?php foreach($this->tpl_vars[\'${1}\'] as $${2}){?>', $str);
			}
		}
		if(!empty($this->loop_elem) && stripos($matches[1], $this->loop_elem)!==FALSE)
		{
			$str = trim($matches[1]);
			if(stripos($str, '.')!==FALSE)
			{
				$elem = explode('.', $str);
				return '<?php echo $'.$this->loop_elem.'[\''.end($elem).'\'];?>';
			}
			else
			{
				return '<?php echo $'.$str.';?>';
			}
		}
		// 变量
		return '<?php echo $this->tpl_vars[\''.$matches[1].'\'];?>';
	}
	public function tag_left($tag='<{')
	{
		$this->sep_l = $tag;
	}
	public function tag_right($tag='<{')
	{
		$this->sep_r = $tag;
	}
	public function skin($skin='')
	{
		$this->skin = $skin;
	}
}class verify
{
	private static $class_name=__CLASS__;
	private static $verify=null;
	public static function main($function='',$var=null,$args=array())
	{
		if(is_null(self::$verify)) self::$verify=new self::$class_name;
		if(!empty($args)) return self::$verify->$function($var,$args);
		else return self::$verify->$function($var);
	}
	/**
	 * 变量是null或者空字符串会返回false
	 * @param  [type] $var 变量
	 * @return bool        空的返回false
	 */
	protected function not_null($var=null)
	{
		return is_array($var)?count(array_filter($var)):!(is_null($var)||str_replace(' ', '', $var)=='');
	}
	// 验证手机号
	protected function phone($num=0)
	{
		return preg_match('/1[358]\d{9}/', $num)?$num:false;
	}
	// 验证身份证号
	protected function identify($num=0)
	{
/*		区域    生日       同日生人标号，男性为基数
		411102 1991-03-19              009               4 = 前十七位的矫正算法 */
		if(!preg_match('/\d{11}/', $num)) return false;
		$sum=0;
		$coef = array(7,9,10,5,8,4,2,1,6,3,7,9,10,5,8,4,2);
		$rema = array(1,0,'X',9,8,7,6,5,4,3,2);
		for($i=0,$len=count($coef);$i<$len;$i++) $sum+=$num[$i]*$coef[$i];
		return $rema[$sum%11]==$num[$len]?$num:false;
	}
	// 验证邮箱
	protected function email($email='')
	{
		return filter_var($email,FILTER_VALIDATE_EMAIL);
	}
	// 验证ip
	protected function ip($ip='',$args=array(false))
	{
		list($is_v6)=$args;
		$options=($is_v6)?array('flags'=>FILTER_FLAG_IPV6):array();
		return filter_var($ip,FILTER_VALIDATE_IP,$options);
	}
	// 验证url
	// 不用php验证是因为 他不支持没有协议的url
	protected function url($url='')
	{
		$pattern = '/^(http:\/\/|https:\/\/)?([a-z0-9\-]+\.){1,}[a-z0-9\-]+\/?(.*)?$/i';		
		return preg_match($pattern, $url)?$url:false;
	}
	// 数字验证
	protected function number($num=null,$args=array(null,null))
	{
		list($min,$max)=$args;
		if(is_null($min))
		{
			return filter_var($num,FILTER_VALIDATE_INT);
		}
		else
		{
			$options=array(
				'options'=>array('min_range'=>$min,'max_range'=>$max)
				);
			return filter_var($num,FILTER_VALIDATE_INT,$options);
		}
	}
	// 字符串长度过滤
	protected function str_len($str='',$args=array(null,null))
	{
		list($min,$max)=$args;
		if(is_null($min))
		{
			return mb_strlen($str,'utf-8');
		}
		else
		{
			$len = mb_strlen($str,'utf-8');
			return ($len>=$min&&$len<=$max)?$str:false;
		}
	}
	// 修正字符串长度
	protected function sanitize_str($str='',$args=array(0,''))
	{
		list($len,$tag)=$args;
		if($len<1) return '';
		return (mb_strlen($str,'utf-8')>$len)?mb_substr($str, 0, $len, 'utf-8').$tag:$str;
	}
	/**
	 * 对字符串进行过滤
	 * @param  string  $str  字符串
	 * @param  array   $lists 允许的类型
	 * alp 字母
	 * num 数字
	 * cc  汉字
	 *     其他字符
	 * @param  integer $max   最大长度
	 * @param  integer $min   最小长度
	 * @return 
	 */
	protected function validate_str($str='',$args=array(array('alp','num','cc','-_'),0,0))
	{
		list($lists,$max,$min)=$args;
		$pattern_segs=array(
			'alp'=>'a-z',
			'num'=>'0-9',
			'cc'=>'\x{4e00}-\x{9fa5}'
			);
		$pattern='/[';
		foreach($lists as $li)
		{
			if(in_array($li, array_keys($pattern_segs))) $pattern.=$pattern_segs[$li];
			else $pattern.=preg_quote($li);
		}
		$pattern.=$max>0?']{'.$min.','.$max.'}/iu':']+/iu';
		return preg_match($pattern, $name)?$name:false;
	}
	// 是否存在一个指定列表中
	protected function in($str='',$in=array())
	{
		return in_array($str, $in)?$str:false;
	}
}