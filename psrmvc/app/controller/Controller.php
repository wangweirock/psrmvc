<?php
namespace Controller;
use \Framework\Tpl;
class Controller extends Tpl
{
	public function __construct()
	{
		parent::__construct($GLOBALS['config']['TPL_DIR'], $GLOBALS['config']['TPL_CACHE_DIR']);
	}

	public function display($file = null, $isExecute = true, $uri = null)
	{
		if($file == null)
		{
			$file = strtolower($_GET['m']).'/'.$_GET['a'].'.html';
		}

		parent::display($file, $isExecute, $uri);
	}

	//成功跳转方法
	public function success($message = '操作成功', $url = null, $sec = 3)
	{
		if($url == null){
			$url = $_SERVER['HTTP_REFERER'];
		}
		$this->assign('msg', ['message' => $message, 'url' => $url, 'sec' => $sec]);
		$this->display('success.html');
	}

		//成功跳转方法
	public function error($message = '操作失败', $url = null, $sec = 3)
	{
		if($url == null){
			$url = $_SERVER['HTTP_REFERER'];
		}
		$this->assign('msg', ['message' => $message, 'url' => $url, 'sec' => $sec]);
		$this->display('error.html');
	}

}