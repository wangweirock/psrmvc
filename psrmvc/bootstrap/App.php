<?php

class App
{
	//应用程序启动
	static public function run()
	{

		$GLOBALS['config'] = include BASE_PATH.'config/config.php';
		include BASE_PATH.'bootstrap/Alias.php';
		self::route();
	}

	static public function route()
	{

		$_GET['m'] = isset($_GET['m']) ? $_GET['m'] : 'Index';
		$_GET['a'] = isset($_GET['a']) ? $_GET['a'] : 'index';

		$controller = '\Controller\\'.$_GET['m'];
		call_user_func(array(new $controller(), $_GET['a']));

	}

	static public function test()
    {
        echo '这是一个测试';
    }
}
