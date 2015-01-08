<?php
/**
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
}