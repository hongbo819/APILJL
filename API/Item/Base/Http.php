<?php
/**
 * 数组操作基础类库
 */
class API_Item_Base_Http
{
/**
     *  利用curl的形式获得页面请求 请用这个函数取代file_get_contents
     */
    public static function curlPage($paramArr){
       return API_Http::curlPage($paramArr);
    }
    /**
     *  利用curl POST数据
     */
    public static function curlPost($paramArr){
        return API_Http::curlPost($paramArr);
    }
    /**
     *  利用 curl_multi_** 的函数,并发多个请求
     */
    public static function multiCurl($paramArr){
        return API_Http::multiCurl($paramArr);
    }
    /**
     * 设置404 Header信息
     */
    public static function send404Header(){
        API_Http::send404Header();
    }
    /**
     * 设置各种码的header信息
     * @param int $code
     */
    public static function sendHeaderCode($paramArr){
        $options = array(
            'code'  => '', #数组
        );
        if (is_array($paramArr))$options = array_merge($options, $paramArr);
        extract($options);
        API_Http::sendHeaderCode($code);
    }
}
