<?php
return array(
	'base'=>array(
		'timezone'=>'PRC',
		'is_gz'=>true
		),
	'site'=>array(
		'log_dir'=>'',
		'default_skin'=>'default',
		'display_errors'=>true,
		'tpl_ext'=>'tpl',
		),
	'router'=>array(
		'rewriteable'=>false,
		'target'=>'r',
		'default_controller'=>'index/site',
		'route_maps'=>array(
			'api/index'=>'index/test/fantasy',
			),
		),
	'session'=>array(
		'sess_name'=>'HACKPHP_SESSIONID',
		'auto'=>false,
		'alive_time'=>3600,
		'cookie_expire'=>3600*24,
		'cookie_path'=>'/',
		'cookie_domain'=>HOST,
		'dsn'=>'mysql://root:@127.0.0.1:3306/test/sess_tab',
		),
	'db'=>array(
		'driver'=>'pdo_mysql',
		'host'=>'127.0.0.1',
		'port'=>3306,
		'dbname'=>'test',
		'user'=>'root',
		'passwd'=>'',
		'charset'=>'utf8',
		'prefix'=>'',
		'page'=>array(
			'lists_count'=>1,
			)
		)
	);