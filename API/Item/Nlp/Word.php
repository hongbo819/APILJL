<?php
/**
* 机器智能 - 词语相关
* reload=941c1277f81a40f52e2729871b697b38
*/
class API_Item_Nlp_Word
{
    private static $seggerUrl = "http://service.api.xxx.com/api/segger.api";// "http://10.15.185.107:8080/api/segger.api"; #分词的api地址
    
    /**
     * 阿拉丁词库分词
     */
    public static function aladsegger($paramArr) {
		$options = array(
            'content'     => '', #要分词的内容
            'segger'      => 'default',  #词库，默认default
            'num'         => 10,  #获得分词的数量
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);
        
        $url = "http://service.api.xxx.com/api/aladinsegger.api";
        //$url = "http://10.15.185.126:8080/api/aladinsegger.api";
        $outArr = array();
        $content = strip_tags(mb_convert_encoding($content,"utf-8","gbk"));
        $data = API_Http::curlPost(array('url'=>$url,'postdata'=>array('txt'=>$content)));
        //$data = API_Http::curlPage(array('url'=>$url . "?txt=".urlencode($content)));
        if(!$data)return false;
        $data = mb_convert_encoding($data,"gbk","utf-8");
        
        #数据的整理
        $outArr = array();
        if($data){
            $dataArr = explode("\n", $data);
            foreach($dataArr as  $d){
                if(!$d)continue;
                list($wd,$val) = explode("/",$d);
                if(substr($val, 0,7) == 'manusub'){#品牌子类
                    $tmpArr = explode("_", $val);
                    $outArr['manusub'][] = array(
                        'subcateId' => $tmpArr[1],
                        'manuId'    => $tmpArr[2],
                    );
                }else if(substr($val, 0,6) == 'series'){
                    $outArr['serires'][] = substr($val, 6);

                }else if(substr($val, 0,3) == 'pro'){
                    $v = substr($val, 3);
                    if($v) $outArr['pro'][] = $v;

                }else{#类型词
                    $outArr['type'][] =  $val;
                }
            }
            $outArr['txt'] = $data;
        }

        return $outArr;

    }

    /**
     * 分词
     */
    public static function segger($paramArr) {
		$options = array(
            'content'     => '', #要分词的内容
            'segger'      => 'default',  #词库，默认default
            'mustIn'      => 0,   #是否必须是词库里面词
            'num'         => 10,  #获得分词的数量
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);
        $outArr = array();
        $content = strip_tags(mb_convert_encoding($content,"utf-8","gbk"));
        #$data = API_Http::curlPage(array('url'=>self::$seggerUrl . "?segger=default&content=".urlencode($content)));
        $data = API_Http::curlPost(array('url'=>self::$seggerUrl,'postdata'=>array('segger'=>$segger,'cnt'=>$num,'content'=>$content)));
        if($data){
            $dataArr = explode("\n", $data);
            foreach($dataArr as $d){
                if(!$d)continue;
                list($wd,$score) = explode("/",$d);
                if($mustIn && $score < 0)continue;
                $outArr[] = array('wd'=>$wd,'score'=>$score);
            }
        }

        return $outArr;
    }

    

