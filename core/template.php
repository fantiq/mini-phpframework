<?php
/**
 * 模版引擎 需要controller来继承
 */
class template
{
	private $sep_l='<{';
	private $sep_r='}>';
	private $tpl_vars = array();
	private $tpl_ext='';
	private $tpl_dir=''; // 模版文件目录
	private $com_dir=''; // 编译文件目录
	private $cac_dir=''; // 缓存文件目录
	private $skin = ''; // 皮肤
	private $expire = false; //缓存 expire 分钟
	private $loop_elem = null; // 是否有循环语句需要解析
	public function __construct()
	{
		$this->skin = config::get('site.default_skin','');
		$this->ext = config::get('site.tpl_ext','html');
	}
	private function init($path='')
	{
		$this->tpl_dir = empty($path)?view_dir:trim($path);
		if(!empty($this->skin))
		{
			$this->tpl_dir.=DIRECTORY_SEPARATOR.$this->skin;
		}
		$this->com_dir=$this->tpl_dir.DIRECTORY_SEPARATOR.'compile';
		$this->cac_dir=$this->tpl_dir.DIRECTORY_SEPARATOR.'cache';
		// 创建文件夹
		foreach(array($this->tpl_dir, $this->com_dir, $this->cac_dir) as $dir)
		{
			if(!file_exists($dir))
			{
				mkdir($dir, 0755);
				chmod($dir, 0755);
			}
		}
	}
	/**
	 * 显示模版文件
	 * @param  string $path
	 * @return [type]
	 */
	public function show($path='')
	{
		$this->init();
		$tpl_file = $this->tpl_dir.DIRECTORY_SEPARATOR.trim($path).'.'.$this->ext; // 模版文件
		$com_file = $this->com_dir.DIRECTORY_SEPARATOR.md5(trim($path)); // 编译文件
		// 编译模版
		$this->compile($tpl_file, $com_file);
		ob_start();
		// 缓存
		if($this->expire>0 || $this->expire=='static')
		{
			$cac_file = $this->cac_dir.DIRECTORY_SEPARATOR.md5(trim($path));
			// 文件不存在或者过期或者编译文件修改过 生成新缓存文件
			$this->cache_file($com_file,$cac_file);
		}
		else
		{
			include $com_file;
			response::add_contents(ob_get_clean());
		}
	}
	/**
	 * 为模版文件赋值
	 * @param  string $key key
	 * @param  mixed $val  val
	 * @return void
	 */
	public function assign($key=null,$val=null)
	{
		$this->tpl_vars[$key] = $val;
	}
	/**
	 * 设置缓存文件的过期时间
	 * @param  integer $expire 缓存时间 单位 min
	 * @return void
	 */
	public function cache($expire=0)
	{
		if($expire>=0) $this->expire = $expire;
		else $this->expire = 'static'; // 静态化文件
	}
	/**
	 * 当缓存文件不存在、编译文件修改、缓存文件过期则生成缓存文件
	 * @param  string $com_file [description]
	 * @param  string $cac_file [description]
	 * @return [type]           [description]
	 */
	private function cache_file($com_file='',$cac_file='')
	{
		if($this->expire<=0)
		{
			@unlink($cac_file);
			return false;
		}
		$new_cache_file = false;
		// 缓存文件不存在 || 编译文件修改
		if(!file_exists($cac_file)||filemtime($cac_file)<filemtime($com_file))
		{
			$new_cache_file = true;
		}
		else
		{
			// 检测缓存文件是否过期
			include $cac_file;
			$out = ob_get_clean();
			if(preg_match_all('/^cache--(\d+)-->/', $out, $matches))
			{
				if($matches[1][0]<timer::get_stamp()) // 缓存文件过期重新生成
				{
					$new_cache_file = true;
				}
			}
		}
		if($new_cache_file)
		{
			$out = file_get_contents($com_file);
			if($this->expire!='static')
			{
				$out = 'cache--'.($this->expire*3600+timer::get_stamp()).'-->'.$out;
			}
			if(!file_put_contents($cac_file, $out))
			{
				trigger_error('生成缓存文件<b>{'.$cac_file.'}</b>失败!',E_USER_ERROR);
			}
		}
		$out=preg_replace('/^cache--\d+--\>/i', '', $out);
		response::add_contents($out);
		unset($out);
	}
	private function compile($tpl_file='',$com_file='')
	{
		if(!file_exists($tpl_file))
		{
			trigger_error('请求的模版文件<b>{'.$tpl_file.'}</b>不存在!',E_USER_ERROR);
		}
		// 编译文件不存在或者模版文件被修改
		if(is_debug||!file_exists($com_file) || filemtime($com_file)<filemtime($tpl_file))
		{
			$out = file_get_contents($tpl_file);
			//$out = str_replace(array("\r","\n","\t"), '', $out); // 压缩文件
			$pattern = '/'.preg_quote($this->sep_l).'(.*?)'.preg_quote($this->sep_r).'/i';
			$out = preg_replace_callback($pattern, array($this,'parse'), $out);
			if(!file_put_contents($com_file, $out))
			{
				trigger_error('生成编译文件<b>{'.$com_file.'}</b>失败!',E_USER_ERROR);
			}
			unset($out);
		}
	}
	/**
	 * 解析模版文件
	 * @param  array  $matches 匹配到的标签
	 * @return string          处理后的字符串
	 */
	private function parse($matches=array())
	{
		$matches[1]=trim($matches[1]);
		// 解析php代码
		if(stripos($matches[1], 'include')===0)
		{
			return preg_replace('/^include\s+(.+)\s*/i', "<?php include '".$this->tpl_dir.DIRECTORY_SEPARATOR."\\\\1';?>", $matches[1]);
		}
		if(preg_match('/^php.*/i', $matches[1]))
		{
			return str_replace('=', ' echo ', preg_replace('/^php(.*)/i', '<?php \\1;?>', rtrim($matches[1],'; ')));
		}
		if(preg_match('/^\/\w+$/i', $matches[1]))
		{
			return "<?php }?>";
		}
		if(stripos($matches[1], 'else')!==FALSE)
		{
			return "<?php }else{?>";
		}
		if(stripos($matches[1], 'if')!==FALSE) // if条件语句
		{
			$str = str_replace(array('neq','eq','lt','gt','if',' '), array('!=','==','<','>','',''), $matches[1]);
			if(strpos($str, "'")!==FALSE||strpos($str, "\"")!==FALSE)
			{
				if(preg_match('/(\w+)(==|!=|>|<)([\042|\047]+\w+[\042|\047]+)/i', $str))
				{
					$str = preg_replace('/(\w+)(==|!=|>|<)([\042|\047]+\w+[\042|\047]+)/i', '<?php if($this->tpl_vars[\'${1}\']${2}${3}){?>', $str);
				}
				else
				{
					$str = preg_replace('/([\042|\047]+\w+[\042|\047]+)(==|!=|>|<)(\w+)/i', '<?php if(${1}${2}$this->tpl_vars[\'${3}\']){?>', $str);
				}
			}
			else
			{
				if(preg_match('/\w+(==|!=|>|<)\d+/i', $str))
				{
					$str = preg_replace('/(\w+)(==|!=|>|<)(\d+)/i', '<?php if($this->tpl_vars[\'${1}\']${2}${3}){?>', $str);
				}
				else
				{
					$str = preg_replace('/(\w+)(==|!=|>|<)(\w+)/i', '<?php if($this->tpl_vars[\'${1}\']${2}$this->tpl_vars[\'${3}\']){?>', $str);
				}
			}
			return $str;
		}
		if(stripos($matches[1], 'foreach')!==FALSE && stripos($matches[1], '->')!==FALSE) // 循环
		{
			$str = str_replace(array('foreach',' '), '', $matches[1]);
			$this->loop_elem=substr($str, stripos($str, '->')+2);
			if(stripos($str, '=')!==FALSE)
			{
				return preg_replace('/(\w+)=(\w+)->(\w+)/i', '<?php foreach($this->tpl_vars[\'${1}\'] as $${2}=>$${3}){?>', $str);
			}
			else
			{
				return preg_replace('/(\w+)->(\w+)/i', '<?php foreach($this->tpl_vars[\'${1}\'] as $${2}){?>', $str);
			}
		}
		if(!empty($this->loop_elem) && stripos($matches[1], $this->loop_elem)!==FALSE)
		{
			$str = trim($matches[1]);
			if(stripos($str, '.')!==FALSE)
			{
				$elem = explode('.', $str);
				return '<?php echo $'.$this->loop_elem.'[\''.end($elem).'\'];?>';
			}
			else
			{
				return '<?php echo $'.$str.';?>';
			}
		}
		// 变量
		return '<?php echo $this->tpl_vars[\''.$matches[1].'\'];?>';
	}
	public function tag_left($tag='<{')
	{
		$this->sep_l = $tag;
	}
	public function tag_right($tag='<{')
	{
		$this->sep_r = $tag;
	}
	public function skin($skin='')
	{
		$this->skin = $skin;
	}
}