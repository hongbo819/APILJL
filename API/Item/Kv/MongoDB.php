<?php
/**
* MongoDB操作封装
*/
class API_Item_Kv_MongoDB
{

    /**
     * 获得所有的MongoDB服务器信息
     */
    public static function getAllServer(){
        return API_MongoDB::$serverCfgArr;
    }

    /**
     * 查看db或
     */
    public static function stats($paramArr){
		$options = array(
            'server'    => 'localhost', #服务器名，参照见API_Redis的定义
            'db'        => false, #数据库
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);

        if(!$server)return false;

        return API_MongoDB::stats($server,$db);
    }



    /**
     * 获得一条数据
     */
    public static function findOne($paramArr){
		$options = array(
            'server'      => 'localhost', #服务器名，参照见API_Redis的定义
            'db'          => false, #数据库
            'collection'  => false, #collection
            'key'         => false, #获得数据的key
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);
        
        return API_MongoDB::findOne($server,$db,$collection,$key);
    }

    /**
     * 保存一条数据
     */
    public static function save($paramArr){
		$options = array(
            'server'      => 'localhost', #服务器名，参照见API_Redis的定义
            'db'          => false, #数据库
            'collection'  => false, #collection
            'key'         => false, #获得数据的key
            'value'       => false, #保存的数值
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);

        return API_MongoDB::save($server,$db,$collection,$key,$value);
    }
}

