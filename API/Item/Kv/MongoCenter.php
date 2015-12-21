<?php
/**
* MongoDB缓存中心
*/
class API_Item_Kv_MongoCenter
{
    private static $_cacheCfg = array(
        #评论系统
        'comment' => array(
            'read' => array(
                'server'  => 'commentsmongodb:27017',//'127.0.0.1:27017',
                'slaveOk' => 1,
            ),
            'write' => array(
                'server'  => 'commentsmongodb:30000'
            ),

        ),
        #手机
        'sj' => array(
            'read' => array(
                'server'  => 'othermongodb:27017',//'127.0.0.1:27017',
                'slaveOk' => 1,
            ),
            'write' => array(
                'server'  => 'othermongodb:30000'
            ),
        ),
        #笑话
        'xiaohua' => array(
            'read' => array(
                'server'  => 'othermongodb:27017',//'127.0.0.1:27017',
                'slaveOk' => 1,
            ),
            'write' => array(
                'server'  => 'othermongodb:30000'
            ),
        ),
        //desk
        'desk' => array(
            'read' => array(
                'server'  => 'othermongodb:27017',//'127.0.0.1:27017',
                'slaveOk' => 1,
            ),
            'write' => array(
                'server'  => 'othermongodb:30000'
            ),
        ),
        #下载
        'xiazai' => array(
            'read' => array(
                'server'  => 'othermongodb:27017',//'127.0.0.1:27017',
                'slaveOk' => 1,
            ),
            'write' => array(
                'server'  => 'othermongodb:30000'
            ),
        ),
        #商城
        'shop' => array(
            'read' => array(
                'server'  => 'othermongodb:27017',//'127.0.0.1:27017',
                'slaveOk' => 1,
            ),
            'write' => array(
                'server'  => 'othermongodb:30000'
            ),

        ),
        #wap
        'wap' => array(
            'ttl'  => true,#设置TTL属性
            'read' => array(
                'server'  => 'othermongodb:27017',//bbsmongodbnew:27017',//'127.0.0.1:27017',
                'slaveOk' => 1,
            ),
            'write' => array(
                'server'  => 'othermongodb:30000'//bbsmongodbnew:27017'
            ),
        ),
        #魔镜
        'mojing' => array(
            'read' => array(
                'server'  => 'othermongodb:27017',//'127.0.0.1:27017',
                'slaveOk' => 1,
            ),
            'write' => array(
                'server'  => 'othermongodb:30000'
            ),
        ),
    );
    private static $_cachePool = array();
    
    /**
     * 获得数据库对象
     */
    public static function getDb($paramArr){
		$options = array(
            'module'      => false, #模块名，如评论comment
            'readWrite'   => 1, #读写 1读 2写
            'retry'       => true,  #是否尝试连接
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);

        if(!$module || !isset(self::$_cacheCfg[$module]))return false;
        $cfg = $readWrite == 2 ? self::$_cacheCfg[$module]["write"] : self::$_cacheCfg[$module]["read"];
        $extParam = array();

        if(isset($cfg["slaveOk"]))$extParam['slaveOkay'] = 1;
        #服务器数据
        $server     = $cfg["server"];
        $db         = $module;

        return API_MongoDB::selectDB($server,$db,$extParam);

    }

