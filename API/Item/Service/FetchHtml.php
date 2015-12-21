<?php
/**
 * 用Dom的形式抓取页面
 */
class API_Item_Service_FetchHtml extends API_Item_Service_ServiceAbstract{

    private static $proxySaveArr = array(); #代理地址存储数组
	/**
	 * 获得Dom操作对象
	 */
	public static function getHtmlOrDom($paramArr) {
		$options = array(
			'url'      => 'http://www.baidu.com/', #站点URL
			'charset'  => 'utf-8', #页面编码
			'timeout'  => 3,  #请求页面的超时时间
			'isGearman'=> false,  #是否用Gearman
			'getDom'   => false,  #是否获得Dom
			'snoopy'   => false,  #是否使用snoopy
			'fileGetContents'   => false,  #是否用php的函数进行抓取
			'ungzip'   => false,  #是否解压gzip文件
			'referer'  => false,  #snoopy referer
			'proxy'    => false,  #是否使用代理
			'maxredirs' => 6,  #允许跳转的次数
            'header'    => false,#定制Header头儿
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);
        
        #获得页面内容
        if($isGearman){
            $htmlStr = API_Gearman::doNormal(array(
                'taskName'       => 'adsl_get_contents',   #任务名
                'taskContent'    => $url,   #任务内容
            ));
        }else{
            if($snoopy){   
                $htmlStr = self::snoopyFetch(array('url'=>$url,'referer'=>$referer,'proxy'=>$proxy,'maxredirs'=>$maxredirs,'header'=>$header));
            }elseif($fileGetContents){
                $htmlStr = file_get_contents($url);
            }else{
                $htmlStr = API_Http::curlPage(array('url'=>$url,'timeout'=>$timeout));
            }
            
        } 
        if(!$htmlStr){
            return false;
        }
        if($ungzip){
            $htmlStr = API_Item_Base_String::gzdecode(array('input'=>$htmlStr));
        }
        if(!$getDom){
            return $htmlStr;
        }

        #载入处理类
        require_once LJL_API_ROOT . '/Libs/FetchHtml/SimpleHtmlDom.php';
        if($charset == "UTF-8" || $charset == "utf-8"){
            $htmlStr = mb_convert_encoding($htmlStr,"GBK","utf-8");
        }
        $dom = new simple_html_dom(null, $lowercase = true, $forceTagsClosed=true, 'GBK');//还是用gbk去解析
        $dom->load($htmlStr, $lowercase= true, $stripRN=true);

        return $dom;
	}

    public static function htmlToDom($paramArr) {
		$options = array(
			'htmlStr'  =>  '', #字符串
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);
        if(!$htmlStr)return false;
        
        require_once LJL_API_ROOT . '/Libs/FetchHtml/SimpleHtmlDom.php';

        $dom = new simple_html_dom(null, $lowercase = true, $forceTagsClosed=true, 'GBK');//还是用gbk去解析
        $dom->load($htmlStr, $lowercase= true, $stripRN=true);
        return $dom;

    }
    
