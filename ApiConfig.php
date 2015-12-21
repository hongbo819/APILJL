<?php
    /**
     * API配置文件
     */
    if(!defined('IN_LJL_API')) die("Hacking attempt");
    
    #引用项目是否是框架项目
    defined('LJL_API_ISFW') || define('LJL_API_ISFW', true);
   
    #数据库账户信息(可在/API/Db/下的具体类中设置)
    //defined('DB_USERNAME')  || define('DB_USERNAME', 'root');
    //defined('DB_PASSWORD')  || define('DB_PASSWORD', 'lacgyl7415.nbas');