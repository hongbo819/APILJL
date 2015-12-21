<?php
/**
 * 数组操作基础类库
 */

class API_Item_Base_Array
{
	//获取多个词首字母
	public static function  getAbcSortArr($paramArr) {
		$options = array(
            'data'  => '', #数组
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);

		if(!is_array($data))
		    $data = explode(',', $data);
        $classArr = array();
        if($data){
            foreach($data as $key => $value){
                //进行字母索引
                $f_letter = API_Item_Base_String::getFirstLetter(array('input'=>$value));
                $akey = ord($f_letter);
                
                $classArr[$key] = array("val"=>$value,"letter"=>$f_letter);
            }
        }
        return $classArr;
    }
    /**
     * 判断数组中的值是否在某个字符串中
     */
    public static function isArrvalInStr($str, $Arr, $returnVal=false){
        if(empty($str)) return false;
        foreach((array)$Arr as $v){
            if(false !== strpos($str, $v)){
                return $returnVal ? $v : true;
            }
        }
        return false;
    }
}
