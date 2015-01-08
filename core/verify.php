<?php
class verify
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