    /**
     * 获得详情
     */
    public static function getDetail($paramArr) {
		$options = array(
            'url'      => '',    #要抓取的url
            'charset'  => false, #页面编码
            'dom'      => false, #可以直接传入dom
			'cfgArr'   => '',    #配置数组
			'referer'   => 0,  #referer
			'proxy'     => false,  #是否使用代理
			'maxredirs' => 3,  #允许跳转的次数
            'header'    => false,#定制Header头儿
            'snoopy'             => true,#使用snoopy
            'fileGetContents'    => false,#使用fileGetContents
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);
        
        if(!$url && !$dom)return false;
        
        if(!$dom){
            $dom = self::getHtmlOrDom(array("url"=>$url,'getDom'=>1,'snoopy'=>$snoopy,'fileGetContents'=>$fileGetContents,'charset'=>$charset,'referer' => $referer,'proxy' => $proxy, 'maxredirs' => $maxredirs,'header'=>$header));
        }
        if(!$dom)return false;
        $outArr = array();
        
        foreach($cfgArr as $knm => $cfg){
            
            $idx = isset($cfg["idx"]) ? $cfg["idx"] : 0;
            $sel = $cfg['sel'];
            $dm  = $dom->find($sel,$idx);            
            
            if($dm){
                $txt = !empty($cfg["attr"]) ? $dm->$cfg["attr"] : $dm->innertext; #根据哪个属性获得文本内容
                $txt = trim($txt);
                if(!empty($cfg["striptags"])){
                    $txt = strip_tags($txt);
                }
                #过滤标签
                if(!empty($cfg["removeTag"])){
                    foreach($cfg["removeTag"] as $t){     
                        $txt = preg_replace("#<{$t}[^>]*>.*</{$t}>#isU", "", $txt);
                    }
                }
                #过滤标签名，不过滤标签之间的内容
                if(!empty($cfg["removeTagName"])){
                    foreach($cfg["removeTagName"] as $t){     
                        $txt = preg_replace("#</?{$t}[^>]*/?>#isU", "", $txt);
                    }
                }

                #开始字符串
                if(!empty($cfg["stringStart"])){    
                   $txt = substr($txt, strpos($txt, $cfg["stringStart"]) +  strlen($cfg["stringStart"]));                                    
                }

                #结束字符串
                if(!empty($cfg["stringEnd"])){   
                   $txt = substr($txt,0,strpos($txt, $cfg["stringEnd"]));
                }

                #字符串的替换
                if(!empty($cfg["replaceArr"])){
                    foreach($cfg["replaceArr"] as $s => $r){                                
                        $txt = str_replace($s, $r, $txt);
                    }
                }
                $outArr[$knm] = $txt;
            }
            
        }
        $dom->clear();
        unset($dom);
        return $outArr;
        
        
    }
    /**
     * 批量抓取一个列表数据
     */
    public static function getList($paramArr) {
		$options = array(
            'url'      => '',    #要抓取的url
            'charset'  => false, #页面编码
            'dom'      => false, #可以直接传入dom
			'cfgArr'   => '',    #配置数组
			'referer'   => 0,  #referer
			'proxy'     => false,  #是否使用代理
			'maxredirs' => 3,  #允许跳转的次数
            'header'    => false,#定制Header头儿
            'fileGetContents'=>false
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);
        
        if(!$url && !$dom)return false;
        
        if(!$dom){
            $dom = self::getHtmlOrDom(array("url"=>$url,'getDom'=>1,'snoopy'=>$fileGetContents?0:1,'fileGetContents'=>$fileGetContents,'charset'=>$charset,'referer' => $referer,'proxy' => $proxy, 'maxredirs' => $maxredirs,'header'=>$header));
        }
        if(!$dom)return false;
        $outArr = array();
        if($cfgArr){
	        foreach($cfgArr as $j => $topCfg){
	            $listArr = $dom->find($topCfg["sel"]);#
	            if($listArr){
	                $subOutArr = array();
	                foreach($listArr as $i => $obj){#item列表，比如文章页的文章列表,
	                    //$i = 0;
	                    if(!empty($topCfg["items"])){#如果获得子元素
	                        foreach ($topCfg["items"] as $knm => $cfg){#遍历每个item的所有元素，比如文章的 标题，时间，作者....
	                            if(empty($cfg["child"])){#从本身节点上获得属性
	                                $dm  = $obj;
	                            }else{#如果是取得孩子节点
	                                $sel = $cfg["sel"];
	                                $idx = isset($cfg["idx"]) ? $cfg["idx"] : 0;
	                                $dm  = $obj->find($cfg["sel"],$idx);
	                            }
	                            if($dm){
	                                $txt = !empty($cfg["attr"]) ? $dm->$cfg["attr"] : $dm->innertext; #根据哪个属性获得文本内容
	                                $txt = trim($txt);
	                                if(!empty($cfg["striptags"])){
	                                    $txt = strip_tags($txt);
	                                }
	                                #过滤标签
	                                if(!empty($cfg["removeTag"])){
	                                    foreach($cfg["removeTag"] as $t){     
	                                        $txt = preg_replace("#<{$t}[^>]*>.*</{$t}>#isU", "", $txt);
	                                    }
	                                }
	                                #过滤标签名，不过滤标签之间的内容
	                                if(!empty($cfg["removeTagName"])){
	                                    foreach($cfg["removeTagName"] as $t){     
	                                        $txt = preg_replace("#</?{$t}[^>]*/?>#isU", "", $txt);
	                                    }
	                                }
	                                
	                                #开始字符串
	                                if(!empty($cfg["stringStart"])){    
	                                   $txt = substr($txt, strpos($txt, $cfg["stringStart"]) +  strlen($cfg["stringStart"]));                                    
	                                }
	                                
	                                #结束字符串
	                                if(!empty($cfg["stringEnd"])){   
	                                   $txt = substr($txt,0,strpos($txt, $cfg["stringEnd"]));
	                                }
	                                
	                                #字符串的替换
	                                if(!empty($cfg["replaceArr"])){
	                                    foreach($cfg["replaceArr"] as $s => $r){                                
	                                        $txt = str_replace($s, $r, $txt);
	                                    }
	                                }
	                                $subOutArr[$i][$knm] = $txt;
	                            }
	                        }
	                    }else{
	                         $subOutArr[$i] = $obj->innertext;
	                    }
	                     //$i++;
	                }
	                $outArr[$j] = $subOutArr;
	            }
	        }
        }
        
        $dom->clear();
        unset($dom);
        return $outArr;
        
        
    }
    
