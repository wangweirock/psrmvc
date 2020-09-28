<?php
namespace Framework;

/**
 * 这是一个俺写的验证码类，牛
 */
class Verify
{
	private $verifyCode;	//验证码字符串
	public $verifyNums; //验证码字符串个数
	public $verifyType; //验证码的字符类型（1.数字、2.字母的、3.混合的）

	public $bgColor;
	public $fontColor;

	public $width;	//图片的宽度
	public $height;	//图片的高度
	public $imgType; //验证码图片类型(jpg、jpeg、png、bmp....)

	private $res;


	//构造函数，初始化一些可以被初始化的参数
	public function __construct($width = 100, $height = 50, $imgType = 'png', $verifyNums = '4', $verifyType = 3)
	{
		$this->width = $width;
		$this->height = $height;
		$this->imgType = $imgType;
		$this->verifyNums = $verifyNums;
		$this->verifyType = $verifyType;

		$this->verifyCode = $this->createVerifyCode(); //初始化，随机生成验证码函数
	}

	//随机生成验证码
	private function createVerifyCode()
	{
		switch ($this->verifyType) {
			case 1:
				//生成纯数字的
				//time();
				$str = join('', array_rand(range(0, 9), $this->verifyNums));
				break;
			case 2:
				//纯字母的
				$str = implode('', array_rand(array_flip(range('a', 'z')), $this->verifyNums));

				break;

			case 3:
				//生成数字+字母的
				$words = 'abcdefghjkmnoprstuvwxyABCDEFGHIJLMNPQRSTUVWXY3456789';
				$str = substr(str_shuffle($words), 0, $this->verifyNums);
				break;

		}

		return $str;
	}


	//开始准备生成图片
	public function show()
	{
		$this->createImg();	//创建图片资源
		$this->fillBg();	//填充背景色
		$this->fillPix();	//填充干扰点
		$this->fillArc();	//填充干扰弧线
		$this->writeFont();	//写字
		$this->outPutImg();	//输出图片
	}

	//创建图片资源
	private function createImg()
	{
		//echo '已经创建好图片资源<br>';
		$this->res = imagecreatetruecolor($this->width, $this->height);
	}

	private function fillBg()
	{
		imagefill($this->res, 0, 0, $this->setDarkColor());
	}

	private function fillPix()
	{
		$nums = ceil(($this->width * $this->height) / 20);
		for($i = 0;$i < $nums; $i++) {
			imagesetpixel($this->res, mt_rand(0, $this->width), mt_rand(0, $this->height), $this->setDarkColor());
		}
	}

	//随机画10条弧线
	private function fillArc()
	{
		for($i = 0; $i < 10; $i++) {
			imagearc(
				$this->res, 
				mt_rand(10, $this->width - 10), 
				mt_rand(5, $this->height - 5), 
				mt_rand(0, $this->width), 
				mt_rand(0, $this->height), 
				mt_rand(0, 180), 
				mt_rand(181, 360), 
				$this->setDarkColor());
		}
	}

	//写文字
	private function writeFont()
	{
		$every = ceil($this->width / $this->verifyNums);
		for($i = 0; $i < $this->verifyNums; $i++){
			$x = mt_rand(($every * $i) + 5, $every * ($i + 1) - 10);
			$y = mt_rand(10, $this->height - 15);
			imagechar($this->res, 6, $x, $y, $this->verifyCode[$i], $this->setLightColor());
		}

	}
	//输出图片资源
	private function outPutImg()
	{
		header('Content-type:image/'.$this->imgType);
		//var_dump('Content-type:image/'.$this->imgType);
		$func = 'image'.$this->imgType;
		$func($this->res);
	}

	//随机生成深色
	private function setDarkColor()
	{
		return imagecolorallocate($this->res, mt_rand(130, 255), mt_rand(130, 255), mt_rand(130, 255));
	}

	//随机生成亮的颜色
	private function setLightColor()
	{
		return imagecolorallocate($this->res, mt_rand(0, 130), mt_rand(0, 130), mt_rand(0, 130));
	}

	//设置一下验证码字符串只能调用，不能修改
	public function __get($name)
	{
		if($name == 'verifyCode') {
			return $this->verifyCode;
		}
	}

	//析构方法，自动销毁图片资源
	public function __destruct()
	{
		imagedestroy($this->res);
	}

}


$verify = new Verify(100, 50, 'jpeg', 4, 3);
//var_dump($verify->verifyCode);

$verify->show();

//var_dump(join('', array_rand(range(0, 9), 4)));

//var_dump(implode('', array_rand(array_flip(range('a', 'z')), 4)));
//$words = 'abcdefghjkmnoprstuvwxyABCDEFGHIJLMNPQRSTUVWXY3456789';
//var_dump(substr(str_shuffle($words), 0, 4));



function chuiNiu()
{
	echo '谁喜欢吹牛,爱谁谁<br>';
}

$func = 'chuiNiu';

$func();
