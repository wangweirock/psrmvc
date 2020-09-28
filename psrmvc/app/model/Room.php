<?php
namespace Model;
use \Framework\Model;
class Room extends Model
{
	public function getInfo($id)
	{
		return $this->find(array('id' => 1));	//在模型里面可以直接使用数据库的操作方法
	}

}