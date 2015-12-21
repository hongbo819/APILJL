<?php

function getMathCode($paramArr) {
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
    $w = $width;
    $h = $height;
    $im = imagecreate($w, $h);

    //imagecolorallocate($im, 14, 114, 180); // background color
    $red = imagecolorallocate($im, 255, 0, 0);
    $white = imagecolorallocate($im, 255, 255, 255);

    $num1 = rand(1, 20);
    $num2 = rand(1, 20);

    $text = $num1 + $num2;//拿到答案
    $redis->setex($key, 3600, $text); //最长1小时的缓存周期
    
    $gray = imagecolorallocate($im, 118, 151, 199);
    $black = imagecolorallocate($im, mt_rand(0, 100), mt_rand(0, 100), mt_rand(0, 100));

    //画背景
    imagefilledrectangle($im, 0, 0, $w, $h, $black);
    //在画布上随机生成大量点，起干扰作用;
    for ($i = 0; $i < 80; $i++) {
        imagesetpixel($im, rand(0, $w), rand(0, $h), $gray);
    }
    
    $wfloat = round($w/5) ;
    $hfloat = round($h/3.5);
    
    imagestring($im, 5, $wfloat+5, $hfloat+4, $num1, $red);
    imagestring($im, 5, $wfloat+30, $hfloat+3, "+", $red);
    imagestring($im, 5, $wfloat+45, $hfloat+4, $num2, $red);
    imagestring($im, 5, $wfloat+70, $hfloat+3, "=", $red);
    imagestring($im, 5, $wfloat+80, $hfloat+2, "?", $white);

    header("Content-type: image/png");
    imagepng($im);
    imagedestroy($im);
}


