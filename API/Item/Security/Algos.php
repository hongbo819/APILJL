<?php
/**
* 安全算法相关
*/
class API_Item_Security_Algos
{
     /**
     * 获得用户唯一码 (用户信息+进程信息+时间戳+随机数)
     */
    public static function getUniqueCode(){
        #获得唯一的用户信息
        $cookieArr = LJL_API_ISFW ? LJL_Api::$_globalVars['_COOKIE'] : $_COOKIE;
        #根据具体项目更改cookie
        $userId    = isset($cookieArr["xx_userid"]) ? $cookieArr["xxx_userid"]
                                                     : isset($cookieArr["ip_ck"]) ? $cookieArr["ip_ck"] : $_SERVER['REMOTE_ADDR'];
        $userIp    = $_SERVER['REMOTE_ADDR'];
        #时间信息
        $time      = microtime(true);
        #服务器信息
        $server = $_SERVER['SERVER_ADDR'];
        #进程信息
        $pid = '';
        if (function_exists('getmypid')) {
             $pid = getmypid();
        }
        $rand = rand(0, 1000);
        return md5($userId . $userIp .$time. $pid . $server .$rand );

    }
     /**
     * URL的签名算法，返回一个token字符串
     */
    public static function urlSign($paramArr){
        
        $options = array(
            'queryParam'  => '',  #请求参数，可以传入参数数组（一维） 也可以传入name=a&age=12的参数
            'cryptkey'    => '',  #签名的密钥
            'timeInfo'    => 0,   #添加时间信息
        );
        if (is_array($paramArr))$options = array_merge($options, $paramArr);
        extract($options);

        if(!$queryParam)return '';

        if(is_string($queryParam)) parse_str($queryParam,$queryParam);
        #对参数数组进行排序，保证参数传入的顺序不同，同样能得到结果
        ksort($queryParam);
        $queryString = array();
        foreach ($queryParam as $key => $val){
            array_push($queryString, $key . '=' . $val);
        }
        $queryString = join('&', $queryString);
        if($timeInfo){
            //为了获取时间 可逆
            $queryString .= "#" .time(); #将时间戳并入
            $sign =  self::fastEncode(array('value'=>$queryString,'cryptkey'=>$cryptkey));
        }else{
            //没有时间信息 不可逆
            $sign = hash_hmac("sha1",$queryString,$cryptkey);
        }        
       
        return $sign;
    }
    
     /**
     * 获得URL的签名时间戳
     */
    public static function getUrlSignTm($paramArr){
        $options = array(
            'signStr'     => '',  #url签名串
            'cryptkey'    => '',  #签名的密钥
        );
        if (is_array($paramArr))$options = array_merge($options, $paramArr);
        extract($options);
        $sign =  self::fastDecode(array('value'=>$signStr,'cryptkey'=>$cryptkey));
        #时间戳部分
        $tm   = (int)substr($sign, strpos($sign, "#")+1);
        #签名部分
        $data = substr($sign, 0,strpos($sign, "#"));
        if($data)parse_str($data,$data);
        return array(
            'data' => $data,
            'tm'   => $tm,
        );
    }

