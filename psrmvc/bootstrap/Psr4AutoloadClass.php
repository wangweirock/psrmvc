<?php

//实现命名空间与真实路径的对应关系，并进行自动加载
class Psr4AutoloadClass
{
	//保存一下命名空间与真实的类名的对应关系
	public $namespaces = [
		// 'framework' => 'vendor/bob/framework/src',
		// 'message' => 'vendor/bob/message/src',
		// 'controller' => 'app/controller',
		// 'model' => 'app/model'
	];

	//公共的调用方法，调用自动加载这个函数的
	public function register()
	{
		spl_autoload_register(array($this, 'loadClass'));
	}

	//命名空间与文件夹结构对应起来
	public function addNamespace($namespace, $path)
	{
		$path = rtrim($path, '/').'/';
		$this->namespaces[$namespace] = $path;
	}

	//自动加载方法，真实的自动加载的函数
	public function loadClass($class)
	{
		$pos = strrpos($class, '\\'); //10
		$namespace = substr($class, 0, $pos);	//截取出来命名空间
		$realClass = substr($class, $pos + 1); //截取出来真实的类名

		$this->loadMap($namespace, $realClass);	//调用处理映射关系的方法
	}

	//处理映射关系的
	public function loadMap($namespace, $realClass)
	{

		if(!isset($this->namespaces[$namespace]))
		{
			$realPath = rtrim($namespace, '/').'/'.$realClass.'.php';
		}

		foreach ($this->namespaces as $name => $path) {
			if($namespace == $name){
				$realPath = $path.$realClass.'.php';
			}
		}

		$this->requireFile(BASE_PATH.$realPath);

	}

	//请求文件，include文件
	public function requireFile($path)
	{
		if(file_exists($path))
		{
			include $path;
		}else{
			exit('还没有创建'.$path.'这个文件！');
		}
	}
}




