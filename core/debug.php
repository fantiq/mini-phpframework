<?php
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
