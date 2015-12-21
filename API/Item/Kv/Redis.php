<?php
/**
* Redis操作封装
*/
class API_Item_Kv_Redis
{
    /**
     * 获得Redis的连接对象
     */
    public static function getObj($paramArr){
		$options = array(
            'serverName'    => 'Default', #服务器名，参照见API_Redis的定义
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);

        if(!$serverName)return false;

        return API_Redis::getLink($serverName); #获得redis对象

    }

    /**
     * 获得所有的Redis服务器信息
     */
    public static function getAllServer(){
        return API_Redis::$server;
    }

    /*--------------------------------------------------------------------
                                   string类型
    ---------------------------------------------------------------------*/
    /**
     * string类型的get方法
     */
    public static function stringGet($paramArr){
		$options = array(
            'serverName'    => 'Default', #服务器名，参照见API_Redis的定义
            'key'           => false, #获得数据的Key
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);

        if(!$key || !$serverName)return false;

        $redis = self::getObj(array('serverName'=>$serverName));

        return $redis->get($key);
    }
    /**
     * string类型的mget方法
     */
    public static function stringMGet($paramArr){
		$options = array(
            'serverName'    => 'Default', #服务器名，参照见API_Redis的定义
            'key'           => false, #获得数据的Key,多个键值以数组或者,分割
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);

        if(!$key || !$serverName)return false;
        if(!is_array($key)){
            $key = explode(',', $key);
        }
        $redis = self::getObj(array('serverName'=>$serverName));

        return $redis->mget($key);
    }
    /**
     * string类型的自增方法
     */
    public static function stringIncr($paramArr){
		$options = array(
            'serverName'    => 'Default', #服务器名，参照见API_Redis的定义
            'key'           => false, #获得数据的Key,多个键值以数组或者,分割
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);

        if(!$key || !$serverName)return false;
        $redis = self::getObj(array('serverName'=>$serverName));

        return $redis->incr($key);
    }
    public static function stringDecr($paramArr){
		$options = array(
            'serverName'    => 'Default', #服务器名，参照见API_Redis的定义
            'key'           => false, #获得数据的Key,多个键值以数组或者,分割
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);

        if(!$key || !$serverName)return false;
        $redis = self::getObj(array('serverName'=>$serverName));

        return $redis->decr($key);
    }

    /**
     * string类型的set方法
     */
    public static function stringSet($paramArr){
		$options = array(
            'serverName'    => 'Default', #服务器名，参照见API_Redis的定义
            'key'           => false, #获得数据的Key
            'value'         => false, #数据值
            'life'          => 86400, #缓存时间
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);

        if(!$key || !$serverName)return false;

        $redis = self::getObj(array('serverName'=>$serverName));
        if($life){
            $redis->setex($key, $life, $value);
        }else{
            $redis->set($key, $value);
        }
        return true;
    }
    
    /**
     * string类型的append方法
     */
    public static function stringAppend($paramArr){
		$options = array(
            'serverName'    => 'Default', #服务器名，参照见API_Redis的定义
            'key'           => false, #获得数据的Key
            'value'         => false, #数据值
            'life'          => 86400, #缓存时间
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);

        if(!$key || !$serverName)return false;

        $redis = self::getObj(array('serverName'=>$serverName));
        $result = $redis->append($key, $value);
        if($life){
            $redis->setTimeOut($key, $life);
        }
        return $result;
    }

     /**
     *  删除操作
     */
    public static function delete($paramArr){
		$options = array(
            'serverName'    => 'Default', #服务器名，参照见API_Redis的定义
            'key'           => false,     #要删除的Key，可以是数组
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);

        if(!$key || !$serverName)return false;

        $redis = self::getObj(array('serverName'=>$serverName));

        return $redis->delete($key);
    }
    /*--------------------------------------------------------------------
                                    列表类型
     ---------------------------------------------------------------------*/
    /*
     * @Desc 获取列表类型
     * @Version 14-6-10 下午5:04
     */
    public static function listRange($paramArr){
        $options = array(
            'serverName'    => 'Default', #服务器名，参照见API_Redis的定义
            'key'           => false,     #
            'start'         => 0,
            'end'           => -1,
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);

        if(!$key || !$serverName)return false;
        
        $redis = self::getObj(array('serverName'=>$serverName));
        return $redis->lRange($key, $start, $end);
    }
    
