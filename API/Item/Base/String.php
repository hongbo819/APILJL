<?php
/**
 * 字符串操作基础类库
 */

class API_Item_Base_String
{

    /**
     * 获得汉字的首字母
     * @author cuihb
     * @param 要得到那个字符串的首字母
     * @return 获得的首字母
     */
    public static function getFirstLetter($paramArr) {
		$options = array(
            'input'  => '', #字符串
			'code'   => 'utf-8' #编码gbk/utf-8
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);

        $dict = array(
            'A' => 0XB0C4, 'B' => 0XB2C0, 'C' => 0XB4ED, 'D' => 0XB6E9, 'E' => 0XB7A1,
            'F' => 0XB8C0, 'G' => 0XB9FD, 'H' => 0XBBF6, 'J' => 0XBFA5, 'K' => 0XC0AB,
            'L' => 0XC2E7, 'M' => 0XC4C2, 'N' => 0XC5B5, 'O' => 0XC5BD, 'P' => 0XC6D9,
            'Q' => 0XC8BA, 'R' => 0XC8F5, 'S' => 0XCBF9, 'T' => 0XCDD9, 'W' => 0XCEF3,
            'X' => 0XD1B8, 'Y' => 0XD4D0, 'Z' => 0XD7F9,
        );
        if($code == 'utf-8') $input=iconv("UTF-8","gbk", $input);
        $str_1 = substr($input, 0, 1);
        if ($str_1 >= chr(0x81) && $str_1 <= chr(0xfe)) {
            $num = hexdec(bin2hex(substr($input, 0, 2)));
            foreach ($dict as $k => $v) {
                if($v>=$num) break;
            }
            if ($num == 0XDFC9) { $k = 'G'; }
            elseif ($num == 0XECAB) { $k = 'J'; }
            elseif ($num == 0XE0BD) { $k = 'D'; }
            elseif ($num == 0XDFC8) { $k = 'X'; }
            elseif ($num == 0XF7C8) { $k = 'M'; }
            elseif ($num == 0Xf7e8) { $k = 'Q'; }
            elseif ($num == 0Xf0a8) { $k = 'J'; }
            elseif ($num > 0XD7FF) {    //非常用字（3008个）按部首排列，无法拼音
                return ' ';
            }
            return $k;
        }else{
            return strtoupper($str_1);
        }
    }


