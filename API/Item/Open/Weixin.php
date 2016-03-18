<?php
/**
* 微信接口 针对公众号
* 文档：http://mp.weixin.qq.com/wiki/index.php?title=%E6%B6%88%E6%81%AF%E6%8E%A5%E5%8F%A3%E6%8C%87%E5%8D%97
*/

class API_Item_Open_Weixin
{
    private static $ourWxId    = "";#我们的微信ID
    private static $userOpenId = "";#用户的OPENID
    private static $EventKey   = "";#用户的OPENID
    /**
     * 签名的验证
     */
	private static function checkSignature($paramArr) {
		$options = array(
			'signature'    => '', #微信加密签名
			'timestamp'    => '', #时间戳
			'nonce'        => '', #随机数
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);
        
		$token = self::$TOKEN;
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );

		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}

    /**
     * 申请消息接口的验证
     * 网址接入 公众平台用户提交信息后，微信服务器将发送GET请求到 这个参数
     */
	public static function verification($paramArr) {
		$options = array(
			'token'     => '', #TOKEN
			'signature' => '',
		    'timestamp' => '',
		    'nonce'     => '',
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options); 
        self::$TOKEN = $token;
        //进行验证
        return self::checkSignature(array(
			'signature'    => $signature,
			'timestamp'    => $timestamp,
			'nonce'        => $nonce,
		));
    }


    /**
     * 接受用户发送的消息
     */
    public static function receiveMsg($paramArr) {
		$options = array(
			'subscribeCallback'        => false, #订阅回调函数
			'unsubscribeCallback'      => false, #取消订阅回调函数
			'clickCallback'            => false, #自定义菜单点击事件调函数
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);
		
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        
        if(!$postStr)return array();
        
        $outArr = array();
        $postObj           = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);

        self::$userOpenId  = $postObj->FromUserName ? (string)$postObj->FromUserName : "";
        self::$ourWxId     = $postObj->ToUserName ? (string)$postObj->ToUserName : "";
        $msgType           = (string)$postObj->MsgType;
        
        $outArr = (array)$postObj;
        $outArr["OurWeixinId"] = (string)self::$ourWxId;
        $outArr["UserOpenId"]  = (string)self::$userOpenId;        
        if('event' == $msgType){#事件的处理            
            switch((string)$postObj->Event){
                case "subscribe": #订阅事件
                    if($subscribeCallback){
                        call_user_func($subscribeCallback, $outArr);
                    }
                    break;
                case "unsubscribe": #取消订阅
                    if($unsubscribeCallback){
                        call_user_func($unsubscribeCallback, $outArr);
                    }
                    break;
                case "CLICK": #自定义菜单点击事件
                    self::$EventKey    = $postObj->EventKey ? (string)$postObj->EventKey : "";
                    $outArr["MenuKey"]  = (string)self::$EventKey;
                    if($clickCallback){
                        call_user_func($clickCallback, $outArr);
                    }
                    break;

            }
        }else{#普通消息
            if("text"== $msgType){                
                $outArr["Content"]    = (string)$postObj->Content;
            }elseif("image"== $msgType){
                $outArr["PicUrl"]     = (string)$postObj->PicUrl;
            }
            return $outArr;
        }

        
    }

    /**
     * 获得回复消息的通用部分
     */
    private static function getAnswerBaseXml($type){
        return "<xml>
                <ToUserName><![CDATA[". self::$userOpenId ."]]></ToUserName>
                <FromUserName><![CDATA[". self::$ourWxId ."]]></FromUserName>
                <CreateTime>".SYSTEM_TIME."</CreateTime>
                ";
    }

    /**
     * 回复文本消息
     */
    public static function answerText($paramArr) {
		$options = array(
			'content'        => '', #内容
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);

        if(!$content)return '';
        
        $xmlStr  = self::getAnswerBaseXml();
        $content = trim($content);
        
        return $xmlStr . "<MsgType><![CDATA[text]]></MsgType>
               <Content><![CDATA[{$content}]]></Content>
               </xml>";        
    }

     /**
     * 回复音乐消息
     */
    public static function answerMusic($paramArr) {
		$options = array(
			'title'           => '', #标题
			'desc'            => '', #描述
			'musicUrl'        => '', #音乐链接
			'hQMusicUrl'      => '', #高质量音乐链接，WIFI环境优先使用该链接播放音乐
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);


        $xmlStr  = self::getAnswerBaseXml();
		$title = mb_convert_encoding(trim($title), "UTF-8","GBK");
		if($desc){
			$desc = mb_convert_encoding(trim($desc), "UTF-8","GBK");
		}
        return $xmlStr . "<MsgType><![CDATA[music]]></MsgType>
                         <Music>
                         <Title><![CDATA[{$title}]]></Title>
                         <Description><![CDATA[{$desc}]]></Description>
                         <MusicUrl><![CDATA[{$musicUrl}]]></MusicUrl>
                         <HQMusicUrl><![CDATA[{$hQMusicUrl}]]></HQMusicUrl>
                         </Music>
                         </xml>";
    }

    /**
     * 回复列表
     */
    public static function answerList($paramArr) {
		$options = array(
			'dataArr'        => array(), #列表内容 array('Title'=>''，'Description'=>''，'PicUrl'=>''，'Url'=>'');
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);

        if(!$dataArr)return '';

        $xmlStr  = self::getAnswerBaseXml();
        $xmlStr .= "<MsgType><![CDATA[news]]></MsgType>
                    <ArticleCount>".count($dataArr)."</ArticleCount>
                    <Articles>";
        foreach($dataArr as $d){
            $textTpl = "
                         <item>
                         <Title><![CDATA[%s]]></Title>
                         <Description><![CDATA[%s]]></Description>
                         <PicUrl><![CDATA[%s]]></PicUrl>
                         <Url><![CDATA[%s]]></Url>
                         </item>";

            $xmlStr .= sprintf($textTpl,$d['Title'], $d['Description'], $d['PicUrl'], $d['Url']);
        }
        $xmlStr .= "
                    </Articles>
                    </xml>";
        
        return $xmlStr;
    }
    /**
     * 根据OPENID获得LJL的用户ID
     */
    public static function getLjlUserByOpenId($paramArr) {
		$options = array( 
			'appId'         => '', #公众账号 ID 如 hongboystry,jishuren
			'openId'        => '', #OPEN ID
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);
        
        $db = API_Db_User::instance();
        return $db->getOne("select ljluserid from weixin_user_map where appid = '{$appId}' and openid = '{$openId}'");
    }
    
    
    /**
     * 设置ljl用户id和微信OPENID的映射
     */
    public static function setLJLUserOpenIdMap($paramArr) {
		$options = array( 
			'appId'         => '', #公众账号 ID 如 hongboystry,jishuren
			'openId'        => '', #OPEN ID
			'userId'        => '', #LJL用户ID
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);
        
        $db = API_Db_User::instance();
        $db->query("insert into weixin_user_map(appid,openid,ljluserid,tm) 
                    values( '{$appId}','{$openId}','{$userId}',".SYSTEM_TIME.")");
        return true;
    }
}