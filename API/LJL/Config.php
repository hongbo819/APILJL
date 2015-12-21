<?php
/**
* 获取配置
*/

class LJL_Config
{
    /**
    * 配置缓存
    * @var array
    */
	private static $_cache;

	/**
	* 获取
	*
	* @param mixed $key 配置地址
	* @param mixed $arrKeyName 配置子键，可多维，用.号分隔
	* @param enum $type PHP|INI|EVAL
	* @return array|false
	*/
	public static function get($key, $arrKeyName = '', $type = 'PHP')
	{        
        $fileExt = array( #不同配置文件的扩展名映射关系
            'ini'   => 'ini',
            'php'   => 'php',
            'eval'  => 'php',
        );
		$path = LJL_API_ROOT . '/Config/' . str_replace('_', '/', $key) . '.' . $fileExt[strtolower($type)];
		if (isset(self::$_cache[$path])) {
			$config = self::$_cache[$path];
		} else {
			if (!API_File::exists($path)) {
				self::$_cache[$path] = false;
				return false;
			}
            $type = strtoupper($type);
			switch ($type) {
				case 'INI':
					$config = parse_ini_file($path);
					break;
                case 'EVAL': #config中可以有变量，这样可以将变量传入到config中
                    if(is_array($arrKeyName)){
                        extract($arrKeyName);
                    }
					$config = include($path);
                    break;
				default:
				case 'PHP':
					$config = include($path);
					break;
			}
			self::$_cache[$path] = $config;
		}

		if ($arrKeyName && $type != 'EVAL') {
			if (strpos('.', $arrKeyName)) {
				$keyArr = explode('.', $arrKeyName);
				foreach ($keyArr as $key) {
					if (!$config) {
						break;
					}

					$config = empty($config[$key]) ? false : $config[$key];
				}
			} else {
				$config = empty($config[$arrKeyName]) ? false : $config[$arrKeyName];
			}
		}
		return $config;
	}
}