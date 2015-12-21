<?php
/**
 * 文件操作基础类库
 */

class API_Item_Base_File
{

    /**
     * 判断文件是否存在，包括判断远程文件是否存在
     */
	public static function exists($paramArr)
	{
	    $options = array(
	        'file'  => '', #文件地址
	    );
	    if (is_array($paramArr))$options = array_merge($options, $paramArr);
	    extract($options);
	    return API_File::exists($file);
	}
	
    /**
     * 递归复制
     */
	public static function copyDir($paramArr)
	{
		$options = array(
			'source' => false,
			'dest'   => false,
			'overwrite'=>false
		);
		if(is_array($paramArr)) $options = array_merge($options, $paramArr);
		extract($options);
		return API_File::copyDir($source, $dest, $overwrite);
	}

    /**
     * 删除文件或整个目录
     */
	public static function rm($paramArr)
	{
		$options = array(
			'path'  => false,
			'recursive' =>false //递归删除
		);
		if(is_array($paramArr)) $options = array_merge($options, $paramArr);
		extract($options);
		
		return API_File::rm($path, $recursive);
	}


	/**
     *  列出文件列表
	 */
	public static function ls($paramArr)
	{
        $options = array(
			'__dir'  => './',
			'__pattern' =>'*.*' //匹配规则
		);
		if(is_array($paramArr)) $options = array_merge($options, $paramArr);
		extract($options);
		return API_File::ls($__dir, $__pattern);
	}

    /**
     * 将一个URL从相对路径转换为完整URL
     *
     */
    public static function urlRelativeToAbs($paramArr) {
		$options = array(
            'srcurl'   => 0, #相对路径
            'baseurl'  => false, #进行相对的绝对路径
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);//($srcurl, $baseurl) {


        $srcinfo = parse_url($srcurl);
        if(isset($srcinfo['scheme'])) {
            return $srcurl;
        }

        $baseinfo = parse_url($baseurl);
        $url = $baseinfo['scheme'].'://'.$baseinfo['host'];
        if(substr($srcinfo['path'], 0, 1) == '/') {
            $path = $srcinfo['path'];
        }else{
            $path = dirname($baseinfo['path']).'/'.$srcinfo['path'];
        }
        $rst = array();
        $path_array = explode('/', $path);
        if(!$path_array[0]) {
            $rst[] = '';
        }
        foreach ($path_array AS $key => $dir) {
            if ($dir == '..') {
                if (end($rst) == '..') {
                    $rst[] = '..';
                }elseif(!array_pop($rst)) {
                    $rst[] = '..';
                }
            }elseif($dir && $dir != '.') {
                $rst[] = $dir;
            }
        }
        if(!end($path_array)) {
            $rst[] = '';
        }
        $url .= implode('/', $rst);
        return str_replace('\\', '/', $url);
    }

}
