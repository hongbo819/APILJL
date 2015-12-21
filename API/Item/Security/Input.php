<?php
/**
* 安全算法相关 - 输入的安全过滤
*/
class API_Item_Security_Input
{
     /**
     * 预防sql注入的过滤
     */
    public static function sqlFilter($paramArr){
        $options = array(
            'value'  => false,
            'from'   => 'G', #来源的区分，G来自get的数据 P来自post的数据 C来自cookie的数据
            'recDb'  => false, #是否记录到数据库
        );
        if (is_array($paramArr))  $options = array_merge($options, $paramArr);
        extract($options);

        if(!$value)return false;
        
        #如果是数组，就递归处理
        if(is_array($value)){
            $data = array();
            foreach($value as $k => $v){
                $options["value"] = $v;
                $data[$k] = self::sqlFilter($options);
            }
            return $data;
        }

        #不同的来源，过滤字符不同
        $filterArr = array(
            "G" => "'|(and|or)\\b.+?(>|<|=|in|like)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)",
            "P" => "\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)",
            "C" => "\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)",
        );
        $filterStr = isset($filterArr[$from]) ? $filterArr[$from] : $filterArr["G"];


        if (preg_match("/".$filterStr."/is",$value)==1){
            if($recDb){ #将数据过滤的结果，记录到数据库，便于监控恢复
                $db     = API_Db_Eagleeye::instance();
                $server = $_SERVER["SERVER_NAME"];
                $php    = $_SERVER["SCRIPT_NAME"];
                $query  = $_SERVER["QUERY_STRING"];
                $tm     = $_SERVER["REQUEST_TIME"];
                $method = $_SERVER["REQUEST_METHOD"];
                $cookie = isset($_SERVER["HTTP_COOKIE"]) ? $_SERVER["HTTP_COOKIE"] : '';
                $ip     = API_Item_Service_Area::getClientIp();

                $detail = "METHOD:{$method}\n\nQUERY:{$query}\n\nCOOKIE:{$cookie}";
                $sql    = "insert into eagleeye_sqlinject(server,php,tm,ip,reqstr,detail) values('{$server}','{$php}','{$tm}','{$ip}','{$value}','{$detail}')";
                $db->query($sql);
            }

            return false;#如果不合法就清空数据
        }else{
            return $value;
        }

    }

}

