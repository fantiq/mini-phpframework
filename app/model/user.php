<?php
class user extends hkp\model
{
	public function login($email,$pass)
	{
		return $this->db->select()->from('users')->where("email='$email' and password='".md5($pass)."'")->find();
	}
}