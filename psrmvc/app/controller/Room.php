<?php
namespace Controller;
use \Model\Room as RoomModel;
use \Framework\Verify;
class Room extends Controller
{
	private $model;
	public function __construct()
	{
		parent::__construct();
		$this->model = new RoomModel();
	}

	public function love()
	{
		echo '在房间 ... ... &&*****00002&&& ... sadadsa787**&^kjkjj ... ...<br>';
		// $room = new RoomModel();
		// var_dump($room->getInfo());
		$this->assign('info', $this->model->getInfo($_GET['id']));
		$this->display();
	}

	public function yan()
	{
		$yan = new Verify();
		$yan->show();
	}

	public function chat()
	{
		$this->display();
	}

	public function smoke()
	{
		$this->success('请求抽烟成功！');
	}
}