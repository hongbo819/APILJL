<?php
/**
* ImageMagick 的相关图片操作封装
*/
class API_Item_Image_IM
{

    //private static $convert = '/usr/bin/convert_re';
    private static $convert = '/usr/local/imagemagick/bin/convert';
    #预定水印图片
    #数组的定义是从大到小的，
    private static $waterImg = array(
        'LJL' => array(
                '1280' => array('path'=>'/www/cuihongbo/image/logo/1280.png','xpadding'=>100,'ypadding'=>100),#>1280采用的水印
                '1024' => array('path'=>'/www/cuihongbo/image/logo/1024.png','xpadding'=>100,'ypadding'=>100),#>1024采用的水印
                '800'  => array('path'=>'/www/cuihongbo/image/logo/800.png','xpadding'=>60,'ypadding'=>60),
                '450'  => array('path'=>'/www/cuihongbo/image/logo/450.png','xpadding'=>20,'ypadding'=>20),
            ),
        'BLOG' => array(
                '1024' => array('path'=>'/www/LJL/Html/Blogstaticfile/1.png','xpadding'=>100,'ypadding'=>100),#>1024采用的水印
                '450'  => array('path'=>'/www/LJL/Html/Blogstaticfile/3.png','xpadding'=>20,'ypadding'=>20),
            ),
//        'FLEA' => array(
//                '450' => array('path'=>'/www/img/html/logo/used_watermark.png','xpadding'=>20,'ypadding'=>20),#>1280采用的水印
//            ),
//        'FLEAFD' => array(#应用在fdfs上的
//                '450' => array('path'=>'/www/fdfs/html/logo/used_watermark.png','xpadding'=>20,'ypadding'=>20),#>1280采用的水印
//            ),
//        'CMS' => array(
//                'deep' => array('path'=>'/www/fdfs/html/logo/watermark_deep.png','xpadding'=>100,'ypadding'=>100),#>1280采用的水印
//                'light' => array('path'=>'/www/fdfs/html/logo/ljl_water.png','xpadding'=>100,'ypadding'=>100)#>1024采用的水印
//         ),
//        'FNCMS' => array(#蜂鸟水印
//                'deep' => array('path'=>'/www/fdfs/html/logo/fn_watermark_deep.png','xpadding'=>100,'ypadding'=>100),#>1280采用的水印
//                'light' => array('path'=>'/www/fdfs/html/logo/fn_watermark_light.png','xpadding'=>100,'ypadding'=>100)#>1024采用的水印
//        ),
//        'SJBBS' => array(
//                '450' => array('path'=>'/www/fdfs/html/logo/sjbbs.png','xpadding'=>20,'ypadding'=>20),#>1280采用的水印
//            ),
//        'DIYBBS' => array(
//                '450' => array('path'=>'/www/fdfs/html/logo/diybbs.png','xpadding'=>20,'ypadding'=>20),#>1280采用的水印
//        ),
//        'NBBBS' => array(
//                '450' => array('path'=>'/www/fdfs/html/logo/nbbbs.png','xpadding'=>20,'ypadding'=>20),#>1280采用的水印
//        ),
//        'PADBBS' => array(
//                '450' => array('path'=>'/www/fdfs/html/logo/padbbs.png','xpadding'=>20,'ypadding'=>20),#>1280采用的水印
//        ),
//        'OABBS' => array(
//                '450' => array('path'=>'/www/fdfs/html/logo/oabbs.png','xpadding'=>20,'ypadding'=>20),#>1280采用的水印
//        ),
//        'SOFTBBS' => array(
//                '450' => array('path'=>'/www/fdfs/html/logo/softbbs.png','xpadding'=>20,'ypadding'=>20),#>1280采用的水印
//        ),
//        'BBSWATER' => array(
//                '450' => array('path'=>'/www/fdfs/html/logo/bbswater.png','xpadding'=>20,'ypadding'=>20),#>1280采用的水印
//        ),
//        'PRO' => array(
//            '1280' => array('path'=>'/www/fdfs/html/logo/1280.png','xpadding'=>100,'ypadding'=>100),#>1280采用的水印
//            '1024' => array('path'=>'/www/fdfs/html/logo/1024.png','xpadding'=>100,'ypadding'=>100),#>1024采用的水印
//            '800'  => array('path'=>'/www/fdfs/html/logo/800.png','xpadding'=>60,'ypadding'=>60),
//            '450'  => array('path'=> '/www/fdfs/html/logo/450.png','xpadding'=>20,'ypadding'=>20),
//        ),
    );
    /**
     * 图片顺时针旋转
     */
    public static function rotate($paramArr){
		$options = array(
            'srcPath'       => '', #源文件路径
            'desPath'       => '', #目标文件路径
            'degree'        => 90, #向右旋转的度数（顺时针）
            'background'    => 'white', #如果旋转不是90的整数倍，需要背景色填空，否则很丑
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);

        if(!$srcPath || !$desPath )return false;
        
        exec(self::$convert . " -background {$background} -rotate {$degree} '{$srcPath}' '{$desPath}'",$rtn);
        return true;

    }

    /**
     * 图片格式转换
     */
    public static function transFileType($paramArr){
		$options = array(
            'srcPath'       => '',    #源文件路径
            'format'        => 'jpg', #转换的文件格式
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);

        if(!$srcPath || !$format )return false;
        if(!in_array($format,array("jpg","png","bmp","gif")))return false;

        $basePath = substr($srcPath, 0,  strrpos($srcPath, "."));
        exec(self::$convert . " '{$srcPath}' '{$basePath}.{$format}'",$rtn);
        return true;

    }

