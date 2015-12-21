<?php
/**
 * 数据访问助手类
 */
class API_Dao{
    private static $initDbName  = ""; #初始化数据库连接类,以后方法读取初始化的赋值
    private static $initTblName = ""; #初始化数据表名
    
    /**
     * 初始化数据
     */
    public static function init($paramArr) {
        $options = array(
            'dbName'        =>  'Db_UserData',    #数据库名
            'tblName'       =>  '',    #表名
        );
        if (is_array($paramArr))$options = array_merge($options, $paramArr);
        extract($options);
        
        self::$initDbName  = $dbName;
        self::$initTblName = $tblName;
        return true;
    }
    
    /**
     *  获得一行数据
     */
    public static function getRow($paramArr) {
        $options = array(
            'dbName'        =>  self::$initDbName,    #数据库名
            'tblName'       =>  self::$initTblName,    #表名
            'cols'          =>  '*',   #列名
            'limit'         =>  1,    #条数
            'offset'        =>  0,     #offset
            'whereSql'      =>  '',    #where条件
            'orderSql'      =>  '',    #orderby
            'debug'         =>  0 ,      #显示sql
            'isWrite'       => ''
            
        );
        if (is_array($paramArr))$options = array_merge($options, $paramArr);
        extract($options);

        $db = API_DbAdv::instance($dbName);
        if($isWrite) $db->forceReadMaster();  #强制用主库，防止 读写分离导致数据不同步
        $limitSql = "";
        if($limit){
            $limitSql = " limit ";
            if($offset)$limitSql .= $offset . ",";
            $limitSql .= $limit;
        }
        
        $sql      = "select {$cols}  from {$tblName} where 1 {$whereSql} {$orderSql} {$limitSql}";
        if($debug)echo $sql ;
        
        $data     = $db->getAll($sql);
        return $data && !empty($data[0]) ? $data[0] : false;
        
    }
    
