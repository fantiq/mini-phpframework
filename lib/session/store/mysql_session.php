<?php
use hkp\store;
class mysql_session extends store
{
	private $table='';
	private $conn=null;
	private $dsn=array(
		'hostname'=>'',
		'username'=>'',
		'password'=>'',
		'dbname'=>''
		);
	public function __construct($info=array())
	{
		$this->dsn['hostname']=(array_key_exists('port', $info)&&is_numeric($info['port']))?$info['host'].':'.$info['port']:$info['host'];
		$this->dsn['username']=$info['user'];
		$this->dsn['password']=empty($info['pass'])?'':$info['pass'];
		$this->dsn['port']=$info['port'];
		$db_tab=explode('/', trim($info['path'],'/'));
		$this->dsn['dbname']=$db_tab[0];
		$this->table=$db_tab[1];
	}
	public function create($sid,$user_id,$expire_time,$last_active)
	{
		$sid=(string)$sid;
		$user_id=(int)$user_id;
		$expire_time=(int)$expire_time;
		$last_active=(int)$last_active;
		$data = addslashes(serialize(array()));
		$sql = "insert into $this->table(sid,user_id,expire_time,last_active,data) values('$sid',$user_id,$expire_time,$last_active,'$data')";
		return $this->exec($sql);
	}
	public function fetch($sid)
	{
		$sql = "select * from $this->table where sid='$sid'";
		if(!($data = $this->exec($sql))) return false;
		$data['data']=unserialize($data['data']);
		return $data;
	}
	public function update($sid,$expire_time,$last_active,$data)
	{
		$data = serialize($data);
		$last_active = (int)$last_active;
		$expire_time = (int)$expire_time;
		$sql = "update $this->table set data='$data',expire_time=$expire_time,last_active=$last_active where sid='$sid'";
		return $this->exec($sql);
	}
	public function clean()
	{
		$sql = "delete from $this->table where expire_time<".timer::get_stamp();
		return $this->exec($sql);
	}
	public function destroy($sid)
	{
		$sql = "delete from $this->table where sid='$sid'";
		return $this->exec($sql);
	}
	public function connect()
	{
		$this->conn = mysql_connect($this->dsn['hostname'],$this->dsn['username'],$this->dsn['password']) or exit('fail to onnect your mysql!');
		mysql_select_db($this->dsn['dbname']);
	}
	public function is_online($user_id=0)
	{
		$sql='select * from $this->table where user_id=$user_id';
		return $this->exec($sql,true);
	}
	public function num_online()
	{
		$sql = "select count(*) from $this->table";
		return $this->exec($sql,true);
	}
	private function exec($sql='',$count=false)
	{
		$this->connect();
		$res = mysql_query($sql);
		if(is_bool($res)) return $res;
		return $count?mysql_num_rows($res):mysql_fetch_assoc($res);
	}
	public function __destruct()
	{
		@mysql_close($this->conn);
	}
}