    /**
     * 尺寸转换
     */
    public static function zoom($paramArr){
		$options = array(
            'srcPath'       => '', #源文件路径
            'desPath'       => '', #目标文件路径
            'size'          => '', #要缩放的图片尺寸 如120x90
            'progressive'   => 0,  #是否进行渐进式渲染
            'quality'       => 90,  #图片品质
            'stripExif'     => true, #是否去掉Exif信息 拍摄相机信息等，除非显示拍摄相机的需求，否则无用
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);

        if(!$srcPath || !$size )return false;
        $cmdSubStr = " -quality {$quality} ";

        $ext = strtolower(substr($srcPath, strrpos($srcPath, ".")+1));
        if($ext == "jpeg") $ext = "jpg";
        #是否进行渐进式渲染
        if($progressive && "jpg" == strtolower($ext))$cmdSubStr .= " -interlace plane ";

        #尺寸的限制
        $sizeArr = explode("x",$size);
        if($sizeArr && count($sizeArr) > 1){
            if($sizeArr[0] > 8000 || $sizeArr[1] > 8000)return false; #如果尺寸过大，返回
        }

        $seeGifAsJpg = false; #将gif强制定成了jpg
        #获得图片的信息
        if($size){
            $info = API_Item_Image_Util::getImgInfo(array('path' => $srcPath));
            $imgHeight = $info["height"];
            $imgWidth  = $info["width"];
            if($info && $imgWidth < $sizeArr[0] && $imgHeight < $sizeArr[1]){#如果原图还要压缩尺寸小，就返回原图
                copy($srcPath,$desPath);
                return true;
            }
            
            #有些系统将gif图片命名成了jpg，造成covert图片出问题
            if($info["ext"] == "gif" && $ext == "jpg"){
                $seeGifAsJpg = true;
                
            }
        }

        
        #上面对gif图进行了jpg命名，命名回去
        if($seeGifAsJpg){            
            $orgDesPath  = $desPath;
            $desPath     = str_replace(array(".jpg",".jpeg"), ".gif", $orgDesPath);
            exec(self::$convert . " -resize '{$size}' {$cmdSubStr} '{$srcPath}' '{$desPath}'",$rtn);
            exec("mv '{$desPath}' '{$orgDesPath}'",$rtn);
            #exec("rm '{$srcPath2}' ",$rtn);
        }else{
            exec(self::$convert . " -colorspace RGB -resize '{$size}' {$cmdSubStr} '{$srcPath}' '{$desPath}'",$rtn);            
        }
        
        #对图片进行优化
        if(!$progressive && !$seeGifAsJpg){#这个版本的优化暂不支持渐进式
            //self::optimizeJpg(array('srcPath'=>$desPath,'stripExif'=>$stripExif));
        }
        return true;

    }
    /**
     * 从图片中截取一个区域
     */
    public static function cut($paramArr){
		$options = array(
            'srcPath'       => '', #源文件路径
            'desPath'       => '', #目标文件路径
            'size'          => '', #要压缩的图片尺寸，如100x80
            'left'          => 0,  #距离左侧的像素值
            'top'           => 0,  #距离顶部的像素值
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);

        if(!$srcPath || !$size || !$desPath )return false;
        exec(self::$convert . " -crop {$size}+{$left}+{$top} '{$srcPath}' '{$desPath}'",$rtn);
        return true;

    }
    
