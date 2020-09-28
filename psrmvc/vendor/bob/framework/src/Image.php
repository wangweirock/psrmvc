<?php
namespace Framework;
//实现目标：水印、缩略图
//希望使用静态方法来完善这个类，使用静态方法快、效率高、用起来简单直接。
//Image::water(目标图片, 水印图片， 位置);
//静态方法，可以快速的让你封装工具类，并且使用很方便
//$image = new Image();
//Image::water('666.jpg', '360logo.png', 5, 'www', 100);
//Image::thumb('./666.jpg', 250, 400);
//版本帝，一天一更新，最大的价值就是发布了新版本，媒体才会报道你
class Image
{

	//路径
	static public $path = './';

	//构造方法
	public function __construct($path = './')
	{
		$this->path = rtrim($path, '/').'/';
	}

	//生成水印的方法
	static public function water($dstImg, $waterImg, $position = 9, $prefix = 'water_', $opacity = 50)
	{
		$dstImg = self::$path.$dstImg;
		//$waterImg = $this->path.$waterImg;

		if(!file_exists($dstImg))
		{
			exit('目标图片不存在！');
		}
		if(!file_exists($waterImg))
		{
			exit('水印图片不存在！');
		}

		//通过自己自定的函数来获取图片和水印图片的信息
		$dstImgInfo = self::getImageInfo($dstImg);
		$waterImgInfo = self::getImageInfo($waterImg);

		//检查图片大小的函数
		if(!self::checkSize($dstImgInfo, $waterImgInfo))
		{
			exit('水印图片大小不能超过目标图片的大小！');
		}

		//获取水印在目标图片中的位置
		$pos = self::getPosition($dstImgInfo, $waterImgInfo, $position);

		//创建图片资源，准备合并
		$dstImgRes = self::createImgRes($dstImg, $dstImgInfo);
		$waterImgRes = self::createImgRes($waterImg, $waterImgInfo);

		//合并图片，生成新图片
		$newImgRes = self::merge($dstImgRes, $waterImgRes, $waterImgInfo, $pos, $opacity);

		$newPath = self::$path.$prefix.uniqid().$dstImgInfo['name'];

		//最后生成新的图片
		self::saveImg($newImgRes, $newPath, $dstImgInfo);

		imagedestroy($dstImgRes);
		imagedestroy($waterImgRes);
		imagedestroy($newImgRes);

		return $newPath;
	}

	//获取图片信息
	static private function getImageInfo($imgPath)
	{
		$info = getimagesize($imgPath);

		return array(
				'width' => $info[0],
				'height' => $info[1],
				'mime' => $info['mime'],
				'name' => basename($imgPath)
			);
	}

	//检查目标图片与水印图片的大小是不是匹配的
	static private function checkSize($dstImgInfo, $waterImgInfo)
	{
		if($waterImgInfo['width'] > $dstImgInfo['width'] || $waterImgInfo['height'] > $dstImgInfo['height'])
		{
			return false;
		}else{
			return true;
		}
	}

	static private function getPosition($dstImgInfo, $waterImgInfo, $position)
	{
		switch($position)
		{
			case 1:
				$x = 0;
				$y = 0;
				break;
			case 2:
				//$x = ceil(($dstImgInfo['width'] / 2) - ($waterImgInfo['width'] / 2));
				$x = ceil(($dstImgInfo['width'] - $waterImgInfo['width']) / 2);
				$y = 0;
				break;
			case 3:
				$x = $dstImgInfo['width'] - $waterImgInfo['width'];
				$y = 0;
				break;

			case 4:
				$x = 0;
				$y= ceil(($dstImgInfo['height'] - $waterImgInfo['height']) / 2);
				break;
			case 5:
				$x = ceil(($dstImgInfo['width'] - $waterImgInfo['width']) / 2);
				$y= ceil(($dstImgInfo['height'] - $waterImgInfo['height']) / 2);
				break;

			case 6:
				$x = $dstImgInfo['width'] - $waterImgInfo['width'];
				$y= ceil(($dstImgInfo['height'] - $waterImgInfo['height']) / 2);
				break;

			case 7: 
				$x = 0;
				$y = $dstImgInfo['height'] - $waterImgInfo['height'];
				break;
			case 8:
				$x = ceil(($dstImgInfo['width'] - $waterImgInfo['width']) / 2);
				$y = $dstImgInfo['height'] - $waterImgInfo['height'];
				break;

			case 9:
				$x = $dstImgInfo['width'] - $waterImgInfo['width'];
				$y = $dstImgInfo['height'] - $waterImgInfo['height'];
				break;
		}

		return [
			'x' => $x,
			'y' => $y
		];
	}

