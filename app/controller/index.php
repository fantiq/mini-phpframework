<?php
class index extends controller
{
	/**
	 * 优先调用方法
	 * @param  string $name [description]
	 * @return [type]       [description]
	 */
	public function _before($name='')
	{
		//echo 'is the name '.$name.' exist<br>';
		/*$this->auto=false;
		exit();*/
	}
	public function site()
	{
/*		$arr=array(1,2,3,3);
		$this->debug($arr,'test',0);
		$name='fantasy';
		$this->debug($name,'test2');*/
		$this->text("<h2>Hello World!</h2>framework运行成功");
		// $this->text("other text!");
	}
	public function test($name='')
	{
		$user = model('users');
		$data = $user->get_username();
		// $this->debug($data,'db_test',1);
		$this->set_skin('default');
		$this->assign('data',$data);
		$this->assign('user',$name);
		$this->output='index.tpl';
	}
	public function my()
	{
		$options=array(
			'name'=>array(
				'number'=>array(),
				'not_null'=>array()
				),
			);
		$data = $this->verify_opts($options,'get');
		var_dump($data);
		$this->debug($data,'',1);
		//var_dump($this->verify('num',$_GET['id']));
	}
	public function api()
	{
		// 
		$this->output=array();
	}
}