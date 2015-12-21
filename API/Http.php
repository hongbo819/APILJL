<?php

class API_Http {

    /**
     *  是发送head头，可以应用在请求统计脚本
     */
    public static function sendHeaderOnly($paramArr) {
		$options = array(
            'url'       => false, #url
            'getFull'   => false, #是否获得所有的文本
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);


		if (!$url) return false;
		$urlArr = parse_url($url);
		if (!is_array($urlArr) || empty($urlArr)){
			return false;
		}
		//var_dump($urlArr);die;

		//获取请求数据
		$host = $urlArr['host'];
                $path = "/";
                if(isset($urlArr['path']) || isset($urlArr['query'])){
                    $path = (isset($urlArr['path']) ? $urlArr['path'] :'') ."?". (isset($urlArr['query']) ? $urlArr['query'] : '');
                }
		$port = isset($urlArr['port']) ? $urlArr['port'] : "80";
		
		//连接服务器
		$fp = fsockopen($host, $port, $errNo, $errStr, 30);
		if (!$fp){
			return false;
		}

		//构造请求协议
		$requestStr = "GET ".$path." HTTP/1.1\r\n";
		$requestStr .= "Host: ".$host."\r\n";
		$requestStr .= "Connection: Close\r\n\r\n";

		//发送请求
		fwrite($fp, $requestStr);
		$firstHeader = fgets($fp, 1024);
		fclose($fp);

        if($getFull){
            return $firstHeader;
        }
        $headerArr = explode(" ",$firstHeader);
        //var_dump($headerArr);die;
        if(count($headerArr) >= 3){
            return $headerArr[1];
        }
		return true;

    }
    public static function sendHeader($arg, $exit = 0) {
        if (is_string($arg)) {
            header($arg);
        } elseif (is_int($arg)) {
            if (self::getStatusByCode($arg)) {
                header(self::getStatusByCode($arg));
            } else {
    
                return false;
            }
        }
        if ($exit) {
            exit(0);
        }
    }
    /**
     *  利用curl的形式获得页面请求 请用这个函数取代file_get_contents
     */
    public static function curlPage($paramArr){
       if (is_array($paramArr)) {
			$options = array(
				'url'       => false, #要请求的URL数组
				'timeout'   => 2,#超时时间 s
				'recErrLog' => 0,#是否记录错误日志
				'reConnect' => 0,#是否出错后重连
				'keepAlive' => 0,#是否执行保持长链接的处理
			);
			$options = array_merge($options, $paramArr);
			extract($options);
		}
        $timeout = (int)$timeout;

        if(0 == $timeout || empty($url))return false;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        if(defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')){
            curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 ); #避免首先解析ipv6
        }
        if($keepAlive){
            $rch = curl_copy_handle($ch);
            curl_setopt($rch, CURLOPT_FORBID_REUSE, false);
            curl_setopt($rch, CURLOPT_HTTPHEADER, array(
                'Connection: Keep-Alive',
                'Keep-Alive: 3'
            ));
            $data = curl_exec($rch);
        }else{
            $data = curl_exec($ch);
        }
        #记录错误日志
        if($recErrLog || $reConnect){
           $errNo  = curl_errno($ch);
           if($reConnect && (28 == $errNo || 7 == $errNo || 6 == $errNo)){ #超时重连 6:name lookup timed out
                $errMsg = curl_error($ch);
                $data = self::curlPage(array('url'=>$url,'timeout'=>1,'recErrLog'=>1,'reConnect'=>0));#这次不需要重连
                LJL_Log::write("[api_curl_toreconn][{$url}] [{$errNo}]{$errMsg}", LJL_Log::TYPE_ERROR);
           }elseif($errNo && $recErrLog){#记录错误
               $errMsg = curl_error($ch);
               LJL_Log::write("[api_curl][{$url}] [{$errNo}]" . $errMsg, LJL_Log::TYPE_ERROR);
           }
        }

        if(!$keepAlive){
            curl_close($ch);
        }

