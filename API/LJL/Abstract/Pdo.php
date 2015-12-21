<?php
/**
* 
* @copyright (c) {date}
*/

abstract class API_LJL_Abstract_Pdo
{
	/**
	* 当前数据库链接
	* 
	* @var PDO
	*/
	protected $db;
	
	/**
	* 主数据库链接
	* 
	* @var PDO
	*/
	protected $master;
	
	/**
	* 从数据库链接
	* 
	* @var PDO
	*/
	protected $slave;
	
	/**
	* 是否强制主库
	* 
	* @var boolean
	*/
	protected $forceReadMaster = false;
	
	/**
	* 数据库字符集
	* 
	* @var string
	*/
	protected $charset = '';
	
	/**
	* 数据库用户名
	* 
	* @var string
	*/
	protected $username = 'root';
	
	/**
	* 数据库密码
	* 
	* @var string
	*/
	protected $password;
	
	/**
	* 数据库引擎
	* 
	* @var string
	*/
	protected $engine = 'mysql';
	
	/**
	* SQL语句注释
	* 
	* @var string
	*/
	protected $sqlComment = '';
	
	/**
	* 是否PING
	* 
	* @var mixed
	*/
	protected $ping = true;
    
    /**
     * 是否返回结果集总数
     * @var bool 
     */
    protected $_returnTotal = false;
	
	/**
	* 数据库单例
	* 
	* @var LJL_Abstract_Pdo
	*/
	protected static $_instance = array();
	
	public function __construct()
	{
		$this->init();
	}
	
	private function init()
	{
		if (!empty($this->servers['engine'])) {
			$this->engine = $this->servers['engine'];
		}
		
		if (defined('DB_CHARSET')) {
			$this->charset = DB_CHARSET;
		}
		
		if (defined('DB_USERNAME')) {
			$this->username = DB_USERNAME;
		}
		
		if (defined('DB_PASSWORD')) {
			$this->password = DB_PASSWORD;
		}
		
		if (!empty($this->servers['charset'])) {
			$this->charset = $this->servers['charset'];
		}
		
		if (!empty($this->servers['username'])) {
			$this->username = $this->servers['username'];
		}
		
		if (isset($this->servers['password'])) {
			$this->password = $this->servers['password'];
		}
	}
	
	public static function instance($dbName = null)
	{
		$dbName = $dbName ? $dbName : get_called_class();
		
		if (empty($dbName)) {
			return false;
		}
		
		if (substr($dbName, 0, 2) != 'Db' && substr($dbName, 0, 6) != 'API_Db') {
			return false;
		}
		
		if (!isset(self::$_instance[$dbName])) {
			self::$_instance[$dbName] = new $dbName();
			#self::$_instance[$dbName]->query('SET SESSION WAIT_TIMEOUT=1');
		}
		return self::$_instance[$dbName];
	}
	
	/**
	* 强制从写库读取
	* @return LJL_Abstract_Pdo
	*/
	public function forceReadMaster()
	{
		$this->forceReadMaster = true;
		return $this;
	}
	
	/**
	* 不允许PING
	* @return LJL_Abstract_Pdo
	*/
	public function noPing()
	{
		$this->ping = false;
		return $this;
	}
	
	/**
	* 创建数据库链接
	* 
	* @param enum $type {master|slave}
	* @return PDO
	*/
	protected function createDbConn($dbType = 'master')
	{
		if (empty($this->$dbType)) {
			$dns = $this->engine . ':dbname=' . $this->servers[$dbType]['database'] .
			';host=' . $this->servers[$dbType]['host'];
			try {
				$this->$dbType = new PDO($dns, $this->username, $this->password);
				if ($this->charset) {
					$this->$dbType->exec("SET NAMES '{$this->charset}'");
				}
			} catch (PDOException $e) {
				trigger_error($e->getMessage(), E_USER_WARNING);
				return false;
			}
		}
		$this->db =& $this->$dbType;
		return true;
	}
	
	protected function chooseDbConn($sql)
	{
		if (empty($sql)) {
			return false;
		}
		$sql = trim($sql);
		
		//检查SQL是否是select查询
		if (stripos($sql, 'SELECT') === 0 && !$this->forceReadMaster) {
			if (!$this->createDbConn('slave')) {
				$this->createDbConn('master');
			}
		} else {
			$this->createDbConn('master');
		}
        
        if (empty($this->db)) {
            throw new LJL_Exception('Dose not exist instance of DB server!');
        }
		
		return true;
	}
	
	protected function ping()
	{
		error_reporting(0);
		try {
			if (!$this->db->query('SELECT 1')) {
				throw new PDOException('db server has gone away!');
			}
		} catch (PDOException $e) {
			return  false;
		}
		return true;
	}
	
	/**
	* 查询
	* 
	* @param string $sql
	* @return PDOStatement
	*/
	public function query($sql = '')
	{
        static $reconnectNum = 0;
		#添加日志功能,重置时间
		if(IS_DEBUGGING)LJL_Log::resetTime();
		$this->chooseDbConn($sql);
		$sql .= $this->sqlComment;
		$query = $this->db->query($sql);
		if (empty($query)) {
			$error = $this->errorInfo();
            if ($reconnectNum < 3 && $error[0] == 'HY000' && in_array($error[1],array(2003,2004,2006,2055,2013))) {
                $this->db = null;
                $reconnectNum ++;
                if ($reconnectNum > 1) {
                    usleep(50000);
                }
                return $this->query($sql);
            }
			trigger_error($error[2], E_USER_WARNING);
		}
        $reconnectNum = 0;

		#添加日志功能
        /*
		if(IS_DEBUGGING){
			$nowTime    = date("H:i:s");
			$nowUrl     = str_replace("_check_mysql_query=", "", $_SERVER["REQUEST_URI"]);
			$sql        = str_replace("\n", "",$sql);
			$sql        = preg_replace("#\s{2,}#", " ", $sql);
			$logContent = "{$nowUrl} [{$nowTime}][".$this->servers['slave']['host']." - ".$this->servers['slave']['database']."] SQL:".$sql." \n";
			LJL_Log::checkUriAndWrite(array('message'=>$logContent , 'paramName'=>'_check_mysql_query','recTime'=>true));
		}*/
		return $query;
	}
	
