<?php
namespace Framework;

//写一个分页类

class Page
{
	//记录总条数
	protected $total;
	//每页显示几条
	protected $nums;
	//总页数
	protected $totalPages;
	//当前页码
	protected $currentPage;
	//上一页页码
	protected $prevPage;
	//下一页页码
	protected $nextPage;
	//首页页码
	protected $firstPage;
	//尾页页码
	protected $endPage;
	//url
	protected $url;
	//limit,传到数据库的limit
	protected $limit;


	//构造函数，初始化
	public function __construct($total, $nums)
	{
		$this->total = $total;
		$this->nums = $nums;

		$this->totalPages = $this->getTotalPages();
		$this->currentPage = $this->getCurrentPage();

		$this->getPrevPage();
		$this->getNextPage();
		$this->getFirstPage();
		$this->getEndPage();
		$this->getUrl();

	}

	//显示分页连接render();
	public function render()
	{
		return array(
			['first' => $this->getFirstUrl()],
			['prev' => $this->getPrevUrl()],
			['current' => $this->getCurrentUrl()],
			['next' => $this->getNextUrl()],
			['end' => $this->getEndUrl()]
			);
		//最后来调用这个函数，显示分页的情况
	}

	//计算总页数
	protected function getTotalPages()
	{
		return ceil($this->total / $this->nums);
	}

	//获取当前页码
	protected function getCurrentPage()
	{
		if(isset($_GET['page']) && intval($_GET['page']) > 0)
		{
			$this->currentPage = intval($_GET['page']);
			return $this->currentPage;
		}else{
			$this->currentPage = 1;
			return $this->currentPage;
		}
	}
	//获取上一页
	protected function getPrevPage()
	{
		$this->prevPage = $this->currentPage-1;
		if($this->prevPage < 1)
		{
			$this->prevPage = 1;
		}

		return $this->prevPage;
	}
	//获取下一页
	protected function getNextPage()
	{
		$this->nextPage = $this->currentPage + 1;
		return $this->nextPage;
	}

	//获取首页
	protected function getFirstPage()
	{
		$this->firstPage = 1;
		return $this->firstPage;
	}
	//获取尾页
	protected function getEndPage()
	{
		$this->endPage = $this->totalPages;
		return $this->endPage;
	}

	//获取当前页url
	protected function getCurrentUrl()
	{
		return $this->url.'&page='.$this->currentPage;
	}

	//获取上一页url
	protected function getPrevUrl()
	{
		return $this->url.'&page='.$this->prevPage;
	}

	//获取下一页url
	protected function getNextUrl()
	{
		return $this->url.'&page='.$this->nextPage;
	}

	//获取首页url
	protected function getFirstUrl()
	{
		return $this->url.'&page='.$this->firstPage;
	}
	//获取尾页url
	protected function getEndUrl()
	{
		return $this->url.'&page='.$this->endPage;
	}
	//生成limit记录的
	public function limit()
	{
		return ($this->currentPage - 1) * $this->nums.','.$this->nums;
	}

	//生成url地址
	protected function getUrl()
	{
		$url = $_SERVER['REQUEST_URI'];
		$parse = parse_url($url);
		$path = $parse['path'];
		$query = isset($parse['query']) ? $parse['query'] : false;
		if($query)
		{
			//这个是在有参数的时候，把page这个参数先给干掉，因为我们要重新拼接
			parse_str($query, $query);
			unset($query['page']);
			$uri = $parse['path'].'?'.http_build_query($query);
		}else{
			$uri = rtrim($parse['path'], '?').'?';
		}


		//智能识别https和http协议，还有端口号，他们也是拼接出来的。
		$protocal = (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';

		switch ($_SERVER['SERVER_PORT']) {
			case 80:
				$uri = $protocal.$_SERVER['SERVER_NAME'].$uri;
				break;
			case 443:
				$uri = $protocal.$_SERVER['SERVER_NAME'].$uri;
				break;

			default:
				$uri = $protocal.$_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].$uri;
				break;
		}

		$this->url = $uri;
	}

}


$page = new Page(102, 10);

//echo $page->getTotalPages();
//echo $page->getCurrentPage();

//echo $page->getPrevPage();

//echo $page->limit();

//echo $page->getUrl();

var_dump($page->render());