<?php
namespace Framework;

//数据库模型类
class Model
{
	//sql语句
	protected $sql;
	//主机地址
	protected $host;
	//数据库用户名
	protected $user;
	//数据库密码
	protected $password;
	//数据端口号
	protected $port = 3306;
	//字符集
	protected $charset = 'utf8';
	//数据库名
	protected $dbName;

	//数据表
	protected $dbTable;
	
	//表前缀
	protected $dbPrefix;
	//数据库连接资源
	protected $res;

	protected $options = [];

	protected $fields = [];

	protected $cachePath;

	protected $dbCacheIs;

	//初始化我们可以被初始化的东西
	public function __construct($config = null)
	{
		if($config == null)
		{
			$config = $GLOBALS['config'];
		}
		$this->host = $config['HOST'];
		$this->user = $config['USER'];
		$this->password = $config['PASSWORD'];
		$this->port = $config['DB_PORT'];
		$this->charset = $config['DB_CHARSET'];
		$this->dbName = $config['DB_NAME'];
		$this->dbPrefix = $config['DB_PREFIX'];
		$this->cachePath = $config['DB_CACHE_PATH'];
		$this->dbCacheIs = $config['DB_CACHE_IS'];
		$this->dbTable = $this->dbPrefix.str_replace('model\\', '', strtolower(get_class($this)));

		$this->connect();
		$this->initOptions();
		$this->getFields();

	}

	//连接数据库
	protected function connect()
	{
		$this->res = mysqli_connect($this->host, $this->user, $this->password, $this->dbName, $this->port);
		if(!$this->res)
		{
			exit('<font color="red">MYSQL ERROR：'.mysqli_connect_error().'</font>');
		}

		mysqli_set_charset($this->res, $this->charset);
		return $this->res;
	}

	//获取结果集
	public function query($sql = null)
	{
		if($sql  == null){
			$sql = $this->sql;
		}
		$result = mysqli_query($this->res, $sql);
		if($result)
		{
			$data = [];
			while($row = mysqli_fetch_assoc($result))
			{
				$data[] = $row;
			}
			return $data;
		}else{
			exit('<font color="red">MYSQL ERROR：'.mysqli_error($this->res).'</font>');
		}
	}

	//仅执行sql语句，不返回结果集
	public function exec($sql = null)
	{
		$sql = $sql ? $sql : $this->sql;

		$result = mysqli_query($this->res, $sql);
		if($result)
		{
			return $result;
		}else{
			exit('<font color="red">MYSQL ERROR：'.mysqli_error($this->res).'</font>');
		}
	}


	//查询多条记录
	public function select($where = null)
	{
		if($where != null)
		{
			$this->where($where);
		}
		$sql = 'SELECT %FIELDS% FROM '.$this->dbTable.' %WHERE% %GROUP% %HAVING% %ORDER% %LIMIT% ';
		$this->sql = str_replace(
			['%FIELDS%', '%WHERE%', '%GROUP%', '%HAVING%', '%ORDER%', '%LIMIT%'],
			[$this->options['fields'], $this->options['where'], $this->options['group'], $this->options['having'], $this->options['order'], $this->options['limit']], $sql);
		$this->initOptions();
		return $this->query();
	}

	//查询单条记录
	public function find($where = null)
	{
		if($where != null)
		{
			$this->where($where);
		}
		$sql = 'SELECT %FIELDS% FROM '.$this->dbTable.' %WHERE% %GROUP% %HAVING% %ORDER% %LIMIT% ';
		$this->sql = str_replace(
			['%FIELDS%', '%WHERE%', '%GROUP%', '%HAVING%', '%ORDER%', '%LIMIT%'],
			[$this->options['fields'], $this->options['where'], $this->options['group'], $this->options['having'], $this->options['order'], $this->options['limit']], $sql);
		$data = $this->query();
		$this->initOptions();
		return $data[0];
	}

	//添加数据
	public function add($data = array())
	{
		if(is_array($data))
		{
			$fields = '';
			$values = '';
			foreach ($data as $key => $value) {
				if(in_array($key, $this->fields)){
					$fields .= $key.',';
					$values .= '\''.$value.'\',';
				}
			}
			$fields = rtrim($fields, ',');
			$values = rtrim($values, ',');
		}else{
			return false;
		}

		$sql = 'INSERT INTO '.$this->dbTable.'(%FIELDS%) VALUES(%VALUES%) ';

		$this->sql = str_replace(
			[
				'%FIELDS%', '%VALUES%'
			], [
				$fields, $values
			], $sql);
		$this->initOptions();
		return $this->exec();

		//记住，这里可以封装一下获取上一个sql插入的id
	}

