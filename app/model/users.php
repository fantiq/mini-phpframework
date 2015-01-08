<?php
class users extends model
{
	public function get_username()
	{
		$data = $this->db->select('user')->from('users')->where('id>10')->findall();
		return $data;
	}
}