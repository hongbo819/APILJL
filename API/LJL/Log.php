<?php

class LJL_Log
{
    const TYPE_ERROR = 1;
    const TYPE_EXCEPTION = 2;
    const TYPE_LOG = 3;
    protected static $type = array(
        self::TYPE_ERROR => 'error',
        self::TYPE_EXCEPTION => 'exception',
        self::TYPE_LOG => 'log',
    );
	protected static $timer = 0;
    public static function write($message, $type,$hasMark=true)
    {
        if (empty($message))
        {
            trigger_error('$message dose not empty! ');

            return false;
        }
        if (empty($type))
        {
            trigger_error('$type dose not empty! ');

            return false;
        }
        if (!isset(self::$type[$type]))
        {
            trigger_error('Unknow log type: ' . $type);

            return false;
        }
        $var = defined('LJL_API_LOG') ? LJL_API_LOG : SYSTEM_VAR;
        //var_dump($var);die;
        $path = $var . '/log/' . self::$type[$type] . '/' . date('Y/m/d') . '.log';
		if($hasMark){
			$mark = "\n\n===========================================================================\n";
			$mark .= 'time:' . date('Y/m/d H:i:s') . "\n";
			$message = $mark . $message;
		}
        return API_File::write($message, $path, (FILE_APPEND | LOCK_EX));
    }

	/**
	 * 检查url中的参数名,如果有该参数,就写日志
	 */
	public  static function checkUriAndWrite($paramArr) {
		$options = array(
			'message'        =>  '',
			'paramName'      =>  '',
			'type'           =>  self::TYPE_LOG,
			'hasMark'        =>  false,
			'recTime'        =>  false #是否记录时间
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);
		if(isset($_SERVER["REQUEST_URI"]) && strpos($_SERVER["REQUEST_URI"],"?") !== false){
			$addParam = substr($_SERVER["REQUEST_URI"],strpos($_SERVER["REQUEST_URI"],"?")+1);
			
			parse_str($addParam,$addParamArr);
			
			if(isset($addParamArr[$paramName])){
				
				if($recTime){#是否计算花费的时间
					$message = " [" . round( (microtime(true) - self::$timer)*1000,2 ) . " ms]".$message;
				}
				LJL_Log::write($message,$type,$hasMark);
			}
		}

	}
	/**
	 * 重设时间,为了统计某一个执行的时间用
	 */
	public static function resetTime() {
		self::$timer = microtime(true);
	}

}