    /**
     * 截取正方形区域
     */
    public static function square($paramArr){
		$options = array(
            'srcPath'       => '', #源文件路径
            'desPath'       => '', #目标文件路径
            'size'          => '', #要压缩的图片尺寸，如100x80
            'position'      => 0, #是否从中部中截取
            'offset'        => -1, #裁图
            'quality'       => 90,  #图片品质
            'stripExif'     => true, #是否去掉Exif信息 拍摄相机信息等，除非显示拍摄相机的需求，否则无用
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);

        if(!$srcPath || !$size || !$desPath )return false;
        $posArr = array('leftUp','middleUp','rightUp','leftMiddle','middleMiddle','rightMiddle','leftBottom','middleBottom','rightBottom');
        $pos    = $position == 0 ? 'middleMiddle': $posArr[$position-1];
        if(!$pos) $pos = 'middleMiddle';
        #获得图片的信息
        $info = API_Item_Image_Util::getImgInfo(array('path' => $srcPath));

        $imgHeight = $info["height"];
        $imgWidth  = $info["width"];
        list($zoomWidth,$zoomHeight) = explode("x",$size); #要压缩的尺寸

		$srcSize    = $imgWidth.'x'.$imgHeight;
        $newSize    = $size;
		$newSizeArr = explode('x',$newSize);
		if($imgWidth < $zoomWidth || $imgHeight < $zoomHeight){
            copy($srcPath,$desPath);
            return true;
        }
		$scale      = min($zoomWidth/$imgWidth,$zoomHeight/$imgHeight);
		#原图比例
		$srcCate    =  $imgWidth/$imgHeight;
		#目标图比例
		$newSrcCate =  $zoomWidth/$zoomHeight;
        #截取位置
        $offset    = $offset;       
		$beishu 	= 1;
        #切割
        if($offset >= 0){
            if($imgWidth >= $zoomWidth && $imgHeight >= $zoomHeight){
                if($imgWidth <= $imgHeight){
                    $sHeight =  round($zoomWidth*$imgHeight/$imgWidth);
                    $newSize = $zoomWidth.'x'.$zoomHeight;
                    if($imgHeight >= $offset){
                        $sys    = ' -resize '.$zoomWidth.'x'.$sHeight.' -crop '.$newSize.'+0+'.$offset.' -quality '.$quality.' '.$srcPath.' '.$desPath;
                    }
                }
            }
            if($sys){
                exec(self::$convert . $sys, $rtn);
                #对图片进行优化
                //self::optimizeJpg(array('srcPath'=>$desPath,'stripExif'=>$stripExif));
            }else{
                return false;
            }
            return true;
        }
		if($scale < 1){
			#原图宽大于高
			if($srcCate > 1){
				#压缩的宽度
				$ywidth = round(($zoomHeight/$imgHeight)*$imgWidth);
				if($ywidth >= $zoomWidth && $zoomHeight != $imgHeight){
                    $imgInfo = array($ywidth,$zoomHeight);
                    $imgPostion = self::getCoordinatesPostion(array('imgArr' => $imgInfo, 'imgHandArr'=>array($zoomWidth,$zoomHeight),'type'=>$pos));
					$sys    = ' -resize '.$ywidth.'x'.$zoomHeight.' -crop '.$newSize.'+'.$imgPostion['0'].'+'.$imgPostion['1'].' -quality '.$quality.' '.$srcPath.' '.$desPath;
				}else if($zoomHeight != $imgHeight){
					$yhight = round(($zoomWidth/$imgWidth)*$imgHeight);
                    $imgInfo = array($zoomWidth,$yhight);
                    $imgPostion = self::getCoordinatesPostion(array('imgArr' => $imgInfo, 'imgHandArr'=>array($zoomWidth,$zoomHeight),'type'=>$pos));
                    $sys    = ' -resize '.$zoomWidth.'x'.$yhight.' -crop '.$newSize.'+'.$imgPostion['0'].'+'.$imgPostion['1'].' -quality '.$quality.' '.$srcPath.' '.$desPath;
				}else{
                    $imgInfo = array($imgWidth,$imgHeight);
                    $imgPostion = self::getCoordinatesPostion(array('imgArr' => $imgInfo, 'imgHandArr'=>array($zoomWidth,$zoomHeight),'type'=>$pos));
					$sys    = ' -resize '.$srcSize.' -crop '.$newSize.'+'.$imgPostion['0'].'+'.$imgPostion['1'].' -quality '.$quality.' '.$srcPath.' '.$desPath;
				}
			#原图宽小于高
			}else if($srcCate < 1){
				$yheight = round(($zoomWidth/$imgWidth)*$imgHeight);
				if($yheight >= $zoomHeight && $zoomWidth != $imgWidth){
                    $imgInfo = array($zoomWidth,$yheight);
                    $imgPostion = self::getCoordinatesPostion(array('imgArr' => $imgInfo, 'imgHandArr'=>array($zoomWidth,$zoomHeight),'type'=>$pos));
                    $sys    = ' -resize '.$zoomWidth.'x'.$yheight.' -crop '.$newSize.'+'.$imgPostion['0'].'+'.$imgPostion['1'].' -quality '.$quality.' '.$srcPath.' '.$desPath;
				}else if($zoomHeight != $imgHeight){
                    $ywidth = round(($zoomHeight/$imgHeight)*$imgWidth);
                    $imgInfo = array($ywidth,$zoomHeight);
                    $imgPostion = self::getCoordinatesPostion(array('imgArr' => $imgInfo, 'imgHandArr'=>array($zoomWidth,$zoomHeight),'type'=>$pos));
                    $sys    = ' -resize '.$ywidth.'x'.$zoomHeight.' -crop '.$newSize.'+'.$imgPostion['0'].'+'.$imgPostion['1'].' -quality '.$quality.' '.$srcPath.' '.$desPath;
				}else{
                    $imgInfo = array($imgWidth,$imgHeight);
                    $imgPostion = self::getCoordinatesPostion(array('imgArr' => $imgInfo, 'imgHandArr'=>array($zoomWidth,$zoomHeight),'type'=>$pos));
                    $sys    = ' -resize '.$srcSize.' -crop '.$newSize.'+'.$imgPostion['0'].'+'.$imgPostion['1'].' -quality '.$quality.' '.$srcPath.' '.$desPath;
				}
			#原图宽等于高
			}else{
				$ywidth  = round(($zoomHeight/$imgHeight)*$imgWidth);
				$yheight = round(($zoomWidth/$imgWidth)*$imgHeight);
				if($newSrcCate > 1){
                    $imgInfo = array($zoomWidth,$zoomWidth);
                    $imgPostion = self::getCoordinatesPostion(array('imgArr' => $imgInfo, 'imgHandArr'=>array($zoomWidth,$zoomHeight),'type'=>$pos));
                    $sys    = ' -resize '.$zoomWidth.'x'.$yheight.' -crop '.$newSize.'+'.$imgPostion['0'].'+'.$imgPostion['1'].' -quality '.$quality.' '.$srcPath.' '.$desPath;
				}else if($newSrcCate < 1){
                    $imgInfo    = array($zoomHeight,$zoomHeight);
                    $imgPostion = self::getCoordinatesPostion(array('imgArr' => $imgInfo, 'imgHandArr'=>array($zoomWidth,$zoomHeight),'type'=>$pos));
                    $sys    = ' -resize '.$ywidth.'x'.$zoomHeight.' -crop '.$newSize.'+'.$imgPostion['0'].'+'.$imgPostion['1'].' -quality '.$quality.' '.$srcPath.' '.$desPath;
				}else{
					$sys    = ' -resize '.$zoomWidth.'x'.$zoomHeight.' -crop '.$newSize.'+0+0 -quality '.$quality.' '.$srcPath.' '.$desPath;
				}
			}
            
    
            exec(self::$convert . $sys, $rtn);
            #对图片进行优化
            //self::optimizeJpg(array('srcPath'=>$desPath,'stripExif'=>$stripExif));
            
		}else{
            copy($srcPath,$desPath);
		}
        
        return true;

    }
    /**
     * 获取坐标
     * @param imgArr 需要处理的图像数组 array(0=>width,1=>height)
     * @param imgHandArr 处理之后的图像大小数组 array(0=>width,1=>height)
     * @param type   截图类型
     */
     public static function getCoordinatesPostion($paramArr){
         $options = array(
            'imgArr' => '',         #imgArr 需要处理的图像数组 array(0=>width,1=>height)
            'imgHandArr'=>'',       #imgHandArr 处理之后的图像大小数组 array(0=>width,1=>height)
            'type'=>'middleMiddle'  #截取位置
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);
        if(!is_array($imgArr) || !is_array($imgHandArr)) return false; 
        switch ($type)	{
            case 'leftUp':
                return array(0,0);
                break;
            case 'middleUp':
                $startX = round(($imgArr[0]-$imgHandArr[0])/2);
                return array($startX,0);
                break;
            case 'rightUp':
                $startX = $imgArr[0]-$imgHandArr[0];
                return array($startX,0);
                break;
            case 'leftMiddle':
                $startY = round(($imgArr[1]-$imgHandArr[1])/2);
                return array(0,$startY);
                break;
            case 'middleMiddle':
                $startX = round(($imgArr[0]-$imgHandArr[0])/2);
                $startY = round(($imgArr[1]-$imgHandArr[1])/2);
                return array($startX,$startY);
                break;
            case 'rightMiddle':
                $startX = $imgArr[0]-$imgHandArr[0];
                $startY = round(($imgArr[1]-$imgHandArr[1])/2);
                return array($startX,$startY);
                break;
            case 'leftBottom':
                $startY = $imgArr[1]-$imgHandArr[1];
                return array(0,$startY);
                break;
            case 'middleBottom':
                $startX = round(($imgArr[0]-$imgHandArr[0])/2);
                $startY = $imgArr[1]-$imgHandArr[1];
                return array($startX,$startY);
                break;
            case 'rightBottom':
                $startX = $imgArr[0]-$imgHandArr[0];
                $startY = $imgArr[1]-$imgHandArr[1];
                return array($startX,$startY);
                break;
        }
     }
    
    
    /**
     * 将图片转化RGB模式，防止CMYK图片显示问题
     */
    public static function toRGBJpg($paramArr){
		$options = array(
            'srcPath'       => '', #源文件路径
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);


        exec(self::$convert . " -colorspace RGB '{$srcPath}' '{$srcPath}'",$rtn);

    }
    