	//删除数据
	public function del($where = null)
	{
		if($where)
		{
			$this->where($where);
		}

		$sql = "DELETE FROM ".$this->dbTable.' %WHERE%';

		$this->sql = str_replace(
			[
                '%WHERE%'
			], [
				$this->options['where']
			], $sql);
		$this->initOptions();
		return $this->exec();
	}

	//保存更新
	public function save($data)
	{
		$set = ' SET ';
		if(is_array($data)){
			foreach ($data as $key => $value) {
				if(in_array($key, $this->fields))
				{
					$set .= $key.'=\''.$value.'\',';
				}
			}
			$set = rtrim($set, ',');
		}else{
			exit('你不嫌麻烦吗？');
		}

		$sql = 'UPDATE '.$this->dbTable.' %SET% %WHERE%';
		$this->sql = str_replace(
			[	
				'%SET%', '%WHERE%'
			],
			[
				$set, $this->options['where']
			], $sql);
		$this->initOptions();
		return $this->exec();
	}

	//求和
	public function sum()
	{

	}

	//总数
	public function count()
	{

	}

	//求最大值
	public function max()
	{

	}

	//求最小值
	public function min()
	{

	}

	//求平均数
	public function avg()
	{

	}

	//计算where条件
	public function where($where)
	{
		if(is_array($where))
		{
			$whereSql = '';
			foreach ($where as $key => $value) {
				if(in_array($key, $this->fields))
				{
					$whereSql .= $key.'=\''.$value.'\' AND ';
				}
			}
			$whereSql = ' where '.rtrim($whereSql, ' AND');
			$this->options['where'] = $whereSql;
		}else{
			$this->options['where'] = ' '.$where.' ';
		}
		return $this;
	}

	//生成order部分的语句
	public function order($order)
	{
		$orderSql = ' ORDER BY '.$order;
		$this->options['order'] = $orderSql;
		return $this;
	}

	//生成limit部分的语句
	public function limit($limit)
	{
		$limitSql = ' LIMIT '.$limit.' ';
		$this->options['limit'] = $limitSql;
		return $this;
	}

	//生成group部分的语句
	public function group($group)
	{
		$this->options['group'] = ' GROUP BY '.$group;
		return $this;
	}

	
	public function having($having)
	{
		$this->options['having'] = ' HAVING '.$having;
		return $this;
	}

	public function fields($fields)
	{
		$fieldsSql = '';

		if(is_array($fields))
		{	
			foreach ($fields as $value) {
				if(in_array($value, $this->fields))
				{
					$fieldsSql .= ' '.$value.',';
				}
			}

			$fieldsSql = rtrim($fieldsSql, ',').' ';
		}else{
			$fieldsSql = ' '.$fields.' ';
		}
		$this->options['fields'] = $fieldsSql;
		return $this;
	}

	public function table($table)
	{
		$this->dbTable = ' `'.$this->dbPrefix.$table.'` ';
		return $this;
	}

	public function getFields()
	{
		if(file_exists($this->cachePath.$this->dbTable.'.php') && $this->dbCacheIs)
		{
			$this->fields = include($this->cachePath.$this->dbTable.'.php');
			return $this->fields;

		}else{

			$this->sql = 'DESC '.$this->dbTable;
			$fields = $this->query();

			if(count($fields) > 0)
			{	
				$newFields = [];
				foreach ($fields as $value) {
					if($value['Key'] == 'PRI')
					{
						$newFields['_PK'] = $value['Field'];
					}
					$newFields[] = $value['Field'];
				}

				file_put_contents($this->cachePath.$this->dbTable.'.php', '<?php return '.var_export($newFields, true).';');

				$this->fields = $newFields;
				return $this->fields;
			}else{
				exit($this->dbTable.'没有字段或者表不存在！');
			}
		}
		
	}

	public function getLastSql()
	{
		return $this->sql;
	}

	//获取最近一条插入的自增id
	public function getInsertId()
	{
		return mysqli_insert_id($this->res);
	}

	//初始化我们的sql数组
	protected function initOptions()
	{
		$this->options = [
			'table_name' => '',
			'where'      => '',
			'order'      => '',
			'fields'     => '*',
			'having'     => '',
			'group'      => '',
			'limit'      => ''
		];
	}

	public function __call($method, $args)
	{
		if(substr($method, 0, 5) == 'getBy')
		{
			$field = strtolower(substr($method, 5));
			$sql = 'SELECT * FROM user WHERE '.$field.'=\''.$args[0].'\' LIMIT 1';
		}
	}

}