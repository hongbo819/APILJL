<?php
/**
 * Redis类
 */
class API_Redis{

    protected static $redisArr  = array();#创建的redis对象的集合
    protected static $redis     = false;  #当前方法操作的redis对象
    protected static $dalCfg    = false;

    /**
     * 服务器配置
     */
    public static  $server = array(
        'Default' =>  array( #主服务，本业务的服务器redis
                    'host' => '127.0.0.1', #IP
                    'port' => '6526'       #port
                ),
        'ResysQ' =>  array( #消息队列队列服务器
                    'host' => '127.0.0.1', #IP
                    'port' => '6526'           #port
                ),
        'Mojing1' =>  array( #魔镜的Reids
                    'host' => '130.15.184.97', #IP
                    'port' => '6379'           #port
                ),
        '10.15.184.60' =>  array( #私有云的Reids
                    'host' => '130.15.184.60', #IP
                    'port' => '6379'           #port
                ),
        '10.15.184.63' =>  array( #私有云的Reids
                    'host' => '130.15.184.63', #IP
                    'port' => '6379'           #port
                ),
        '10.15.184.112' =>  array( #私有云的Reids
                    'host' => '130.15.184.112', #IP
                    'port' => '6379'           #port
                ),
        'RubbishKill' =>  array( #垃圾过滤
                    'host' => '130.15.185.118', #IP
                    'port' => '6504'           #port
                ),
        '10.15.184.205' =>  array( #推荐系统的队列
                    'host' => '130.15.184.205',  #IP
                    'port' => '6379'           #port
                ),
        '10.15.184.80' =>  array( #万维
                    'host' => '130.15.184.80',  #IP
                    'port' => '6379'           #port
                ),
        'SaaS' =>  array( #SaaS
                    'host' => '130.15.184.161',  #IP
                    'port' => '6379'           #port
                )
    );
    public static function init($server = 'Default'){
        if (!isset(self::$redisArr[$server])){
            if (class_exists("Redis")) {
                #获得链接信息
                $hostInfo        = self::$server[$server];
                //var_dump($hostInfo);die;
                if($hostInfo){
                    #连接redis
                    self::$redis    = new Redis();
                    self::$redis->connect($hostInfo['host'],$hostInfo['port']);
                    self::$redisArr[$server] = self::$redis;
                }
            } else {
                die("Redis接口模块不可用");
            }
        }else{
            self::$redis = self::$redisArr[$server];
        }

    }
    /**
     * 获得Redis链接，可以直接用这个链接进行数据操作
     */
    public static function getLink($server){
        if(!self::$server[$server])return false;
        self::init($server);
        return self::$redis;
        
    }

    /**
     * 数据压缩格式
     */
    private static function compress($value){
        $value = array('D' => $value);
        return serialize($value);
    }

    /**
     * 数据压缩格式
     */
    private static function unCompress($value){
        $unValue = unserialize($value);
        return $unValue && isset($unValue['D']) || $unValue['D']==null ? $unValue['D'] : $value;
    }

    /*--------------------------------------------------------------------
                            key-value类型
    ---------------------------------------------------------------------*/
    /**
     * key-value 写
     */
    public static function set($server,$key,$value,$time=86400){
        
        if(!self::$server[$server] || !$key)return false;
        self::init($server);

        $value  = self::compress($value);

        $time   = $keyInfo['time'];
        if ($time <> 0) {
            return self::$redis->setex($key,$time,$value);
        } else {
            return self::$redis->set($key,$value);
        }
    }

    /**
     * key-value 读
     */
    public static function get($server,$key){

        if(!self::$server[$server] || !$key)return false;
        self::init($server);
        return self::unCompress(self::$redis->get($key));
    }


