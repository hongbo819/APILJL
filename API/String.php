<?php

/*
|---------------------------------------------------------------
| Various static string helper methods.
|---------------------------------------------------------------
| @package LJL
|
*/
class API_String
{

	public static function trimWhitespace($var)
	{
		if (!isset($var)) {
			return false;
		}
		if (is_array($var)) {
			$newArray = array();
			foreach ($var as $key => $value) {
				$newArray[$key] = self::trimWhitespace($value);
			}
			return $newArray;
		} else {
			return trim($var);
		}
	}

	/*
	|---------------------------------------------------------------
	| Returns cleaned user input.
	|---------------------------------------------------------------
	| @access  public
	| @param   string $var  The string to clean.
	| @return  string       $cleaned result.
	*/
	public static function clean($var)
	{
		if (!isset($var)) {
			return false;
		}
		$var = self::trimWhitespace($var);
		if (is_array($var)) {
			$newArray = array();
			foreach ($var as $key => $value) {
				$newArray[$key] = self::clean(self::addslashes($value));
			}
			return $newArray;
		} else {
			return strip_tags($var);
		}
	}

	public static function removeJs($var)
	{
		if (!isset($var)) {
			return false;
		}
		$var = self::trimWhitespace($var);
		if (is_array($var)) {
			$newArray = array();
			foreach ($var as $key => $value) {
				$newArray[$key] = self::removeJs($value);
			}
			return $newArray;
		} else {
			$search = "/<script[^>]*?>.*?<\/script\s*>/i";
			$replace = '';
			$clean = preg_replace($search, $replace, $var);
			return $clean;
		}
	}

	public static function toValidVariableName($str)
	{
		//  remove illegal chars
		$search = '/[^a-zA-Z1-9_]/';
		$replace = '';
		$res = preg_replace($search, $replace, $str);
		//  ensure 1st letter is lc
		$firstLetter = strtolower($res[0]);
		$final = substr_replace($res, $firstLetter, 0, 1);
		return $final;
	}

	public static function toValidFileName($origName)
	{
		return self::dirify($origName);
	}

	//  from http://kalsey.com/2004/07/dirify_in_php/
	public static function dirify($s)
	{
		 $s = self::_convertHighAscii($s);     ## convert high-ASCII chars to 7bit.
		 $s = strtolower($s);                       ## lower-case.
		 $s = strip_tags($s);                       ## remove HTML tags.
		 // Note that &nbsp (for example) is legal in HTML 4, ie. semi-colon is optional if it is followed
		 // by a non-alphanumeric character (eg. space, tag...).
//         $s = preg_replace('!&[^;\s]+;!','',$s);    ## remove HTML entities.
		 $s = preg_replace('!&#?[A-Za-z0-9]{1,7};?!', '', $s);    ## remove HTML entities.
		 $s = preg_replace('![^\w\s-]!', '',$s);    ## remove non-word/space chars.
		 $s = preg_replace('!\s+!', '_',$s);        ## change space chars to underscores.
		 return $s;
	}