    /**
     * 在文本中查找Tag,返回匹配的Tag
     */
    public static function strMatchTag($paramArr) {
		$options = array(
            'contentStr'  => '',     #文本
            'keywordArr'  => false,  #关键字数组，或者不同的词逗号分隔
            'charset'     =>'utf-8'
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);

        if(!$keywordArr || !$contentStr) return false;
        if(!is_array($keywordArr)){
            $keywordArr = explode(",", $keywordArr);
        }
        
        $contentStr = strip_tags($contentStr);        

        $keyArr = array();
        #对数组进行二级映射
        foreach ($keywordArr as $key=>$val) {
            $keyArr[strtolower($val[0])][strtolower($val[1])][$val] = $val;
        }
        $loopCnt = 0;
        $data = array();
        #文本单字节循环，跟数组进行对比
        for($i=0;$i<strlen($contentStr);$i++) {
            if($loopCnt++ > 100000)break; //防止未知情况陷入死循环

            $fstr = strtolower($contentStr[$i]);
            if (isset($keyArr[$fstr])) {
                $sstr = strtolower($contentStr[$i+1]);
                if (isset($keyArr[$fstr][$sstr])) {
                	
                    foreach ($keyArr[$fstr][$sstr] as $key => $val) {
                        $key2 = str_replace('&','&amp;',$key);
                        $str  = preg_replace('#([A-Z]+)#e',"strtolower('\\1')",substr($contentStr,$i,strlen($key2)));
                        $find = preg_replace('#([A-Z]+)#e',"strtolower('\\1')",$key2);
                        //var_dump($find);die;
                        if ($str == $find) {
                            //判断如果本身全是字母且前后有字母的话就不执行这个替换
                            if (preg_match('#^[a-z0-9\s]+$#is',$key2) && (preg_match('#[a-z0-9]#is',$contentStr[$i-1]) || preg_match('#[a-z0-9]#is',$contentStr[$i+strlen($key2)]))) {
                               continue;
                            }

                            $kword = $val;
                            //条件成立，计数
                            if (isset($data[$kword])) {
                                $data[$kword]++;
                            } else {
                                $data[$kword]=1;
                            }
                        }
                    }
                }
            }
            //如果当前字符判断为中文字符，就多跳过一个
            if (ord($contentStr[$i]) > 128) {
                $i++;
                if($charset == 'utf-8'){
                    $i++;
                }
            }
        }
        arsort($data);
        return $data;
    }

    

    /**
     * 在文本中添加链接
     */
    public static function addLinkToTxt($paramArr) {
		$options = array(
            'contentStr'  => '',         #文本
            'keywordArr'  => false,      #关键字数组，或者不同的词逗号分隔
            'linkClass'   => 'kwd_lnk',  #添加链接的样式名
            'onlyOnce'    => true,       #一个关键字是否只替换一次
            'keywordMeta' => false,      #关键字的附属信息
            'charset'     => 'utf-8'
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);

        if(!$keywordArr || !$contentStr) return false;


        #数组变成二级hash形式        
        $keyArr = array();
        foreach ($keywordArr as $key => $val) {
        	// var_dump($key);die;
            $keyArr[strtolower($key[0])][strtolower($key[1])][$key] = $val;
        }
        $keywordArr = $keyArr;
       


        $loopCnt = 0;
        for($i=0;$i<strlen($contentStr);$i++) {
            if($loopCnt++ > 100000)break; //防止未知情况陷入死循环

            //遇到标签的情况
            if ($contentStr[$i] == '<') {
                if (strtolower($contentStr[$i+1]) == 'a') {    //a直接跳过
                    $tmpPosI = stripos($contentStr,'</a>',$i);
                    if($tmpPosI===false)break;//如果没有找到，说明文章最后标签没有闭合，跳出,否则$tmpPosI=0 陷入死循环

                    $i = $tmpPosI + 3;
                } else {    //跳到标签结束
                    $tmpPosI = strpos($contentStr,'>',$i);
                    if($tmpPosI===false)break;

                    $i = $tmpPosI - 1;
                }
            }
			
            $fstr = strtolower($contentStr[$i]);#当前指向一个字符
            
            if (isset($keywordArr[$fstr])) {#如果这个字符在特殊字符数组中
            	
                $sstr = strtolower($contentStr[$i+1]);#指向下一个字符

                if (isset($keywordArr[$fstr][$sstr])) {#如果这个字符在特殊字符数组中

                    foreach ($keywordArr[$fstr][$sstr] as $key=>$val) {#key是文字，val是链接
                        
                        $key2 = str_replace('&','&amp;',$key); #文字替换掉
                        $str  = preg_replace('#([A-Z]+)#e',"strtolower('\\1')",substr($contentStr,$i,strlen($key2)));
                        $find = preg_replace('#([A-Z]+)#e',"strtolower('\\1')",$key2);
						
                        if ($str == $find) {
                            //判断如果本身全是字母且前后有字母的话就不执行这个替换
                            if (preg_match('#^[a-z0-9\s]+$#is',$key2) &&
                               (preg_match('#[a-z0-9]#is',$contentStr[$i-1]) || preg_match('#[a-z0-9]#is',$contentStr[$i+strlen($key2)]))) {
                                continue;
                            }
                            if (is_array($val)) {   //增加一个关键词对多个连接的替换
                                $url = array_shift($val);
                                if (!$val) {
                                    unset($keywordArr[$fstr][$sstr][$key]);
                                } else {
                                    $keywordArr[$fstr][$sstr][$key] = $val;
                                }
                            } else {
                                $url = $val;
                                if($onlyOnce){
                                    unset($keywordArr[$fstr][$sstr][$key]);
                                }
                            }
                            //条件成立，执行替换  $key2
                            $dataRel = "";
                            if($keywordMeta && isset($keywordMeta[$key2]) ){
                                $dataRel = ' data-rel="'.$keywordMeta[$key2].'"';
                            }
                           // var_dump($i);die;
                            $contentStr = substr($contentStr,0,$i).
                                '<a href="'.$url.'" class="'.$linkClass.'"'.$dataRel.'>'. substr($contentStr,$i,strlen($key2)).'</a>'.
                                substr($contentStr,$i+strlen($key2));
                            $i += strlen('<a href="'.$url.'" class="'.$linkClass.'"'.$dataRel.'>'.$key2.'</a>')-1;
                        }
                    }
                }
            }
            //如果当前字符判断为中文字符，就多跳过一个
            if (ord($contentStr[$i]) > 128) {
                $i++;
                if($charset == 'utf-8') $i++;
            }
        }
        return $contentStr;

    }

