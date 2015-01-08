<?php
/**
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
}