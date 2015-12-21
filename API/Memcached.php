<?
/***********************************************************
 * memcache类，
***********************************************************/
class API_Memcached 
{
    public static $_memObjs = array(); #缓存对象
    public static $_ipArr = array(

        #默认用memcache
        'Default' => array(
            'memcached' => false,
            'ip' => array(#
                    '10.15.184.30',
                    '10.15.184.104',
                    '10.15.184.110',
                    '10.15.184.124',
                    '10.15.184.125',
                    '10.15.184.199',
                    '10.15.184.209',
            )
        ),
        
        #互动专用memcache
        'User'    => array(
            'memcached' => true,
            'ip' => array(#
                    '10.15.184.56',
                    '10.15.184.57',
                    '10.15.184.58',
                    '10.15.184.59',
                    '10.15.184.190',
                    '10.15.184.210',
                    '10.15.184.215'
            )
        ),

    );
    
    public static  function getIpArr($link){
        if($link){
            return self::$_ipArr[$link];
        }else{
            return self::$_ipArr;
        }
    }

    /**
     * 获得链接对象
     */
    public static function getLink($link){
      
        if(!isset(self::$_ipArr[$link]))$link= 'Default';
        if(!isset(self::$_memObjs[$link])){
            $isMemcached = false; #表明是memcache的算法
            if(self::$_ipArr[$link]['memcached']){
                try{
                    $mem = new Memcached();
                    $isMemcached = true;
                }catch(Exception $e){
                    $mem = new Memcache;
                }
            }else{
                $mem = new Memcache;
            }
            foreach (self::$_ipArr[$link]['ip'] as $value){
                $mem->addServer($value,11211,false);
            }

            self::$_memObjs[$link] = array(
                'memcached' => $isMemcached,
                'obj'       => $mem
            );
        }
        return self::$_memObjs[$link];
        
    }


	public static $mem;								#memcached类
	public static $CONNECT_OL; 						#是否已经链接memcached服务器
	public static $IP = array(
                    '10.15.184.56',
                    '10.15.184.57',
                    '10.15.184.58',
                    '10.15.184.59',
                    '10.15.184.190',
                    '10.15.184.210',
                    '10.15.184.215'
                    );
    public static $PORT = "11211";
	public static function init(){					#初始化
		self::$mem = new Memcache;
		foreach (self::$IP as $key=>$value)
        {
                self::$mem->addServer($value,11211,false);
        }
	}
	
	public static function connect($ip = "",$port = ""){	#链接memcached服务器
		self::init();
		if("" == $ip){
			$ip = self::$IP;
		}
		if("" == $port){
			$port = self::$PORT;
		}
		if(self::$mem->connect($ip,$port)){
			self::$CONNECT_OL = true;
			return 1;
		}else{
			return 0;
		}
		
	}
	
	public static function get($key){				#得到memcached内容
		self::init();
		if(!$key){
			return false;
		}
		$mem_key = md5($key);
		$mem_content = self::$mem->get($mem_key);
		return $mem_content;
	}
	
	public static function set($key,$value,$time=0){	#设置memcached内容
		if(!$key){
			return false;
		}
        if(!self::$mem) self::init();
		$mem_key = md5($key);
		self::$mem->set($mem_key,$value,0,$time);
	}
	
    /**
     * 设置多值
     *
     * @param key-value数组 $keyArr
     * @param 有效期 $time
     */
    public static  function setMulti($keyArr,$time=0){
        if(!is_array($keyArr)){
            return false;
        }
        foreach ($keyArr as $key=>$val){
            self::set($key,$val,$time);
        }
        return true;
    }

    /**
     * 获取多值
     *
     * @param array $keyArr
     */
    public static  function getMulti($keyArr){
        if(!is_array($keyArr)){
            return false;
        }
        $results_arr = array();
        foreach ($keyArr as $key){
            $val = self::get($key);
            $results_arr[$key] = $val;
        }
        return $results_arr;
    }

    /**
     * 删除一个或多个元素
     *
     * @param string/array $key
     */
    public static  function delete($key){
        if(!$key){
            return false;
        }
        return self::set($key,'');
    }  
    
    
    /**
     * 对值追加内容
     *
     * @param 键名 $key
     * @param 要追加的值 $value
     */
    public static  function append($key, $value){
        if(!$key){
            return false;
        }
        $cur_val = self::get($key);
        $new_val = $cur_val.$value;
        return self::set($key,$new_val);
    }
    
    /**
     * 在获取一个值的同时用另一个新的值覆盖当前key的值
     *
     * @param 键名 $key
     * @param 追加的值 $value
     */
    public static function getSet($key, $value, $time=0){
        if(!$key){
            return false;
        }
        $val = self::get($key);
        self::set($key,$value,$time);
        return $val;
    }
    
	public static function mem_close(){					#关闭memcached
		self::init();
		self::$mem->close();
	}
}