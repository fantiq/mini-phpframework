<?php
/**
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
}