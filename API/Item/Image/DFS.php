<?php 
	/**
	 * 图片存储类 
	 * @author cuihongbo
	 * 2015-1-26
	 * 图片上传到0-f根文件夹下为分布式做准备
	 */
	class API_Item_Image_DFS{
		public function __construct(){
		}
		/**
		 * 上传前，获取图片名称 和 图片上传路径
		 * @param $time 图片上传时间
		 * @return array
		 */
		public static function imgStorage($paramArr){
            $options = array(
                'rootdir'  => '', #可以不指定，默认根目录的0-f文件夹下
                );
            if (is_array($paramArr))$options = array_merge($options, $paramArr);
            extract($options);
			$time = SYSTEM_TIME;
			$imgName = self::imgName();
			$imgDir  = self::imgDir($imgName, $time);
			return array($imgName, $time, rtrim($rootdir, '/').$imgDir);
		}
		/**
		 * 获取图片路径
		 * @param $imgName 图片名称
		 * @param $time  图片上传时间
		 */
		public static function getImgDir($paramArr){
            $options = array(
                'imgName'   =>  '',#图片名称,必须
                'time'       => '', #图片上传时间,必须
                'rootdir'    => '', #可以不指定，默认根目录的0-f文件夹下
                );
            if (is_array($paramArr))$options = array_merge($options, $paramArr);
            extract($options);
			if(!$imgName || !$time) return false;
			$imgDir = self::imgDir($imgName, $time);
			return rtrim($rootdir, '/').$imgDir;
		}
		/**
		 * 生成imgname
		 */
		private static function imgName(){
			$uniqid = uniqid();
			$time   = microtime(true);
			$randNum= mt_rand(0, 1000);
			return md5($uniqid.$time.$randNum);
		}
		
		/**
		 * 生成imgDir
		 */
		private static function imgDir($imgName, $time){
			$imgDir  = '/'.$imgName[0].'/'.date('Ym/d/a/', $time);
			return $imgDir;
		}
		
		
	}
?>