	//根据图片的mime类型创建图片资源
	static private function createImgRes($imgPath, $imgInfo)
	{
		switch ($imgInfo['mime']) {
			case 'image/jpg':
			case 'image/jpeg':
			case 'image/pjpeg':
				$res = imagecreatefromjpeg($imgPath);
				break;
			case 'image/png':
			case 'image/x-png':
				$res = imagecreatefrompng($imgPath);
				break;
			case 'image/gif':
				$res = imagecreatefromgif($imgPath);
				break;
			case 'image/bmp':
			case 'image/wbmp':
				$res = imagecreatefromwbmp($imgPath);
				break;
			default:
				exit('您的破图片不支持！');
				break;
		}
		return $res;
	}

	static private function merge($dstImgRes, $waterImgRes, $waterImgInfo, $pos, $opacity)
	{
		imagecopymerge($dstImgRes, $waterImgRes, $pos['x'], $pos['y'], 0, 0, $waterImgInfo['width'], $waterImgInfo['height'], $opacity);
		return $dstImgRes;
	}

	static private function saveImg($newImgRes, $newPath, $dstImgInfo)
	{
		switch ($dstImgInfo['mime']) {
			case 'image/jpg':
			case 'image/jpeg':
			case 'image/pjpeg':
				$res = imagejpeg($newImgRes, $newPath);
				break;
			case 'image/png':
			case 'image/x-png':
				$res = imagepng($newImgRes, $newPath);
				break;
			case 'image/gif':
				$res = imagegif($newImgRes, $newPath);
				break;
			case 'image/bmp':
			case 'image/wbmp':
				$res = imagewbmp($newImgRes, $newPath);
				break;
			default:
				exit('您的破图片不支持！');
				break;
		}
	}

	//生成缩略图的方法
	static public function thumb($img, $width, $height, $prefix = 'thumb_')
	{
		if(!file_exists($img))
		{
			exit('破图片不存在！');
		}

		//获取图片的信息
		$imgInfo = self::getImageInfo($img);
		//创建图片资源
		$imgRes = self::createImgRes($img, $imgInfo);

		//获取缩略图新的大小
		$newSize = self::getNewSize($width, $height, $imgInfo);

		$newImgRes = self::kidOfImage($imgRes, $newSize, $imgInfo);

		//拼装缩略后的图片名字
		$newPath = $prefix.$newSize['width'].'X'.$newSize['height'].'_'.$imgInfo['name'];

		self::saveImg($newImgRes, $newPath, $imgInfo);

		imagedestroy($newImgRes);

		return $newPath;
	}

	//重新计算缩略后的图成比例的宽高问题
	static private function getNewSize($width, $height, $imgInfo)
	{
		$size = [];

		//判断缩略后的图的宽度是不是比原图小
		if($width < $imgInfo['width'])
		{
			$size['width'] = $width;
		}

		//判断缩略后的图的高度是不是比原图小
		if($height < $imgInfo['height'])
		{
			$size['height'] = $height;
		}

		//即使小了还不行，还得成比例。
		//如果缩略后的宽度是合适的，那么按照比例重新设置缩略后的高度
		if( $imgInfo['width'] * $size['width'] > $imgInfo['height'] * $size['height'] )
		{
			$size['height'] = $imgInfo['height'] * $size['width'] / $imgInfo['width'];
		}else{
			//缩略后的高度是合适的，按照比例重新设置缩略后宽度。
			$size['width'] = $imgInfo['width'] * $size['height'] / $imgInfo['height'];
 		}

 		return $size;
	}

	//解决可能出现的黑边问题
	static private function kidOfImage($srcImg, $size, $imgInfo)
	{
		$newImg = imagecreatetruecolor($size["width"], $size["height"]);		
		$otsc = imagecolortransparent($srcImg);
		if ( $otsc >= 0 && $otsc < imagecolorstotal($srcImg)) {
			 $transparentcolor = imagecolorsforindex( $srcImg, $otsc );
				 $newtransparentcolor = imagecolorallocate(
				 $newImg,
				 $transparentcolor['red'],
				 $transparentcolor['green'],
				 $transparentcolor['blue']
			 );

			 imagefill( $newImg, 0, 0, $newtransparentcolor );
			 imagecolortransparent( $newImg, $newtransparentcolor );
		}

		imagecopyresized( $newImg, $srcImg, 0, 0, 0, 0, $size["width"], $size["height"], $imgInfo["width"], $imgInfo["height"] );
		imagedestroy($srcImg);
		return $newImg;
	}



		// 获取图片信息
		// 获取位置信息（123 456 789）
		// 创建图片资源
		// 合并图片资源
		// 重新命名
		// 保存图片

		// 生成缩略图的方法

		// 获取图片信息
		// 获取需要缩放的图片大小
		// 创建图片资源
		// 处理一下可能出现的黑边问题（不需要了解）
		// 重新命名
		// 保存图片


}


//写一遍
	

	





