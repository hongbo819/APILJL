<?php
/**
 * 相似判断
 */
class API_Item_Base_Sim
{
    /**
     * 计算最长公共子序列
     * @link http://blog.csdn.net/v_july_v/article/details/6695482#t0
     * @param type $paramArr
     * @return type 
     */
    public static function getLcs($paramArr=array()){
		$options = array(
            'str1' => "", #文本1 
            'str2' => "", #文本2
		    'code' => 'utf-8'
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);

        if(!$str1 || !$str2) return "";

        $str1 = stripslashes($str1);
        $str2 = stripslashes($str2);
        
        $str1 = API_Item_Base_String::splitStr(array('input'=>$str1,'code'=>$code));
        $str2 = API_Item_Base_String::splitStr(array('input'=>$str2,'code'=>$code));
        
        $len1 = count($str1);
        $len2 = count($str2);
        // 构造二维数组记录子问题x[i]和y[i]的LCS的长度
        $opt = array();
        for($i=0; $i<=$len1; $i++) {
            for($j=0; $j<=$len2; $j++) {  $opt[$i][$j] = 0; }
        }
        
        // 动态规划计算所有子问题
        for($i=$len1-1; $i>=0; $i--) {
            for($j=$len2-1; $j>=0; $j--) {
                if($str1[$i]==$str2[$j]) {
                    $opt[$i][$j] = $opt[$i+1][$j+1]+1;
                } else {
                    $opt[$i][$j] = max($opt[$i+1][$j], $opt[$i][$j+1]);
                }
            }
        }
        
        $i=0; $j=0;
        $lcs = "";
        while($i<$len1 && $j<$len2) {
            if($str1[$i] == $str2[$j]){
                $lcs .= $str1[$i];
                $i++;
                $j++;
            } else if($opt[$i+1][$j]>=$opt[$i][$j+1]) {
                $i++;
            } else {
                $j++;
            }
        }
        return $lcs;
    }
    /**
     * 计算最长公共字串
     * @link http://blog.csdn.net/imzoer/article/details/8031478
     */
    public static function maxSeq($paramArr=array()) {
        $options = array(
             'str1' => "", #文本1
             'str2' => "", #文本2
             'code' => 'utf-8'
         );
         if (is_array($paramArr))$options = array_merge($options, $paramArr);
         extract($options);
         
         $lcslen = 0;
         $pos_x = 0;
         $pos_y = 0;
         $flag = array();
         
         $str1 = API_Item_Base_String::splitStr(array('input'=>$str1,'code'=>$code));
         $str2 = API_Item_Base_String::splitStr(array('input'=>$str2,'code'=>$code));
         
         $len1 = count($str1);
         $len2 = count($str2);
         
         for ($i = 0; $i < $len1; $i++) {
             $flag[$i][0] = 0;
         }
         for ($i = 0; $i < $len2; $i++) {
             $flag[0][$i] = 0;
         }
         for ($i = 1; $i < $len1; $i++) {
             for ($j = 1; $j < $len2; $j++) {
                 if ($str1[$i - 1] == $str2[$j - 1]) {
                     $flag[$i][$j] = $flag[$i - 1][$j - 1] + 1;
                     if ($flag[$i][$j] > $lcslen) {
                         $lcslen = $flag[$i][$j];
                         $pos_x = $i;
                         $pos_y = $j;
                     }
                 } else {
                     $flag[$i][$j] = 0;
                 }
             }
         }
         $sb = '';
         while ($flag[$pos_x][$pos_y] != 0) {
             $sb = $str1[$pos_x - 1] . $sb;
             $pos_x--;
             $pos_y--;
         }
         return $sb;
    }
    /**
     * 计算两文本相似度
     */
    public static function getTextSim($paramArr){
		$options = array(
            'str1'      => "", #文本1 
            'str2'      => "", #文本2
            'lcs'       => 0,  #是否返回最长公共子序列
		);
		if (is_array($paramArr))$options = array_merge($options, $paramArr);
		extract($options);
        
        $len1 = strlen($str1);
        $len2 = strlen($str2);
        if($len1+$len2==0) return 1.000;
        
        $lcsStr = self::getLcs(array('str1'=>$str1, 'str2'=>$str2));
        $sim = strlen($lcsStr)/($len1+$len2-strlen($lcsStr));
        $sim = sprintf("%.3f", $sim);
        
        $res = array('sim'=>$sim);
        if($lcs) { $res['lcs'] = $lcsStr; }
        return $res;
    }
    /**
     * 文本相似判断
     */
    public static function simText($paramArr) {
        $options = array(
            'str1'      => "", #文本1
            'str2'      => "", #文本2
            'lcs'       => 0,  #最长公共子序列
            'diff'      => 0,  #显示差异
        );
        if (is_array($paramArr))$options = array_merge($options, $paramArr);
        extract($options);
    
        $str1 = stripslashes($str1);
        $str2 = stripslashes($str2);
    
        $str1 = API_Item_Base_String::splitStr(array('input'=>$str1));
        $str2 = API_Item_Base_String::splitStr(array('input'=>$str2));
    
        $len1 = count($str1);
        $len2 = count($str2);
    
        array_unshift($str1, '');
        array_unshift($str2, '');
    
        $C = $L = array();
        $res = '';
    
        #动态规划算法自底向上地计算过程
        for($j=0; $j<=$len1; $j++) $C[0][$j] = 0;
        for($i=0; $i<=$len2; $i++) $C[$i][0] = 0;
        for ($i=1; $i<=$len2; $i++) {
            for ($j=1; $j<=$len1; $j++) {
                if ($str1[$j]==$str2[$i]) {
                    $C[$i][$j] = $C[$i-1][$j-1] + 1;
                    $L[$i][$j] = '`';
                } else if($C[$i-1][$j]>=$C[$i][$j-1]) {
                    $C[$i][$j] = $C[$i-1][$j];
                    $L[$i][$j] = '↑';
                } else {
                    $C[$i][$j] = $C[$i][$j-1];
                    $L[$i][$j] = '←';
                }
            }
        }
        #返回最长公共子序列
        $i = $len2;
        $j = $len1;
        $commonArr = array();
        $orgin['str1'] = $orgin['str2'] = array();
        while($i>0 && $j>0) {
            $direct = $L[$i][$j];
            if($direct=='`') {
                array_unshift($commonArr, $str1[$j]);
                $orgin['str1'][] = $j;
                $orgin['str2'][] = $i;
                $i--; $j--;
            }
            if($direct=='↑') { $i--; }
            if($direct=='←') { $j--; }
        }
    
    
        #用Tanimoto系数计算相似度
        $Nc = count($commonArr);
        $Na = $len1;
        $Nb = $len2;
        $sim = 0;
        if($Na+$Nb-$Nc>0) $sim = $Nc/($Na+$Nb-$Nc);
        $sim = sprintf("%.3f", $sim);
    
        $res = array('sim'=>$sim);
        if($lcs) {
            $res['lcs'] = implode('', $commonArr);
        }
    
    
        #加颜色，绿色为公共，红色为新文本
        if($diff) {
            $result['str1'] = self::markTextColor(array('strArr'=>$str1, 'orgin'=>$orgin['str1']));
            $result['str2'] = self::markTextColor(array('strArr'=>$str2, 'orgin'=>$orgin['str2']));
            $res['result'] = $result;
        }
    
        return $res;
    }
    /**
     * 标识颜色
     */
    private static function markTextColor($paramArr=array())
    {
        $options = array(
            'strArr'        => '',
            'orgin'         => array(),
            'samecolor'     => 'green',
            'nosamecolor'   => 'red'
        );
        $options = array_merge($options, $paramArr);
        extract($options);
    
        $result = '';
        $len = count($strArr);
        for($i=1; $i<$len; $i++) {
            if(in_array($i, $orgin)) {
                $char = '<font color="'.$samecolor.'">'.$strArr[$i].'</font>';
            } else {
                $char = '<font color="'.$nosamecolor.'">'.$strArr[$i].'</font>';
            }
            $result .= $char;
        }
    
        return $result;
    }
}