    /**
     * 将汉字转成拼音
     */
    public static function getPinyin($paramArr) {
		$options = array(
            'input' => '', #字符串
            'code'  => 'utf-8' 
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);
        $_String = $code == 'utf-8' ? iconv('utf-8', 'gbk', $input) : $input;//仅支持gbk格式
        $_DataKey = "a|ai|an|ang|ao|ba|bai|ban|bang|bao|bei|ben|beng|bi|bian|biao|bie|bin|bing|bo|bu|ca|cai|can|cang|cao|ce|ceng|cha".
                    "|chai|chan|chang|chao|che|chen|cheng|chi|chong|chou|chu|chuai|chuan|chuang|chui|chun|chuo|ci|cong|cou|cu|".
                    "cuan|cui|cun|cuo|da|dai|dan|dang|dao|de|deng|di|dian|diao|die|ding|diu|dong|dou|du|duan|dui|dun|duo|e|en|er".
                    "|fa|fan|fang|fei|fen|feng|fo|fou|fu|ga|gai|gan|gang|gao|ge|gei|gen|geng|gong|gou|gu|gua|guai|guan|guang|gui".
                    "|gun|guo|ha|hai|han|hang|hao|he|hei|hen|heng|hong|hou|hu|hua|huai|huan|huang|hui|hun|huo|ji|jia|jian|jiang".
                    "|jiao|jie|jin|jing|jiong|jiu|ju|juan|jue|jun|ka|kai|kan|kang|kao|ke|ken|keng|kong|kou|ku|kua|kuai|kuan|kuang".
                    "|kui|kun|kuo|la|lai|lan|lang|lao|le|lei|leng|li|lia|lian|liang|liao|lie|lin|ling|liu|long|lou|lu|lv|luan|lue".
                    "|lun|luo|ma|mai|man|mang|mao|me|mei|men|meng|mi|mian|miao|mie|min|ming|miu|mo|mou|mu|na|nai|nan|nang|nao|ne".
                    "|nei|nen|neng|ni|nian|niang|niao|nie|nin|ning|niu|nong|nu|nv|nuan|nue|nuo|o|ou|pa|pai|pan|pang|pao|pei|pen".
                    "|peng|pi|pian|piao|pie|pin|ping|po|pu|qi|qia|qian|qiang|qiao|qie|qin|qing|qiong|qiu|qu|quan|que|qun|ran|rang".
                    "|rao|re|ren|reng|ri|rong|rou|ru|ruan|rui|run|ruo|sa|sai|san|sang|sao|se|sen|seng|sha|shai|shan|shang|shao|".
                    "she|shen|sheng|shi|shou|shu|shua|shuai|shuan|shuang|shui|shun|shuo|si|song|sou|su|suan|sui|sun|suo|ta|tai|".
                    "tan|tang|tao|te|teng|ti|tian|tiao|tie|ting|tong|tou|tu|tuan|tui|tun|tuo|wa|wai|wan|wang|wei|wen|weng|wo|wu".
                    "|xi|xia|xian|xiang|xiao|xie|xin|xing|xiong|xiu|xu|xuan|xue|xun|ya|yan|yang|yao|ye|yi|yin|ying|yo|yong|you".
                    "|yu|yuan|yue|yun|za|zai|zan|zang|zao|ze|zei|zen|zeng|zha|zhai|zhan|zhang|zhao|zhe|zhen|zheng|zhi|zhong|".
                    "zhou|zhu|zhua|zhuai|zhuan|zhuang|zhui|zhun|zhuo|zi|zong|zou|zu|zuan|zui|zun|zuo|zhen";

        $_DataValue = "-20319|-20317|-20304|-20295|-20292|-20283|-20265|-20257|-20242|-20230|-20051|-20036|-20032|-20026|-20002|-19990".
                      "|-19986|-19982|-19976|-19805|-19784|-19775|-19774|-19763|-19756|-19751|-19746|-19741|-19739|-19728|-19725".
                      "|-19715|-19540|-19531|-19525|-19515|-19500|-19484|-19479|-19467|-19289|-19288|-19281|-19275|-19270|-19263".
                      "|-19261|-19249|-19243|-19242|-19238|-19235|-19227|-19224|-19218|-19212|-19038|-19023|-19018|-19006|-19003".
                      "|-18996|-18977|-18961|-18952|-18783|-18774|-18773|-18763|-18756|-18741|-18735|-18731|-18722|-18710|-18697".
                      "|-18696|-18526|-18518|-18501|-18490|-18478|-18463|-18448|-18447|-18446|-18239|-18237|-18231|-18220|-18211".
                      "|-18201|-18184|-18183|-18181|-18012|-17997|-17988|-17970|-17964|-17961|-17950|-17947|-17931|-17928|-17922".
                      "|-17759|-17752|-17733|-17730|-17721|-17703|-17701|-17697|-17692|-17683|-17676|-17496|-17487|-17482|-17468".
                      "|-17454|-17433|-17427|-17417|-17202|-17185|-16983|-16970|-16942|-16915|-16733|-16708|-16706|-16689|-16664".
                      "|-16657|-16647|-16474|-16470|-16465|-16459|-16452|-16448|-16433|-16429|-16427|-16423|-16419|-16412|-16407".
                      "|-16403|-16401|-16393|-16220|-16216|-16212|-16205|-16202|-16187|-16180|-16171|-16169|-16158|-16155|-15959".
                      "|-15958|-15944|-15933|-15920|-15915|-15903|-15889|-15878|-15707|-15701|-15681|-15667|-15661|-15659|-15652".
                      "|-15640|-15631|-15625|-15454|-15448|-15436|-15435|-15419|-15416|-15408|-15394|-15385|-15377|-15375|-15369".
                      "|-15363|-15362|-15183|-15180|-15165|-15158|-15153|-15150|-15149|-15144|-15143|-15141|-15140|-15139|-15128".
                      "|-15121|-15119|-15117|-15110|-15109|-14941|-14937|-14933|-14930|-14929|-14928|-14926|-14922|-14921|-14914".
                      "|-14908|-14902|-14894|-14889|-14882|-14873|-14871|-14857|-14678|-14674|-14670|-14668|-14663|-14654|-14645".
                      "|-14630|-14594|-14429|-14407|-14399|-14384|-14379|-14368|-14355|-14353|-14345|-14170|-14159|-14151|-14149".
                      "|-14145|-14140|-14137|-14135|-14125|-14123|-14122|-14112|-14109|-14099|-14097|-14094|-14092|-14090|-14087".
                      "|-14083|-13917|-13914|-13910|-13907|-13906|-13905|-13896|-13894|-13878|-13870|-13859|-13847|-13831|-13658".
                      "|-13611|-13601|-13406|-13404|-13400|-13398|-13395|-13391|-13387|-13383|-13367|-13359|-13356|-13343|-13340".
                      "|-13329|-13326|-13318|-13147|-13138|-13120|-13107|-13096|-13095|-13091|-13076|-13068|-13063|-13060|-12888".
                      "|-12875|-12871|-12860|-12858|-12852|-12849|-12838|-12831|-12829|-12812|-12802|-12607|-12597|-12594|-12585".
                      "|-12556|-12359|-12346|-12320|-12300|-12120|-12099|-12089|-12074|-12067|-12058|-12039|-11867|-11861|-11847".
                      "|-11831|-11798|-11781|-11604|-11589|-11536|-11358|-11340|-11339|-11324|-11303|-11097|-11077|-11067|-11055".
                      "|-11052|-11045|-11041|-11038|-11024|-11020|-11019|-11018|-11014|-10838|-10832|-10815|-10800|-10790|-10780".
                      "|-10764|-10587|-10544|-10533|-10519|-10331|-10329|-10328|-10322|-10315|-10309|-10307|-10296|-10281|-10274".
                      "|-10270|-10262|-10260|-10256|-10254|-9254";
        $_TDataKey   = explode('|', $_DataKey);
        $_TDataValue = explode('|', $_DataValue);

        $_Data = array_combine($_TDataKey, $_TDataValue);
        arsort($_Data);
        reset($_Data);

        $_Res = '';
        for($i=0; $i<strlen($_String); $i++) {
            $_P = ord(substr($_String, $i, 1));
            if($_P>160) {
                $_Q = ord(substr($_String, ++$i, 1)); $_P = $_P*256 + $_Q - 65536;
            }

            if ($_P>0 && $_P<160 ) $_Res .= chr($_P);
            elseif(-9254 == $_P) $_Res .= 'zhen';
            elseif(-13886 == $_P) $_Res .= 'shan3';
            elseif($_P<-20319 || $_P>-10247) $_Res .= '';
            else {
                foreach($_Data as $k=>$v){ if($v<=$_P) break; }
                $_Res .= $k;
            }

        }
        return preg_replace("/[^a-z0-9]*/", '', $_Res);
    }
    /**
     * 过滤Xss代码
     */
    public static function filterXss($paramArr) {
		$options = array(
            'input'  => '', #字符串
		);
		$options = array_merge($options, $paramArr);
		extract($options);
        

        $normalPat = array(
            '&'  => '&amp;',
            '\'' => '&lsquo;',
            '"'  => '&quot;',
            '<'  => '&lt;',
            '>'  => '&gt;'
        );


		$input = html_entity_decode($input, ENT_NOQUOTES, 'UTF-8');

        $input = str_replace(array('&amp;', '&lt;', '&gt;'), array('&amp;amp;', '&amp;lt;', '&amp;gt;'), $input);
		$input = str_replace(array('&', '%', 'script', 'http', 'localhost'), array('', '', '', '', ''), $input);
		foreach($normalPat as $pat => $rep){
			$input = str_replace($pat,$rep,$input);
		}
        
        $input = preg_replace('!(&#|\\\)[xX]([0-9a-fA-F]+);?!','chr(hexdec("$2"))', $input);
        // Clean up entities
		$input = preg_replace('!(&#0+[0-9]+)!','$1;',$input);
        
        $patterns = array(
            // Match any attribute starting with "on" or xmlns
            '#(<[^>]+[\x00-\x20\"\'\/])(on|xmlns)[^>]*>?#iUu',
            // Match javascript:, livescript:, vbscript: and mocha: protocols
            '!((java|live|vb)script|mocha|feed|data):(\w)*!iUu',
            '#-moz-binding[\x00-\x20]*:#u',
            // Match style attributes
            '#(<[^>]+[\x00-\x20\"\'\/])style=[^>]*>?#iUu',
            // Match unneeded tags
            '#</*(applet|meta|xml|blink|link|style|script|embed|object|iframe|frame|frameset|ilayer|layer|bgsound|title|base)[^>]*>?#i'
        );

        foreach($patterns as $pattern) {
            if(preg_match($pattern, $input)){
                $input = "";
                break;
            }
        }
        return $input;
    }
    /**
     * 中文替换 by cui
     */
    public static function replaceCnStr($paramArr){
    	$options = array(
            'needle'  => '', #被替换的字符串
	    	'str'  => '', #替换为的字符串
	    	'haystack'  => '', #字符串
	    	'code'  => 'utf-8', #编码 utf-8/gb2312/gbk/big5
		);
		$options = array_merge($options, $paramArr);
		extract($options);
    	$re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
		$re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
		$re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
		$re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
		
		preg_match_all($re[$code], $haystack, $match_haystack);
		preg_match_all($re[$code], $needle, $match_needle);
		
		for($i = 0; $i < count($match_needle); $i ++){
			if(!in_array($match_needle[0][$i], $match_haystack[0]))return $haystack;//无匹配
		}
		
		$match_haystack = $match_haystack[0];
		$match_needle = $match_needle[0];
		
		for($i = 0; $i < count($match_haystack); $i ++){
			if($match_haystack[$i] == "")continue;
			if($match_haystack[$i] == $match_needle[0]){
				if(count($match_needle) == 1){//如果只一个字符
					$match_haystack[$i] = $str;
				}else{
					$flag = true;
					for($j = 1; $j < count($match_needle); $j ++){
						if($match_haystack[$i + $j] != $match_needle[$j]){
							$flag = false;
							break;
						}
					}
					if($flag){//匹配
						$match_haystack[$i] = $str;
						for($j = 1; $j < count($match_needle); $j ++){
							$match_haystack[$i + $j] = "";
						}
					}
				}
			}
		}
		return implode("", $match_haystack);
    }
    /**
     * 将字符串拆解成数组(识别汉字)
     * @param type $paramArr 
     */
    public static function splitStr($paramArr) {
		$options = array(
            'input'  => '', #字符串
             'code'  => 'utf-8'#编码 utf-8/gbk
		);
		$options = array_merge($options, $paramArr);
		extract($options);
        
        $retArr = array();
        
        if(!$input) return $retArr;
        if(strtolower($code) == 'utf-8')
            return self::splitStrUtf8(array('input'=>$input));
        if(strtolower($code) == 'gbk')
            return self::splitStrGbk(array('input'=>$input));
    }
    
