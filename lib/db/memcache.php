<?php
class memcache_engine
{
	public function __construct()
	{
		$this->dbh = new Memcache;
		$this->dbh->connect(config::get('host','127.0.0.1'),config::get('port',11211));
	}
}