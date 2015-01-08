<?php
/**
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
}