        return $data;
    }
    
    /*
     * @Desc 利用代理获取地址内容
     * @Version 14-5-9 下午3:52 
     * @Todo 可以完善错误判断
     * @From 抓取需要 
     */
    public static function curlPageByProxy($paramArr)
    {
        $options = array(
			'url'       =>  0, #URL
            'proxy'     => true,  #是否使用代理
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);
        
		#$url = "https://assoc-datafeeds-cn.amazon.com/datafeed/listFeeds?format=text/html";
        #代理配置
        $user = "xxxx";
        $pass = "xxxx";
        $proxy_host = "vps.xxx.com:80";
        $proxy_port = 80;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_NOPROGRESS, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 6000);
        curl_setopt($ch, CURLOPT_PROXYPORT, $proxy_port);
        curl_setopt($ch, CURLOPT_PROXY, $proxy_host);
        curl_setopt($ch, CURLOPT_USERPWD, $user.":".$pass);

		$response = curl_exec($ch);
		if(curl_errno($ch) > 0){
			throw_exception("CURL ERROR:$url ".curl_error($ch));
		}
		curl_close($ch);
		return $response;
    }
    
	 /**
     *  利用curl POST数据
     */
    public static function curlPost($paramArr){
       
		$options = array(
			'url'      => false, #要请求的URL数组
			'postdata' => '', #POST的数据
			'timeout'  => 2,#超时时间 s
		);
        if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);

        $timeout = (int)$timeout;
        if(0 == $timeout || empty($url))return false;

        $ch = curl_init();
		curl_setopt ($ch, CURLOPT_URL, $url);
		curl_setopt ($ch, CURLOPT_POSTFIELDS, http_build_query($postdata));
		curl_setopt ($ch, CURLOPT_POST, true);
		curl_setopt ($ch, CURLOPT_AUTOREFERER, true);
        if(isset($_SERVER['HTTP_USER_AGENT'])){
            curl_setopt ($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        }
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        if(defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')){
            curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 ); #避免首先解析ipv6
        }
		$content = curl_exec( $ch );
		curl_close ( $ch );

        return $content;
    }
    /**
     *  利用 curl_multi_** 的函数,并发多个请求
     */
    public static function multiCurl($paramArr){
       if (is_array($paramArr)) {
			$options = array(
				'urlArr'   => false, #要请求的URL数组
				'timeout'  => 10,#超时时间 s
			);
			$options = array_merge($options, $paramArr);
			extract($options);
		}
        $timeout = (int)$timeout;

        if(0 == $timeout)return false;

        $mh = curl_multi_init();

		foreach ($urlArr as $i => $url) {
		    $conn[$i] = curl_init($url);
		    curl_setopt_array($conn[$i], array(
                                                        CURLOPT_URL => $url,
                                                        CURLOPT_HEADER => false,
                                                        CURLOPT_RETURNTRANSFER => true,
                                                        CURLOPT_FOLLOWLOCATION => 1,//是否跟踪301、302等叶面
                                                        CURLOPT_TIMEOUT => $timeout,
                                                ));  //不直接输出结果
		    curl_multi_add_handle($mh, $conn[$i]);
		}
		
		$active = null;
		$res = array();
		do {
		    $status = curl_multi_exec($mh, $active);
		    $info = curl_multi_info_read($mh);
		    if (false !== $info) {
		        //采集信息处理
		        $res[] = array(
		            'content'   => curl_multi_getcontent($info['handle']),
		            'info'      => $info,
		        );
		        curl_close($info['handle']);
		    }
		} while ($status === CURLM_CALL_MULTI_PERFORM || $active);
		
		curl_multi_close($mh);
		
		return $res;
    }
    /**
     * 设置404 Header信息
     */
    public static function send404Header(){
        LJL_Api::run("Base.Page.setExpires", array('second'=>0)); #清除过期时间
        header('Content-type:text/html; Charset=utf-8');
        header(self::getStatusByCode(404)); #设置404 header信息
    }
    /**
     * 设置各种码的header信息
     * @param int $code
     */
    public static function sendHeaderCode($code){
        LJL_Api::run("Base.Page.setExpires", array('second'=>0)); #清除过期时间
        header('Content-type:text/html; Charset=utf-8');
        header(self::getStatusByCode($code)); #设置404 header信息
    }
    protected static function getStatusByCode($code) {
        $status = array(
            100 => "HTTP/1.1 100 Continue",
            101 => "HTTP/1.1 101 Switching Protocols",
            200 => "HTTP/1.1 200 OK",
            201 => "HTTP/1.1 201 Created",
            202 => "HTTP/1.1 202 Accepted",
            203 => "HTTP/1.1 203 Non-Authoritative Information",
            204 => "HTTP/1.1 204 No Content",
            205 => "HTTP/1.1 205 Reset Content",
            206 => "HTTP/1.1 206 Partial Content",
            300 => "HTTP/1.1 300 Multiple Choices",
            301 => "HTTP/1.1 301 Moved Permanently",
            302 => "HTTP/1.1 302 Found",
            303 => "HTTP/1.1 303 See Other",
            304 => "HTTP/1.1 304 Not Modified",
            305 => "HTTP/1.1 305 Use Proxy",
            307 => "HTTP/1.1 307 Temporary Redirect",
            400 => "HTTP/1.1 400 Bad Request",
            401 => "HTTP/1.1 401 Unauthorized",
            402 => "HTTP/1.1 402 Payment Required",
            403 => "HTTP/1.1 403 Forbidden",
            404 => "HTTP/1.1 404 Not Found",
            405 => "HTTP/1.1 405 Method Not Allowed",
            406 => "HTTP/1.1 406 Not Acceptable",
            407 => "HTTP/1.1 407 Proxy Authentication Required",
            408 => "HTTP/1.1 408 Request Time-out",
            409 => "HTTP/1.1 409 Conflict",
            410 => "HTTP/1.1 410 Gone",
            411 => "HTTP/1.1 411 Length Required",
            412 => "HTTP/1.1 412 Precondition Failed",
            413 => "HTTP/1.1 413 Request Entity Too Large",
            414 => "HTTP/1.1 414 Request-URI Too Large",
            415 => "HTTP/1.1 415 Unsupported Media Type",
            416 => "HTTP/1.1 416 Requested range not satisfiable",
            417 => "HTTP/1.1 417 Expectation Failed",
            500 => "HTTP/1.1 500 Internal Server Error",
            501 => "HTTP/1.1 501 Not Implemented",
            502 => "HTTP/1.1 502 Bad Gateway",
            503 => "HTTP/1.1 503 Service Unavailable",
            504 => "HTTP/1.1 504 Gateway Time-out"  
        );
        if (!empty($status[$code])) {
            
            return $status[$code];
        }
        return false;
    }
}
