<?php
class memcache_session extends store
{
	private $mem=null;
	private $dsn=array(
		'hostname'=>'',
		'port'=>11211
		);
	public function __construct($info=array())
	{
		$this->dsn['hostname']=array_key_exists('host', $info)?$info['host']:'127.0.0.1';
		$this->dsn['port']=array_key_exists('port', $info)&&is_numeric($info['port'])?$info['port']:11211;
	}
	public function create($sid,$user_id,$expire_time,$last_active)
	{
		$sid=(string)$sid;
		$user_id=(int)$user_id;
		$expire_time=(int)$expire_time;
		$last_active=(int)$last_active;
		$data = serialize(array());
		$datas = array('user_id'=>$user_id,'expire_time'=>$expire_time,'last_active'=>$last_active,'data'=>$data);
		$this->mem->set('sid',$datas);
	}
	public function fetch($sid)
	{
		return $this->mem->get($sid);
	}
	public function update($sid,$expire_time,$last_active,$data)
	{
		$data = serialize($data);
		$last_active = (int)$last_active;
		$expire_time = (int)$expire_time;
		//
		$datas = $mem->get($sid);
		$datas['expire_time']=$expire_time;
		$datas['last_active']=$last_active;
		$datas['data']=$data;
		$mem->replace($sid,$datas);
	}
	public function clean()
	{
		
	}
	public function destroy($sid)
	{
		$mem->delete($sid);
	}
	public function connect()
	{
		$this->mem = new Memcache;
		$this->mem->connect($this->dsn['host'],$this->dsn['port']);
	}
	public function is_online($user_id=0)
	{
		// 
	}
	public function num_online()
	{
		
	}
	public function __destruct()
	{
		$this->mem->close();
	}
}