<?php
function getGifCode($paramArr) {
    $options = array(
        'width' => 80,
        'height' => 20,
        'numCnt' => 4,
        'text' => 'ABCD',
    );
    if (is_array($paramArr)) {
        $options = array_merge($options, $paramArr);
    }
    extract($options);
    $font = LJL_API_ROOT . '/Config/Fonts/Ga.ttf';
     //$len = strlen($phrase);
   
        
    //$font_size=18;
    $len=$numCnt;
    $font_size = ($width/($len+1))-5;
 
    $size = $width / $len;
    $box = imagettfbbox($size, 0, $font, $text);
    $textWidth = $box[2] - $box[0];
    $textHeight = $box[1] - $box[7];
    $x = ($width - $textWidth) / 2  ;
    $y = ($height - $textHeight) / 2 + $size;
    
    
    $str = '';
    for ($i = 0; $i < $len; $i++) {
        $str .= substr($text,$i,1);
    }
    

    for ($num = 0; $num < 10; $num++) {
        ob_start();
        $image = imagecreatetruecolor($width, $height); //创建图片
        $bg_color = imagecolorallocate($image, 255, 255, 255); //设置背景颜色
        $border_color = imagecolorallocate($image, 100, 100, 100); //设置边框颜色
        $text_color = imagecolorallocate($image, 0, 0, 0); //设置验证码颜色
        imagefilledrectangle($image, 0, 0, $width - 1, $height - 1, $bg_color); //填充图片背景色
        imagerectangle($image, 0, 0, $width - 1, $height - 1, $border_color); //填充图片边框颜色
        for ($i = 0; $i < 5; $i++) {
            $line_color = imagecolorallocate($image, rand(0, 255), rand(0, 255), rand(0, 255)); //干扰线颜色
            imageline($image, rand(0, $width), 0, $width, $height, $line_color); //画一条线段
        }
        
        // 设定文字颜色数组

    $colorList[] = ImageColorAllocate($image, 15, 73, 210);

    $colorList[] = ImageColorAllocate($image, 0, 64, 0);

    $colorList[] = ImageColorAllocate($image, 0, 0, 64);

    $colorList[] = ImageColorAllocate($image, 0, 128, 128);

    $colorList[] = ImageColorAllocate($image, 27, 52, 47);

    $colorList[] = ImageColorAllocate($image, 51, 0, 102);

    $colorList[] = ImageColorAllocate($image, 0, 0, 145);

    $colorList[] = ImageColorAllocate($image, 0, 0, 113);

    $colorList[] = ImageColorAllocate($image, 0, 51, 51);

    $colorList[] = ImageColorAllocate($image, 158, 180, 35);

    $colorList[] = ImageColorAllocate($image, 59, 59, 59);

    $colorList[] = ImageColorAllocate($image, 0, 0, 0);

    $colorList[] = ImageColorAllocate($image, 1, 128, 180);

    $colorList[] = ImageColorAllocate($image, 0, 153, 51);

    $colorList[] = ImageColorAllocate($image, 60, 131, 1);

    $colorList[] = ImageColorAllocate($image, 0, 0, 0);
        // 添加干扰线

        for ($i = 0; $i < 500; $i++) {
            $dot_color = imagecolorallocate($image, rand(0, 255), rand(0, 255), rand(0, 255)); //干扰点颜色
            imagesetpixel($image, rand() % $width, rand() % $height, $dot_color); //画一个像素点
        }

        for ($k = 0; $k < 3; $k++) {

            $colorRandom = mt_rand(0, sizeof($colorList) - 1);

// $todrawline = rand(0,1);

            $todrawline = 1;

            if ($todrawline) {

                imageline($image, mt_rand(0, $width), mt_rand(0, $height), mt_rand(0, $width), mt_rand(0, $height), $colorList[$colorRandom]);
            } else {

                $w = mt_rand(0, $width);

                $h = mt_rand(0, $width);

                $num1 = rand(90, 180);
                $num2 = rand(180, 270);
                imagearc($image, $width - floor($w / 2), floor($h / 2), $w, $h, $num1, $num2, $colorList[$colorRandom]);
            }
        }
        
        for ($i = 0; $i < $len; $i++) {
           // imagettftext($image, $font_size, rand(-3, 3), $font_size / 2 + ($font_size + 5) * $i, $height / 1.25 - rand(2, 3), $text_color, $font, $str[$i]); //用规定字体向图像写入文本
            imagettftext($image,$font_size,rand(-5,5),$x+$font_size/2+($font_size+3)*$i,$y,$text_color,$font, $str[$i]);//用规定字体向图像写入文本
        }
        imagegif($image);
        imagedestroy($image);
        $imagedata[] = ob_get_contents();
        ob_clean();
    }
    require_once('GIFEncoder.class.php');
    $gif = new GIFEncoder($imagedata);
    ob_clean(); //防止出现'图像因其本身有错无法显示'的问题
    header('Content-type:image/gif');
    echo $gif->GetAnimation();
}

