<?php
/**
 * MongoDB类
 */
class API_MongoDB{

    protected static $serverArr  = array();#创建的Mongo对象的集合
    protected static $dbArr      = array();#连接的DB
    protected static $colArr     = array();#Collection的数组
    public static $serverCfgArr  = array(
        'localhost' => 'localhost',//:27017
        'proAdmin'  => '10.15.184.41',
    );

    /**
     * 服务器连接
     */
    public static function connect($server = 'localhost'){
        if(isset(self::$serverCfgArr[$server])){
            $serverCnn = 'mongodb://' .self::$serverCfgArr[$server];
        }else{
            $serverCnn = 'mongodb://' .$server;
        }
        
        if (!isset(self::$serverArr[$server])){
           
            if (class_exists("Mongo")) {
                 
                self::$serverArr[$server] = new Mongo($serverCnn, array('timeout' => 1000, 'persist' => $server));
                
            } else {
                die("Mongo接口模块不可用");
            }
        }
        return self::$serverArr[$server];
    }

    /**
     * 数据库连接
     */
    public static function selectDB($server,$db,$extParam=false){
        if (!isset(self::$dbArr[$server]) || !isset(self::$dbArr[$server][$db])){            
            $mongo = self::connect($server);
            $dbIns = $mongo->$db;
            if($extParam && isset($extParam['slaveOkay'])){
                if($extParam['slaveOkay']){
                    $dbIns->setSlaveOkay(true);
                }else{
                    $dbIns->setSlaveOkay(false);
                }
            }
            self::$dbArr[$server][$db] = $dbIns;
        }
        return self::$dbArr[$server][$db];

    }

    /**
     * 获得某个Collection
     */
    public static function selectCollection($server,$db,$collection,$extParam=false){
        if (!isset(self::$colArr[$server]) || !isset(self::$colArr[$server][$db]) || !isset(self::$colArr[$server][$db][$collection])){
            $dbIns = self::selectDB($server, $db,$extParam);
            self::$colArr[$server][$db][$collection] = $dbIns->$collection;
        }
        return self::$colArr[$server][$db][$collection];
    }

    /**
     * 获得一条数据
     */
    public static function findOne($server,$db,$collection,$key,$extParam=false){
        #获得collection
        $cols = self::selectCollection($server,$db,$collection,$extParam);
        $data = false;
        try{
            $data = $cols->findOne(array('_id' => $key));
        } catch (MongoCursorException $e) {
        }
        return $data;
        
    }

    /**
     * 保存一条数据[不存在insert 否则update]
     */
    public static function save($server,$db,$collection,$key,$value){
        #获得collection
        $cols = self::selectCollection($server,$db,$collection);
        if(is_array($value)){
            $value['_id'] = $key;
        }else{
            $value = array('_id' => $key,$value);
        }
        return $cols->save($value);
        
    }
    /*
     * 第二种插入方法，先执行remore后insert
     */
    public static function save2($server,$db,$collection,$key,$value){
        self::remove($server, $db, $collection, $key);
        self::insert($server, $db, $collection, $key, $value);
    }
    /**
     * 插入一条数据
     */
    public static function insert($server,$db,$collection,$key,$value){
        #获得collection
        $cols = self::selectCollection($server,$db,$collection);
        if(is_array($value)){
            $value['_id'] = $key;
        }else{
            $value = array('_id' => $key,$value);
        }
        return $cols->insert($value);

    }

    /**
     * 删除一条数据
     */
    public static function remove($server,$db,$collection,$key){
        #获得collection
        $cols = self::selectCollection($server,$db,$collection);
        $cols->remove(array('_id' => $key), array("justOne" => true));
        
    }
    /**
     * 获得状态
     */
    public static function stats($server,$db=false){
        $data = array();
        if($server && $db){
            $dbIns = self::selectDB($server, $db);
            $data['server'] = $dbIns->execute("db.serverStatus()");
            $data['db']     = $dbIns->execute("db.stats()");
        }
        return $data;
        
    }
    /**
     * 执行命令
     */
    public static function doCmd($server,$db=false,$cmd){
            if($db){
                $dbIns = self::selectDB($server, $db);
                $data = $dbIns->execute($cmd);
            
            }else{
                $mongo = self::connect($server);
                $data = $mongo->execute($cmd);
            }

            return $data;
    }
    /**
     * 获得上次的错误信息
     */
    public static function lastError($server,$db){
         $dbIns = self::selectDB($server, $db);
         return $dbIns->lastError();
    }
    /**
     * 关闭操作
     */
    public static function close($server){
        if(isset(self::$serverCfgArr[$server])){
            $cnn = self::$serverCfgArr[$server];
            if($cnn){
                $cnn.close();
            }
        }
        return true;
    }


}

