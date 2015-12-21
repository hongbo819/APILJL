<?php
/**
 * Redis队列操作封装
 */

class API_Item_Queue_RedisQ
{  
    /**
     * 插入数据
     */
    public static function push($paramArr){
        $options = array(
            'serverName'=> 'ResysQ', #服务器名，参照见API_Redis的定义 ResysQ
            'key'       => false,  #队列名
            'value'     => false,  #插入队列的数据
        );
        if (is_array($paramArr))$options = array_merge($options, $paramArr);
        extract($options);
        if(!$serverName || !$key)return false;
        $redis = API_Redis::getLink($serverName);
        return $redis->rpush($key,$value);
    }

    /**
     * 获得数据,一次一个
     */
    public static function pop($paramArr){
        $options = array(
            'serverName'    => 'ResysQ', #服务器名，参照见API_Redis的定义 ResysQ
            'key'      => false,  #队列名
        );
        if (is_array($paramArr))$options = array_merge($options, $paramArr);
        extract($options);
        if(!$serverName || !$key)return false;

        $redis = API_Redis::getLink($serverName);
        return $redis->lpop($key);
    }

     /**
     * 获得数据
     */
    public static function pops($paramArr){
        $options = array(
            'serverName'  => 'ResysQ', #服务器名，参照见API_Redis的定义 ResysQ
            'key'         => false,  #队列名
            'num'         => 2,      #多个数据
        );
        if (is_array($paramArr))$options = array_merge($options, $paramArr);
        extract($options);
        if(!$serverName || !$key)return false;
        $data = array();
        for($i = 0; $i<$num; $i++){
            $d = self::pop($paramArr);
            if(!$d)break;
            $data[$i] = $d;
        }
        return $data;
    }
    
    /**
     * 获得队列中现有值的数量
     */
    public static function getSize($paramArr){
        $options = array(
            'serverName'    => 'ResysQ', #服务器名，参照见API_Redis的定义 ResysQ
            'key'      => false,  #队列名
        );
        if (is_array($paramArr))$options = array_merge($options, $paramArr);
        extract($options);
        if(!$serverName || !$key)return false;
    
        $redis = API_Redis::getLink($serverName);
        return $redis->lSize($key);
    }
    /**
     * 获得队列 列表详情，不弹出，只是查看
     */
    public static function range($paramArr){
        $options = array(
            'serverName'  => 'ResysQ',     #服务器名，参照见API_Redis的定义 ResysQ
            'key'         => false,  #队列名
            'offset'      => 0,      #开始索引值
            'len'         => 2,      #结束索引值
        );
        if (is_array($paramArr))$options = array_merge($options, $paramArr);
        extract($options);
        
        if(!is_numeric($offset)  ||  !is_numeric($len) || empty($key) || empty($serverName)){
            return false;
        }
        
        $redis = API_Redis::getLink($serverName);
        $data  = array();
        $data  = $redis ->lRange($key,$offset,$len);
        return $data;
        
    }
}
