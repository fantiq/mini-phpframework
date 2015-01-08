<?php
class model
{
	protected $db=null;
	public function __construct()
	{
		$this->db_adapter();
	}
	private function db_adapter()
	{
		$conf = config::get('db.driver','pdo_mysql');
		$conf = explode('_', $conf);
		if(array_key_exists(1, $conf)) config::set('db.driver',$conf[1]);
		loader::load_file(lib_dir.DIRECTORY_SEPARATOR.'DB'.DIRECTORY_SEPARATOR.$conf[0].'.php');
		$conf[0]=$conf[0].'_engine';
		$this->db = new $conf[0];
	}
	public function __destruct()
	{
		if(is_debug) debug::add_sql_string($this->db->sql());
	}
}
/*class abstractDB
{
	abstract function where();
}
*/