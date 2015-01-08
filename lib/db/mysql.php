<?php
/**
 * MySQL封装
 */
class mysql_engine
{
	protected $dbh=null;
	protected $result=null;		// 预处理的返回class 
	private $params=array(); 	// statment中需要bind的参数
	private $opera_type='';  	// 操作类型 select insert update delete
	private $dbname = ''; 		// 数据库名
	private static $t_engines=array();
	private static $t_rows=array();
	private static $sql='';
	//生成sql时候的片段
	private $query_string=array(
		'select'=>array(
			'field'=>'',
			'from'=>'',
			'join'=>'',
			'order'=>'',
			'group'=>'',
			'limit'=>''
			),
		'insert'=>'',
		'update'=>'',
		'delete'=>'',
		'where'=>''
		);
	/**
	 * 直接执行sql
	 * @param  [type] $method 执行的方法
	 * @param  string $sql    sql字符串
	 * @return source
	 */
	public function query($sql='',$method=null,$row=false)
	{
		if($method=='modify')
		{
			return $dbh->exec($sql);
		}
		else
		{
			return $row==false?$this->findall():$this->find();
		}
	}
	/**
	 * 根据请求type组合生成SQL字符串
	 * 并传递绑定参数执行execute
	 * @return bool
	 */
	private function exec()
	{
		if(empty($this->opera_type)) return false;
		// 组合生成SQL
		switch ($this->opera_type) {
			case 'select':
				self::$sql=$this->query_string['select']['field'].
				$this->query_string['select']['from'].
				$this->query_string['select']['join'].
				$this->query_string['where'].
				$this->query_string['select']['group'].
				$this->query_string['select']['order'].
				$this->query_string['select']['limit'];
				break;
			case 'insert':
				self::$sql=$this->query_string['insert'];
				break;
			case 'update':
				self::$sql=$this->query_string['update'].$this->query_string['where'];
				break;
			case 'delete':
				self::$sql=$this->query_string['delete'].$this->query_string['where'];
				break;
			default:
				return false;
				break;
		}
		/**
		 *	MySQL这里需要str_replace处理数据防止注入
		 */
		foreach($this->params as $k=>$v) self::$sql = str_replace($k, mysql_real_escape_string($v), self::$sql);
		($this->result = mysql_query(self::$sql)) || $this->error();
		return true;
	}
	/**
	 * 输出全部结果集
	 * @return result
	 */
	public function findall()
	{
		$data = array();
		$this->exec();
		while($row = mysql_fetch_assoc($this->result)) $data[]=$row;
		return $data;
	}
	/**
	 * 输出一行结果集
	 * @param  string $index 索引
	 * @return result
	 */
	public function find()
	{
		$this->exec();
		return mysql_fetch_assoc($this->result);
	}
	/**
	 * 总记录数
	 * @return int 记录数
	 */
	public function count($tablename='')
	{
		$tablename = $this->get_table($tablename);
		$info = $this->get_table_info($tablename);
		if($info['engine']=='InnoDB')
		{
			return array('count'=>$info['rows']);
		}
		else
		{
			return $this->query('SELECT COUNT(*) AS count FROM '.$tablename,null,1);
		}
	}
	/**
	 * 最后插入后数据的行数
	 * @return int 行数
	 */
	public function lastID()
	{
		return mysql_insert_id();
	}
	/**
	 * 写入数据到数据库
	 * @param  string $tablename 数据表名称
	 * @param  array  $data      写入的数据
	 * @return void
	 */
	public function insert($tablename='',$data=array())
	{
		if(empty($data)) return false;
		$this->opera_type='insert';
		$sql='';
		$k=0;
		if(array_key_exists(0, $data)&&is_array($data[0]))
		{
			$keys=array_keys($data[0]);
			$this->query_string['insert']='INSERT INTO '.$this->get_table($tablename).'('.implode(',',$keys).') VALUES';
			foreach($data as $d)
			{
				$seg='(';
				$k++;
				for($i=0,$len=count($d);$i<$len;$i++)
				{
					$seg.=':'.$keys[$i].$k.',';
					$this->params[':'.$keys[$i].$k]=$d[$keys[$i]];
				}
				$sql.=rtrim($seg,',').'),';
			}
			$sql = rtrim($sql,',');
		}
		else
		{
			$this->query_string['insert']='INSERT INTO '.$this->get_table($tablename).'('.implode(',', array_keys($data)).') VALUES(';
			foreach($data as $k=>$v)
			{
				$sql.=':'.$k.',';
				$this->params[':'.$k]=$v;
			}
			$sql = rtrim($sql,',').')';
		}
		$this->query_string['insert'].=$sql;
		$this->exec();
	}
	/**
	 * 更新数据
	 * @param  string $tablename 数据表名称
	 * @param  array  $data      更新的数据
	 * @return void
	 */
	public function update($tablename='',$data=array())
	{
		$this->opera_type='update';
		$lists='';
		foreach($data as $field=>$value)
		{
			$lists .= $field.'=:'.$field.',';
			$this->params[':'.$field]=$value;
		}
		$lists=trim($lists,', ');
		$this->query_string['update']='UPDATE '.$this->get_table($tablename).' SET '.$lists;
		$this->exec();
	}
	/**
	 * 删除数据表记录
	 * @param  string $tablename 数据表名称
	 * @return void
	 */
	public function delete($tablename='')
	{
		$this->opera_type='delete';
		$this->query_string['delete']='DELETE FROM '.$this->get_table($tablename);
		$this->exec();
	}
	/**
	 * 生成where条件语句
	 * string where id>10 NAD name='fantasy'  
	 * array  array('id'=>'>10 AND','name'=>'fantasy')
	 * array  array('id'=>array(10,'>','AND'),'name'=>array('fantasy','='));
	 * @param  mixed 	$condition 条件
	 * @return void
	 */
	public function where($condition=null)
	{
		if(!verify::main('not_null',$condition)) return $this;
		$this->query_string['where']=' WHERE ';
		if(is_array($condition))
		{
			$sql='';
			foreach($condition as $key=>$val)
			{
				$key = trim($key); // 字段
				if(is_array($val))
				{
					// 数组传递 值 比较符 连接
					// 0、值 1、比较符 2、后向连接
					$comp=array_key_exists(1, $val)?$val[1]:'=';
					$sql.=$key.$comp.':'.$key;
					if(array_key_exists(2, $val)) $sql.=' '.strtoupper($val[2]).' ';
					$this->params[':'.$key]=$val[0];
				}
				else
				{
					//带有比较符号
					if(preg_match('/^\s*([><=])\s*([^\s]+)\s+(.*)$/i', $val,$matchs))
					{
						/*1 比较符 | 2 值 | 3 连接*/
						$sql.=$key.trim($matchs[1]).':'.$key.' '.strtoupper(trim($matchs[3])).' ';
						$this->params[':'.$key]=$matchs[2];
					}
					else
					{
						$sql.=$key.'=:'.$key.' AND ';
						$this->params[':'.$key]=$val;
					}
				}
			}
			$this->query_string['where'].=rtrim($sql,"AND|OR|NOT|&&|\|\||!| ");
		}
		else
		{
			$this->query_string['where'].=preg_replace_callback('/([a-z0-9\-]+)\s*[=<>]([^\s]+)/i', array($this,'prep_params'), $condition);
		}
		return $this;
	}
	public function select($fields='')
	{
		$this->opera_type='select';
		if(empty($fields)) $fields='*';
		$this->query_string['select']['field']='SELECT '.$fields;
		return $this;
	}
	public function from($tablename='')
	{
		$this->query_string['select']['from']=' FROM '.$this->get_table($tablename);
		return $this;
	}
	public function join($method='',$tablename='',$on='')
	{
		$method=strtoupper($method)=='LEFT'?' LEFT':' RIGHT';
		$this->query_string['select']['join']=$method.' JOIN '.$this->get_table($tablename).' ON '.$on;
		return $this;
	}
	public function order($field='',$m=null)
	{
		if(is_null($m))
		{
			$this->query_string['select']['order']=' ORDER BY '.$field.' ';
		}
		else
		{
			$m=in_array(strtoupper($m), array('ASC','DESC'))?$m:'ASC';
			$this->query_string['select']['order']=' ORDER BY '.$field.' '.$m;
		}
		return $this;
	}
	public function group($field='')
	{
		$this->query_string['select']['limit']=' GROUP BY '.$field;
		return $this;
	}
	public function limit($start=0,$list_count=10)
	{
		$this->query_string['select']['limit']=' LIMIT '.$start.','.$list_count;
		return $this;
	}
	/**
	 * 将条件语句生成statment类型的参数型 防sql注入
	 * @param  array  $matchs 匹配到的条件片段中的值
	 * @return string  处理后的条件片段
	 */
	private function prep_params($matchs=array())
	{
		$key=':'.$matchs[1];
		$val = trim($matchs[2],"'\"");
		//参数绑定
		$this->params[$key]=$val;
		return str_replace($matchs[2], $key, $matchs[0]);
	}
	//---------------------事务处理------------------------
	/**
	 * 开启一个事务处理
	 * 对表引擎进行检测 表是否支持事务
	 * @param  string $tablename 表名称
	 * @return bool
	 */
	public function begin($tablename='')
	{
		if(is_array($tablename))
		{
			foreach($tablename as $t)
			{
				$info = $this->get_table_info($this->get_table($t));
				if($info['engine']!=='InnoDB') return false;
			}
			return $this->dbh->beginTransaction();
		}
		else
		{
			$info = $this->get_table_info($this->get_table($tablename));
		}
		return ($info['engine']=='InnoDB')?$this->dbh->beginTransaction():false;
	}
	// 提交操作
	public function commit()
	{
		return $this->dbh->commit();
	}
	// 回滚
	public function roll()
	{
		return $this->dbh->rollBack();
	}
	private function get_table_info($tablename='')
	{
		if(array_key_exists($tablename, self::$t_engines)&&array_key_exists($tablename, self::$t_rows))
		{
			return array(
				'engine'=>self::$t_engines[$tablename],
				'rows'  =>self::$t_rows[$tablename]
				);
		}
		$info = $this->query('show table status from test where name=\''.$tablename.'\'',null,1);
		self::$t_engines[$tablename]=$info['Engine'];
		self::$t_rows[$tablename]=$info['Rows'];
		return $info;
	}
	//---------------------------------------------
	/**
	 * 生成tablename
	 * 默认对表名会加上前缀
	 * {tablename}会显示表的原名
	 * @param  string $tablename 表名
	 * @return string            处理后的表名
	 */
	private function get_table($tablename='')
	{
		return preg_match("/^\{[a-z0-9\_]+\}$/i", $tablename)?$tablename:config::get('db.prefix').$tablename;
	}
	/**
	 * 错误显示
	 * @param  string $msg 错误消息
	 * @return void
	 */
	public function error($msg='')
	{
		trigger_error(mysql_errno().':'.mysql_error(),E_USER_ERROR);
		exit();
	}
	/**
	 * [__construct 构造函数]
	 */
	public function __construct()
	{
		$this->connect();
	}
	/**
	 * 数据库连接
	 * @return void
	 */
	public function connect()
	{
		if(!$this->dbh = mysql_connect(config::get('db.host','127.0.0.1').':'.config::get('port',3306),config::get('db.user','root'),config::get('db.passwd',''))) $this->error();
		mysql_select_db(config::get('db.dbname')); // 切换数据库
	}
	public function sql()
	{
		return self::$sql;
	}
	public function __destruct()
	{
		mysql_close($this->dbh);
	}
}