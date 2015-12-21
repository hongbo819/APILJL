<?php

/**
 * 验证码相关
 */
class API_Item_Security_Captcha {

    private static $server = "Default"; #计数服务器的名称
    private static $keyPre = "api_captcha:"; #存到redis中的key的前缀

    /**
     * 获得显示信息，在现实验证码的时候，首先调用
     */
    public static function getInfo() {
        $uniqCode = API_Item_Security_Algos::getUniqueCode();
        return array(
            'token' => $uniqCode,
            'image' => "?token=" . $uniqCode,
        );
    }

    /**
     * 进行验证码验证
     */
    public static function doCheck($paramArr) {
        $options = array(
            'token' => 0,
            'text' => '',
            'clear'=>false,   #是否清除验证码
            'debug'=>false,   #debug 打印 redis中保存的验证码
        );
        if (is_array($paramArr))
            $options = array_merge($options, $paramArr);
        extract($options);

        if (!$token) {
            $token = LJL_Api::$_globalVars['_COOKIE']["ip_ck"];
        }
        if (!$token || !$text)   
            return false;

        #从redis中获得数据
        $redis = API_Redis::getLink(self::$server); #获得redis对象
        $key = self::$keyPre . $token;
        
        $checkNumCheck = self::checkNumCheck(array('preKey'=>$key));
        #验证验证次数，防止暴力破解的轮询
        if(false == $checkNumCheck){
            return false;
        }
        $saveText = $redis->get($key); //最长1小时的缓存周期
        
        $text = strtolower($text);
        $saveText = strtolower($saveText);
        #admin 那台机子验证失败，debug 看看是什么原因
        if($debug){
            var_dump($saveText);
        }
        if($clear){
            $redis->delete($key);   
        }
        if($saveText == $text){
            return true;
        }else{
            return false;
        }
    }
    /**
     * @todo  验证次数验证，每小时最多验证60次，防止暴力破解
     * @param type $redis
     * @param type $preKey
     * @return boolean
     */
    public static function checkNumCheck($paramArr){
        $options = array(
            'preKey' => 0,
        );
        if (is_array($paramArr))
            $options = array_merge($options, $paramArr);
        extract($options);
        $redis = API_Redis::getLink(self::$server); #获得redis对象
        #获取当前 小时 时间值，以小时为单位记录，失效时间为两小时
        $timeKey = date("YmdH");
        $key = $preKey.':'.$timeKey;   
        
        $checkNum = $redis->get($key);
        if(!$checkNum){
            $res= $redis->setex($key,7200,1);
            return true;
        }else{
            if($checkNum>60 ){
                return false;
            }else{
                #不足60次，累加
                $redis->incr($key); 
                return true;
            }
        }
    }

    /**
     * 显示验证码
     */
    public static function showImage($paramArr) {
        $options = array(
            'token' => 0,
            'numCnt' => 4,
            'width' => 100,
            'height' => 40,
            'type' => 1, # 1.黑白 2.彩色
            'plex' =>5,
        );
        if (is_array($paramArr)) {
            $options = array_merge($options, $paramArr);
        }
        extract($options);
        if (!$token && LJL_Api::$_globalVars['_COOKIE'] && isset(LJL_Api::$_globalVars['_COOKIE']["ip_ck"])) {
            $token = LJL_Api::$_globalVars['_COOKIE']["ip_ck"];
        }
        if (!$token){
            #不返回了，直接生成 ip_ck cookie 值
            $token = API_Item_Base_Page::createIpCk(array('setCookie'=>1));
//            return false;
        }
        
        #获得唯一的用户码
        $uniqCode = API_Item_Security_Algos::getUniqueCode();
        $text = self::getAuthCode($uniqCode, $numCnt);

        #将数据数据到Redis 
        $redis = API_Redis::getLink(self::$server); #获得redis对象
        $key = self::$keyPre . $token;
        $redis->setex($key, 3600, $text); //最长1小时的缓存周期
        #输出图片
        $param = array(
            'token'=>$token,
            'width' => $width,
            'height' => $height,
            'numCnt' => $numCnt,
            'text' => $text,
        );

        switch ($type) {
            case 2:
                self::getCodeColorImage($param); #黑白
                break;
            case 3:
                require_once LJL_API_ROOT . "/Item/Security/Captchas/GifCode.php";
                getGifCode($param);
                break;
            case 4:
                require_once LJL_API_ROOT . "/Item/Security/Captchas/GgCode.php";
                getAuthImage($param);
                break;
             case 5:
                require_once LJL_API_ROOT . "/Item/Security/Captchas/code_math.php";
                $param['redis'] =$redis;
                $param['key'] =$key;
                getMathCode($param);
                break;
            case 6:
                require_once LJL_API_ROOT . "/Item/Security/Captchas/CaptchaBuilderInterface.php";
                require_once LJL_API_ROOT . "/Item/Security/Captchas/PhraseBuilderInterface.php";
                require_once LJL_API_ROOT . "/Item/Security/Captchas/CaptchaBuilder.php";
                require_once LJL_API_ROOT . "/Item/Security/Captchas/PhraseBuilder.php";
                require_once LJL_API_ROOT . "/Item/Security/Captchas/captchaBuilderFunc.php";
                $param['plex'] = $plex;
                getCaptchaBuilder($param);  
                break;
            case 1:
            default:
                self::getCodeSimpleImage($param); #彩色
                break;
        }
        exit;
    }

            