    /**
     * 将字符串拆解成数组(识别汉字)针对GBK
     */
    public static function splitStrGbk($paramArr) {
        $options = array(
            'input'  => '', #字符串
        );
        $options = array_merge($options, $paramArr);
        extract($options);
    
        $retArr = array();
    
        if(!$input) return $retArr;
    
        $len = strlen($input);
        for($i=0; $i<$len; $i++) {
            if(ord($input[$i])>0xa0) {
                $retArr[] = substr($input, $i, 2);
                $i++;
            } else {
                $retArr[] = $input[$i];
            }
        }
        return $retArr;
    }
    /**
     * 将字符串拆解成数组(识别汉字)针对utf-8
     */
    public static function splitStrUtf8($paramArr) {
        $options = array(
            'input'  => '', #字符串
        );
        $options = array_merge($options, $paramArr);
        extract($options);
        $result = array();
        $len = strlen($input);
        $i = 0;
        while($i < $len){
            $chr = ord($input[$i]);
            if($chr == 9 || $chr == 10 || (32 <= $chr && $chr <= 126)) {
                $result[] = substr($input,$i,1);
                $i +=1;
            }elseif(192 <= $chr && $chr <= 223){
                $result[] = substr($input,$i,2);
                $i +=2;
            }elseif(224 <= $chr && $chr <= 239){
                $result[] = substr($input,$i,3);
                $i +=3;
            }elseif(240 <= $chr && $chr <= 247){
                $result[] = substr($input,$i,4);
                $i +=4;
            }elseif(248 <= $chr && $chr <= 251){
                $result[] = substr($input,$i,5);
                $i +=5;
            }elseif(252 <= $chr && $chr <= 253){
                $result[] = substr($input,$i,6);
                $i +=6;
            }
        }
        return $result;
    }
    /**
     * 中文截取,仅支持utf8
     */
    public static function getShort($paramArr) {
        $options = array(
            'str'  => '', #字符串
            'length' => '',#截取长度
            'ext' => '', #后缀 ...
        );
        $options = array_merge($options, $paramArr);
        extract($options);
        
        $strlenth = 0;
        $output = '';
        preg_match_all("/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/", $str, $match);
        foreach ($match[0] as $v) {
            preg_match("/[\xe0-\xef][\x80-\xbf]{2}/", $v, $matchs);
            if (!empty($matchs[0])) {
                $strlenth += 1;
            } elseif (is_numeric($v)) {
                //$strlenth	+=	0.545; // 字符像素宽度比例 汉字为1
                $strlenth += 0.5; // 字符字节长度比例 汉字为1
            } else {
                //$strlenth	+=	0.475; // 字符像素宽度比例 汉字为1
                $strlenth += 0.5; // 字符字节长度比例 汉字为1
            }
            if ($strlenth > $length) {
                break;
            }
            $output .= $v;
        }
        $output .= $ext;
        return $output;
    }
    /**
     * 解压gzip字符串
     */
    public static function gzdecode($paramArr) {
		$options = array(
            'input'  => '', #字符串
		);
		$options = array_merge($options, $paramArr);
		extract($options);
        $data = $input;
          $len = strlen($data);   
          if ($len < 18 || strcmp(substr($data,0,2),"\x1f\x8b")) {   
           return null;  // Not GZIP format (See RFC 1952)   
          }   
          $method = ord(substr($data,2,1));  // Compression method   
          $flags  = ord(substr($data,3,1));  // Flags   
          if ($flags & 31 != $flags) {   
           // Reserved bits are set -- NOT ALLOWED by RFC 1952   
           return null;   
          }   
          // NOTE: $mtime may be negative (PHP integer limitations)   
          $mtime = unpack("V", substr($data,4,4));   
          $mtime = $mtime[1];   
          $xfl  = substr($data,8,1);   
          $os    = substr($data,8,1);   
          $headerlen = 10;   
          $extralen  = 0;   
          $extra    = "";   
          if ($flags & 4) {   
           // 2-byte length prefixed EXTRA data in header   
           if ($len - $headerlen - 2 < 8) {   
             return false;    // Invalid format   
           }   
           $extralen = unpack("v",substr($data,8,2));   
           $extralen = $extralen[1];   
           if ($len - $headerlen - 2 - $extralen < 8) {   
             return false;    // Invalid format   
           }   
           $extra = substr($data,10,$extralen);   
           $headerlen += 2 + $extralen;   
          }   

          $filenamelen = 0;   
          $filename = "";   
          if ($flags & 8) {   
           // C-style string file NAME data in header   
           if ($len - $headerlen - 1 < 8) {   
             return false;    // Invalid format   
           }   
           $filenamelen = strpos(substr($data,8+$extralen),chr(0));   
           if ($filenamelen === false || $len - $headerlen - $filenamelen - 1 < 8) {   
             return false;    // Invalid format   
           }   
           $filename = substr($data,$headerlen,$filenamelen);   
           $headerlen += $filenamelen + 1;   
          }   

          $commentlen = 0;   
          $comment = "";   
          if ($flags & 16) {   
           // C-style string COMMENT data in header   
           if ($len - $headerlen - 1 < 8) {   
             return false;    // Invalid format   
           }   
           $commentlen = strpos(substr($data,8+$extralen+$filenamelen),chr(0));   
           if ($commentlen === false || $len - $headerlen - $commentlen - 1 < 8) {   
             return false;    // Invalid header format   
           }   
           $comment = substr($data,$headerlen,$commentlen);   
           $headerlen += $commentlen + 1;   
          }   

          $headercrc = "";   
          if ($flags & 1) {   
           // 2-bytes (lowest order) of CRC32 on header present   
           if ($len - $headerlen - 2 < 8) {   
             return false;    // Invalid format   
           }   
           $calccrc = crc32(substr($data,0,$headerlen)) & 0xffff;   
           $headercrc = unpack("v", substr($data,$headerlen,2));   
           $headercrc = $headercrc[1];   
           if ($headercrc != $calccrc) {   
             return false;    // Bad header CRC   
           }   
           $headerlen += 2;   
          }   

          // GZIP FOOTER - These be negative due to PHP's limitations   
          $datacrc = unpack("V",substr($data,-8,4));   
          $datacrc = $datacrc[1];   
          $isize = unpack("V",substr($data,-4));   
          $isize = $isize[1];   

          // Perform the decompression:   
          $bodylen = $len-$headerlen-8;   
          if ($bodylen < 1) {   
           // This should never happen - IMPLEMENTATION BUG!   
           return null;   
          }   
          $body = substr($data,$headerlen,$bodylen);   
          $data = "";   
          if ($bodylen > 0) {   
           switch ($method) {   
             case 8:   
               // Currently the only supported compression method:   
               $data = gzinflate($body);   
               break;   
             default:   
               // Unknown compression method   
               return false;   
           }   
          } else {   
           // I'm not sure if zero-byte body content is allowed.   
           // Allow it for now...  Do nothing...   
          }   

          // Verifiy decompressed size and CRC32:   
          // NOTE: This may fail with large data sizes depending on how   
          //      PHP's integer limitations affect strlen() since $isize   
          //      may be negative for large sizes.   
          if ($isize != strlen($data) || crc32($data) != $datacrc) {   
           // Bad format!  Length or CRC doesn't match!   
           return false;   
          }   
          return $data;   
    }  
}
