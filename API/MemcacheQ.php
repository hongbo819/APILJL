<?php
/**
 * 
 * memcacheq操作封装,就是在memcache的基础上把端口换了,把set,get操作进行了下重新
 * 
 * 
 */

class API_MemcacheQ
{
    //memcache对象
    private static $memObj;
    //服务器
    private static $IP = array(
            '10.15.185.118'
    );
    private static $PORT = "22201";
    //socket连接的资源对象
    private static $_socket = null;
    private static $EOL     = "\r\n";
    
    public static function init()
    {
        if (class_exists("Memcache")) {
	        self::$memObj = new Memcache();
			foreach (self::$IP as $key=>$value)
	        {
	         	self::$memObj->addServer($value,self::$PORT,false);
	        }
        } else {
            die("警告：系统未安装memcache服务");
        }
    }

    /**
     * 插入数据
     *
     */
    public static function push($key, $value)
    {
        self::init();
    	if ($key) {
	        if (self::$memObj -> set($key, $value)) {
	            return true;
	        }
    	}
    	return false;
    }

    /**
     * 获得数据
     *
     * @param key $key
     */
    public static function pop($key)
    {
        self::init();
        if ($re = self::$memObj -> get($key)) {
            return $re;
        } else {
            return false;
        }
    }

    /**
     * 获得所有的可用队列
     */
    public static function getQueues(){
		#$this->_queues = array();

		$response = self::sendCommand('stats queue', array('END'));
        
        $outArr = array();
		foreach($response as $i => $line) {
			$queue = explode(' ', str_replace('STAT ', '', $line));
            $qName = $queue[0];
            list($iCnt,$oCnt) = explode("/", $queue[1]);
            $outArr[$qName] = array(
                'i' => $iCnt,
                'o' => $oCnt,
                'l' => $iCnt - $oCnt,
            );
		}
        print_r($outArr);
        #return $outArr;

		#return $this->_queues;
	}
    /**
     * 关闭连接，释放资源
     */
    public static function close(){

		if (is_resource(self::$_socket)) {
			$cmd = 'quit' . self::$EOL;
			fwrite(self::$_socket, $cmd);
			fclose(self::$_socket);
            self::$_socket = null;
		}

    }

    /**
     * 发送Socket命令的方式与memcacheq通讯
     */
    private static function sendCommand($command, array $terminator, $include_term=false)
	{
		if (!is_resource(self::$_socket)) {
			self::$_socket = fsockopen(self::$IP[0], self::$PORT, $errno, $errstr, 10);
		}
		if (self::$_socket === null) {
			return false;
		}

		$response = array();

		$cmd = $command . self::$EOL;
		fwrite(self::$_socket, $cmd);

		$continue_reading = true;
		while (!feof(self::$_socket) && $continue_reading) {
			$resp = trim(fgets(self::$_socket, 1024));
			if (in_array($resp, $terminator)) {
				if ($include_term) {
					$response[] = $resp;
				}
				$continue_reading = false;
			} else {
				$response[] = $resp;
			}
		}

		return $response;
    }
    
 
    
}
