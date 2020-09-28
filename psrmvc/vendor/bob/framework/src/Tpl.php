<?php
namespace Framework;

//$tpl = new Tpl();

//调用一下，清楚缓存目录cache
//$tpl->cleanCache();

// $tpl->assign('pangKai', '太胖的胖的不行不行的了的胖凯');
// $tpl->assign('saobin', '到底了是生了几个');
// $tpl->assign('yaoCheng', '成哥，求求你别杀我');
// $tpl->assign('url', 'http://img.1985t.com/uploads/attaches/2016/09/96081-Fpjj6BG.jpg');
// $tpl->display('index.html');

//前提是先写一遍，自己继续完善这个类。

//404: 找不到女朋友
//403: 拒绝访问
//405: 请求方法不允许
//500: 我不行了，别追我
//502: 服务器网关错误
//301: 重定向
//302: 临时重定向
//200: 成功

//模版引擎类
class Tpl
{
	//缓存目录
	protected $cacheDir = 'cache/';

	//模版存放的目录
	protected $tplDir = 'views/';

	//缓存有效期,一个小时
	protected $cacheLifeTime = 3600;

	//定义一个存变量的数组
	public $vars = [];

	
	public function __construct($tplDir = null, $cacheDir = null, $cacheLifeTime = null)
	{
		if(isset($tplDir))
		{
			$this->tplDir = $this->checkDir($tplDir);
		}

		if(isset($cacheDir))
		{
			$this->cacheDir = $this->checkDir($cacheDir);
		}

		if(isset($cacheLifeTime))
		{
			$this->cacheLifeTime = $cacheLifeTime;
		}

		//自己实现个三目运算符写法
	}

	public function assign($key, $value)
	{
		$this->vars[$key] = $value;
	}

	//调用公共的方法显示一个模版
	public function display($file, $isExecute = true, $uri = null)
	{
		$tplFilePath = $this->tplDir.$file;
		//弄一下,怼、整、搞、
		if(!file_exists($tplFilePath))
		{
			exit('这个模版路径或者文件不存在!');
		}

		//user.php?xx=xx
		//设置编译后的模版文件的名字，加上了可能出现的uri
		$cacheFile = md5($file.$uri).'.php';

		//设置完整的缓存文件目录
		$cacheDirPath = $this->cacheDir.$cacheFile;

		extract($this->vars);

		if(file_exists($cacheDirPath))
		{
			//当模版文件被修改了,也就是模版文件修改时间大于缓存文件的修改时间
			if(filemtime($tplFilePath) > filemtime($cacheDirPath))
			{
				unlink($cacheDirPath);
				$html = $this->compile($tplFilePath);
				$this->makeCacheFile($cacheDirPath, $html);

			}

			//如果有这个缓存文件，就执行
			if((filemtime($cacheDirPath) + $this->cacheLifeTime) < time())
			{
				$html = $this->compile($tplFilePath);
				$this->makeCacheFile($cacheDirPath, $html);

			}

		}else{
			//重新编译模版文件
			$html = $this->compile($tplFilePath);
			$this->makeCacheFile($cacheDirPath, $html);
		}

		if($isExecute)
		{
			include $cacheDirPath;
		}
	}

	//生成缓存文件
	private function makeCacheFile($cacheDirPath, $html)
	{
		if(!empty($html))
		{
			if(!file_put_contents($cacheDirPath, $html))
			{
				exit('模版缓存文件写入失败！');
			}
		}

	}

	//编译模版文件
	private function compile($tplFilePath)
	{
		$html = file_get_contents($tplFilePath);

		$keys = [
			'{if %%}' => '<?php if(\1): ?>',
            '{else}' => '<?php else : ?>',
            '{else if %%}' => '<?php elseif(\1) : ?>',
            '{elseif %%}' => '<?php elseif(\1) : ?>',
            '{/if}' => '<?php endif;?>',
            '{$%%}' => '<?=$\1;?>',
            '{foreach %%}' => '<?php foreach(\1) :?>',
            '{/foreach}' => '<?php endforeach;?>',
            '{for %%}' => '<?php for(\1):?>',
            '{/for}' => '<?php endfor;?>',
            '{while %%}' => '<?php while(\1):?>',
            '{/while}' => '<?php endwhile;?>',
            '{continue}' => '<?php continue;?>',
            '{break}' => '<?php break;?>',
            '{$%% = $%%}' => '<?php $\1 = $\2;?>',
            '{$%%++}' => '<?php $\1++;?>',
            '{$%%--}' => '<?php $\1--;?>',
            '{comment}' => '<?php /* ',
            '{/comment}' => ' */ ?>',
            '{/*}' => '<?php /* ',
            '{*/}' => '* ?>',
            '{section}' => '<?php ',
            '{/section}' => '?>',
			'{{%%(%%)}}' => '<?=\1(\2);?>',
			'{include %%}' => '<?php include "'.$this->tplDir.'\1";?>',
		];

		//遍历上面的定义的类似正则表达式
		foreach ($keys as $key => $value) {
			$pattern = '#'.str_replace('%%', '(.+)', preg_quote($key, '#')).'#imsU';

			if(stripos($pattern, 'include'))
			{
				$html = preg_replace_callback($pattern, array($this, 'parseInclude'), $html);
			}else{
				$html = preg_replace($pattern, $value, $html);
			}
		}

		return $html;
	}

	private function parseInclude($html)
	{
		$file = str_replace(['\'', '"'], '', $html[1]);

		$cacheFile = md5($html[1]).'.php';
		$this->display($file, false);
		return '<?php include "'.$cacheFile.'" ?>';
	}

	//检查目录
	private function checkDir($dir)
	{
		$dir = rtrim($dir, '/');
		if(!file_exists($dir) || !is_dir($dir))
		{
			mkdir($dir, 0777, true);
		}

		if(!is_writable($dir) || !is_readable($dir))
		{
			chmod($dir, 0777);
		}

		return $dir.'/';
	}

	//清除缓存目录里面的文件，说白了就是删除文件
	public function cleanCache($dir = null)
	{
		if(!$dir){
			$res = opendir($this->cacheDir);
			$path = $this->cacheDir;
		}else{
			$res = opendir($dir);
			$path = $dir;
		}

		while($file = readdir($res))
		{
			if($file == '.' || $file == '..')
			{
				continue;
			}

			chmod($path, 0777);

			if(is_dir($file))
			{
				$this->cleanCache($file);
			}else{
				$str = rtrim($path, '/').'/'.$file;
				var_dump($str);
				unlink($str);
			}
		}
	}
}