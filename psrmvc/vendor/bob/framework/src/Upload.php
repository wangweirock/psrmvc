<?php
namespace Framework;

// $up = new Upload(array('path' => './pang/kai/ylyp'));
// $up->up('file');
// $up->errorInfo;


//作业：先写一遍，然后再修改，按照日期把上传文件给归纳起来


//上传文件的类，可以上传单个文件
class Upload
{
	//设置文件上传后保存的目录
	private $path = './upload';

	//允许上传的文件mime类型
	private $allowMime = ['image/jpg', 'image/jpeg', 'image/pjpeg', 'image/png', 'image/x-png', 'image/gif', 'image/wbmp'];

	//允许上传文件的后缀名
	private $allowSub = ['jpg', 'jpeg', 'gif', 'png', 'bmp'];

	//允许上传的大小
	private $allowSize = 2048000;

	//是否要上传启用随机的文件名
	private $isRandName = true;

	//是否要设置文件上传后的文件名前缀
	private $prefix = 'up_';

	//上传文件的原名
	private $orgName;

	//上传文件的新名字
	private $newName;

	//上传文件本身的类型
	private $type;

	//上传文件的大小
	private $size;

	//上传文件错误的编号
	private $errorNum = 0;

	//上传文件的错误信息
	private $errorInfo;

	//上传文件的临时路径
	private $tmpPath;

	//上传文件的后缀名
	private $subName;

	//上传文件的mime类型
	private $mime;


	//构造方法，来初始化一些参数
	public function __construct($options = array())
	{
		if(count($options) > 0)
		{
			//遍历传进来的数组，来给存在的属性进行赋值
			foreach ($options as $key => $value) {
				$this->setOption($key, $value);
			}
		}
	}

	//给存在的属性设置传进来的值
	private function setOption($attr, $value)
	{
		if(array_key_exists($attr, get_class_vars(get_class($this))))
		{
			$this->$attr = $value;
		}
	}


	//公共调用调用的上传方法
	public function up($fileName)
	{

		//检查设置的那个路径对不对
		if(!$this->checkPath())
		{
			return false;
		}

		if(!$this->setFile($fileName))
		{
			return false;
		}

		//检查文件大小、mime类型、后缀名是不是被允许
		if($this->checkSize() && $this->checkMime() && $this->checkSub())
		{
			//重新命名
			$this->setNewName();
			if($this->move())
			{
				return $this->newName;
			}else{
				return false;
			}
			//检查是不是上传的文件

		}else{
			return false;
		}

	}

	//移动上传文件到新的目录
	private function move()
	{
		if(is_uploaded_file($this->tmpPath))
			{
				$this->path = rtrim($this->path, '/').'/'.$this->newName;
				if(move_uploaded_file($this->tmpPath, $this->path))
				{
					return true;
				}else{
					$this->errorNum = -5;
					return false;
				}
			}else{
				$this->errorNum = -6;
				return false;
			}
	}

	private function setNewName()
	{
		if($this->isRandName)
		{
			$this->newName = $this->prefix.uniqid().'.'.$this->subName;
		}else{
			$this->newName = $this->prefix.$this->orgName;
		}
	}


	private function checkPath()
	{
		if(!file_exists($this->path))
		{
			$this->errorNum = -1;
		}
		//如果目录不存在就创建
		if(!is_dir($this->path))
		{
			return mkdir($this->path, 0777, true);	//递归创建一个可读可写可执行的目录，
		}

		//判断这个目录是不是可以被写入，如果不能被写入，那么就改变权限
		if(!is_writable($this->path))
		{
			return chmod($this->path, 0777);
		}

		return true;
	}

	//赋值并处理上传的文件
	private function setFile($fileName)
	{
		if(isset($_FILES[$fileName]))
		{
			if($_FILES[$fileName]['error'])
			{
				$this->errorNum = $_FILES[$fileName]['error'];
				return false;
			}
		}else{
			$this->errorNum = -100;
			return false;
		}

		//赋值传过来的文件信息给成员属性
		$this->orgName = $_FILES[$fileName]['name'];
		$this->mime = $_FILES[$fileName]['type'];
		$this->tmpPath = $_FILES[$fileName]['tmp_name'];
		$this->size = $_FILES[$fileName]['size'];
		$pathinfo = pathinfo($_FILES[$fileName]['name']);
		$this->subName = $pathinfo['extension'];

		return true;
	}

	//检查文件的大小是不是被允许
	private function checkSize()
	{
		if($this->allowSize > $this->size)
		{
			return true;
		}else{
			$this->errorNum = -2;
			return false;
		}
	}

	//检查mime类型，是不是被允许
	private function checkMime()
	{
		if(in_array($this->mime, $this->allowMime))
		{
			return true;
		}else{
			$this->errorNum = -3;
			return false;
		}
	}

	//检查上传文件的后缀是不是被允许
	private function checkSub()
	{
		if(in_array($this->subName, $this->allowSub)){
			return true;
		}else{
			$this->errorNum = -4;
			return false;
		}
	}


	private function getError()
	{
		$error = [
			-100 => '你tm没有上传文件你不知道吗',
			-6 => '你可能接受到了一个假的上传文件',
			-5 => '不能移动上传文件',
			-4 => '不被允许的文件后缀名称',
			-3 => '不被允许的mime类型',
			-2 => '上传的文件大小，超过了允许上传的文件大小',
			-1 => '指定的路径不存在',
			0 => '上传成功',
			1 => '上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值',
			2 => '上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值',
			3 => '文件只有部分被上传',
			4 => '没有文件被上传',
			6 => '找不到临时文件夹',
			7 => '文件写入失败'
		];


		$this->errorInfo = $error[$this->errorNum];
	}


	public function __get($name)
	{
		if($name == 'errorInfo')
		{
			$this->getError();
			return $this->errorInfo;
		}
	}

}


