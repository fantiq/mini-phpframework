<?php
class response extends http
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
}