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
}