	/**
	* 获取一行中第一个字段值
	* 
	* @param string $sql
	* @return PDOStatement
	*/
	public function getOne($sql)
	{
		$query = $this->query($sql);
		return ($query instanceof PDOStatement) ? $query->fetchColumn() : null;
	}
	
	/**
	* 获取一行
	* 
	* @param string $sql
	* @param enum $fetchStyle
	* @return PDOStatement
	*/
	public function getRow($sql, $fetchStyle = PDO::FETCH_ASSOC)
	{
		$query = $this->query($sql);
		$row = ($query instanceof PDOStatement) ? $query->fetch($fetchStyle) : null;

		return $row;
	}
    
    /**
     * 获取一列
     * @param string $sql SQL语句
     * @param string|int $column 获取哪个字段，为数字则按下标提取，为字符则按字段名提取
     */
    public function getCol($sql, $column = 0)
    {
        $query = $this->query($sql);
        $fetchStyle = is_numeric($column) ? PDO::FETCH_NUM : PDO::FETCH_ASSOC;
        $results = false;
        while ($row = $query->fetch($fetchStyle)) {
            $results[] = $row[$column];
        }
        return $results;
    }

	/**
	* 获取全部
	* 
	* @param string $sql
	* @param enum $fetchStyle
	* @return PDOStatement
	*/
	public function getAll($sql, $fetchStyle = PDO::FETCH_ASSOC)
	{
        if ($this->_returnTotal && stripos(trim($sql), 'SELECT') === 0) {
            $sql = 'SELECT SQL_CALC_FOUND_ROWS ' . substr($sql, 7);
            $this->_returnTotal = false;
        }
		$query = $this->query($sql);
		$result = ($query instanceof PDOStatement) ? $query->fetchAll($fetchStyle) : null;

		return $result;
	}
    
    /**
     * 获取成对数据
     * @param string $sql SQL语句
     * @param string $keyName 用于数组KEY的字段名
     * @param string $valName 用于数组value的字段名
     * @return array ($keyName => $valName)
     */
    public function getPairs($sql, $keyName = '', $valName = '')
    {
        $query = $this->query($sql);
        $pairs = array();
        
        if (!($keyName && $valName)) {
            while($row = $query->fetch(PDO::FETCH_NUM)) {
                $pairs[$row[0]] = $row[1];
            }
        } else {
            while($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $pairs[$row[$keyName]] = $row[$valName];
            }
        }
        
        return $pairs;
    }
    
    /**
     * 回调处理每一行
     * @param string $sql SQL语句
     * @param callback $callback 回调函数
     * @return bool 
     */
    public function execAll($sql, $callback)
    {
        $query = $this->query($sql);
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            call_user_func($callback, $row);
        }
        return true;
    }
    
   /**
     * 结果集影响的行数
     * @return int
     */
    public function getTotal()
    {
        return $this->getOne('SELECT FOUND_ROWS()');
    }

    /**
     * 设置是否返回结果集总数
     * @param bool $return
     * @return LJL_Abstract_Pdo
     */
    public function setReturnTotal($return = true)
    {
        $this->_returnTotal = $return;
        return $this;
    }

	public function beginTransaction()
	{
		return ($this->master instanceof PDO) ? $this->master->beginTransaction() : false;
	}

	public function commit()
	{
		return ($this->master instanceof PDO) ? $this->master->commit() : false;
	}

	public function errorCode()
	{
		return ($this->db instanceof PDO) ? $this->db->errorCode() : false;
	}

	public function errorInfo()
	{
		return ($this->db instanceof PDO) ? $this->db->errorInfo() : false;
	}

	public function exec($statement = '')
	{
		$this->chooseDbConn($statement);
		$ret = ($this->db instanceof PDO) ? $this->db->exec($statement) : false;
		$this->forceReadMaster = false;

		return $ret;
	}

	public function lastInsertId()
	{
		return ($this->master instanceof PDO) ? $this->master->lastInsertId() : false;
	}

	public function prepare($statement = '', array $options = array())
	{
		$this->chooseDbConn($statement);
		$ret = ($this->db instanceof PDO) ? $this->db->prepare($statement, $options) : false;
		if (true == $this->forceReadMaster)
		{
			$this->forceReadMaster = false;
		}

		return $ret;
	}
	
	public function quote($string, $parameterType = PDO::PARAM_STR)
	{
		return ($this->db instanceof PDO) ? $this->db->quote($string, $parameterType) : false;
	}

	public function rollBack()
	{
		return ($this->master instanceof PDO) ? $this->master->rollBack() : false;
	}

	public function setAttribute($attribute, $value)
	{
		return ($this->db instanceof PDO) ? $this->db->setAttribute($attribute, $value) : false;
	}

	public function getAvailableDrivers()
	{
		return ($this->db instanceof PDO) ? $this->db->getAvailableDrivers() : false;
	}

	public function getAttribute($attribute)
	{
		return ($this->db instanceof PDO) ? $this->db->getAttribute($attribute) : false;
	}
} 
