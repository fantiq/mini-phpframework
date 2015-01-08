<?php
class data
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