    /*
     * @Desc 获取列表类型的大小
     * @Version 14-6-10 下午5:04
     */
    public static function listSize($paramArr){
        $options = array(
            'serverName'    => 'Default', #服务器名，参照见API_Redis的定义
            'key'           => false,     #要删除的Key，可以是数组
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);

        if(!$key || !$serverName)return false;
        
        $redis = self::getObj(array('serverName'=>$serverName));
        return $redis->lSize($key);
    }
    
    /*
     * @Desc 获取列表类型某个索引的值
     * @Version 14-6-10 下午5:04
     */
    public static function listIndexGet($paramArr){
        $options = array(
            'serverName'    => 'Default', #服务器名，参照见API_Redis的定义
            'key'           => false,     #要删除的Key，可以是数组
            'index'         => 0,
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);

        if(!$key || !$serverName)return false;
        
        $redis = self::getObj(array('serverName'=>$serverName));
        return $redis->lGet($key,$index);
    }
    
    /*
     * @Desc 设置列表类型某个索引的值
     * @Version 14-6-10 下午5:04
     */
    public static function listIndexSet($paramArr){
        $options = array(
            'serverName'    => 'Default', #服务器名，参照见API_Redis的定义
            'key'           => false,     #
            'index'         => 0,
            'value'         => false,       #数据值
            'life'        => 0,           #设置过期时间（为0，则使用原值）
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);

        if(!$key || !$serverName)return false;
        
        $redis = self::getObj(array('serverName'=>$serverName));
        
        $result = $redis->lSet($key,$index,$value);
        if($life){ $redis->setTimeout($key,$expire); }
        return $result;
    }
    
    /*
     * @Desc 删除列表类型某个值
     * @Version 14-6-10 下午5:04
     */
    public static function listRemove($paramArr){
        $options = array(
            'serverName'    => 'Default', #服务器名，参照见API_Redis的定义
            'key'           => false,     #要删除的Key，可以是数组
            'index'         => 0,
            'value'         => false,       #数据值
            'life'        => 0,           #设置过期时间（为0，则使用默认值）
            'count'         => 0,           #默认删除所有的
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);

        if(!$key || !$serverName)return false;
        
        $redis = self::getObj(array('serverName'=>$serverName));
        
        $result = $redis->lRem($key,$count,$value);
        return $result;
    }

    /*--------------------------------------------------------------------
     hash类型
     ---------------------------------------------------------------------*/
    /*
     * @Desc 获取散列类型key的某个索引的值
     */
    public static function hashSet($paramArr){
        $options = array(
            'serverName'    => 'Default', #服务器名，参照见API_Redis的定义
            'key'           => false,     #要删除的Key，可以是数组
            'index'         => 0,
            'value'         => false, #数据值
            'life'          => 0, #缓存时间
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);

        if(!$key || !$serverName)return false;
        
        $redis = self::getObj(array('serverName'=>$serverName));
        $res = $redis->hSet($key, $index, $value);
        if($life) $redis->setTimeout($key, $life);
        return $res;
    }
    /*
     * @Desc 获取散列类型key的某个索引的值或所有值
     * @Version 14-6-10 下午5:04
     */
    public static function hashGet($paramArr){
        $options = array(
            'serverName'    => 'Default', #服务器名，参照见API_Redis的定义
            'key'           => false,     #要删除的Key，可以是数组
            'index'         => 0,
            'value'         => false, #数据值
            'life'          => 86400, #缓存时间
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);

        if(!$key || !$serverName)return false;
        
        $redis = self::getObj(array('serverName'=>$serverName));
        if(!$index) return $redis->hGetAll($key);
        return $redis->hGet($key,$index);
    }
    