    /**
     * 获得多条数据
     */
    public static function getRows($paramArr) {
        $options = array(
            'dbName'        =>  self::$initDbName,    #数据库名
            'tblName'       =>  self::$initTblName,    #表名
            'cols'          =>  '*',   #列名
            'offset'        =>  0,     #offset
            'limit'         =>  '',    #条数
            'whereSql'      =>  '',    #where条件
            'groupBy'       =>  '',    #group by
            'orderSql'      =>  '',    #where条件
            'debug'         =>  0,      #显示sql
            'isWrite'       => ''
        );
        if (is_array($paramArr))$options = array_merge($options, $paramArr);
        extract($options);

        $db = API_DbAdv::instance($dbName);
        if($isWrite) {
            $db->forceReadMaster();  #强制用主库，防止 读写分离导致数据不同步
        }
        $limitSql = "";
        if($limit){
            $limitSql = " limit ";
            if($offset)$limitSql .= $offset . ",";
            $limitSql .= $limit;
        }
        if($groupBy) $groupBy = ' group by ' . $groupBy;
        $sql      = "select {$cols}  from {$tblName} where 1 {$whereSql} {$groupBy} {$orderSql} {$limitSql}";
        if($debug)echo $sql;
        
        $data     = $db->getAll($sql);
        return $data;
    }
    /**
     * 根据条件获得单条数据
     */
    public static function getOne($paramArr) {
		$options = array(
			'dbName'        =>  self::$initDbName,    #数据库名
			'tblName'       =>  self::$initTblName,    #表名
			'cols'          =>  '',    #列名
			'whereSql'      =>  '',    #where条件
            'debug'         =>  0      #显示sql  
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);
        if($cols)$col = $cols; #兼容传入col的情况
        if(!$col || strpos($col, ',')) return false;
		$db = API_DbAdv::instance($dbName);
        $sql      = "select {$col}  from {$tblName} where 1 {$whereSql} ";
        if($debug)echo $sql;
        
        $data     = $db->getOne($sql);

        return $data;
    }
    /**
     * 获得数据列表
     */
    public static function getList($paramArr) {
        $options = array(
            'dbName'        =>  self::$initDbName,    #数据库名
            'tblName'       =>  self::$initTblName,   #表名
            'cols'          =>  '*',   #列名
            'pageSize'      =>  20,    #每页数量
            'page'          =>  1,     #当前页
            'pageUrl'       =>  '',    #页面URL规则
            'whereSql'      =>  '',    #where条件
            'orderSql'      =>  '',    #orderby条件
            'iswrite'       =>  false, #强制使用写库读取
            'getAll'        =>  false, #是否获得所有数据
            'pageTpl'       =>  9,     #分页模板
            'jsOnclick' 	=> '',
            'limit'         => '',
            'groupbySql'    => '',
            'debug'         =>  0,
        );
        if (is_array($paramArr))$options = array_merge($options, $paramArr);
        extract($options);

        $db     = API_DbAdv::instance($dbName);
        if($iswrite) $db->forceReadMaster();  #强制用主库，防止 读写分离导致数据不同步
        if($limit){
            $limit = ' limit '.$limit;
            #获得数量信息
            $sql     = "select {$cols} from {$tblName} where 1 {$whereSql} {$groupbySql} {$limit}";
            $allCnt  = count($db->getAll($sql));
        }else{
            #获得数量信息
            $sql     = "select count('x') cnt from {$tblName} where 1 {$whereSql} {$groupbySql} {$limit}";
            if ($groupbySql) {
                $allCnt  = count($db->getAll($sql));
            } else {
                $allCnt  = $db->getOne($sql);
            }
        }
        #获得分页信息
        $pageCfg = array(
            'page'   => $page,                  #当前页码
            'rownum' => $pageSize,               #一页显示多少条
            'target' => '_self',                #链接打开形式
            'total'  => $allCnt,                #总数
            'url'      => $pageUrl,               #当前页链接
            'jsOnclick'=> $jsOnclick
        );
        $pageObj = new Libs_Global_Page($pageCfg);

        $pageBar = $pageObj->display($pageTpl);
        $offset  = ($page -1) * $pageSize;
            
        $limitSql  = $getAll ? "" : " limit {$offset},{$pageSize}";
        $sql       = "select {$cols}  from {$tblName} where 1 {$whereSql} {$groupbySql} {$orderSql} {$limitSql} ";
        $data      = $db->getAll($sql);
        if($debug)echo $sql;
        return array(
            'allCnt'  => $allCnt,
            'pageBar' => $pageBar,
            'data'    => $data,
        );
       
    }
    /**
     * 执行插入数据到数据库中
     */
    public static function insertItem($paramArr) {
        $options = array(
            'colArr'        =>  false, #验证列名
            'addItem'       =>  false, #数据列
            'isReplace'     =>  false, #是否执行replace into
            'dbName'        =>  self::$initDbName,    #数据库名
            'tblName'       =>  self::$initTblName,   #表名
            'debug'         =>  0
        );
        if (is_array($paramArr))$options = array_merge($options, $paramArr);
        extract($options);

        if(!$addItem || !$dbName || !$tblName)return false;

        #验证字段有效性
        if(!empty($colArr)){
            foreach ($addItem as $key => $val) {
                if(!in_array($key,$colArr)){
                    echo "字段无效:【{$key}】";exit;
                }
            }
        }

        #拼装SQL
        $iSql1 = $iSql2 = $comma = "";
        foreach ($addItem as $key => $val) {
            $iSql1 .= $comma."`{$key}`";
            $iSql2 .= $comma."'{$val}'";
            $comma = ",";
        }
        $iSql  = $isReplace ? "REPLACE " : "INSERT ";       
        $iSql .= "INTO {$tblName} ({$iSql1}) VALUES ({$iSql2})";
        $db    = API_DbAdv::instance($dbName);
        $db->query($iSql);
        if($debug)echo  $iSql;
        
        return $db->lastInsertId();
    }


    /**
     * 执行更新数据库中的数据
     */
    public static function updateItem($paramArr) {
        $options = array(
            'colArr'        =>  false, #验证列名
            'editItem'      =>  false, #更新数据
            'dbName'        =>  self::$initDbName,    #数据库名
            'tblName'       =>  self::$initTblName,   #表名
            'where'         =>  '',    #条件
            'debug'         =>  0
        );
        if (is_array($paramArr))$options = array_merge($options, $paramArr);
        extract($options);

        if(!$editItem || !$dbName || !$tblName || !$where)return false;

        #验证字段有效性
        if(!empty($colArr)){
            foreach ($editItem as $key => $val) {
                if(!in_array($key,$colArr)){
                    echo "字段无效:【{$key}】";exit;
                }
            }
        }

        #拼装SQL
        $subSql = $s = "";
        foreach($editItem as $key=>$value){
            $subSql .= $s." `$key` ='".$value."'";
            $s = ",";
        }
        $sql = "UPDATE {$tblName} SET {$subSql} WHERE {$where}";
        if($debug)echo $sql;

        $db = API_DbAdv::instance($dbName);
        $db->query($sql);
        return true;
    }
        