    /**
     * 词性标注
     */
    public static function labelWord($paramArr) {
		$options = array(
            'text'  => '',         #文本
            'retry' => true,       #出错重试
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);
        if(!$text)return '';
        
        $fp = fsockopen("10.15.184.103", 12308, $errno, $errstr, 30);
        $contents = "";
        if ($fp) {
            $text = mb_convert_encoding($text, "UTF-8","GBK");
            $text = urlencode($text)."\n";
            fwrite($fp, $text);
            while (!feof($fp)) {
               $contents .= fread($fp, 2048);
            }
            fclose($fp);
           // echo $contents;die;
            //内容的后续处理
            if($contents){
                $contents   = substr($contents, 1,-2);
                $contentArr = explode(",", $contents);
                $outArr     = array();
                if($contentArr){
                    foreach($contentArr as $v){
                        $varr = explode("/",$v);
                        $outArr[] = array('w'=>$varr[0],'l'=>$varr[1]);
                    }
                }
                return $outArr;
            }
        }
        return false;
    }

    /**
     * 获得词性对照对比表
     */
    public static function wordVerb(){
        return array(
                "n" => "名词",
                "a" => "形容词",
                "v" => "动词",
                "d" => "副词",
                "Ag" => "形语素",
                "ad" => "副形词",
                "an" => "名形词",
                "b" => "区别词",
                "c" => "连词",
                "Dg" => "副语素",
                "e" => "叹词",
                "f" => "方位词",
                "g" => "语素",
                "h" => "前接成分",
                "i" => "成语",
                "j" => "简称略语",
                "k" => "后接成分",
                "l" => "习用语",
                "m" => "数词",
                "Ng" => "名语素",
                "nr" => "人名",
                "ns" => "地名",
                "nt" => "机构团体",
                "nz" => "其他专名",
                "o" => "拟声词",
                "p" => "介词",
                "q" => "量词",
                "r" => "代词",
                "s" => "处所词",
                "Tg" => "时语素",
                "t" => "时间词",
                "u" => "助词",
                "Vg" => "动语素",
                "vd" => "副动词",
                "vn" => "名动词",
                "w" => "标点符号",
                "x" => "非语素字",
                "y" => "语气词",
            );
    }
}

