<?php
class session
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
}