<?php
/**
* 地区相关的API
*/
class API_Item_Service_Http extends API_Item_Service_ServiceAbstract
{
    /**
     * 是发送head头，可以应用在请求统计脚本
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