    /**
     * 获得简单的验证码图片
     */
    public static function getCodeSimpleImage($paramArr) {
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


        $font = LJL_API_ROOT . '/Config/Fonts/' . rand(1, 6) . '.ttf';
        $size = 14;

        $image = imagecreate($width, $height);

        $whiteColor = imagecolorallocate($image, 255, 255, 255);
        $blackColor = imagecolorallocate($image, 0, 0, 0);

        $count = $width * $height / 8;

        for ($i = 0; $i < $count; $i++) {
            $randomColor = imagecolorallocate($image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
            imagesetpixel($image, mt_rand(0, $width), mt_rand(0, $height), $randomColor);
        }

        $fontSize = imagettfbbox($size, 0, $font, $text);

        $centerX = abs($fontSize[2] - $fontSize[0]);
        $centerY = abs($fontSize[5] - $fontSize[3]);

        $x = ($width - $centerX) / 2;
        $y = ($height - $centerY) / 2 + $centerY;

        imagettftext($image, $size, mt_rand(-2, +2), $x, $y - 2, $blackColor, $font, $text);
        imagerectangle($image, 0, 0, $width - 1, $height - 1, $blackColor);
        header("Content-type:image/png");
        imagepng($image);
        imagedestroy($image);
        exit;
    }

    /**
     * 获得彩色的验证码图片
     */
    public static function getCodeColorImage($paramArr) {

        $options = array(
            'width' => 80,
            'height' => 30,
            'numCnt' => 4,
            'text' => 'ABCD',
        );
        if (is_array($paramArr)) {
            $options = array_merge($options, $paramArr);
        }
        extract($options);


        $img = imagecreate($width, $height);
        $bgcolor = self::getRandColor($img, 200); //背景色
        $authCode = $text;

        for ($i = 0; $i < $numCnt; $i++) {
            $padding_left = rand(5, 10);
            $left = $padding_left + ($width - 10) * $i / $numCnt;
            //加入多边形色块干扰
            imagefilledpolygon($img, array(
                rand($left, $left + 20), rand(0, $height),
                rand($left, $left + 20), rand(0, $height),
                rand($left, $left + 20), rand(0, $height),
                rand($left, $left + 20), rand(0, $height),
                rand($left, $left + 20), rand(0, $height),
                rand($left, $left + 20), rand(0, $height),
                rand($left, $left + 20), rand(0, $height),
                rand($left, $left + 20), rand(0, $height),
                rand($left, $left + 20), rand(0, $height),
                rand($left, $left + 20), rand(0, $height),
                    ), 10, self::getRandColor($img, 180));
            $fontFile = LJL_API_ROOT . '/Config/Fonts/' . rand(1, 6) . '.ttf';
            imagettftext($img, rand(18, 24), rand(-30, 30), $left, rand(22, 26), self::getRandColor($img, 0, 120), $fontFile, $authCode[$i]);
        }
        //干扰像素，随机位置，随机颜色
        for ($i = 0; $i < 300; $i++) {
            $rand_x = rand(0, $width - 1);
            $rand_y = rand(0, $height - 1);
            imagesetpixel($img, $rand_x, $rand_y, self::getRandColor($img));
        }
        header("Content-type:image/png");
        imagepng($img);
        imagedestroy($img);
        exit;
    }

    /**
     * 获得随机的颜色
     */
    private static function getRandColor($img, $min=0, $max=255) {
        return imagecolorallocate($img, rand($min, $max), rand($min, $max), rand($min, $max));
    }

    /**
     * 根据传入的字符获得验证码
     */
    private static function getAuthCode($codeStr, $len) {
        //算法需要调整成复杂的
        $codeStr = strtoupper(substr($codeStr, 0, $len));
        $codeStr = str_replace(array("0", "O"), array("A", "B"), $codeStr);
        return $codeStr;
    }

}