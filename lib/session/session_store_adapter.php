<?php
// session 操作文件适配器
namespace hkp;
use hkp\loader;
use hkp\config;
use hkp\session;
class session_store_adapter
{
	public static function store_adapter()
	{
		// 获取session存储引擎
		$info = parse_url(config::get('session.dsn',''));
		loader::load_file(dirname(__FILE__).DIRECTORY_SEPARATOR.'store'.DIRECTORY_SEPARATOR.$info['scheme'].'_session.php');
		$store = $info['scheme'].'_session';
		return new $store($info);
	}
}
/**
 * session数据存储在这个类里面便于存储
 */
class sess_data
{
	private $sid='';
	private $expire_time=0;
	private $last_active=0;
	private $user_id=0;
	private $data=array();
	public function __construct($sid='',$user_id=0,$expire_time=0,$last_active=0,$data=array())
	{
		// 赋值
		$this->sid=$sid;
		$this->expire_time=$expire_time;
		$this->last_active=$last_active;
		$this->user_id;
		$this->data=$data;
	}
	public function get($key=null)
	{
		if(is_null($key))
		{
			return $this->data;
		}
		elseif($this->has($key))
		{
			return $this->data[$key];
		}
		else
		{
			return null;
		}
	}
	public function set($key=null,$val=null)
	{
		if(is_array($key))
		{
			$this->data = array_merge($this->data,$key);
		}
		elseif(!is_null($key))
		{
			$this->data[$key]=$val;
		}
		else
		{
			return null;
		}
		session::need_update(true);
	}
	public function del($key=null)
	{
		if(is_array($key))
		{
			foreach($key as $v)
			{
				if($this->has($v))
				{
					unset($this->data[$v]);
				}
			}
		}
		elseif($this->has($key))
		{
			unset($this->data[$key]);
		}
		else
		{
			return false;
		}
		session::need_update(true);
	}
	public function get_user_id()
	{
		return $this->user_id;
	}
	public function get_expire_time()
	{
		return $this->expire_time;
	}
	public function get_last_active()
	{
		return $this->last_active;
	}
	private function has($key='')
	{
		return array_key_exists($key, $this->data);
	}
}
/**
 * session引擎需要继承的
 */
abstract class store
{
	abstract public function __construct();
	abstract public function fetch($sid);
	abstract public function create($sid,$user_id,$expire_time,$last_active);
	abstract public function update($sid,$expire_time,$last_active,$data);
	abstract public function destroy($sid);
	abstract public function clean();
	abstract public function is_online($user_id);
	abstract public function num_online();
}