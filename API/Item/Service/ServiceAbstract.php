<?php
/**
* 插件抽象类
*/
abstract class API_Item_Service_ServiceAbstract
{
	/**
	* @var LJL_Product_Caching_GetCacheLoader
	*/
	protected static $cache;	
	
	
	/**
	* 加载缓存数据
	*/
	protected static function loadProCache($moduleName, $param = array(), $num = 0)
	{
        if(!$moduleName)return false;

        $paramStr = "";
        if($param){
            foreach($param as $k => $v){
                $paramStr .= "&{$k}=" .  urlencode($v);
            }
        }

        $url = "http://zcloud.xxx.com/proMongo?modName={$moduleName}{$paramStr}";
		
		$data = API_Http::curlPage(array('url'=>$url,'timeout'=>1));#远程请求数据
        if($data){
           $data = API_JsonDecode::decode($data);
        }
		return $data;
	}

}
