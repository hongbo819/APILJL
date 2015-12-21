<?php
/**
* 图片相关的工具
*/
class API_Item_Image_Util
{   
    private static $identify = '/usr/local/imagemagick/bin/identify';
    /**
     * 获得图片的详细信息(仅限图片)
     * 服务器必须安装ImageMagick
     */
    public static function getImgInfo($paramArr) {
		$options = array(
            'path'  => '', #地址
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);

        #exec("identify -verbose ${path}",$rtnArr);
        #参数说明，请详细见：http://www.imagemagick.org/script/escape.php
        exec(self::$identify.' -format "%b|%m|%w|%h|%r|%q " '.$path,$rtnArr);
        $data = array();
        if($rtnArr && isset($rtnArr[0])){
            list($data['size'],$ext,$data['width'],$data['height'],$data['colorsp'],$data['depth']) = explode("|", $rtnArr[0]);
            #扩展名处理
            $ext = strtolower($ext);
            if($ext == "jpeg")$ext = "jpg";
            $data['ext'] = $ext;

        }
        return $data;
    }
    
    /**
     * 获得文件类型
     * 判断是否是图片，只需判断 type 是否是image就可以
     */
    public static function getImgType($paramArr) {
		$options = array(
            'path'  => '', #地址
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);

        exec("file -ib ${path}",$rtnArr);
        $data = array();
        if($rtnArr){
            $mime = $rtnArr[0];
            $mineArr = explode("/", $mime);
            $data['type'] = $mineArr[0];
            $ext = $mineArr[1];
            #名称的规范
            if("jpeg" == $ext){
                $ext = "jpg";
            }elseif("x-ms-bmp" == $ext){
                $ext = "bmp";
            }
            $data['ext']  = $ext;
            $data['mime'] = $mime;
        }
        return $data;
    }

    /**
     * 下载图片
     * nginx 或者 apache都需要开启一下 sendfile模块
     */
    public static function downImg($paramArr) {
		$options = array(
            'file'  => '', #地址
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);
        if(!$file)return false;

        $filename = basename($file);
        //echo $filename;die;
        header("Content-type: application/octet-stream");

        //处理中文文件名,防止乱码
        $ua = $_SERVER["HTTP_USER_AGENT"];
        $encoded_filename = rawurlencode($filename);
        if (preg_match("/MSIE/", $ua)) {
            header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');
        } else if (preg_match("/Firefox/", $ua)) {
            header("Content-Disposition: attachment; filename*=\"utf8''" . $filename . '"');
        } else {
            header('Content-Disposition: attachment; filename="' . $filename . '"');
        }
//        header("Accept-Ranges: bytes");
        //apache 让Xsendfile发送文件
        //header("X-Sendfile: $file");
        //nginx 
        header("X-Accel-Redirect: $file");
    }

    /**
     * 下载远程图片，并保存。用wget的方法...
     */
    public static function downRemoteImg($paramArr) {
		$options = array(
            'url'    => false, #远程图片
            'path'   => '',#要保存的路径
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);
		//接口方式
		exec('wget -q --tries=3 -O '.$path.' '.$url);
		return true;
    }
    /**
     * 下载远程图片
     */
    public static function fetchRemoteImg($paramArr) {
		$options = array(
            'url'    => false, #远程图片
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);
		//接口方式
// 		$getUrl = LJL_Api::run("Security.Algos.fastEncode" , array('value'=>$url,'cryptkey'=>'fetchImg'));
// 		$u = API_Http::curlPage(array('url'=>"http://image.xxx.com./fetchImg.php?u={$getUrl}"));
//         return $u;
    }
    /**
     * 根据内容判断文件类型
     */
    public static function getFileTypeByContent($paramArr) {
		$options = array(
            'content'    => false, #文件内容
            'filepath'   => false, #文件路径
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);

        if(!$content && !$filepath)return false;

        if($filepath){
            $file     = fopen($filepath, "rb");
            $bin      = fread($file, 2); //只读2字节
            fclose($file);
        }elseif($content){
            $bin      = substr($content, 0,2);
        }
        
        $strInfo  = @unpack("c2chars", $bin);
        $typeCode = intval($strInfo['chars1'].$strInfo['chars2']);
        
        $fileType = '';
        switch ($typeCode){
            case 7790:
                $fileType = 'exe';
                break;
            case 7784:
                $fileType = 'midi';
                break;
            case 8297:
                $fileType = 'rar';
                break;
            case 255216:
                $fileType = 'jpg';
                break;
            case 7173:
                $fileType = 'gif';
                break;
            case 6677:
                $fileType = 'bmp';
                break;
            case 13780:
                $fileType = 'png';
                break;
            default:
                $fileType = 'unknown';
        }
        //再做一次调整
        if ($strInfo['chars1']=='-1' && $strInfo['chars2']=='-40' ) {
            $fileType = 'jpg';
        }
        if ($strInfo['chars1']=='-119' && $strInfo['chars2']=='80' ) {
            $fileType = 'png';
        }
        return $fileType;
    }
}