    /**
     * 进行优化图片，对图片进行压缩
     * jpegoptim 是一个插件需要安装该插件
     */
    public static function optimizeJpg($paramArr){
		$options = array(
            'srcPath'       => '', #源文件路径
            'stripExif'     => true, #是否去掉Exif信息
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);

        $ext = substr($srcPath, strrpos($srcPath, ".")+1);
        if("jpg" != strtolower($ext))return false;
        
        $cmdSubStr = $stripExif ? " --strip-exif " : "" ;
        exec("/usr/local/bin/jpegoptim --strip-com {$cmdSubStr} --strip-iptc --max=90 {$srcPath}" , $rtn);
        return true;
    }

    /**
     * 对bmp进行优化，转成jpg格式
     */
    public static function optimizeBmp($paramArr){
		$options = array(
            'srcPath'       => '', #源文件路径
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);

        #首先重命名文件，变成扩展名为jpg，这样执行convert的时候，压缩率更高
        $jpgFile     = str_replace(".bmp", "", $srcPath) . ".jpg";
        rename($srcPath, $jpgFile);
        #return self::$convert . " -format jpg '{$jpgFile}' '{$jpgFile}'";
        exec(self::$convert . " -format jpg '{$jpgFile}' '{$jpgFile}'",$rtn);
        #恢复原来的文件名
        rename($jpgFile, $srcPath);

        return true;
    }

    /**
     * 对png进行优化
     */
    public static function optimizePng($paramArr){
		$options = array(
            'srcPath'       => '', #源文件路径
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);

        return true;
    }
    /**
     * 添加水印,可以完成图片的叠加
     */
    public static function watermark($paramArr){
		$options = array(
            'srcPath'       => '', #源文件路径
            'desPath'       => '', #目标文件路径
            'waterGroup'    => 'LJL', #预定的水印图片组
            'waterImg'      => '', #指定水印图片
            'position'      => 9,  #水印位置
            'xpadding'      => 0,  #距离左右边的空隙,如果指定水印组，这个参数无效
            'ypadding'      => 0,  #距离上下边的空隙
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);


        #获得图片的信息
        $info = API_Item_Image_Util::getImgInfo(array('path' => $srcPath));
        if(!$info)return false;
        
        $imgHeight = isset($info["height"]) ? $info["height"] : 0;
        $imgWidth  = isset($info["width"]) ? $info["width"] : 0 ;
        
        if($imgHeight == 0 || $imgWidth == 0)return false;

        #水印位置  1：左上 2：上 3：右上 4：左 5：中 6：右 7：左下 8：下 9：右下
        $posArr = array(1=>"NorthWest",2=>"North",3=>'NorthEast',4=>'West',5=>'Center',6=>'East',7=>'SouthWest',8=>'South',9=>'SouthEast');
        $positionStr = isset($posArr[$position]) ? $posArr[$position] : "SouthEast";

        //$waterImg = false;
        #获得水印的图片
        if($waterGroup){
            $waterImgGp = self::$waterImg[$waterGroup];
            if($waterImgGp){
                #从组找出水印
                foreach($waterImgGp as $k => $v){
                    if($imgWidth >= $k){
                        $waterImg = $v['path'];
                        $xpadding = $v['xpadding'];
                        $ypadding = $v['ypadding'];

                        if(in_array($position,array(2,5,8))){#中间的时候
                            $xpadding = 0;
                        }
                        break;
                    }
                }
            }
        }
        if(!$waterImg)return false;

        $cmd = self::$convert . " {$srcPath} {$waterImg} -gravity {$positionStr} -geometry +{$xpadding}+{$ypadding} -composite {$desPath}";
        exec($cmd,$rtn);
        return $cmd;
        
    }

    /**
     * 图片锐化
     */
    public static function sharpen($paramArr){
		$options = array(
            'srcPath'       => '', #源文件路径
            'desPath'       => '', #目标文件路径
            'adaptive'      => 0,  #是否自适应边缘锐化
            'radius'        => '', #锐化比例
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);
        $sumCmd = $adaptive ? "-adaptive-sharpen" : "-sharpen";
        exec(self::$convert . " {$sumCmd} {$radius} '{$srcPath}' '{$desPath}'",$rtn);
        return true;

    }