    /*
     * @Desc 获取散列类型key的所有值
     * @Version 14-6-10 下午5:04
     */
    public static function hashGetAll($paramArr){
        $options = array(
            'serverName'    => 'Default', #服务器名，参照见API_Redis的定义
            'key'           => false,     #要删除的Key，可以是数组
            'index'         => 0,
            'value'         => false, #数据值
            'life'          => 86400, #缓存时间
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);

        if(!$key || !$serverName)return false;
        
        $redis = self::getObj(array('serverName'=>$serverName));
        return $redis->hGetAll($key);
    }
    /*
     * @Desc 散列类型指定字段incrby
     */
    public static function hashIncrBy($paramArr){
        $options = array(
            'serverName'    => 'Default', #服务器名，参照见API_Redis的定义
            'key'           => false,     #
            'index'         => 0,
            'incrby'         => false, #整数数据值
            'life'          => 0, #缓存时间
        );
        if (is_array($paramArr))$options = array_merge($options, $paramArr);
        extract($options);
    
        if(!$key || !$serverName)return false;
    
        $redis = self::getObj(array('serverName'=>$serverName));
        $res = $redis->hIncrBy($key, $index, $incrby);
        if($life) $redis->setTimeout($key, $life);
        return $res;
    }
    /*--------------------------------------------------------------------
     set（集合）类型
     ---------------------------------------------------------------------*/
    /**
     * 增加集合元素
     */
    public static function ssetAdd($paramArr){
        $options = array(
            'serverName'    => 'Default', #服务器名，参照见API_Redis的定义
            'key'           => false,     #
            'value'         => 0,
            'life'          => 0, #缓存时间
        );
        if (is_array($paramArr))$options = array_merge($options, $paramArr);
        extract($options);
        
        if(!$key || !$serverName)return false;
        $redis = self::getObj(array('serverName'=>$serverName));
    
        $res = $redis->sAdd($key, $value);
        if ($life > 0) {
            $redis->setTimeout($key, $life);
        }
        return $res;
    }
    /**
     * 获得当前key下所有元素
     */
    public static function ssetMembers($paramArr){
        $options = array(
            'serverName'    => 'Default', #服务器名，参照见API_Redis的定义
            'key'           => false,     #
        );
        if (is_array($paramArr))$options = array_merge($options, $paramArr);
        extract($options);
        
        if(!$key || !$serverName)return false;
        $redis = self::getObj(array('serverName'=>$serverName));
    
        return $redis->sMembers($key);
        
    }
    /**
     * 删除当前key下指定元素
     */
    public static function ssetRemove($paramArr){
        $options = array(
            'serverName'    => 'Default', #服务器名，参照见API_Redis的定义
            'key'           => false,     #
            'value'         => false,     #
        );
        if (is_array($paramArr))$options = array_merge($options, $paramArr);
        extract($options);
        
        if(!$key || !$serverName)return false;
        $redis = self::getObj(array('serverName'=>$serverName));
    
        return $redis->sRemove($key, $value);
        
    }
    /**
     * 移动集合元素 从集合A到集合B
     */
    public static function ssetMove($paramArr){
        $options = array(
            'serverName'    => 'Default', #服务器名，参照见API_Redis的定义
            'fromKey'       => false,     #要移动涉及的key $fromKey
            'toKey'         => false,     #移动到的key $toKey
            'value'         => false,     #元素 $value
        );
        if (is_array($paramArr))$options = array_merge($options, $paramArr);
        extract($options);
        
        if(!$fromKey || $toKey || !$serverName)return false;
        $redis = self::getObj(array('serverName'=>$serverName));
    
        return $redis->sMove($fromKey, $toKey, $value);
        
    }
    /**
     * 统计集合内元素个数
     */
    public static function ssetSize($paramArr){
        $options = array(
            'serverName'    => 'Default', #服务器名，参照见API_Redis的定义
            'key'         => false,     #key 
        );
        if (is_array($paramArr))$options = array_merge($options, $paramArr);
        extract($options);
        
        if(!$key || !$serverName)return false;
        $redis = self::getObj(array('serverName'=>$serverName));
    
        return $redis->sSize($key);
        
    }
    /**
     * 判断元素是否属于某个key
     */
    public static function ssetIsMember($paramArr){
        $options = array(
            'serverName'    => 'Default', #服务器名，参照见API_Redis的定义
            'key'           => false,     #key 
            'value'         => false,     #value
        );
        if (is_array($paramArr))$options = array_merge($options, $paramArr);
        extract($options);
        
        if(!$key || !$serverName)return false;
        $redis = self::getObj(array('serverName'=>$serverName));
    
        return $redis->sIsMember($key, $value);
        
    }
    /**
     * 求交集
     */
    public static function ssetInter($paramArr){
        $options = array(
            'serverName'    => 'Default', #服务器名，参照见API_Redis的定义
            'keyArr'           => false,     #key 
        );
        if (is_array($paramArr))$options = array_merge($options, $paramArr);
        extract($options);
        
        if(!is_array($keyArr))
            $keyArr = explode(',', $keyArr);
        
        if(!$keyArr || !$serverName)return false;
        $redis = self::getObj(array('serverName'=>$serverName));
    
        return $redis->sInter($keyArr);
        
    }
    /**
     * 求并集
     */
    public static function ssetUnion($paramArr){
        $options = array(
            'serverName'    => 'Default', #服务器名，参照见API_Redis的定义
            'keyArr'           => false,     #key 
        );
        if (is_array($paramArr))$options = array_merge($options, $paramArr);
        extract($options);
        
        if(!is_array($keyArr))
            $keyArr = explode(',', $keyArr);
        
        if(!$keyArr || !$serverName)return false;
        $redis = self::getObj(array('serverName'=>$serverName));
    
        return $redis->sUnion($keyArr);
        
    }
    /**
     * 求差集 A-B的操作
     */
    public static function ssetDiff($paramArr){
        $options = array(
            'serverName'    => 'Default', #服务器名，参照见API_Redis的定义
            'keyArr'           => false,     #key 
        );
        if (is_array($paramArr))$options = array_merge($options, $paramArr);
        extract($options);
        
        if(!is_array($keyArr))
            $keyArr = explode(',', $keyArr);
        
        if(!$keyArr || !$serverName)return false;
        $redis = self::getObj(array('serverName'=>$serverName));
    
        return $redis->sDiff($keyArr);
        
    }
    /*--------------------------------------------------------------------
                                                                                有序集合
     ---------------------------------------------------------------------*/
    /*
     * @Desc 增加有序集合元素
     */
    public static function zsetAdd($paramArr){
        $options = array(
            'serverName'    => 'Default', #服务器名，参照见API_Redis的定义
            'key'           => false,     #
            'score'         => 0,         #用于对value的排序
            'value'         => false,
            'life'          => 0,       #过期时间
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);

        if(!$key || !$serverName)return false;
        
        $redis = self::getObj(array('serverName'=>$serverName));
        $result = $redis->zAdd($key, $score, $value);
        if($life)
            $redis->setTimeout($key, $life);
        return $result;
    }
    /*
     * @Desc 获取集合类型名称为key的zset（元素已按score从小到大排序）中的index从start到end的所有元素
     * @Version 14-6-10 下午5:04
     */
    public static function zsetRange($paramArr){
        $options = array(
            'serverName'    => 'Default', #服务器名，参照见API_Redis的定义
            'key'           => false,     #
            'start'         => 0,
            'end'           => -1,
            'withscores'    => 1,       #是否按照分数排序
            'orderBy'       => 0,       #逆序是否
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);

        if(!$key || !$serverName)return false;
        
        $redis = self::getObj(array('serverName'=>$serverName));
        if($orderBy){
            $result = $redis->zRevRange($key, $start, $end ,$withscores);
        }else{
            $result = $redis->zRange($key, $start, $end ,$withscores);
        }
        return $result;
    }
    