	protected static function _convertHighAscii($s)
	{
		// Seems to be for Latin-1 (ISO-8859-1) and quite limited (no ae/oe, no y:/Y:, etc.)
		 $aHighAscii = array(
		   "!\xc0!" => 'A',    # A`
		   "!\xe0!" => 'a',    # a`
		   "!\xc1!" => 'A',    # A'
		   "!\xe1!" => 'a',    # a'
		   "!\xc2!" => 'A',    # A^
		   "!\xe2!" => 'a',    # a^
		   "!\xc4!" => 'A',    # A:
		   "!\xe4!" => 'a',    # a:
		   "!\xc3!" => 'A',    # A~
		   "!\xe3!" => 'a',    # a~
		   "!\xc8!" => 'E',    # E`
		   "!\xe8!" => 'e',    # e`
		   "!\xc9!" => 'E',    # E'
		   "!\xe9!" => 'e',    # e'
		   "!\xca!" => 'E',    # E^
		   "!\xea!" => 'e',    # e^
		   "!\xcb!" => 'E',    # E:
		   "!\xeb!" => 'e',    # e:
		   "!\xcc!" => 'I',    # I`
		   "!\xec!" => 'i',    # i`
		   "!\xcd!" => 'I',    # I'
		   "!\xed!" => 'i',    # i'
		   "!\xce!" => 'I',    # I^
		   "!\xee!" => 'i',    # i^
		   "!\xcf!" => 'I',    # I:
		   "!\xef!" => 'i',    # i:
		   "!\xd2!" => 'O',    # O`
		   "!\xf2!" => 'o',    # o`
		   "!\xd3!" => 'O',    # O'
		   "!\xf3!" => 'o',    # o'
		   "!\xd4!" => 'O',    # O^
		   "!\xf4!" => 'o',    # o^
		   "!\xd6!" => 'O',    # O:
		   "!\xf6!" => 'o',    # o:
		   "!\xd5!" => 'O',    # O~
		   "!\xf5!" => 'o',    # o~
		   "!\xd8!" => 'O',    # O/
		   "!\xf8!" => 'o',    # o/
		   "!\xd9!" => 'U',    # U`
		   "!\xf9!" => 'u',    # u`
		   "!\xda!" => 'U',    # U'
		   "!\xfa!" => 'u',    # u'
		   "!\xdb!" => 'U',    # U^
		   "!\xfb!" => 'u',    # u^
		   "!\xdc!" => 'U',    # U:
		   "!\xfc!" => 'u',    # u:
		   "!\xc7!" => 'C',    # ,C
		   "!\xe7!" => 'c',    # ,c
		   "!\xd1!" => 'N',    # N~
		   "!\xf1!" => 'n',    # n~
		   "!\xdf!" => 'ss'
		 );
		 $find = array_keys($aHighAscii);
		 $replace = array_values($aHighAscii);
		 $s = preg_replace($find, $replace, $s);
		 return $s;
	}

	protected function _to7bit($text)
	{
		if (!function_exists('mb_convert_encoding')) {
			return $text;
		}
		$text = mb_convert_encoding($text,'HTML-ENTITIES',mb_detect_encoding($text));
		$text = preg_replace(
		   array('/&szlig;/','/&(..)lig;/',
				 '/&([aouAOU])uml;/','/&(.)[^;]*;/'),
		   array('ss',"$1","$1".'e',"$1"),
		   $text);
		return $text;
	}

	/*
	|---------------------------------------------------------------
	| Replaces accents in string.
	|---------------------------------------------------------------
	| @todo make it work with cyrillic chars
	| @todo make it work with non utf-8 encoded strings
	| @see API_String::isCyrillic()
	| @param string $str
	| @return string
	*/
	public static function replaceAccents($str)
	{
		if (!self::_isCyrillic($str)) {
			$str = self::_to7bit($str);
			$str = preg_replace('/[^A-Z^a-z^0-9()]+/',' ',$str);
		}
		return $str;
	}

	/*
	|---------------------------------------------------------------
	| Checks if strings has cyrillic chars.
	|---------------------------------------------------------------
	| @param string $str
	| @return boolean
	*/
	protected function _isCyrillic($str)
	{
		$ret = false;
		if (function_exists('mb_convert_encoding') && !empty($str)) {
			// codes for Russian chars
			$aCodes = range(1040, 1103);
			// convert to entities
			$encoded = mb_convert_encoding($str, 'HTML-ENTITIES',
				mb_detect_encoding($str));
			// get codes of the string
			$aChars = explode(';', str_replace('&#', '', $encoded));
			array_pop($aChars);
			$aChars = array_unique($aChars);
			// see if cyrillic chars there
			$aNonCyrillicChars = array_diff($aChars, $aCodes);
			// if string is the same -> no cyrillic chars
			$ret = count($aNonCyrillicChars) != count($aChars);
		}
		return $ret;
	}

