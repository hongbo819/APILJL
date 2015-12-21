<?php
/**
* Memcache操作封装
*/
class API_Item_Kv_Memcached
{
    /**
     * 获得Memcache的连接对象
     */
    public static function getLink($paramArr){
		$options = array(
            'serverName'    => 'Default', #服务器名，参照见API_Memcached的定义
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);
        if(!$serverName)return false;
        
        return API_Memcached::getLink($serverName); #获得redis对象

    }

    /**
     * 封装的get请求
     */
    public static function get($paramArr){
		$options = array(
            'serverName'    => 'Default', #服务器名，参照见API_Redis的定义
            'key'           => '', #
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);

        $link = self::getLink(array('serverName' => $serverName));
        if($link){
            $link = $link['obj'];
            return $link->get($key);
        }
    }
    
    /**
     * 封装的set请求
     */
    public static function set($paramArr){
		$options = array(
            'serverName'    => 'Default', #服务器名，参照见API_Redis的定义
            'key'           => '', #
            'value'         => '', #
            'time'          => 0, #
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);

        $link = self::getLink(array('serverName' => $serverName));
        if($link){
            if($link['memcached']){#如果是memcached服务器,memcache和memcached方法是不同的
                $time = $time ? $time() + $time : 0;
                $link = $link['obj'];
                $link->set($key, $value,$time);
            }else{
                $link = $link['obj'];
                $link->set($key, $value,0,$time);
            }
        }

    }

}

