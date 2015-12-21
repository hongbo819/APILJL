<?php
    /**
     * 酷汇部落模块数据，利用到了访问助手类
     * @author cuihb
     * @time 2015-2-2
     */
     class API_Item_Kuhui_Buluo {
         
         /*
          * 获取酷汇部落相关数据
          */
         public static function getKuhuiTestList($paramArr){
             $options = array(
                    'subid'  => '', #子类id
                    'manuid' => '', #品牌id
                    'offset' => 0,  #开始数量
                    'limit'  => 10, #读取数量,默认10条
                    'orderBy'  => ' order by addTime desc ', #排序sql
                    'col'    => '*',
		);
             if (is_array($paramArr))$options = array_merge($options, $paramArr);
             extract($options);
             $whereSql = ' AND isDel=0 ';
             if($subid) $whereSql = ' AND subid in('.$subid.') ';
             if($manuid) $whereSql = ' AND manuid in('.$manuid.') ';
             
             $outArr = array();
             $outArr = API_Dao::getRows(array(
                'dbName'    =>  'API_Db_Kuhui',      #数据库名
                'tblName'   =>  'kh_test',         #表名
                'cols'      =>  $col,                #列名
                'offset'    =>  $offset,     #offset
                'limit'     =>  $limit,      #条数
                'orderSql'  =>  $orderBy,    #group by
                'whereSql'  =>  $whereSql, #where条件
            ));
             return $outArr;
         }
         /**
          * 酷汇表插入相关信息
          */
         public static function addKuhuiTestData($paramArr){
             $options = array(
                    'subid'  => '', #子类id
                    'manuid' => '', #品牌id
                    'title' => 0,  #开始数量
                    'addTime'  => 0, #读取数量,默认10条
		);
             if (is_array($paramArr))$options = array_merge($options, $paramArr);
             extract($options);
             
             $addItem = $options;
              API_Dao::insertItem(array(
                    'addItem'    =>  $addItem, #数据
                    'dbName'     =>  'API_Db_Kuhui',      #数据库名
                    'tblName'    =>  'kh_test',         #表名
                ));
            return true;
         }
         /**
          * 修改酷汇表相关信息
          */
         public static function updateKuhuiTestData($paramArr){
              $options = array(
                    'subid'  => '', #子类id
                    'manuid' => '', #品牌id
                    'title' => 0,  #开始数量
                    'addTime'  => 0, #读取数量,默认10条
                    'where' => '',
		);
             if (is_array($paramArr))$options = array_merge($options, $paramArr);
             extract($options);
             unset($options['where']);
             $editItem = $options;
              API_Dao::updateItem(array(
                    'colArr'        =>  false, #验证列名
                    'editItem'      =>  $editItem, #更新数据
                    'dbName'        =>  'API_Db_Kuhui',    #数据库名
                    'tblName'       =>  'kh_test',   #表名
                    'where'         =>  $where,    #条件
                    'debug'         =>  0
                ));
            return true;
         }
         /**
          * 执行删除酷汇表中相关信息
          */
         public static function delKuhuiTestData($paramArr){
             $options = array(
                    'where' => '',
		);
             if (is_array($paramArr))$options = array_merge($options, $paramArr);
             extract($options);
              API_Dao::delItem(array(
                    'dbName'        =>  'API_Db_Kuhui',    #数据库名
                    'tblName'       =>  'kh_test',   #表名
                    'where'         =>  $where,    #条件
                    'debug'         =>  '',     #where条件
                ));
            return true;
         }
         
     }

?>
