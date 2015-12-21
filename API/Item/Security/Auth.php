<?php
/**
* 验证安全相关
*/
class API_Item_Security_Auth
{
    /**
     * 验证当前用户是否是后台登录状态
     */
    public static function adminIsLogin($paramArr){
        $options = array(
            'S_uid'         => false,
            'S_checkcode'   => false,
            'remoteCheck'   => true, //是否进行允许远程登录的判断
            'recAdminLog'   => false, //是否记录admin的日志
        );
        if (is_array($paramArr))  $options = array_merge($options, $paramArr);
        extract($options);

        $cookieArr = LJL_API_ISFW ? LJL_Api::$_globalVars['_COOKIE'] : $_COOKIE;
        #如果有参数传入，就按照参数的进行验证
        if($S_uid)       $cookieArr["S_uid"]        = $S_uid;
        if($S_checkcode) $cookieArr["S_checkcode"]  = $S_checkcode;

        if(!isset($cookieArr["S_uid"]) || !isset($cookieArr["S_checkcode"])){
            return false;
        }
        
        $db  = API_Db_Power::instance();
        $sql = "select check_code,is_remote_login,ip from s_user where user_id='{$cookieArr["S_uid"]}' and check_code='{$cookieArr["S_checkcode"]}'";
        $row = $db->getRow($sql);

        if(!$row){
            return false;
        }else{
            #进行远程登录的判断
            if($remoteCheck){
                $ip = $_SERVER['REMOTE_ADDR'];
                $ipList = array('202.142.18.130','218.241.221.98','112.92.40.137','113.106.194.227',
                                 '113.106.194.221','113.106.194.222','220.243.137.3','220.243.137.2',
                                 '118.26.190.173','123.125.1.173','123.125.0.173','61.135.194.106',
                                 '113.97.240.20', '122.198.133.215', '61.148.243.55','221.179.181.193','118.67.127.154',
                                 '220.181.149.235');
                //is_remote_login 表示该用户禁止远程登录
                if($row['is_remote_login'] == 0 && "10.19."!=substr($ip,0,6) && "118.67.127"!=substr($ip,0,10)
                      && "10.15."!=substr($ip,0,6) && "118.67.127."!=substr($ip,0,11) && !in_array($ip, $ipList)){
                    return false;
                }
            }
            #记录操作到后台日志中
            if($recAdminLog){
                self::recAdminLog(array('S_uid'=>$S_uid,'S_checkcode'=>$S_checkcode));
            }
            return true;
        }

    }

    /**
     * 验证前台用户是否是登录状态
     */
    public static function webIsLogin($paramArr){
        $options = array(
            'xxx_userid'  => false,
            'xxx_check'   => false,
            'xxx_cipher'  => false,
        );
        if (is_array($paramArr))  $options = array_merge($options, $paramArr);
        extract($options);

        $cookieArr = LJL_API_ISFW ? LJL_Api::$_globalVars['_COOKIE'] : $_COOKIE;
        #如果有参数传入，就按照参数的进行验证
        if($xxx_userid)$cookieArr["xxx_userid"] = $xxx_userid;
        if($xxx_check) $cookieArr["xxx_check"]  = $xxx_check;
        if($xxx_cipher)$cookieArr["xxx_cipher"] = $xxx_cipher;


        if(!isset($cookieArr["xxx_userid"]) || !isset($cookieArr["xxx_check"]) || !isset($cookieArr["xxx_cipher"])){
            return false;
        }
        
        return $cookieArr;
    }

}