    /*
     * @Desc 获取集合类型名称为key的zset中score >= star且score <= end的所有元素
     */
    public static function zsetRangeByScore($paramArr){
        $options = array(
            'serverName'    => 'Default', #服务器名，参照见API_Redis的定义
            'key'           => false,     #要删除的Key，可以是数组
            'start'         => '-inf',
            'end'           => '+inf',
            'life'          => 86400, #缓存时间
            'withscores'    => 1,       #是否按照分数排序
            'limitStart'    => '',      #索引起始点
            'limitNum'      => '',      #个数
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);

        if(!$key || !$serverName)return false;
        
        $limitArr = array();
        if($limitStart && $limitNum){ $limitArr = array($limitStart,$limitNum);}
        $redis = self::getObj(array('serverName'=>$serverName));
        
        if($orderBy){
            $result = $redis->zRevRangeByScore($key, $start, $end ,array('withscores' =>$withscores,'limit'=>$limitArr));
        }else{
            $result = $redis->zRangeByScore($key, $start, $end ,array('withscores' =>$withscores,'limit'=>$limitArr));
        }
        return $result;
    }
    
    /*
     * @Desc 获取集合类型名称为key的zset中score >= star且score <= end的所有元素的个数
     * @Version 14-6-10 下午5:04
     */
    public static function zsetCount($paramArr){
        $options = array(
            'serverName'    => 'Default', #服务器名，参照见API_Redis的定义
            'key'           => false,     #要删除的Key，可以是数组
            'start'         => '-inf',
            'end'           => '+inf',
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);

        if(!$key || !$serverName)return false;
        
        $redis = self::getObj(array('serverName'=>$serverName));
        
        return $redis->zCount($key, $start, $end );
    }
    
    /*
     * @Desc 获取集合类型名称为key的所有元素的个数
     * @Version 14-6-10 下午5:04
     */
    public static function zsetSize($paramArr){
        $options = array(
            'serverName'    => 'Default', #服务器名，参照见API_Redis的定义
            'key'           => false,     #要删除的Key，可以是数组
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);

        if(!$key || !$serverName)return false;
        
        $redis = self::getObj(array('serverName'=>$serverName));
        
        return $redis->zSize($key);
    }
}

