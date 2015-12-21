<?php
     /**
     * 酷汇部落模块数据 实验
     * @author cuihb
     * @time 2015-2-2
     */
     class API_Item_Kuhui_Buluo2 {
          public static function getKuhuiTestList($paramArr){
              $options = array(
                    'limit' => 10, #读取的列表数量
                );

                if (is_array($paramArr))
                    $options = array_merge($options, $paramArr);
                extract($options);
                
                $limit = " limit 0,{$limit} ";
                $DB_Kuhui = API_Db_Kuhui::instance();
                
                $sql = "select * from kh_test $limit ";
                return $DB_Kuhui->getAll($sql);
          }
     }
?>
