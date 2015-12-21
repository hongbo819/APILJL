<?php
/**
* 地区相关的API
*/
class API_Item_Service_Area extends API_Item_Service_ServiceAbstract
{
    private static $_ipCache = array();

	/**
	 * 从数据库获得ip地址相关信息
	 */
	public static function getIpinfoByDb($paramArr){
        $options = array(
            'ip'           => '',
        );
        if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);

        #如果没有传入ip就获得就获得用户的ip
        if(!$ip){
            $ip = self::getClientIp();
        }
        #为了避免多次获得一个ip的数据
        if(isset(self::$_ipCache[$ip])) return self::$_ipCache[$ip];

        $intIp = ip2long($ip);
        $db = API_Db_Ip::instance();
        $outArr = array();
        
        $sql = "select * from ip_location where beginip<'{$intIp}'
         and endip>'{$intIp}' limit 1";
        $outArr = $db->getRow($sql);
        if($outArr){
            return $outArr;
        }
        $outArr = self::ip2location($paramArr);
        if($outArr['country']){
            $beginip = ip2long($outArr['beginip']);
            $endip   = ip2long($outArr['endip']);
            $area    = isset($outArr['area']) ? $outArr['area'] : '';
            $city    = isset($outArr['city']) ? $outArr['city'] : '';
            $provice = isset($outArr['province']) ? $outArr['province'] : '';
            $sql = "insert into ip_location(beginip,endip,ip,area,city,province,contry,tm) values(
                '{$beginip}','{$endip}','{$ip}','{$area}','{$city}','{$provice}','{$outArr['country']}','".SYSTEM_TIME."')";
            $db->query($sql);
        }
        self::$_ipCache[$ip]  = $outArr;
        return $outArr;
	}

	/**
	 * 获得手机号信息
	 */
	public static function getMobileArea($paramArr){
        $options = array(
            'mobile' => '',
        );
        if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);

        if(!$mobile || !preg_match('/^1[3458][0-9]{9}$/', $mobile)) return false;

        $outArr = array();
        #首先尝试从db中查询
        /** add by cui*/
        $db  = API_Db_Ip::instance();
        $sql = "select * from mobile_data where mno = '{$mobile}' limit 1";
        $re  = $db->getRow($sql);
        if($re){
            $outArr = array(
                    '卡号归属地'  => $re["area"],
                    '卡类型'     => $re["card"],
                    '区号'       => $re["areano"],
                    '邮编'       => $re["zip"],
                );
            return $outArr;
        }
        $outData = array();
        #尝试接口1
        $url = "http://www.ip138.com:8080/search.asp?action=mobile&mobile={$mobile}";
        $dom = LJL_Api::run("Service.FetchHtml.getHtmlOrDom" , array('url'=>$url,'charset'=>'gbk','timeout'=>3,'getDom'=>1));
        if($dom){
            $trs = $dom->find(".tdc");
            if($trs){
                foreach($trs as $tr){
                    $tds   = $tr->find("td");
                    if(!isset($tds[0])) continue;
                    $name  = $tds[0]->innertext;
                    
                    $value = $tds[1]->innertext;
                    $name  = str_replace(array(" ","&nbsp;"),"",  strip_tags($name));
                    $name = iconv('gbk', 'utf-8', $name);
                    $value = iconv('gbk', 'utf-8', $value);
                    if(!in_array($name, array('卡类型','区号','邮编','卡号归属地')))continue;
                    $value  = str_replace(" ","",  strip_tags($value));
                    if($name == '邮编')$value = (int)$value;
                    if($name == '卡号归属地')$value = str_replace("&nbsp;", "|", $value);
                    if($name && $value){
                        $outData[$name] = $value;
                    }
                }
            }
        }
        
        if(!$outData){
            #尝试接口2
            $url  = 'http://www.youdao.com/smartresult-xml/search.s?type=mobile&q=' . $mobile;
            $data = API_Http::curlPage(array('url'=>$url,'timeout'=>1));
            if(preg_match("#<location>(.+)</location>#Ui", $data, $m)){
                $outData['卡号归属地'] = trim(trim($m[1]), '|');
            }

        }
        #将取得的数据记录到数据库，方便下次直接使用
        /*bycui*/
        if($outData){
            $sql = "insert into mobile_data(mno,area,card,areano,zip,tm)
                    values('{$mobile}','".$outData['卡号归属地']."','".$outData['卡类型']."','".$outData['区号']."','".$outData['邮编']."','".SYSTEM_TIME."') ";
            $db->query($sql);

        }
        return $outData;
    }

    /**
     * 得到网友的详细IP信息
     * 只会获得第一个IP地址,
     */
    public static function getClientIp(){
        $ip = self::getClientIpMulti();
        $ip = str_replace("#", "", $ip);
        $ipArr = explode(",",$ip);
        $ip = is_array($ipArr) ? $ipArr[0] : $ipArr;
        
        #用户IP局域网判断,如果第一个是局域网就取第二个IP段
        if (is_array($ipArr) && preg_match('/^(192.168.*|127.*)$/', $ip)) {
            $ip = isset($ipArr[1]) ? $ipArr[1] : $ip;
        }
        return $ip;
    
    }
    /**
     * 得到网友的详细IP信息
     * ********特别注意:************
     *      这个获得地址是多个的:10.19.8.12, 118.67.120.27, 127.0.0.1 因此要程序进行区分
     * 如果只想获得一个IP,请用下面的 getClientIp()
     */
    public static function getClientIpMulti(){
        if(isset($_SERVER["X_CNET_FORWARD_FOR"])){//适合公司内部的环境 要在netscaler上配置才行，不能仿造
          $realip = $_SERVER["X_CNET_FORWARD_FOR"];
        }elseif(!empty($_SERVER["HTTP_X_FORWARDED_FOR"])){
            $realip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        }elseif(!empty($_SERVER["HTTP_CLIENT_IP"])){
            $realip = $_SERVER["HTTP_CLIENT_IP"];
        }else{
            $realip = $_SERVER["REMOTE_ADDR"];
        }
        return $realip;
    }
    /**
     * 获取地址信息
     */
    public static function ip2location($paramArr)
    {
        $options = array(
            'ip' => '',
        );
        if (is_array($paramArr))$options = array_merge($options, $paramArr);
        extract($options);
        
        include LJL_API_BASE.'/qqwry/iplocation.inc.php';
        $ipClass = new IpArea();
        $location = $ipClass->get($ip);
        return $location;
    }
}
