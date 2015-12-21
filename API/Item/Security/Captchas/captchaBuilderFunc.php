<?php

function getCaptchaBuilder($paramArr){
   
     $options = array(
                    'width' => 80,
                    'height' => 20,
                    'numCnt' => 4,
                    'text' => 'ABCD',
                    'plex' =>5,
                    
                );
    
    if (is_array($paramArr)) {
       $options = array_merge($options, $paramArr);
    }
    extract($options);
    $textColorArr = array(
        0=>array(0,0,0),
        1=>array(8,46,84),
        2=>array(61,145,64),
        3=>array(0,0,255),
        4=>array(11,23,70),
        5=>array(135,38,87),
        );
    $randNum = rand(1,4);  
    $textColor =  $textColorArr[$randNum];
    $randStart = 250 - 4* $plex;
    $angle = 3* $plex;
    $lines = 3*$plex;
    $fontNum = rand(1,5);
    if(!$fontNum){
        $fontNum = 1;
    }
    $font = LJL_API_ROOT . '/Config/Fonts/captcha'.$fontNum.'.ttf';
    $backgroudColorArr = array(
         rand( $randStart,255), rand( $randStart,255), rand( $randStart,255),
    );
    $distort = false;
    if(6<$plex){
        $distort = true;
    }
        header('Content-type: image/jpeg');
        CaptchaBuilder::create($text)
            ->setTextColor($textColor[0],$textColor[1],$textColor[2])              #文字颜色
            ->setBackgroundColor($backgroudColorArr[0],$backgroudColorArr[1],$backgroudColorArr[2])    #背景色
            ->setMaxBehindLines($lines)              #背景线条数
            ->setMaxFrontLines($lines)               #字体背景线条
            ->setMaxAngle($angle)                   #文字旋转角度
            ->setDistortion($distort)               #是否歪曲文字
            ->setMaxOffset($plex)                   #文字间高度差
            ->build($width,$height,$font )          # 0-5,10,9,12
            ->output()
            ;
        exit();
    }
