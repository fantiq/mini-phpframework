<?php
use hkp\model;
class test extends model
{
	public function get()
	{
		$data = $this->db->select('id,name,email,addtime')->from('data_test')
			->where(array())->group('addtime','ASC')->findall();
		$this->p($data);
		echo $this->db->sql();
	}
	public function add()
	{
		$data = array(
			array('name'=>'小名','email'=>'sadjsakd@sina.com','addtime'=>434234523),
			array('name'=>'小李','email'=>'fdsdsany@sina.com','addtime'=>4342424893),
			);
		// $data = array('name'=>'范易龙','email'=>'fanyilong_v5@sina.com','addtime'=>time());
		$this->db->insert('data_test',$data);
		echo $this->db->lastID();
	}
	public function del()
	{
		$this->db->where('id=1')->delete('data_test');
	}
	public function mod()
	{
		$this->db->where('id=3')->update('data_test',array('name'=>'test'));
	}
	private function p($v)
	{
		echo "<pre>";
		print_r($v);
		echo "</pre>";
	}
	public function mysql_test()
	{
		return $this->db->select()->from('data_test')->findall();
	}
}