    /**
     * 将图片转换为渐进式
     */
    public static function progressive($paramArr){
		$options = array(
            'srcPath'       => '', #源文件路径
            'desPath'       => '', #目标文件路径
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);

        if(!$srcPath || !$desPath)return false;
        
        $ext = substr($srcPath, strrpos($srcPath, ".")+1);
        if("jpg" != strtolower($ext))return false;

        $cmdSubStr = "  -interlace plane ";
        exec(self::$convert . " {$cmdSubStr} '{$srcPath}' '{$desPath}'",$rtn);
        return true;

    }
    /**
     * 转化为8位深度的图片
     */
    public static function to8Depth($paramArr){
		$options = array(
            'srcPath'       => '', #源文件路径
            'desPath'       => '', #目标文件路径
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);
        
        $cmd = self::$convert . " {$srcPath} -depth 8 {$desPath}";
        exec($cmd, $rtn);
        return true;
    }
    
    /**
     * 合并图片
     */
    public static function mergePic($paramArr) {
        $options = array(
            'srcPath1'      => '', #源文件路径1
            'srcPath2'      => '', #源文化路径2
            'desPath'       => '', #目标文件路径
            'type'          => 0,  #合并方式 0=>横向 1=>纵向
        );
        if (is_array($paramArr)) $options = array_merge ($options, $paramArr);
        extract($options);
        
        $mark = $type == 0 ? '+' : '-'; # +用于横向 -用于纵向
        
        $cmd = self::$convert . " {$srcPath1} {$srcPath2} {$mark}append {$desPath}";
        exec($cmd, $rtn);
        return true;
    }
    
    /**
     * 设置图片边框颜色及线宽
     */
    public static function border($paramArr) {
        $options =  array(
            'srcPath'       => '', #源文件路径
            'desPath'       => '', #目标文件路径
            'borderColor'   => '#FF0000', #边框颜色，默认红色, 支持red、yellow颜色单词
            'borderWidth'   => 2,  #左右边框宽度,默认2px
            'borderHeight'  => 2,  #上下边框高度，默认2px
        );
        if (is_array($paramArr)) $options = array_merge($options, $paramArr);
        extract($options);
        
        if (!$srcPath || !$desPath) return false;
        
        if (strpos($borderColor, '#') === 0) $borderColor = "'{$borderColor}'";
        
        $cmd = self::$convert . " {$srcPath} -bordercolor {$borderColor} -border {$borderWidth}x{$borderHeight} {$desPath}";
        exec($cmd, $rtn);
        return true;
    }
    
    /**
     * 带阴影边框
     */
    public static function frame($paramArr) {
        $options = array(
            'srcPath'       => '', #源文件路径
            'desPath'       => '', #目标文件路径
            'color'         => '#FF0000', #边框颜色，默认红色, 支持red、yellow颜色单词
            'borderWidth'   => 25,  #左右边框宽度,默认2px
            'borderHeight'  => 25,  #上下边框高度，默认2px
            'leftAndTop'    => 0,  #左边和上边阴影倾斜程度
            'rightAndBottom'=> 25,  #右边和下边阴影倾斜程度
        );
        
        if (is_array($paramArr)) $options = array_merge($options, $paramArr);
        extract($options);
        
        if (!$srcPath || !$desPath) return false;
        
        if (strpos($color, '#') === 0) $color = "'{$color}'";
        
        $cmd = self::$convert . " {$srcPath} -mattecolor {$color} -frame {$borderWidth}x{$borderHeight}+{$leftAndTop}+{$rightAndBottom} {$desPath}";
        exec($cmd, $rtn);

        return $rtn;
    }
    
    /**
     * 设置对比度
     */
    public static function contrast($paramArr) {
        $options = array(
            'srcPath'       => '', #源文件路径
            'desPath'       => '', #目标文件路径
            'degree'        => 1,  #对比变化度
        );
        
        if (is_array($paramArr)) $options = array_merge ($options, $paramArr);
        extract($options);
        
        if (!$srcPath || !$desPath) return false;
        $cmd = self::$convert . " {$srcPath} -contrast-stretch {$degree}% {$desPath}";
        exec($cmd, $rtn);
        return $rtn;
    }
    
    /**
     * 分解gif动图
     */
    public static function splitGif($paramArr) {
        $options = array(
            'srcPath'       => '', #源文件路径
            'desDir'        => '', #gif子图存放目录
            'desPrefix'     => 'gif_', #拆分出的gif子图文件名前缀, 默认gif_
        );
        
        if (is_array($paramArr)) $options = array_merge ($options, $paramArr);
        extract($options);
        
        if (!$srcPath || !$desDir) return false;
        if (!is_dir($desDir) || !is_writable($desDir)) return false;
        if (!preg_match('/.*\/$/', $desDir)) $desDir .= '/';
        
        $modifyTime = time();
        
        $cmd = self::$convert . " {$srcPath} {$desDir}{$desPrefix}%02d.gif";
        exec($cmd, $rtn);
        
        clearstatcache();#清除文件缓存信息
        $files = array();
        $dh  = opendir($desDir);
        while (false !== ($filename = readdir($dh))) {
            $gifPath = $desDir . $filename;
            if (!preg_match("/^{$desPrefix}\d+\.gif$/", $filename)) continue;
            if(filemtime($gifPath)<$modifyTime) continue;
            $files[] = $filename;
        }
        
        return $files;
    }
    
    /**
     * 从gif动图中得到一张子图
     */
    public static function getOnePicFromGif($paramArr) {
        $options = array(
            'srcPath'       => '', #源文件路径
            'desPath'       => '', #gif子图路径
            'index'         => 0,  #子图索引位置，从0开始
        );
        
        if (is_array($paramArr)) $options = array_merge ($options, $paramArr);
        extract($options);
        
        if (!$srcPath || !$desPath) return false;
        
        $cmd = self::$convert . " {$srcPath}[{$index}] {$desPath}";

        exec($cmd, $rtn);
        return true;
    }
    