    /**
     * 设置多值
     */
    public function setMulti($server,$key){

        if(!self::$server[$server] || !$key)return false;
        self::init($server);

        $keyArr = array();
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $keyArr[$k]   = self::compress($v);
            }
        }
        return self::$redis->mset($keyArr);
    }

    /**
     * 获取多值
     * @param subKey 传入的是数组
     */
    public function getMulti($server,$keyArr){
        if(!self::$server[$server])return false;
        self::init($server);


        $arr = self::$redis->mget($keyArr);
        if (is_array($arr)) {
            foreach ($arr as $key=>$row) {
                $arr[$key]  = self::unCompress($row);
            }
        }
        return $arr;
    }

    /**
     * 删除元素
     */
    public static function delete($server,$key){

        if(!self::$server[$server] || !$key)return false;
        self::init($server);        
        return self::$redis->delete($key);
    }


    /*--------------------------------------------------------------------
                                   hash类型
    ---------------------------------------------------------------------*/
    /**
     * 存单键值
     */
    public static function hashSet($server,$key,$subKey,$value,$time=86400)
    {
        if(!self::$server[$server] || !$key)return false;
        self::init($server);

        $re = self::$redis->hSet($key, $subKey, self::compress($value));
        if ($time > 0) {
            self::$redis->expire($key, $time);
        }
        return $re;
    }

    /**
     * 取单键值
     */
    public static function hashGet($server,$key, $subKey){
        if(!self::$server[$server] || !$key)return false;
        self::init($server);
        return self::unCompress(self::$redis->hGet($key, $subKey));
    }

    /*--------------------------------------------------------------------
                                   set类型
    ---------------------------------------------------------------------*/
    /**
     * 增加集合元素
     */
    public static function sAdd($server,$key,$value,$time=86400){
        if(!self::$server[$server] || !$key)return false;
        self::init($server);

        $re     = self::$redis->sAdd($key, $value);#认为set的处理是不压缩的
        if ($time > 0) {
            self::$redis->expire($key, $time);
        }
        return $re;
    }

    /**
     * 删除一个指定的元素
     */
    public static function sDelete($server,$key , $value){
        if(!self::$server[$server] || !$key)return false;
        self::init($server);
        return self::$redis->sRemove($key, $value);
    }

    /**
     * 移动元素
     *
     * @param 要移动涉及的key $fromKey
     * @param 移动到的key $toKey
     * @param 元素 $value
     */
    public static function sMove($server ,$fromKey, $toKey, $value){

        if(!self::$server[$server])return false;
        self::init($server);

        $value  = self::compress($value);
        return self::$redis->sMove($fromKey, $toKey, $value);
    }

    /**
     * 统计元素个数
     */
    public static function sSize($server,$key){
        
        if(!self::$server[$server] || !$key)return false;
        self::init($server);

        return self::$redis->sSize($key);
    }

    /**
     * 判断元素是否属于某个key
     */
    public static function sIsMember($server,$key, $value){

        if(!self::$server[$server] || !$key)return false;
        self::init($server);
        
        return self::$redis->sIsMember($key, $value);
    }

    /**
     * 求交集
     *
     * @param key集合 $keyArr
     */
    public static function sInter($server,$keyArr = array()){
        if(!self::$server[$server])return false;
        self::init($server);
       
       return self::$redis->sInter($keyArr);
    }

    /**
     * 求交集并存储到另外的key中
     *
     * @param key集合 $keyArr 'output', 'key1', 'key2', 'key3'
     */
    public static function sInterStore($server,$key,$ouput,$keyArr){

        if(!self::$server[$server] || !$key)return false;
        self::init($server);

        array_unshift($keyArr,$ouput);  #插入到数组的开头
        return call_user_func_array(array(self::$redis, "sInterStore"), $keyArr);
    }

    /**
     * 求并集
     *
     * @param key集合 $keyArr
     */
    public static function sUnion($server,$key,$keyArr = array()){

        if(!self::$server[$server] || !$key)return false;
        self::init($server);

        if($keyArr){
            return self::$redis->sUnion($keyArr);
        }
    }

    /**
     * 求差集 A-B的操作
     *
     * @param key集合 $keyArr
     */
    public static function sDiff($server,$key,$keyArr = array()){

        if(!self::$server[$server] || !$key)return false;
        self::init($server);


        if($keyArr){
            return self::$redis->sDiff($keyArr);
        }
    }
    /**
     * 获取当前key下的所有元素
     *
     * @param key集合 $key
     */
    public static function sMembers($server,$key){
        if(!self::$server[$server] || !$key)return false;
        self::init($server);

        return self::$redis->sMembers($key);
    }
    /*--------------------------------------------------------------------
     zset有序集合类型
     ---------------------------------------------------------------------*/
    /**
     * 增加有序集合元素
     * @param key
     * @param score 用于对value排序
     */
    public static function zAdd($server, $key, $score, $value, $time=86400){
        if(!self::$server[$server] || !$key)return false;
        self::init($server);
    
        $re = self::$redis->zAdd($key, $score, $value);
        if ($time > 0) {
            self::$redis->expire($key, $time);
        }
        return $re;
    }
    /**
     * 删除名称为key的zset中score >= star且score <= end的所有元素，返回删除个数
     */
    public static function zRemRangeByScore($server, $key, $start, $end){
        if(!self::$server[$server] || !$key)return false;
        self::init($server);
    
        return self::$redis->zRemRangeByScore($key, $start, $end);
    }
    /**
     * 获取有序集合的数据
     * index 从$begin到$offset的值
     */
    public static function zrevrange($server, $key, $begin, $offset, $withscores = false){
        if(!self::$server[$server] || !$key)return false;
        self::init($server);
    
        return self::$redis->zrevrange($key, $begin, $offset, $withscores);
    }


}

