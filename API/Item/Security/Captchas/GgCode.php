<?php

function getAuthImage($paramArr) {
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
    $len = $numCnt;
    $font_size = ($width / ($len + 1)) - 5;

    $size = $width / $len;
    $box = imagettfbbox($size, 0, $font, $text);
    $textWidth = $box[2] - $box[0];
    $textHeight = $box[1] - $box[7];
    $x = ($width - $textWidth) / 2;
    $y = ($height - $textHeight) / 2 + $size;

    $im_x = $width;
    $im_y = $height;
    $im = imagecreatetruecolor($im_x, $im_y);
    $text_c = ImageColorAllocate($im, mt_rand(0, 100), mt_rand(0, 100), mt_rand(0, 100));
    $tmpC0 = mt_rand(100, 255);
    $tmpC1 = mt_rand(100, 255);
    $tmpC2 = mt_rand(100, 255);
    $buttum_c = ImageColorAllocate($im, $tmpC0, $tmpC1, $tmpC2);
    imagefill($im, 16, 13, $buttum_c);



    for ($i = 0; $i < strlen($text); $i++) {
        $tmp = substr($text, $i, 1);
        $array = array(-1, 1);
        $p = array_rand($array);
        $an = $array[$p] * mt_rand(1, 10); //角度
        $size = 28;
        //imagettftext($im, $size, $an, 15+$i*$size, 35, $text_c, $font, $tmp);
        imagettftext($im, $font_size, rand(-5, 5), $x + $font_size / 2 + ($font_size + 3) * $i, $y, $text_c, $font, $tmp); //用规定字体向图像写入文本
    }


    $distortion_im = imagecreatetruecolor($im_x, $im_y);

    imagefill($distortion_im, 16, 13, $buttum_c);
    for ($i = 0; $i < $im_x; $i++) {
        for ($j = 0; $j < $im_y; $j++) {
            $rgb = imagecolorat($im, $i, $j);
            if ((int) ($i + 20 + sin($j / $im_y * 2 * M_PI) * 10) <= imagesx($distortion_im) && (int) ($i + 20 + sin($j / $im_y * 2 * M_PI) * 10) >= 0) {
                imagesetpixel($distortion_im, (int) ($i + 10 + sin($j / $im_y * 2 * M_PI - M_PI * 0.1) * 4), $j, $rgb);
            }
        }
    }
    //加入干扰象素;
    $count = 160; //干扰像素的数量
    for ($i = 0; $i < $count; $i++) {
        $randcolor = ImageColorallocate($distortion_im, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
        imagesetpixel($distortion_im, mt_rand() % $im_x, mt_rand() % $im_y, $randcolor);
    }

    $rand = mt_rand(5, 30);
    $rand1 = mt_rand(15, 25);
    $rand2 = mt_rand(5, 10);
    for ($yy = $rand; $yy <= +$rand + 2; $yy++) {
        for ($px = -80; $px <= 80; $px = $px + 0.1) {
            $x = $px / $rand1;
            if ($x != 0) {
                $y = sin($x);
            }
            $py = $y * $rand2;

            imagesetpixel($distortion_im, $px + 80, $py + $yy, $text_c);
        }
    }

    //设置文件头;
    Header("Content-type: image/JPEG");

    //以PNG格式将图像输出到浏览器或文件;
    ImagePNG($distortion_im);

    //销毁一图像,释放与image关联的内存;
    ImageDestroy($distortion_im);
    ImageDestroy($im);
}