    /**
     * 创建空白图片
     */
    public static function createBlankPic($paramArr) {
        $options = array(
            'desPath'       => '', #空白图片路径
            'width'         => 0, #图片宽度
            'height'        => 0, #图片高度
            'color'         => '#FFFFFF', #空白图片颜色, 默认白色
        );
        
        if (is_array($paramArr)) $options = array_merge ($options, $paramArr);
        extract($options);
        
        if (strpos($color, '#') === 0) $color = "'{$color}'";
        
        $cmd = self::$convert . " -size {$width}x{$height} xc:{$color} {$desPath}";

        exec($cmd, $rtn);
        return true;
    }
    
    /**
     * 生成文字图片，可用于email，电话，qq等
     */
    public static function createTextPic($paramArr) {
        $options = array(
            'desPath'   => '', #生成图片名
            'text'      => '', #文本内容
            'font'      => 'helvetica', #文本字体
            'width'     => 185,  #图片宽度
            'height'    => 19,  #图片高度
            'txtColor'  => 'black', #文本颜色, 默认黑色
            'bgColor'   => 'white',   #背景色， 默认白色
            'fontSize'  => 16,  #文本字号
            'leftOffset'=> 4,  #文本距左边偏移量
            'topOffset' => 14, #文本距上边偏移量
        );
        
        if (is_array($paramArr)) $options = array_merge ($options, $paramArr);
        extract($options);
        
        if (strpos($txtColor, '#') === 0) $txtColor = "'{$txtColor}'";
        if (strpos($bgColor, '#') === 0) $bgColor = "'{$bgColor}'";
        
        $cmd = self::$convert . " -size {$width}x{$height} null:{$bgColor} {$desPath}";
        exec($cmd, $rtn);
        $cmd = self::$convert . " -font {$font} -fill {$txtColor} -pointsize {$fontSize} -draw 'text {$leftOffset},{$topOffset} \"{$text}\"' {$desPath} {$desPath}";
        exec($cmd, $rtn);
        return true;
    }
    
    /**
     * 图片上添加文本
     */
    public static function addText($paramArr) {
        $options  = array(
            'srcPath'       => '', #源文件路径
            'desPath'       => '', #目标文件路径
            'text'          => '', #文本内容
            'font'          => 'helvetica', #文本字体
            'txtColor'      => 'green', #文本颜色, 默认黑色
            'fontSize'      => 40,  #文本字号
            'leftOffset'    => 50,  #文本距左边偏移量
            'topOffset'     => 50, #文本距上边偏移量
        );
        if (is_array($paramArr)) $options = array_merge ($options, $paramArr);
        extract($options);
        
        if (strpos($txtColor, '#') === 0) $txtColor = "'{$txtColor}'";
        
        $cmd = self::$convert . " -font {$font} -fill {$txtColor} -pointsize {$fontSize} -draw 'text {$leftOffset},{$topOffset} \"{$text}\"' {$srcPath} {$desPath}";
        exec($cmd, $rtn);
        return true;
    }
    
    /**
     * 模糊图片
     * 参数信息，参考http://www.imagemagick.org/script/command-line-options.php#blur
     */
    public static function blur($paramArr) {
        $options = array(
            'srcPath'       => '', #源文件路径
            'desPath'       => '', #目标文件路径
            'radius'        => 80, #只可意会，不可言传，请见参考链接
            'sigma'         => 5,
        );
        
        if (is_array($paramArr)) $options = array_merge ($options, $paramArr);
        extract($options);
        
        $cmd = self::$convert . " -blur {$radius}x{$sigma} {$srcPath} {$desPath}";
        exec($cmd, $rtn);
        return true;
    }
    
    /**
     * 翻转
     */
    public static function turn($paramArr) {
        $options = array(
            'srcPath'       => '', #源文件路径
            'desPath'       => '', #目标文件路径
            'type'          => 0,  #0=>水平翻转 1=>左右翻转
        );
        
        if (is_array($paramArr)) $options = array_merge ($options, $paramArr);
        extract($options);
        
        $type = $type ? 'flop' : 'flip';
        
        $cmd = self::$convert . " -{$type} {$srcPath} {$desPath}";
        exec($cmd, $rtn);
        return true;
    }
    
    /**
     * 各种效果(底片效果、黑白颜色、噪声、油画效果、马赛克、铅笔画效果、毛玻璃效果、漩涡效果、凸起效果)
     */
    public static function effect($paramArr) {
        $options = array(
            'srcPath'       => '', #源文件路径
            'desPath'       => '', #目标文件路径
            'type'          => 0,  #0=>底片效果 1=>黑白效果 2=>噪声 3=>油画效果 4=>马赛克 5=>铅笔画效果 6=>毛玻璃效果 7=>漩涡效果 8=>凸起效果
            'radius'        => 2,  #噪声、油画效果使用该参数
            'factor'        => 2,  #铅笔画使用参数
            'amount'        => 30, #毛玻璃效果
            'degrees'       => 67, #漩涡效果使用,漩涡程度
            'borderWidth'   =>15,   #凸起效果，左右边框宽度
            'borderHeight'  =>15,   #凸起效果，上面边框宽度
        );
        
        if (is_array($paramArr)) $options = array_merge ($options, $paramArr);
        extract($options);
        
        $cmdParam = 'negate';
        if ($type == 1) $cmdParam = 'monochrome';
        else if($type == 2) $cmdParam = 'noise ' . $radius;
        else if($type == 3) $cmdParam = 'paint ' . $radius;
        else if($type == 4) $cmdParam = 'sample 10% -sample 1000%';
        else if($type == 5) $cmdParam = 'charcoal ' . $factor;
        else if($type == 6) $cmdParam = 'spread ' . $amount;
        else if($type == 7) $cmdParam = 'swirl ' . $degrees;
        else if($type == 8) $cmdParam = 'raise ' . $borderWidth . 'x' . $borderHeight;
        
        $cmd = self::$convert . " -{$cmdParam} {$srcPath} {$desPath}";
        exec($cmd, $rtn);
        return true;
    }
    