    /**
     * 获得缓存数据
     */
    public static function get($paramArr){
		$options = array(
            'module'    => false, #模块名，如评论comment
            'tbl'       => 'tbl', #表名
            'key'       => false, #key
            'getMeta'   => false, #是否获得meta数据
            'write'     => false, #是否强制走写库
            'minTm'     => 0,     #限制多长时间以内的缓存
            'retry'     => true,  #是否尝试连接
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);
        
        if(!$module || !isset(self::$_cacheCfg[$module]))return false;
        $cfg = $write ? self::$_cacheCfg[$module]["write"]:self::$_cacheCfg[$module]["read"];
        $extParam = array();

        if(isset($cfg["slaveOk"]))$extParam['slaveOkay'] = 1;
        if($write)$extParam['slaveOkay'] = 0;

        #服务器数据
        $server     = $cfg["server"];
        $db         = $module;
        $md5key     = md5($key);
        $tbl        = $tbl ? $tbl : "tbl";
        $collection = $tbl . '_' . substr($md5key, 0,2);

        #获得数据
        try{
            $data = API_MongoDB::findOne($server,$db,$collection,$md5key,$extParam);
        } catch (Exception $e){
            if(!empty($options['retry'])){
                $options['retry'] = false;
                return self::get($options);
            }
        }

        $out  = false;
        if(isset($data) && $data && isset($data['data'])){
            
            #判断有效期,如果已经过期了，就返回false
            if(!isset($data['life']) || !$data['life'] || $data['life'] > SYSTEM_TIME){
                $out = $data['data'];
                if($minTm && $data['life'] < $minTm){
                    $out = false;
                }
                if($getMeta){
                    $out = array(
                        'data' => $data['data'],
                        'meta' => isset($data['meta']) ? $data['meta'] : false,
                    );
                }
            }
            
        }
        return $out;

    }

     /**
     * 设置缓存数据
     */
    public static function set($paramArr){
		$options = array(
            'module'    => false, #模块名，如评论comment
            'tbl'       => 'tbl', #表名
            'key'       => false, #key
            'data'      => false, #数据
            'meta'      => false, #meta内容，可以存储附属的内容
            'life'      => 0,     #生命周期
            'retry'     => true,  #是否尝试连接
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);


        if(!$module || !isset(self::$_cacheCfg[$module]))return false;
        $cfg = self::$_cacheCfg[$module]["write"];
        $ttlFlag = isset(self::$_cacheCfg[$module]['ttl']) && self::$_cacheCfg[$module]['ttl'] ? true : false; //是否开启TTL
        #服务器数据
        $server     = $cfg["server"];
        $db         = $module;
        $md5key     = md5($key);
        $tbl        = $tbl ? $tbl : "tbl";
        $collection = $tbl . '_' . substr($md5key, 0,2);
        $data       = array(
            'key'   => $key,
            'data'  => $data,
            'life'  => $life ? $life + SYSTEM_TIME : 0,
        );
        //设置TTL的时间
        if($ttlFlag)$data['ttldt'] = new MongoDate(SYSTEM_TIME);
        if($meta)$data['meta'] = $meta;
        
        try{
            API_MongoDB::save($server,$db,$collection,$md5key,$data);
        } catch(Exception $e){
            if(!empty($options['retry'])){
                $options['retry'] = false;
                self::set($options);
            }
        }
        return true;

    }
    
    /**
     * 使缓存失效
     */
    public static function invalid($paramArr){
		$options = array(
            'module'    => false, #模块名，如评论comment
            'tbl'       => 'tbl', #表名
            'key'       => false, #key
            'retry'     => true,  #是否尝试连接
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);


        if(!$module || !isset(self::$_cacheCfg[$module]))return false;
        $cfg = self::$_cacheCfg[$module]["write"];

        #服务器数据
        $server     = $cfg["server"];
        $db         = $module;
        $md5key     = md5($key);
        $tbl        = $tbl ? $tbl : "tbl";
        $collection = $tbl . '_' . substr($md5key, 0,2);


        try{
            $colObj = API_MongoDB::selectCollection($server,$db,$collection);
            $colObj->update(array("_id" => $md5key),array('$set' => array("life" => SYSTEM_TIME)));

        } catch(Exception $e){
            if(!empty($options['retry'])){
                $options['retry'] = false;
                self::invalid($options);
            }
        }
    }

