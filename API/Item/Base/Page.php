<?php
/**
* 页面的基础服务
*/
class API_Item_Base_Page
{
    /**
	* 设置页面的过期时间
	*/
	public static function setExpires($paramArr) {
		$options = array(
            'second'  => 0, #多少秒后过期，如86400
            'point'   => false, #是否整点失效，设置一个时间，会到那个时间段的整点失效
            'esi'     => false, #是否使用ESI
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);
        
        $systemTm     = time();
		$lastModified = $point ? ($systemTm - ($systemTm % $second)) : ($systemTm);
		$expireTime   = $lastModified + $second;

		if(0 == $second){
			header('Cache-Control: no-cache');
		}else{
			header('Cache-Control: max-age=' . $second);
		}
		header('Expires :' . gmdate('D, d M Y H:i:s', $expireTime) . ' GMT');
		header('Last-Modified :' . gmdate('D, d M Y H:i:s', $lastModified) . ' GMT');
        #如果ESI设置特殊的头部
        if($esi){
            header('X-Esi: 1');

            if(0 == $second){
                header('ZCache-Control: no-cache');
            }else{
                header('ZCache-Control: max-age=' . $second);
                header('ZExpires:' . gmdate('D, d M Y H:i:s', $expireTime) . ' GMT');
                header('ZLast-Modified:' . gmdate('D, d M Y H:i:s', $lastModified) . ' GMT');
            }
        }
	}

    /**
     * 统计访问日志，比如记录某一文章ID的访问
     */
    public static function logAccessByType($paramArr) {
		$options = array(
            'type'    => '',    #类型 DOC_DETAIL:文章综述页  PRO_DETAIL:产品综述页
            'param'   => false, #不同类型的参数
            'imei'    => "",    #app 识别码
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);

        $url = '';
        switch ($type){
            case "DOC_DETAIL":#文章内容页
                $param =  array(
                'docId'          => 3269781,         #文章ID
                'classId'        => 210,             #频道ID
                'fullUrl'        => 1,               #全URL
            );
                //$url = API_Item_Urls_Doc::getDocUrl($param);
                $url = "www.baidu.com";//做实验用
                break;

            case "PRO_DETAIL":#产品库各种综述页
                $url = '';
                break;
        }
        if(!$url)return false;
        $url    = urlencode($url);
        $ip     = API_Item_Service_Area::getClientIp();//获得当前用户的IP地址
       
        $refer  = isset($_SERVER['HTTP_REFERER']) ? urlencode($_SERVER['HTTP_REFERER']) : '';//上一层连接
		
        $reqUrl = "http://hongbo.ea3w.com/ext-test/writeLog.php?ip={$ip}&url={$url}&refer={$refer}&imei={$imei}&type={$type}";
        
        #请求这个页面,writeLog.php执行写入日志
        return API_Http::sendHeaderOnly(array('url'=>$reqUrl));

    }
    
    /**
     * 新生成ipck，并且可以设置cookie中
     */
    public static function createIpCk($paramArr) {
		$options = array(
            'setCookie'    => false,    #是否设置到cookie
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);
        
        list($usec, $sec) = explode(" ",microtime());
        $fusec            = substr(($usec),"2","6");
        $sec_str          = '逗你玩' ^ $fusec."JJ";
        $ipCk             = strtr(base64_encode($sec_str.".".($fusec).".$sec"), '+/', '-_');
        if($setCookie){
            setcookie("ip_ck", $ipCk, SYSTEM_TIME + 86400*365, '/');
        }
        return $ipCk;
    }

}

