<?php
/**
 * memcacheq操作封装
 * https://github.com/hutushen222/memcacheq-php/blob/master/memcacheq.php
 */

class API_Item_Queue_MemcacheQ
{
    //memcache对象
    private static $memObj = null;
    //服务器
    private static $IP = array(
            '10.15.185.118'
    );
    private static $PORT = "22201";
    //socket连接的资源对象
    private static $_socket = null;
    private static $EOL     = "\r\n";
    
    public static function init(){
        if(self::$memObj)return true;
        
        if (class_exists("Memcache")) {
	        self::$memObj = new Memcache();
			foreach (self::$IP as $key=>$value){
	         	self::$memObj->addServer($value,self::$PORT,false);
	        }
        } else {
            die("警告：系统未安装memcache扩展");
        }
    }

    /**
     * 插入数据
     */
    public static function push($paramArr){
        $options = array(
            'key'      => false,  #队列名
            'value'    => false,  #插入队列的数据
        );
        if (is_array($paramArr))$options = array_merge($options, $paramArr);
        extract($options);

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
     */
    public static function pop($paramArr){
        $options = array(
            'key'      => false,  #队列名
        );
        if (is_array($paramArr))$options = array_merge($options, $paramArr);
        extract($options);
        
        if($key){
            self::init();
            return self::$memObj -> get($key);
        }
    }

     /**
     * 获得数据
     */
    public static function pops($paramArr){
        $options = array(
            'key'      => false,  #队列名
            'num'      => 2,      #多个数据
        );
        if (is_array($paramArr))$options = array_merge($options, $paramArr);
        extract($options);

        if($key){
            self::init();
            $data = array();
            for($i = 1; $i<=$num; $i++){
                $d = self::$memObj -> get($key);
                if(!$d)break;
                $tmpK = is_array($d) ? md5(serialize($d)) : md5($d);
                $data[$tmpK] = $d; #md5 为了防止重复，只保留1条
            }
            return $data;
        }
    }

    /**
     * 获得所有的可用队列
     */
    public static function getQueues($paramArr){
        $options = array(
            'sort'      => false,  #是否按照未处理，进行排序
            'immClose'  => true,   #是否马上关闭队列连接
        );
        if (is_array($paramArr))$options = array_merge($options, $paramArr);
        extract($options);

		$response = self::sendCommand('stats queue', array('END'));
        
        $outArr = array();
        $sortCntArr = array();
		foreach($response as $i => $line) {
			$queue = explode(' ', str_replace('STAT ', '', $line));
            $qName = $queue[0];
            list($iCnt,$oCnt) = explode("/", $queue[1]);
            $outArr[$qName] = array(
                'i' => $iCnt,
                'o' => $oCnt,
                'l' => $iCnt - $oCnt,
            );
            $sortCntArr[$qName] = $iCnt - $oCnt;
		}
        #关闭socket连接
        if($immClose){
             self::close();
        }
        if($sort){
            #按照未处理数进行排序
            arsort($sortCntArr);
            $outArr2 = array();
            foreach($sortCntArr as $k => $v){
                $outArr2[$k] = $outArr[$k];
            }
            $outArr = $outArr2;

           
        }
        return $outArr;
	}

    /**
     * 删除队列
     */
    public static function deleteQueue($paramArr){
        $options = array(
            'name'      => '',     #队列名称
        );
        if (is_array($paramArr))$options = array_merge($options, $paramArr);
        extract($options);
        if(!$name)return false;
        
		$response = self::sendCommand('delete ' . $name, array('DELETED', 'NOT_FOUND'), true);
        self::close();
		if (in_array('DELETED', $response)) {
			return true;
		}else{
            return false;
        }
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
