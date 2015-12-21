<?php
    /**
     * API入口文件
     */
    if(function_exists("__autoload")){
        die("[LJL_API]__autoload冲突，请修改为spl_autoload_register形式！");
    }
    
    define('IN_LJL_API', true);
    
    //API根目录
    defined('LJL_API_BASE') || define('LJL_API_BASE', dirname(__FILE__));
    defined('LJL_API_ROOT') || define('LJL_API_ROOT', LJL_API_BASE . '/API');
    defined('LJL_API_LOG') || define('LJL_API_LOG', LJL_API_ROOT . '/Log');
    defined('LJL_API_UTF8') || define('LJL_API_UTF8', false);//非utf8项目可能用到
    defined('LJL_API_DEBUG') || define('LJL_API_DEBUG', false);
    defined('LJL_API_LOGLEVEL') || define('LJL_API_LOGLEVEL', E_ALL ^ E_NOTICE);
    
    //引入配置文件
    require (LJL_API_BASE . '/ApiConfig.php');
    
    if(!LJL_API_ISFW){
        define('SYSTEM_TIME', isset ( $_SERVER ['REQUEST_TIME'] ) ? $_SERVER ['REQUEST_TIME'] : time ());
        define('SYSTEM_DATE', date ( 'Y-m-d H:i:s', SYSTEM_TIME ));
        define('IS_DEBUGGING', false);
    }
    
    //注册自动加载类文件
    spl_autoload_register(array('LJL_Api', 'autoload'));
    
    if(!LJL_API_ISFW ){ //如果不是LJL框架，配置自动加载,模拟一下框架的相关文件
        // 将LJL_Api的自动加载包含进来
        foreach (array('Db','LJL') as $nv) {
            LJL_Api::setNameSpace(LJL_API_ROOT . '/' . $nv);
        }
    
    }
    
    if(LJL_API_ISFW ){#LJL框架
        #框架会将$_COOKIE unset掉，所以这个需要提前将$_COOKIE保存起来
        LJL_Api::$_globalVars['_COOKIE'] = $_COOKIE;
    }
    
    if (!function_exists('get_called_class'))
    {
            function get_called_class()
            {
                    $bt = debug_backtrace();
                    $lines = file($bt[1]['file']);
                    preg_match('/([a-zA-Z0-9\_]+)::'.$bt[1]['function'].'/',
                               $lines[$bt[1]['line']-1],
                               $matches);
                    return $matches[1];
            }
    }
    
    class LJL_Api{
        private static $_namespace = array(); //存储需自动加载的命名空间
        public static $_globalVars = array(); //全局变量
        public static $_nowMethod = false; //当前执行的方法
        
        /**
         * 自动加载
         */
        public static function autoload($name) {
            if (trim($name) == '') {
                new Exception('No class or interface named for loading');
            }
            
            if (class_exists($name, false) || interface_exists($name, false)) {
                return;
            }
            
            $namespace = substr($name, 0, strpos($name, '_'));
            
            if(LJL_API_ISFW && in_array($namespace, array('Db','LJL')) ){ #框架的自动加载加载
                return ;
            }
            
            $file = '';
            
            //对命名空间做处理
            if ($namespace == 'API') {
                $file = LJL_API_BASE . '/' . str_replace ('_', DIRECTORY_SEPARATOR, $name) . '.php';
            }
            // 对个性的命名空间做处理
            elseif (isset(self::$_namespace[$namespace])) {
                $file = self::$_namespace[$namespace] . '/' . str_replace('_', DIRECTORY_SEPARATOR, $name) . '.php';
            }
            
            if ($file) {
                include $file;
                if (! class_exists($name, false) && ! interface_exists($name, false)){
                    throw new LJL_Exception('Class or interface does not exist in loaded file');
                }
            }
        }
        
        /**
         * 使用namespace方法实现每个实例的命名空间映射
         */
        public static function setNameSpace($path) {
            if (empty($path)) {
                new Exception('No class or interface named for loading');
            }
            $namespace = substr(strrchr($path, '/'), 1);
            $namespacePath = substr($path, 0, strlen($path) - strlen($namespace) - 1);
            if (!isset(self::$_namespace[$namespace]) || self::$_namespace[$namespace] != $namespacePath) {
                self::$_namespace[$namespace] = $namespacePath;
            } else {
                throw new Exception('Class or interface does not exist in loaded file');
            }
        }
        
        /**
         * 执行API的方法
         */
        public static function run($method, $param = false){
            if(!$method) return false;
            
            $method = str_replace('.', '_', $method);
            $class = 'API_Item_' . substr($method, 0, strrpos($method, '_'));
            $func = substr($method, strrpos($method, '_')+1);
            self::$_nowMethod = $method; //通过私有云调用
            $data = call_user_func_array(array($class, $func), array($param));
            if(LJL_API_UTF8 && $data){
                $data = self::toUTF8($data);
                return $data;
            }
            return $data;
        }
        
        /**
         * UTF8的转换
         */
        private static function toUTF8($input){
            if(is_string($input)){
                return mb_convert_encoding($input, 'UTF-8', 'GBK');
		}elseif(is_array($input)){
			$output = array();
			foreach ($input as $k=>$v){
                                $k = self::toUTF8($k);
				$output[$k] = self::toUTF8($v);
			}
			return $output;
		}else{
                return $input;
            }
	}
    }
    
    
?>