	/*
	|---------------------------------------------------------------
	| Removes chars that are illegal in ini files.
	|---------------------------------------------------------------
	| @param string $string
	| @return string
	*/
	public static function stripIniFileIllegalChars($string)
	{
		return preg_replace("/[\|\&\~\!\"\(\)]/i", "", $string);
	}

	/*
	|---------------------------------------------------------------
	| Converts strings representing constants to int values.
	| Used for when constants are stored as strings in config.
	|---------------------------------------------------------------
	| @param string $string
	| @return integer
	*/
	public static function pseudoConstantToInt($string)
	{
		$ret = 0;
		if (is_int($string)) {
			$ret = $string;
		}
		if (is_numeric($string)) {
			$ret = (int)$string;
		}
		if (LJL_Inflector::isConstant($string)) {
			$const = str_replace("'", '', $string);
			if (defined($const)) {
				$ret = constant($const);
			}
		}
		return $ret;
	}

	/*
	|---------------------------------------------------------------
	| Esacape single quote.
	|---------------------------------------------------------------
	| @param string $string
	| @return  string
	*/
	public static function escapeSingleQuote($string)
	{
		$ret = str_replace('\\', '\\\\', $string);
		$ret = str_replace("'", '\\\'', $ret);
		return $ret;
	}


	/*
	|---------------------------------------------------------------
	| Escape single quotes in every key of given array.
	|---------------------------------------------------------------
	| @param   array $array
	| @static
	*/
	public static function escapeSingleQuoteInArrayKeys($array)
	{
		$ret = array();
		foreach ($array as $key => $value) {
			$k = self::escapeSingleQuote($key);
			$ret[$k] = is_array($value)
				? self::escapeSingleQuoteInArrayKeys($value)
				: $value;
		}
		return $ret;
	}

	/*
	|---------------------------------------------------------------
	| 将一个字串中含有全角或半角的数字字符、字母、空格或'%+-()'字符互换
	|---------------------------------------------------------------
	| @static
	| @access  public
	| @param   string       $str         待转换字串
	| @param   boolean      $reverse     默认true为全角转半角, false为半角转全角
	| @return  string       $str         处理后字串
	*/

	public static function convertSemiangle($str, $reverse = true)
	{
		$arr = array('０' => '0', '１' => '1', '２' => '2', '３' => '3', '４' => '4',
					 '５' => '5', '６' => '6', '７' => '7', '８' => '8', '９' => '9',
					 'Ａ' => 'A', 'Ｂ' => 'B', 'Ｃ' => 'C', 'Ｄ' => 'D', 'Ｅ' => 'E',
					 'Ｆ' => 'F', 'Ｇ' => 'G', 'Ｈ' => 'H', 'Ｉ' => 'I', 'Ｊ' => 'J',
					 'Ｋ' => 'K', 'Ｌ' => 'L', 'Ｍ' => 'M', 'Ｎ' => 'N', 'Ｏ' => 'O',
					 'Ｐ' => 'P', 'Ｑ' => 'Q', 'Ｒ' => 'R', 'Ｓ' => 'S', 'Ｔ' => 'T',
					 'Ｕ' => 'U', 'Ｖ' => 'V', 'Ｗ' => 'W', 'Ｘ' => 'X', 'Ｙ' => 'Y',
					 'Ｚ' => 'Z', 'ａ' => 'a', 'ｂ' => 'b', 'ｃ' => 'c', 'ｄ' => 'd',
					 'ｅ' => 'e', 'ｆ' => 'f', 'ｇ' => 'g', 'ｈ' => 'h', 'ｉ' => 'i',
					 'ｊ' => 'j', 'ｋ' => 'k', 'ｌ' => 'l', 'ｍ' => 'm', 'ｎ' => 'n',
					 'ｏ' => 'o', 'ｐ' => 'p', 'ｑ' => 'q', 'ｒ' => 'r', 'ｓ' => 's',
					 'ｔ' => 't', 'ｕ' => 'u', 'ｖ' => 'v', 'ｗ' => 'w', 'ｘ' => 'x',
					 'ｙ' => 'y', 'ｚ' => 'z',
					 '（' => '(', '）' => ')', '〔' => '[', '〕' => ']', '【' => '[',
					 '】' => ']', '〖' => '[', '〗' => ']', '“' => '[', '”' => ']',
					 '‘' => '[', '’' => ']', '｛' => '{', '｝' => '}', '《' => '<',
					 '》' => '>',
					 '％' => '%', '＋' => '+', '—' => '-', '－' => '-', '～' => '-',
					 '：' => ':', '。' => '.', '、' => ',', '，' => '.', '、' => '.',
					 '；' => ',', '？' => '?', '！' => '!', '…' => '-', '‖' => '|',
					 '”' => '"', '’' => '`', '‘' => '`', '｜' => '|', '〃' => '"',
					 '　' => ' ');
		if (false === $reverse)
		{
			$arr = array_flip($arr);
		}
		return strtr($str, $arr);
	}