    /**
     * 添加水印,可以完成图片的叠加
     */
    public static function cmswatermark($paramArr){
	$options = array(
            'srcPath'       => '', #源文件路径
            'desPath'       => '', #目标文件路径
            'waterGroup'    => 'CMS', #预定的水印图片组
            'waterImg'      => '', #指定水印图片
            'xpadding'      => 0,  #距离左右边的空隙,如果指定水印组，这个参数无效
            'ypadding'      => 0,  #距离上下边的空隙
        );
        if (is_array($paramArr))$options = array_merge($options, $paramArr);
        extract($options);

        $isWaterImage = FALSE;	 #图片水印的状态
        $formatMsg = "no suport format~~"; //暂不支持该文件格式，请用图片处理软件将图片转换为GIF、JPG、PNG格式。
        $groundImage = $srcPath;
        $waterArr = $waterImg ? $waterImg : self::$waterImg[$waterGroup];
        $waterImage = $waterArr['deep']['path'];
        //读取水印文件
        if(!empty($waterImage) && file_exists($waterImage)) {
                $isWaterImage = TRUE;
                $water_info = getimagesize($waterImage);
                $water_w    = $water_info[0];//取得水印图片的宽
                $water_h    = $water_info[1];//取得水印图片的高

                switch($water_info[2])//取得水印图片的格式
                {
                        case 1:$water_im = imagecreatefromgif($waterImage);break;
                        case 2:$water_im = imagecreatefromjpeg($waterImage);break;
                        case 3:$water_im = imagecreatefrompng($waterImage);break;
                        default:die($formatMsg);
                }
        } else {
            die("don't exists water picture!"); //需要的水印的图片不存在！
        }
        //读取背景图片
        if(!empty($groundImage) && file_exists($groundImage)) {
                $ground_info = getimagesize($groundImage);
                $ground_w    = $ground_info[0];//取得背景图片的宽
                $ground_h    = $ground_info[1];//取得背景图片的高

                switch($ground_info[2])//取得背景图片的格式
                {
                        case 1:$ground_im = imagecreatefromgif($groundImage);break;
                        case 2:$ground_im = imagecreatefromjpeg($groundImage);break;
                        case 3:$ground_im = imagecreatefrompng($groundImage);break;
                        default:die($formatMsg);
                }
        }else{
                die("don't exists picture!"); //需要加水印的图片不存在！
        }


        $water_w1=floor($ground_w/4.7);//根据背景判断水印高度
        $water_h1=floor($water_h/$water_w*$water_w1);//根据背景判断水印宽度

        $w = $water_w1;
        $h = $water_h1;
        $label = "图片的";


        //比较图片,看大小是否可以加
        if( ($ground_w<$w) || ($ground_h<$h) ) {
                echo "picture is not big enough~~";
                return;
        }


        $posX = $ground_w - $w - floor($ground_w/55.5);
        $posY = $ground_h - $h - floor($ground_w/62.5);


        $gray = self::getAvgGray($ground_im,$posX,$posY,$water_w1,$water_h1);
        if($gray < 101) {
                $water_im = imagecreatefrompng($waterArr['light']['path']);
        }
        imagecopyresampled($ground_im, $water_im, $posX, $posY, 0, 0,$water_w1,$water_h1, $water_info[0],$water_info[1]);//拷贝水印到目标文件
        switch($ground_info[2])//取得背景图片的格式
        {
                case 1:imagegif($ground_im,$desPath);break;
                case 2:imagejpeg($ground_im,$desPath,95);break;
                case 3:imagepng($ground_im,$desPath);break;
                default:die($errorMsg);
        }
        //$cmd = self::$convert . " {$srcPath} {$waterImage} -gravity SouthEast -geometry +".floor($ground_w/55.5)."+".floor($ground_w/62.5)." -composite {$desPath}";
        //echo $cmd;
        //exec($cmd,$rtn);
        //释放内存
        if(isset($water_info)) unset($water_info);
        if(isset($water_im)) imagedestroy($water_im);
        unset($ground_info);
        imagedestroy($ground_im);  
        return true;
    }
    
    /**
     * 获取某一点rgb值
     */
    public static function getgray($im, $x, $y){
        //获取($x, $y)处的rgb值
        $rgb = imagecolorat($im, $x, $y);
        //计算灰度
        $red = ($rgb >> 16) & 0xFF;
        $green = ($rgb >> 8 )   & 0xFF;
        $blue = $rgb & 0xFF;
        $gray = round(.15*$red + .5*$green - .115*$blue);

        return $gray;
    }

    /**
     * 获取图像区域的平均灰度
     * 总的灰度值/总的像素数
     *
     * @param resource $im
     * @param int $x         起始坐标（水印的起始位置或结束位置）
     * @param int $y
     * @param int $width     水印宽
     * @param int $height
     * @param bool $end         水印是否为结束位置
     * @return int 0-255
     */  
    public static function getAvgGray($im, $x, $y, $width, $height, $end=false){
        $avggray = $gray = 0;
        if ($end) {
                //当传入的($x, $y)坐标为结束位置时 则结束位置为($x,$y)
                $x_width = $x;
                $y_height = $y;
                //开始位置 (结束位置 - 水印宽高)
                $x = $x - $width;
                $y = $y - $height;
        } else {
                $x_width = $x+$width;
                $y_height = $y+$height;
        }
        for ($i = $x; $i <= $x_width; $i++) {
                for ($j = $y; $j <= $y_height; $j++) {
                        $gray += self::getgray($im, $i, $j);
                }
        }
        $avggray = round($gray/($width*$height));
        return $avggray;
    }
    
