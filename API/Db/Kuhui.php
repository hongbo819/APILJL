<?php
    //酷汇站库
    class API_Db_Kuhui extends API_Db_Abstract_Pdo
    {
            
            protected $servers   = array(
                    //这里可以设置账号密码等信息，如果不设置则用ApiConfig中配置
//                    'username' => 'userdata',
//                    'password' => '43f59a7e5d',
                    //'engner' => 'mysql',
                    'master' => array(
                            'host'     => 'localhost',
                            'database' => 'kuhui',
                     ),
                     'slave' => array(
                            'host'     => 'localhost',
                            'database' => 'kuhui',
                     ),
            );
    }
?>