     /**
     * 删除缓存
     */
    public static function delete($paramArr){
		$options = array(
            'module'    => false, #模块名，如评论comment
            'tbl'       => 'tbl', #表名
            'key'       => false, #key
            'retry'     => true,  #是否尝试连接
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);


        if(!$module || !isset(self::$_cacheCfg[$module]))return false;
        $cfg = self::$_cacheCfg[$module]["write"];

        #服务器数据
        $server     = $cfg["server"];
        $db         = $module;
        $md5key     = md5($key);
        $tbl        = $tbl ? $tbl : "tbl";
        $collection = $tbl . '_' . substr($md5key, 0,2);

        try{
            API_MongoDB::remove($server,$db,$collection,$md5key);
        } catch(Exception $e){
            if(!empty($options['retry'])){
                $options['retry'] = false;
                self::delete($options);
            }
        }
        return true;

    }
    //

     /**
     * 执行命令
     * db.ttl.ensureIndex({"Date": 1}, {expireAfterSeconds: 300})
     */
    public static function cmd($paramArr){
		$options = array(
            'module'    => false, #模块名，如评论comment
            'cmd'       => false, #执行的命令
            'retry'     => true,  #是否尝试连接
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);


        if(!$module || !isset(self::$_cacheCfg[$module]))return false;
        $cfg = self::$_cacheCfg[$module]["write"];

        #服务器数据
        $server     = $cfg["server"];
        $db         = $module;
        $data = false;
        try{
           $data    = API_MongoDB::doCmd($server,$db,$cmd);
        } catch(Exception $e){
            if(!empty($options['retry'])){
                $options['retry'] = false;
                 $data  = self::cmd($options);
            }
        }
        return $data;

    }

    /**
     * 执行TTL
     */
    public static function setTtl($paramArr){
		$options = array(
            'module'    => false, #模块名，如评论comment
            'tbl'       => 'tbl', #表名前缀
            'tm'        => 86400*15, #设置ttl的时间
            'retry'     => true,  #是否尝试连接
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);
        
        #服务器数据
        for($i=0;$i<256;$i++){
            $chrs = dechex($i);
            if(strlen($chrs)==1) $chrs = "0".$chrs;
            $cmd = "db.{$tbl}_{$chrs}.ensureIndex({ttldt: 1}, {expireAfterSeconds: {$tm}})";
            self::cmd(array(
                'module'    => $module,
                'cmd'       => $cmd, #执行的命令

            ));
        }

        return true;
    }

    /**
     * remove TTL
     */
    public static function removeTtl($paramArr){
		$options = array(
            'module'    => false, #模块名，如评论comment
            'tbl'       => 'tbl', #表名前缀
            'retry'     => true,  #是否尝试连接
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);

        #服务器数据
        for($i=0;$i<256;$i++){
            $chrs = dechex($i);
            if(strlen($chrs)==1) $chrs = "0".$chrs;
            $cmd = "db.{$tbl}_{$chrs}.dropIndexes(\"ttldt\")";
            self::cmd(array(
                'module'    => $module,
                'cmd'       => $cmd, #执行的命令

            ));
        }

        return true;
    }


    /**
     * 设置capped属性
     */
    public static function doCapped ($paramArr){
		$options = array(
            'module'    => false, #模块名，如评论comment
            'tbl'       => 'tbl', #表名前缀
            'size'      => 0, #表最大容量
            'max'       => 0, #表最多行数
            'retry'     => true,  #是否尝试连接
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);

        $cmdArr = array();
        #服务器数据 db.runCommand( { convertTocapped："test",size:10000,max:1000 } );
        for($i=0;$i<256;$i++){
            $chrs = dechex($i);
            if(strlen($chrs)==1) $chrs = "0".$chrs;
            $cmdArr['convertTocapped'] = "{$tbl}_{$chrs}";
            if($size)$cmdArr['size']   = $size;
            if($max) $cmdArr['max']    = $max;

            self::cmd(array(
                'module'    => $module,
                'cmd'       => $cmdArr, #执行的命令

            ));
        }

        return true;
    }
}