	/**
	 * convert utf-8 encoding data to other encodings
	 *
	 * @param mixed $input
	 * @param string $encoding
	 * @return mixed
	 */
	public static function u8conv($input, $encoding='GBK')
	{
		if(is_string($input))
		{
			if(strtoupper($encoding)=='UTF-8')
			{
				return $input;
			}
			return mb_convert_encoding($input, $encoding, 'UTF-8');
		}
		else
		{
			$output = array();
			foreach ((array)$input as $k=>$v)
			{
				$output[$k] = self::u8conv($v, $encoding);
			}
			return $output;
		}
	}

	/**
	 * 将字符转为utf8字符
	 */
	public static function convToU8($input, $encoding='GBK')
	{
		if(is_string($input))
		{
			return iconv($encoding, 'UTF-8//TRANSLIT', $input);
		}
		else
		{
			$output = array();
			foreach ((array)$input as $k=>$v)
			{
				$output[$k] = self::convToU8($v, $encoding);
			}
			return $output;
		}
	}


	public static function stripslashes($val)
	{
		if (get_magic_quotes_gpc())
		{
			return stripslashes($val);
		} else {
			return $val;
		}
	}

	public static function addslashes($val)
	{
		if (!get_magic_quotes_gpc())
		{
			return addslashes($val);
		}
		else
		{
			return $val;
		}
	}
	
	public static function convertEncodingDeep($value, $target_lang, $source_lang)
	{
		if (empty($value))
		{
			return $value;
		}
		else
		{
			if (is_array($value))
			{
				foreach ($value as $k=>$v)
				{
					#$value[$k] = self::convertEncodingDeep($source_lang, $target_lang, $v);
                    $value[$k] = self::convertEncodingDeep($v,$target_lang,$source_lang);
				}
				return $value;
			}
			elseif (is_string($value))
			{
				return mb_convert_encoding($value, $target_lang, $source_lang);
			}
			else
			{
				return $value;
			}
		}  
	}
	
	public static function addslashesDeep($value)
	{
		if (empty($value) || get_magic_quotes_gpc())
		{
			return $value;
		}
		else
		{
			return is_array($value) ? array_map(array(self, __FUNCTION__), $value) : addslashes($value);
		}           
	}
	   
