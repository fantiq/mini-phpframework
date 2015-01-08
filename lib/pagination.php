<?php
class page
{
	private $count=0; //记录总条数
	private $page_count=0; //总页数
	private $list_num = 0; //每页显示条数
	private $page=0;//当前页数
	public function __construct($count=0,$list_num=0,$page=1)
	{
		$this->count = $count<0?0:$count;
		$this->page=$page<1?1:$page;
		$this->list_num=$list_num<1?config::get('db.page.lists_count',1):$list_num;
		$this->page_count = ceil($this->count/$this->list_num);
	}
	public function set_list_num($list_num=0)
	{
		if(is_numeric($list_num)&&$list_num>0)
		{
			$this->list_num=$list_num;
		}
	}
	public function get_start()
	{
		if($this->page>$this->page_count) $this->page=$page_count;
		$start = ($this->page-1)*$this->list_num;
		return $start<0?0:$start;
	}
	public function get_lists()
	{
		return $this->list_num;
	}
	public function __set($key=null,$val=null)
	{
		return false;
	}
	/**
	 * 计算页数
	 * @param  integer $offset 分页偏移量
	 * @return [type]          [description]
	 */
	public function show($offset=4)
	{
		$limit = ($this->page-$offset)<1?1:$this->page-$offset;
		for($i=$limit;$i<$this->page;$i++)
		{
			echo '<b>'.$i.'</b><br>';
		}
		echo $this->page.'<br>';
		$limit = ($this->page+$offset)>$this->count?$this->count:$this->page+$offset;
		for($i=$this->page+1,++$limit;$i<$limit;$i++)
		{
			echo '<b>'.$i.'</b><br>';
		}
/*		$txt<<<EOF
		<a href=''/></a>
EOF;*/
	}
}
$page = new page(1008,3,7);
echo $page->get_start().'<br>';
echo $page->get_lists().'<br>';
$page->show(5);