    /**
     * 3DES 加密算法(安全性高)
     */
	public static function des3Encrypt($paramArr) {
        $options = array(
            'value'       => 0, #加密的字符
            'cryptkey'    => 0, #加密用的key
        );
        if (is_array($paramArr))$options = array_merge($options, $paramArr);
        extract($options);
		$td = mcrypt_module_open(MCRYPT_3DES, '', MCRYPT_MODE_ECB, '');
		$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_DEV_URANDOM);
		$key = substr($cryptkey, 0, mcrypt_enc_get_key_size($td));
		mcrypt_generic_init($td, $key, $iv);
		$ret = self::base64UrlEncode(mcrypt_generic($td, $value));
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
		return $ret;
	}

    /**
     * 3DES 解密算法
     */
	public static function des3Dencrypt($paramArr) {
        $options = array(
            'value'       => 0, #加密的字符
            'cryptkey'    => 0, #加密用的key
        );
        if (is_array($paramArr))$options = array_merge($options, $paramArr);
        extract($options);
        
		$td = mcrypt_module_open(MCRYPT_3DES, '', MCRYPT_MODE_ECB, '');
		$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_DEV_URANDOM);
		$key = substr($cryptkey, 0, mcrypt_enc_get_key_size($td));
		mcrypt_generic_init($td, $key, $iv);
		$ret = trim(mdecrypt_generic($td, self::base64UrlDecode($value))) ;
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
		return $ret;
	}
    

    /**
     * 快速的加密算法（安全性差）
     */
    public static function fastEncode($paramArr) {
        $options = array(
            'value'       => 0, #加密的字符
            'cryptkey'    => 0, #加密用的key
        );
        if (is_array($paramArr))$options = array_merge($options, $paramArr);
        extract($options);

        return self::_fastCode($value,'ENCODE',$cryptkey);

    }
    /**
     * 快速的解密算法
     */
    public static function fastDecode($paramArr) {
        $options = array(
            'value'       => 0, #加密的字符
            'cryptkey'    => 0, #加密用的key
        );
        if (is_array($paramArr))$options = array_merge($options, $paramArr);
        extract($options);
		//echo $value,$cryptkey;
        return self::_fastCode($value,'DECODE',$cryptkey);

    }
    private static function _fastCode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
    	
        $auth_key = '%&LJL_cui!Q@W#$6y8i';
        $ckey_length = 4;
        $key  = md5($key ? $key : $auth_key);
        $keya = md5(substr($key, 0, 16));
        $keyb = md5(substr($key, 16, 16));
        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

        $cryptkey = $keya.md5($keya.$keyc);
        $key_length = strlen($cryptkey);

        //echo substr($string, $ckey_length);
        $string = $operation == 'DECODE' ? self::base64UrlDecode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
        $string_length = strlen($string);

        $result = '';
        $box = range(0, 255);

        $rndkey = array();
        for($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }

        for($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }

        for($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }
		//var_dump($result);die;
        if($operation == 'DECODE') {
            if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
                return substr($result, 26);
            } else {
                return '';
            }
        } else {
        	//var_dump($keyc.str_replace('=', '', self::base64UrlEncode($result)));
            return $keyc.str_replace('=', '', self::base64UrlEncode($result));
        }
    }
    /**
     * 简单的加密形式
     */
    public static function simpleEncrypt($paramArr) {
        $options = array(
            'value'       => 0, #加密的字符
            'cryptkey'    => 0, #加密用的key
        );
        if (is_array($paramArr))$options = array_merge($options, $paramArr);
        extract($options);

        $mcrypt_cipher_alg  = MCRYPT_RIJNDAEL_128;
		$iv = mcrypt_create_iv(mcrypt_get_iv_size($mcrypt_cipher_alg,MCRYPT_MODE_ECB));

        $newString = mcrypt_encrypt($mcrypt_cipher_alg, $cryptkey, $value, MCRYPT_MODE_ECB, $iv);
        return bin2hex($newString);

    }
    /**
     * 简单的解密形式
     */
    public static function simpleDecrypt($paramArr) {
        $options = array(
            'value'       => 0, #加密的字符
            'cryptkey'    => 0, #加密用的key
        );
        if (is_array($paramArr))$options = array_merge($options, $paramArr);
        extract($options);

        $mcrypt_cipher_alg  = MCRYPT_RIJNDAEL_128;
		$iv = mcrypt_create_iv(mcrypt_get_iv_size($mcrypt_cipher_alg,MCRYPT_MODE_ECB));

        $string    = pack("H*",$value);
        return trim(mcrypt_decrypt($mcrypt_cipher_alg, $cryptkey, $string, MCRYPT_MODE_ECB, $iv));

    }

    /**
     * 适合url传输的base64编码
     */
    private static function base64UrlEncode($value){
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
    
    /**
     * 适合url传输的base64解码
     */
    private static function base64UrlDecode($value){
        return base64_decode(strtr($value, '-_', '+/'));
    }

}