	public static function substr($str, $len, $charset = 'gbk')
	{
		if (!function_exists('cnsubstr_ext') || 'utf-8' == strtolower($charset))
		{
			return self::substr_php($str, $len, $charset);
		}
		else
		{
			return cnsubstr_ext($str, $len);
		}
	}
	public static function substr_php($str, $len, $charset = 'gbk')
	{
		if (empty($str))
		{
			return false;
		}
		if ($len >= strlen($str) || $len < 1)
		{
			return $str;
		}

		$str = str_replace(array('&amp;', '&quot;', '&lt;', '&gt;'), array('&', '"', '<', '>'), $str);

		$strcut = array();
		$temp_str = '';
		$sublen = (strtolower($charset) == 'utf-8') ? 3 : 2;
		for ($i = 0; $i < $len; ++ $i)
		{
			$temp_str = substr($str, 0, 1);

			if (ord($temp_str) > 127)
			{
				++ $i;
				if ($sublen == 3)
				{
					++ $i;
				}
				if($i < $len)
				{
					$strcut[] = substr($str, 0, $sublen);
					$str = substr($str, $sublen);
				}
			}
			else
			{
				if ($i < $len)
				{
					$strcut[] = substr($str, 0, 1);
					$str = substr($str, 1);
				}
			}
		}
		if (!empty($strcut))
		{
			$strcut = join($strcut);
			$strcut = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $strcut);

			return $strcut;
		}
		else
		{
			return '';
		}
	}
	
    /**
     * 加密中文 同JS同名函数功能
     * @param string $str 要转码的字符
     * @param string $encoding 字符的编码方式
     * @return encode string 回返已转码的字符
     */
    public static function escape($str, $encoding = 'GBK', $prefix = '%')
    {
        $return = '';
        for ($x = 0; $x < mb_strlen($str, $encoding); $x++) {
            $s = mb_substr($str, $x, 1, $encoding);
            if (strlen($s) > 1) {//多字节字符
                $return .= $prefix . 'u' . strtoupper(bin2hex(mb_convert_encoding($s, 'UCS-2', $encoding))); 
            } else {
                $return .= $prefix . strtoupper(bin2hex($s));
            }
        }
        return $return; 
    }

    /**
     * UTF-8转GBK 用于rewrite后的关键字处理
     * @param string $str 要转码的字符
     * @return encode string 回返已转码的字符
     */
    public static function kwUrldecode($str)
    {
        $str = urldecode(str_replace('@', '%', $str)); //关键字转码
        $str = iconv('UTF-8', 'GBK', $str);
        return $str; 
    }

    /**
     * GBK转UTF-8 用于rewrite后的关键字处理
     * @param string $str 要转码的字符
     * @return encode string 回返已转码的字符
     */
    public static function kwUrlencode($str)
    {
        $str = iconv('GBK', 'UTF-8', $str);
        $str = urlencode($str);
        $str = str_replace('%', '@', $str);
        return $str;
    }
	
	/**
	* 解析JS的escape编码
	* 
	* @param string $str
	* @param string $encoding
	*/
	public static function unescape($str, $encoding = 'GBK', $prefix = '%')
	{
        $prefix != '%' && $str = str_replace($prefix, '%', $str);
        $str  = rawurldecode($str);
		$text = preg_replace_callback("/%u[0-9A-Za-z]{4}/", array(__CLASS__, 'unicode2Utf8'), $str);
		return self::u8conv($text, $encoding);
	}
    
    /**
     * 处理HTML中的转义字符
     * @param string $str 要处理的转义字符
     * @param string $encoding 转换后的编码方式
     * @return string 
     */
    public static function recode($str, $encoding = 'GBK')
    {
        if (function_exists('recode')) {
            return recode("html..{$encoding}", $str);
        } else {
            return self::phprecode($str, $encoding);
        }
    }
    
    /**
     * PHP版的recode，只处理HTML中的转义字符
     * @param string $str 要处理的转义字符
     * @param string $encoding 转换后的编码方式
     * @return string 
     */
    public static function phprecode($str, $encoding = 'GBK')
    {
        $text = preg_replace_callback("/&#[0-9]{1,5}/", array(__CLASS__, 'htmlDecode'), $str);
        return self::u8conv($text, $encoding);
    }
	
    public static function htmlDecode($ar)
    {
		$str = '';
        foreach ($ar as $val) {
            $c = substr($val, 2);
            if ($c < 0x80) { 
                $str.= chr($c);
            } else if ($c < 0x800) {
                $str.= chr(0xC0 | $c>>6);
                $str.= chr(0x80 | $c & 0x3F); 
            } else if ($c < 0x10000) {
                $str.= chr(0xE0 | $c>>12); 
                $str.= chr(0x80 | $c>>6 & 0x3F); 
                $str.= chr(0x80 | $c & 0x3F); 
            } else if ($c < 0x200000) { 
                $str.= chr(0xF0 | $c>>18); 
                $str.= chr(0x80 | $c>>12 & 0x3F); 
                $str.= chr(0x80 | $c>>6 & 0x3F); 
                $str.= chr(0x80 | $c & 0x3F); 
            }
        }
		return $str;
    }
    
    
	/**
	* 转换UNICODE编码为UTF8
	* 
	* @param mixed $ar
	*/
	public static function unicode2Utf8($ar)
	{
		$c = '';
		foreach($ar as $val) {
			$val = intval(substr($val, 2), 16);
			if ($val < 0x7F) {        // 0000-007F 单字节
				$c .= chr($val);
			} elseif ($val < 0x800) { // 0080-0800 双字节
				$c .= chr(0xC0 | ($val / 64));
				$c .= chr(0x80 | ($val % 64));
			} else {                // 0800-FFFF 三字节
				$c .= chr(0xE0 | (($val / 64) / 64));
				$c .= chr(0x80 | (($val / 64) % 64));
				$c .= chr(0x80 | ($val % 64));
			}
		}
        return $c;
	}
    
    public static function utf82Unicode($str)
    {
        switch(strlen($c)) { 
            case 1:
            return ord($c); 
        case 2:
            $n = (ord($c[0]) & 0x3f) << 6;
            $n += ord($c[1]) & 0x3f;
            return $n;
        case 3:
            $n = (ord($c[0]) & 0x1f) << 12;
            $n += (ord($c[1]) & 0x3f) << 6;
            $n += ord($c[2]) & 0x3f;
            return $n;
        case 4:
            $n = (ord($c[0]) & 0x0f) << 18;
            $n += (ord($c[1]) & 0x3f) << 12;
            $n += (ord($c[2]) & 0x3f) << 6;
            $n += ord($c[3]) & 0x3f;
            return $n;
        }
    }

	/**
	 * 加密解密函数
	 *
	 * @param   string     加解密字符串
	 * @param   string       EN 加密 | DE 解密	 *
	 * @return  string
     * 例子:API_String::mcrypt(serialize($arr),"EN","KEYKEY"); 加密数组
	 */
	public static function mcrypt($string="",$type="EN",$mcrypt_key='LJL_FRAMEWORK'){

		$mcrypt_cipher_alg  = MCRYPT_RIJNDAEL_128;
		$iv = mcrypt_create_iv(mcrypt_get_iv_size($mcrypt_cipher_alg,MCRYPT_MODE_ECB), MCRYPT_RAND);
		switch($type){
			case "EN":
				@$new_string=mcrypt_encrypt($mcrypt_cipher_alg, $mcrypt_key, $string, MCRYPT_MODE_ECB, $iv);
				$new_string = bin2hex($new_string);
				break;
			case "DE":
				@$string=pack("H*",$string);
				@$new_string=mcrypt_decrypt($mcrypt_cipher_alg, $mcrypt_key, $string, MCRYPT_MODE_ECB, $iv);
				$new_string = trim($new_string);
				break;

		}
		return $new_string;
	}


    public static function Pinyin($_String) {
        $_DataKey = "a|ai|an|ang|ao|ba|bai|ban|bang|bao|bei|ben|beng|bi|bian|biao|bie|bin|bing|bo|bu|ca|cai|can|cang|cao|ce|ceng|cha".
                    "|chai|chan|chang|chao|che|chen|cheng|chi|chong|chou|chu|chuai|chuan|chuang|chui|chun|chuo|ci|cong|cou|cu|".
                    "cuan|cui|cun|cuo|da|dai|dan|dang|dao|de|deng|di|dian|diao|die|ding|diu|dong|dou|du|duan|dui|dun|duo|e|en|er".
                    "|fa|fan|fang|fei|fen|feng|fo|fou|fu|ga|gai|gan|gang|gao|ge|gei|gen|geng|gong|gou|gu|gua|guai|guan|guang|gui".
                    "|gun|guo|ha|hai|han|hang|hao|he|hei|hen|heng|hong|hou|hu|hua|huai|huan|huang|hui|hun|huo|ji|jia|jian|jiang".
                    "|jiao|jie|jin|jing|jiong|jiu|ju|juan|jue|jun|ka|kai|kan|kang|kao|ke|ken|keng|kong|kou|ku|kua|kuai|kuan|kuang".
                    "|kui|kun|kuo|la|lai|lan|lang|lao|le|lei|leng|li|lia|lian|liang|liao|lie|lin|ling|liu|long|lou|lu|lv|luan|lue".
                    "|lun|luo|ma|mai|man|mang|mao|me|mei|men|meng|mi|mian|miao|mie|min|ming|miu|mo|mou|mu|na|nai|nan|nang|nao|ne".
                    "|nei|nen|neng|ni|nian|niang|niao|nie|nin|ning|niu|nong|nu|nv|nuan|nue|nuo|o|ou|pa|pai|pan|pang|pao|pei|pen".
                    "|peng|pi|pian|piao|pie|pin|ping|po|pu|qi|qia|qian|qiang|qiao|qie|qin|qing|qiong|qiu|qu|quan|que|qun|ran|rang".
                    "|rao|re|ren|reng|ri|rong|rou|ru|ruan|rui|run|ruo|sa|sai|san|sang|sao|se|sen|seng|sha|shai|shan|shang|shao|".
                    "she|shen|sheng|shi|shou|shu|shua|shuai|shuan|shuang|shui|shun|shuo|si|song|sou|su|suan|sui|sun|suo|ta|tai|".
                    "tan|tang|tao|te|teng|ti|tian|tiao|tie|ting|tong|tou|tu|tuan|tui|tun|tuo|wa|wai|wan|wang|wei|wen|weng|wo|wu".
                    "|xi|xia|xian|xiang|xiao|xie|xin|xing|xiong|xiu|xu|xuan|xue|xun|ya|yan|yang|yao|ye|yi|yin|ying|yo|yong|you".
                    "|yu|yuan|yue|yun|za|zai|zan|zang|zao|ze|zei|zen|zeng|zha|zhai|zhan|zhang|zhao|zhe|zhen|zheng|zhi|zhong|".
                    "zhou|zhu|zhua|zhuai|zhuan|zhuang|zhui|zhun|zhuo|zi|zong|zou|zu|zuan|zui|zun|zuo|zhen";

        $_DataValue = "-20319|-20317|-20304|-20295|-20292|-20283|-20265|-20257|-20242|-20230|-20051|-20036|-20032|-20026|-20002|-19990".
                      "|-19986|-19982|-19976|-19805|-19784|-19775|-19774|-19763|-19756|-19751|-19746|-19741|-19739|-19728|-19725".
                      "|-19715|-19540|-19531|-19525|-19515|-19500|-19484|-19479|-19467|-19289|-19288|-19281|-19275|-19270|-19263".
                      "|-19261|-19249|-19243|-19242|-19238|-19235|-19227|-19224|-19218|-19212|-19038|-19023|-19018|-19006|-19003".
                      "|-18996|-18977|-18961|-18952|-18783|-18774|-18773|-18763|-18756|-18741|-18735|-18731|-18722|-18710|-18697".
                      "|-18696|-18526|-18518|-18501|-18490|-18478|-18463|-18448|-18447|-18446|-18239|-18237|-18231|-18220|-18211".
                      "|-18201|-18184|-18183|-18181|-18012|-17997|-17988|-17970|-17964|-17961|-17950|-17947|-17931|-17928|-17922".
                      "|-17759|-17752|-17733|-17730|-17721|-17703|-17701|-17697|-17692|-17683|-17676|-17496|-17487|-17482|-17468".
                      "|-17454|-17433|-17427|-17417|-17202|-17185|-16983|-16970|-16942|-16915|-16733|-16708|-16706|-16689|-16664".
                      "|-16657|-16647|-16474|-16470|-16465|-16459|-16452|-16448|-16433|-16429|-16427|-16423|-16419|-16412|-16407".
                      "|-16403|-16401|-16393|-16220|-16216|-16212|-16205|-16202|-16187|-16180|-16171|-16169|-16158|-16155|-15959".
                      "|-15958|-15944|-15933|-15920|-15915|-15903|-15889|-15878|-15707|-15701|-15681|-15667|-15661|-15659|-15652".
                      "|-15640|-15631|-15625|-15454|-15448|-15436|-15435|-15419|-15416|-15408|-15394|-15385|-15377|-15375|-15369".
                      "|-15363|-15362|-15183|-15180|-15165|-15158|-15153|-15150|-15149|-15144|-15143|-15141|-15140|-15139|-15128".
                      "|-15121|-15119|-15117|-15110|-15109|-14941|-14937|-14933|-14930|-14929|-14928|-14926|-14922|-14921|-14914".
                      "|-14908|-14902|-14894|-14889|-14882|-14873|-14871|-14857|-14678|-14674|-14670|-14668|-14663|-14654|-14645".
                      "|-14630|-14594|-14429|-14407|-14399|-14384|-14379|-14368|-14355|-14353|-14345|-14170|-14159|-14151|-14149".
                      "|-14145|-14140|-14137|-14135|-14125|-14123|-14122|-14112|-14109|-14099|-14097|-14094|-14092|-14090|-14087".
                      "|-14083|-13917|-13914|-13910|-13907|-13906|-13905|-13896|-13894|-13878|-13870|-13859|-13847|-13831|-13658".
                      "|-13611|-13601|-13406|-13404|-13400|-13398|-13395|-13391|-13387|-13383|-13367|-13359|-13356|-13343|-13340".
                      "|-13329|-13326|-13318|-13147|-13138|-13120|-13107|-13096|-13095|-13091|-13076|-13068|-13063|-13060|-12888".
                      "|-12875|-12871|-12860|-12858|-12852|-12849|-12838|-12831|-12829|-12812|-12802|-12607|-12597|-12594|-12585".
                      "|-12556|-12359|-12346|-12320|-12300|-12120|-12099|-12089|-12074|-12067|-12058|-12039|-11867|-11861|-11847".
                      "|-11831|-11798|-11781|-11604|-11589|-11536|-11358|-11340|-11339|-11324|-11303|-11097|-11077|-11067|-11055".
                      "|-11052|-11045|-11041|-11038|-11024|-11020|-11019|-11018|-11014|-10838|-10832|-10815|-10800|-10790|-10780".
                      "|-10764|-10587|-10544|-10533|-10519|-10331|-10329|-10328|-10322|-10315|-10309|-10307|-10296|-10281|-10274".
                      "|-10270|-10262|-10260|-10256|-10254|-9254";
        $_TDataKey   = explode('|', $_DataKey);
        $_TDataValue = explode('|', $_DataValue);

        $_Data = array_combine($_TDataKey, $_TDataValue);
        arsort($_Data);
        reset($_Data);

        $_Res = '';
        for($i=0; $i<strlen($_String); $i++) {
            $_P = ord(substr($_String, $i, 1));
            if($_P>160) {
                $_Q = ord(substr($_String, ++$i, 1)); $_P = $_P*256 + $_Q - 65536;
            }

            if ($_P>0 && $_P<160 ) $_Res .= chr($_P);
            elseif(-9254 == $_P) $_Res .= 'zhen';
            elseif(-13886 == $_P) $_Res .= 'shan3';
            elseif($_P<-20319 || $_P>-10247) $_Res .= '';
            else {
                foreach($_Data as $k=>$v){ if($v<=$_P) break; }
                $_Res .= $k;
            }

        }
        return preg_replace("/[^a-z0-9]*/", '', $_Res);
    }
    /**
     * 判断字符串中有没有网址
     */
    public static function checkHasUrl($str){
        $re="/([A-Z0-9][A-Z0-9_-]*(?:\.[a-z0-9][A-Z0-9_-]*)+):?(\d+)?\/?/i";
        $check = 0;
        if(preg_match($re,$str)){
            $check = 1;
        }
        return $check;
    }
}