    /**
     * 获得一个代理IP和端口
     */
    public static function getProxyData($paramArr) {
		$options = array(
			'retryCnt'       =>  5, #尝试次数
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);
        $defaultPort = 8088;
        $outArr = false;
        $tmp = 1;
        #如果已经取过至少3个代理，就从这三个中随机获得一个
        if(self::$proxySaveArr && count(self::$proxySaveArr) >= 3){
            $tmpPidx = array_rand(self::$proxySaveArr,1);
            $ip = self::$proxySaveArr[$tmpPidx];
            $outArr = array(
                'ip'    => $ip,
                'port'  => $defaultPort,
            );
        }else{#尝试获得代理
            
            while($tmp < $retryCnt){
                #获得一个有用的
                $proxy = API_Http::curlPage(array('url'=>"http://14.32.120.127/cgi-bin/socket_adsl_restart_control.cgi?file=1",'timeout'=>2));
                if(!$proxy)continue;
                #尝试这个代理是否可用
                $fp = @fsockopen($proxy, $defaultPort, $errno, $errstr, 1);
                if ($fp) {
                    self::$proxySaveArr[] = $proxy; 
                    
                    $outArr = array(
                        'ip'    => $proxy,
                        'port'  => $defaultPort,
                    );
                    fclose($fp);
                    break;
                }else{
                    #echo "CONNECT ERROR: ".$errno;
                }
                $tmp++;
            }
        }
        return $outArr;
    }

	/**
	 * Snoopy的抓取内容
	 */
    private static $proxyHost = false; #已经获得的代理IP
    private static $proxyPort = 8088;
    private static $proxyCnt  = 0;
    public static function snoopyFetch($paramArr) {
		$options = array(
			'url'       => '', #URL
            'agent'     => 1,  #使用的agent
			'referer'   => 0,  #referer
			'proxy'     => false,  #是否使用代理
			'maxredirs' => 3,  #允许跳转的次数
            'header'    => false,#定制Header头儿
            'getredirect' =>false #是否只要跳转后链接
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);
        
        if(!$url)return false;
        
        self::getSnoopyObj();
        $agentStr = "";
        switch($agent){
            case 1:#普通用户
                $agentStr = "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0)";
                break;
            case 2:#百度
                $agentStr = "Mozilla/5.0 (compatible; Baiduspider/2.0; +http://www.baidu.com/search/spider.html)";
                break;
            default:
                $agentStr = $agent ? $agent : "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0)";
                break;
        }
        self::$snoopyObj->agent = $agentStr;
        if($referer)self::$snoopyObj->referer = $referer;
        self::$snoopyObj->maxredirs = $maxredirs; 
        if($proxy){
            $defaultPort = "8088";
            if(!self::$proxyHost){#如果以前没有获得代理，尝试获得一个        
                $proxyArr = self::getProxyData(array('retryCnt'=>3));
                if($proxyArr){
                    self::$proxyHost = $proxyArr['ip'];
                    $defaultPort     = $proxyArr['port'];
                }                
            }
            if(self::$proxyHost){            
                self::$snoopyObj->proxy_host = self::$proxyHost; 
                self::$snoopyObj->proxy_port = $defaultPort; 
                self::$proxyCnt++;
            }
            if(self::$proxyCnt > 50){#这个代理用的多了，就抛弃这个代理，下次重新用一个
                self::$proxyCnt  = 0;
                self::$proxyHost = false;                        
            }
        }
        #定制Header头儿
        if($header){
            foreach($header as $k => $v){
                self::$snoopyObj->rawheaders[$k] = $v;
            }
        }
        
        self::$snoopyObj->fetch($url);
        if($getredirect){
            return self::$snoopyObj->lastredirectaddr;
        }else{
            return self::$snoopyObj->results;
        }
        
    }
    
    /**
     * 获得snoopy对象
     */
    private static $snoopyObj = null;
    private static function getSnoopyObj(){
        if(!self::$snoopyObj){
            require_once LJL_API_ROOT . '/Libs/FetchHtml/Snoopy.php';
            self::$snoopyObj = new Snoopy();
        }
    }

}