     /**
     * 执行删除数据库中的数据
     */
    public static function delItem($paramArr) {
        $options = array(
            'dbName'        =>  self::$initDbName,    #数据库名
            'tblName'       =>  self::$initTblName,   #表名
            'where'         =>  '',    #条件
            'debug'         =>  '',     #where条件
        );
        if (is_array($paramArr))$options = array_merge($options, $paramArr);
        extract($options);

        if(!$dbName || !$tblName || !$where)return false;

        $sql = "DELETE FROM {$tblName} WHERE {$where}";
        if($debug)echo $sql;
        $db = API_DbAdv::instance($dbName);
        $db->query($sql);
        return true;
    }

    
    public static function getRandomRows($paramArr=array()) {
        $options = array(
            'dbName'        => self::$initDbName,  #数据库
            'tblName'       => self::$initTblName, #表名
            'cols'          => '*', #列名
            'limit'         => 1,   #产生条数
            'whereSql'      => '',  #where条件
        );
        if (is_array($paramArr))$options = array_merge($options, $paramArr);
        extract($options);
        
        $db = API_DbAdv::instance($dbName);
        
        $sql = "select count(*) num from {$tblName} where 1 {$whereSql}";
        $cnt = $db->getOne($sql);
        $offset = rand(0, $cnt-1);
        $sql = "select * from {$tblName} where 1 {$whereSql} limit {$offset}, {$limit}";

        $data  = $db->getAll($sql);
        return $data;
    }
    
    public static function getCount($paramArr) {
        $options = array(
            'dbName'        =>  self::$initDbName,    #数据库名
            'tblName'       =>  self::$initTblName,   #表名
            'whereSql'      =>  '',    #where条件
            'groupBy'       =>  '',    #group by
            'debug'         => '',     #where条件
        );
        if (is_array($paramArr))$options = array_merge($options, $paramArr);
        extract($options);

        $db = API_DbAdv::instance($dbName);
        if($groupBy) $groupBy = ' group by ' . $groupBy;
        $sql     = "select count(*)  from {$tblName} where 1 {$whereSql} {$groupBy}";
        if($debug)echo $sql;
        $cnt     = $db->getOne($sql);

        return intval($cnt);
    }
    
    /**
     * 获取一列
     */
    public static function getCol($paramArr)
    {
        $options = array(
            'dbName'        =>  self::$initDbName,    #数据库名
            'tblName'       =>  self::$initTblName,    #表名
            'cols'          =>  '*',   #列名
            'offset'        =>  0,     #offset
            'limit'         =>  '',    #条数
            'whereSql'      =>  '',    #where条件
            'groupBy'       =>  '',    #group by
            'orderSql'      =>  '',    #where条件
            'column'        =>  0,     #
            'debug'         =>  0      #显示sql
            
        );
        if (is_array($paramArr))$options = array_merge($options, $paramArr);
        extract($options);

        $db = API_DbAdv::instance($dbName);
        $limitSql = "";
        if($limit){
            $limitSql = " limit ";
            if($offset)$limitSql .= $offset . ",";
            $limitSql .= $limit;
        }
        if($groupBy) $groupBy = ' group by ' . $groupBy;
        $sql      = "select {$cols}  from {$tblName} where 1 {$whereSql} {$groupBy} {$orderSql} {$limitSql}";
        if($debug)echo $sql;
        $query = $db->query($sql);
        $fetchStyle = is_numeric($column) ? PDO::FETCH_NUM : PDO::FETCH_ASSOC;
        $results = false;
        while ($row = $query->fetch($fetchStyle)) {
            $results[] = $row[$column];
        }
        return $results;
    }
    
    
    /**
     * 获取一对儿
     */
    public static function getPairs($paramArr)
    {
        $options = array(
            'dbName'        =>  self::$initDbName,    #数据库名
            'tblName'       =>  self::$initTblName,    #表名
            'cols'          =>  '*',   #列名
            'offset'        =>  0,     #offset
            'limit'         =>  '',    #条数
            'whereSql'      =>  '',    #where条件
            'groupBy'       =>  '',    #group by
            'orderSql'      =>  '',    #where条件
            'keyName'       =>  '',    #作为key的列
            'valName'       =>  '',    #作为值的列
            'debug'         =>  0      #显示sql
            
        );
        if (is_array($paramArr))$options = array_merge($options, $paramArr);
        extract($options);

        $db = API_DbAdv::instance($dbName);
        $limitSql = "";
        if($limit){
            $limitSql = " limit ";
            if($offset)$limitSql .= $offset . ",";
            $limitSql .= $limit;
        }
        if($groupBy) $groupBy = ' group by ' . $groupBy;
        $sql      = "select {$cols}  from {$tblName} where 1 {$whereSql} {$groupBy} {$orderSql} {$limitSql}";
        if($debug) echo $sql;
        
        return $db->getPairs($sql,$keyName,$valName);
        
    }
}
