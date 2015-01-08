--create session sql--

CREATE TABLE sess_tab(
	id int auto_increment,
	sid char(255) not null default '',
	user_id int not null default 0,
	expire_time int not null default 0,
	last_active int not null default 0,
	data text,
	primary key(id),
	index(sid),
	index(user_id)
	)auto_increment=1,charset=utf8,engine=innodb