    /**
     * fdfs图片加水印
     */
    public static function prowatermark($paramArr){
        $options = array(
            'srcPath'       => '',    #源文件路径
            'desPath'       => '',    #目标文件路径
            'waterGroup'    => 'PRO', #预定的水印图片组 该参数不要改动 
            'waterText'     => '',    #文本
            'textFont'      => 5,     #字号
            'textColor'     => '#FF0000',#颜色
            'isSpc'         => 0,
            'waterPos'      => 9,     #水印位置
        );
        if(is_array($paramArr)) $options = array_merge($options, $paramArr);
        extract($options);
        
		$isWaterImage = FALSE;	 #图片水印的状态
		$formatMsg = "no suport format~~"; #暂不支持该文件格式，请用图片处理软件将图片转换为GIF、JPG、PNG格式。
		$groundImage = $srcPath;
        $waterImageArr = self::$waterImg[$waterGroup];

        #读取背景图片
		if(!empty($groundImage) && file_exists($groundImage)) {
			$ground_info = getimagesize($groundImage);
			$ground_w    = $ground_info[0];#取得背景图片的宽
			$ground_h    = $ground_info[1];#取得背景图片的高
            #取得背景图片的格式
			switch($ground_info[2]){
				case 1:$ground_im = imagecreatefromgif($groundImage);break;
				case 2:$ground_im = imagecreatefromjpeg($groundImage);break;
				case 3:$ground_im = imagecreatefrompng($groundImage);break;
                default: return false;
			}
 		}else{
			return false; #需要加水印的图片不存在！
		}
		if($ground_w>=450 && $ground_w<=799){
			$waterImage = $waterImageArr['450'];
		}
		if($ground_w>=800 && $ground_w<=1023){
			$waterImage = $waterImageArr['800'];
		}
		if($ground_w>=1024 && $ground_w<=1279){
			$waterImage = $waterImageArr['1024'];
		}
		if($ground_w>=1280){
			$waterImage = $waterImageArr['1280'];
		}
        if(empty($waterImage)) return false;
        $waterImage = $waterImage['path'];
		#读取水印文件
		if(!empty($waterImage) && file_exists($waterImage)) {
			$isWaterImage = TRUE;
			$water_info = getimagesize($waterImage);
			$water_w    = $water_info[0];#取得水印图片的宽
			$water_h    = $water_info[1];#取得水印图片的高

			switch($water_info[2])#取得水印图片的格式
			{
				case 1:$water_im = imagecreatefromgif($waterImage);break;
				case 2:$water_im = imagecreatefromjpeg($waterImage);break;
				case 3:$water_im = imagecreatefrompng($waterImage);break;
                default: return false;
			}
		}
		#水印位置
		if($isWaterImage){
            #图片水印
			if($isSpc){
				$water_h1=floor($ground_h/(1024/$water_h));   #根据背景判断水印高度
				$water_w1=floor($water_w/$water_h*$water_h1); #根据背景判断水印宽度
			}else{
				$water_h1=$water_h; #根据背景判断水印高度
				$water_w1=$water_w; #根据背景判断水印宽度
			}
			$w = $water_w1;
			$h = $water_h1;
			$label = "图片的";
		}else{
			$temp = imagettfbbox(ceil($textFont*2.5),0,"./cour.ttf",$waterText); #取得使用 TrueType 字体的文本的范围
			$w = $temp[2] - $temp[6];
			$h = $temp[3] - $temp[7];
			unset($temp);
			$label = "文字区域";
		}

		#比较图片,看大小是否可以加
		if( ($ground_w<$w) || ($ground_h<$h) ) {
			return false;
		}

		switch($waterPos)
		{
			case 0:#随机
                $posX = rand(0,($ground_w - $w));
                $posY = rand(0,($ground_h - $h));
                break;
			case 1:#1为顶端居左
                $posX = 0;
                $posY = 0;
                break;
			case 2:#2为顶端居中
                $posX = ($ground_w - $w) / 2;
                $posY = 0;
                break;
			case 3:#3为顶端居右
                $posX = $ground_w - $w;
                $posY = 0;
                break;
			case 4:#4为中部居左
                $posX = 0;
                $posY = ($ground_h - $h) / 2;
                break;
			case 5:#5为中部居中
                $posX = ($ground_w - $w) / 2;
                $posY = ($ground_h - $h) / 2;
                break;
			case 6:#6为中部居右
                $posX = $ground_w - $w;
                $posY = ($ground_h - $h) / 2;
                break;
			case 7:#7为底端居左
                $posX = 0;
                $posY = $ground_h - $h;
                break;
			case 8:#8为底端居中
                $posX = ($ground_w - $w) / 2;
                $posY = $ground_h - $h;
                break;
			case 9:#9为底端居右
                if($ground_w==800){
                    $posw = 60;
                }else if($ground_w>800){
                    $posw = 100;
                }else{
                    $posw = 20;
                }
                $posX = $ground_w - $w-$posw;
                $posY = $ground_h - $h-$posw;
                break;
			default:#随机
                $posX = rand(0,($ground_w - $w));
                $posY = rand(0,($ground_h - $h));
                break;
		}
        #图片水印
		if($isWaterImage){
			imagecopyresampled($ground_im, $water_im, $posX, $posY, 0, 0,$water_w1,$water_h1, $water_info[0],$water_info[1]);//拷贝水印到目标文件
		}else{
            #文字水印
			if( !empty($textColor) && (strlen($textColor)==7)){
				$R = hexdec(substr($textColor,1,2));
				$G = hexdec(substr($textColor,3,2));
				$B = hexdec(substr($textColor,5));
			}else{
				return false; #水印文字颜色格式不正确！
			}
			imagestring( $ground_im, $textFont, $posX, $posY, $waterText, imagecolorallocate($ground_im, $R, $G, $B));
		}

		#生成水印后的图片
		@unlink($groundImage);
        #取得背景图片的格式
		switch($ground_info[2]){
			case 1:imagegif($ground_im,$groundImage);break;
			case 2:imagejpeg($ground_im,$groundImage,95);break;
			case 3:imagepng($ground_im,$groundImage);break;
			default:return false;
		}
		#释放内存
		if(isset($water_info)) unset($water_info);
		if(isset($water_im)) imagedestroy($water_im);
		unset($ground_info);
		imagedestroy($ground_im);
        return true;
    }
}
