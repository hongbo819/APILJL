<?php
/**
 * 表格相关操作
 *
 * LICENSE:
 * @version 1.0
 * @todo
 */
class API_Table extends API_LJL_Abstract_Pdo {
    //记录最近SQL
    public static $_strsql = '';
        
    //查询时使用
    const ROW = 'RRRRRRRRRRRR';

    //获取组合SQL(插入与更新)
    public static function getSql($tablename = '', $field_arr = array(), $where_arr = array(), $where_style_arr = array()) {
        if (!$field_arr || !$tablename)
            return;
        //组合成SQL语句
        $strsql = self::mergeSql($tablename, $field_arr, $where_arr, $where_style_arr);
        
        //记录最近SQL
        self::$_strsql = $strsql;
        
        return $strsql;
    }

    //获取组合SQL(删除)
    public static function getDelSql($tablename = '', $where_arr = array(), $where_style_arr = array()) {
        if (!$where_arr || !$tablename)
            return;
        //组合成SQL语句
        $strsql = self::mergeDelSql($tablename, $where_arr, $where_style_arr);
        
        //记录最近SQL
        self::$_strsql = $strsql;
        
        return $strsql;
    }

    //生成SQL
    public static function mergeSql($tablename = '', $field_arr = array(), $where_arr = array(), $where_style_arr = array()) {
        if (!$tablename || !$field_arr)
            return;
        //返回SQL语句
        $strsql = '';
        //限定未有$where_arr则为插入
        if ($where_arr) {
            //字段串
            $field_str = '';
            foreach ($field_arr as $field => $value) {
                //过滤防止SQL
                $value = self::escapeStr($value);

                $field_str .= "`{$field}`='{$value}',";
            }
            $field_str = rtrim($field_str, ',');
            //条件串
            $where_str = '';
            foreach ($where_arr as $field => $value) {
                if ($where_str) {
                    $where_str .= " and ";
                }
                //判断条件 
                $where_style = isset($where_style_arr[$field]) ? trim($where_style_arr[$field]) : '=';
                var_dump($field);
                var_dump($where_style_arr[$field]);
                if ('in' == $where_style) {
                    $where_str .= "`{$field}` in ({$value})";
                } else {
                    //过滤防止SQL
                    $value = self::escapeStr($value);

                    $where_str .= "`{$field}`='{$value}'";
                }
            }
            //组合成SQL语句
            $strsql = "update " . $tablename . " set {$field_str} where {$where_str}";
        } else {
            //字段串,值串
            $field_str = $val_str = '';
            foreach ($field_arr as $field => $value) {
                //过滤防止SQL
                $value = self::escapeStr($value);

                $field_str .= "`{$field}`,";
                $val_str .= "'{$value}',";
            }
            $field_str = rtrim($field_str, ',');
            $val_str = rtrim($val_str, ',');
            //组合成SQL语句
            $strsql = "insert into " . $tablename . "({$field_str}) values ({$val_str})";
        }
        return $strsql;
    }

    //生成删除 SQL
    public static function mergeDelSql($tablename = '', $where_arr = array(), $where_style_arr = array()) {
        if (!$tablename || !$where_arr)
            return;

        //返回SQL语句
        $strsql = '';
        //条件串
        $where_str = '';
        foreach ($where_arr as $field => $value) {
            if ($where_str) {
                $where_str .= " and ";
            }
            //判断条件 
            $where_style = isset($where_style_arr[$field]) ? trim($where_style_arr[$field]) : '=';
            if ('in' == $where_style) {
                $where_str .= "`{$field}` in ({$value})";
            } else {
                //过滤防止SQL
                $value = self::escapeStr($value);

                $where_str .= "`{$field}`='{$value}'";
            }
        }
        //组合成SQL语句
        $strsql = "delete from " . $tablename . " where {$where_str}";

        //echo $strsql.'<hr />';
        return $strsql;
    }

    //查询表
    public static function searchTable($param_arr = array()) {
        $options = array(
            'cdb' => '', //配置-连接类(不建议外部配置 , 可函数内配置 , 必须)
            'cols' => '', //配置-普通列(不建议外部配置 , 可函数内配置 , 必须)
            'table' => '', //配置-普通表(不建议外部配置 , 可函数内配置 , 必须)
            'cols_num' => '', //配置-普通列(数字列)(不建议外部配置 , 可函数内配置)
            //直接SQL
            'sql' => '', //扩展-指定SQL模版(不建议外部配置 , 可函数内配置 , 使用extend来调用)
            //leftjoin
            'leftjoin_cols' => '', //扩展-leftjoin表获取到的列(不建议外部配置 , 可函数内配置 , 使用extend来调用)
            'leftjoin_table' => '', //扩展-leftjoin表名(不建议外部配置 , 可函数内配置 , 使用extend来调用)
            'leftjoin_condition' => '', //扩展-leftjoin的条件(不建议外部配置 , 可函数内配置 , 使用extend来调用)
            //innerjoin
            'innerjoin_cols' => '', //扩展-innerjoin表获取到的列(不建议外部配置 , 可函数内配置 , 使用extend来调用)
            'innerjoin_table' => '', //扩展-innerjoin表名(不建议外部配置 , 可函数内配置 , 使用extend来调用)
            'innerjoin_condition' => '', //扩展-innerjoin的条件(不建议外部配置 , 可函数内配置 , 使用extend来调用)
            //groupby
            'groupby_cols' => '', //扩展-groupby表获取到的列(不建议外部配置 , 可函数内配置 , 使用extend来调用)
            'groupby_condition' => '', //扩展-groupby的条件(不建议外部配置 , 可函数内配置 , 使用extend来调用)
            'extend' => '', //使用扩展名称:在表各自函数中设置(允许外部配置)
            'return' => 'all', #返回结果:all-全部,row-一行,one-第一行第一列(允许外部配置),pair-指定字段为键与值组成数组
            'orderby' => '', #排序:自定义字段(允许外部配置)
            'count' => 0, //是否只返回总数(允许外部配置)
            'is_limit' => 0, //是否限制读取返回条数(允许外部配置)
            'limit_offset' => 0, #限制返回条数时,开始标记(允许外部配置)
            'limit_num' => 20, #限制返回条数时,读取条数(允许外部配置)
        );
        //待查询的字段数组
        $search_options = array();
        if (is_array($param_arr)) {
            $search_options = array_diff_key($param_arr, $options);
            $options = array_merge($options, $param_arr);
        }
        extract($options);
        //去掉左右逗号
        $cols = trim($cols, ',');
        $cols_num = trim($cols_num, ',');

        //必须检查
        if (!$cols || !$table || !$cdb) {
            return;
        }

        //数字列
        $cols_num_arr = array();
        if ($cols_num) {
            $cols_num_arr = explode(',', $cols_num);
        }
        //是否innerjoin
        $is_innerjoin = 0;

        //如果是innerjoin , 则借助leftjoin进行生成,只是最终使用inner join
        if ($innerjoin_cols && $innerjoin_table && $innerjoin_condition) {
            //覆盖赋值
            $leftjoin_cols = $innerjoin_cols;
            $leftjoin_table = $innerjoin_table;
            $leftjoin_condition = $innerjoin_condition;

            $is_innerjoin = 1;
        }


        //是否leftjoin
        $is_leftjoin = 0;

        //leftjoin信息
        if ($leftjoin_cols && $leftjoin_table && $leftjoin_condition) {
            //改变列
            $cols = 'a.' . str_replace(',', ',a.', $cols);
            $cols .= ',b.' . str_replace(',', ',b.', $leftjoin_cols);

            //改变条件,增加a与b,左a右b
            $temp_arr = explode('=', $leftjoin_condition);
            $leftjoin_condition = (false === strpos($temp_arr[0], '.') ? 'a.' : '') . $temp_arr[0] . '=' . (false === strpos($temp_arr[1], '.') ? 'b.' : '') . $temp_arr[1];

            if ($orderby) {
                //改变orderby条件,增加a与b
                $temp_arr = explode(',', $orderby);
                $orderby_arr = array();
                if ($temp_arr) {
                    foreach ($temp_arr as $val) {
                        if (false === strpos($val, '.')) {
                            $orderby_arr[] = 'a.' . $val;
                        } else {
                            $orderby_arr[] = $val;
                        }
                    }
                }
                $orderby = implode(',', $orderby_arr);
            }

            //重置模板
            $sql = 'select  #COLS#  from  #TABLE# as a ' . ($is_innerjoin ? ' inner join ' : ' left join ') . $leftjoin_table . ' as b on ' . $leftjoin_condition . '  #WHERE# #ORDERBY# #LIMIT#';

            //leftjoin
            $is_leftjoin = 1;
        }

        //查询条件 
        $where = self::mergeWhere($search_options, $cols_num_arr, $is_leftjoin);

        //groupby信息
        $groupby = '';
        if ($groupby_cols) {
            //改变列
            $cols = $groupby_cols;

            //是否设置条件
            if ($groupby_condition) {
                //设置条件
                $groupby = "group by " . $groupby_condition;
            }
        }


        //orderby信息
        $orderby = $orderby ? 'order by ' . $orderby : '';

        //读取条数
        $limit = '';
        if ($is_limit) {//如果限制读取条数
            $limit = " limit {$limit_offset},{$limit_num}";
        }

        //如果只读取总数
        if ($count) {
            //改变列
            $cols = "count('x')";
            //去掉orderby,limit,groupby等
            $limit = '';
            $orderby = '';
            $groupby = '';
            //只返回一个总数值
            $return = 'one';
        }

        //如果指定SQL模版
        $template = 'select  #COLS#  from  #TABLE#  #WHERE#  #GROUPBY# #ORDERBY# #LIMIT#';
        if ($sql) {
            $template = $sql;
        }

        //最终sql
        $strsql = str_replace(
                array('#COLS#', '#TABLE#', '#WHERE#', '#GROUPBY#', '#ORDERBY#', '#LIMIT#'), array($cols, $table, $where, $groupby, $orderby, $limit), $template);

        //return为数组时,返回键值对
        $pair_key = $pair_val = '';
        if (is_array($return)) {
            $pair_key = $return[0];
            $pair_val = $return[1];
            //是否多个字段---多字段时为数组
            if (is_array($pair_val)) {
                //重置为字符串
                $return = 'pair_extend';
            } else if ($pair_val == self::ROW) {//值为每行
                //重置为字符串
                $return = 'pair_extend_row';
            } else {
                //重置为字符串
                $return = 'pair';
            }
        }

        //记录最近SQL
        self::$_strsql = $strsql;

        //返回
        $res = array();
        switch ($return) {
            case 'row':
                $res = $cdb->getRow($strsql);
                break;
            case 'one':
                $res = $cdb->getOne($strsql);
                break;
            case 'pair':
                $res = $cdb->getPairs($strsql, $pair_key, $pair_val);
                break;
            case 'pair_extend':
                $temp_res = $cdb->getAll($strsql);
                foreach ($temp_res as $row) {
                    $tarr = array();
                    foreach ($pair_val as $v) {
                        $tarr[$v] = $row[$v];
                    }
                    $res[$row[$pair_key]] = $tarr;
                }
                break;
            case 'pair_extend_row':
                $temp_res = $cdb->getAll($strsql);
                foreach ($temp_res as $row) {
                    $res[$row[$pair_key]] = $row;
                }
                break;
            case 'sql'://返回SQL
                $res = $strsql;
                break;
            default:
                $res = $cdb->getAll($strsql);
                break;
        }
        return $res;
    }

    //生成where条件
    public static function mergeWhere($options = array(), $cols_num_arr = array(), $is_leftjoin = 0) {
        $where = '';
        foreach ($options as $key => $val) {
            //设置为无法读取
            if (is_numeric($key) && 0 == $val) {
                $where = "and 1=0";
            } else {
                //如果是leftjoin
                $leftjoin_pre = ''; //前缀
                if ($is_leftjoin && false !== strpos($key, '.')) {
                    list($leftjoin_pre, $key) = explode('.', $key, 2);
                    $leftjoin_pre .= '.';
                }

                //分解方式
                $key_arr = explode('#', $key);

                //键值重置
                $key = $key_arr[0];
                $key_str = '`' . $key_arr[0] . '`';

                //如果是leftjoin
                if ($is_leftjoin) {
                    if (!$leftjoin_pre) {
                        $leftjoin_pre = 'a.';
                    }
                    $key_str = $leftjoin_pre . $key_str;
                }

                //方式
                $type = '=';

                //过滤防止SQL
                if (is_array($val)) {
                    $filter_val = ''; //数组时,各方式自行处理,默认定义无效数值
                } else {
                    $filter_val = self::escapeStr(trim($val));
                }

                //值使用数字或字符串
                $val_str = in_array($key, $cols_num_arr) ? intval($val) : "'" . $filter_val . "'";

                //如果有方式选择
                if (isset($key_arr[1])) {
                    $type = $key_arr[1];
                    switch ($type) {
                        case 'like':
                        case 'not like':

                            $val_str = self::escapeStr(trim($val));
                            //like通配符转义
                            $val_str = str_replace(array('%', '_', '[', ']'), array('\%', '\_', '\[', '\]'), $val_str);


                            //判断
                            if (isset($key_arr[2])) {
                                if ('end' == $key_arr[2]) {
                                    $where .= " and {$key_str} {$type} '%{$val_str}'";
                                } else if ('first' == $key_arr[2]) {
                                    $where .= " and {$key_str} {$type} '{$val_str}%'";
                                } else {
                                    $where .= " and 1=0";
                                }
                            } else {
                                $where .= " and {$key_str} {$type} binary '%{$val_str}%'";
                            }
                            break;
                        case 'in':
                        case 'not in':
                            //非数组转换成数组
                            if (!is_array($val)) {
                                $val = explode(',', $val);
                            }
                            if (is_array($val)) {
                                //临时变量
                                $temp = '';
                                foreach ($val as $v) {
                                    //去掉左右双引号
                                    $v = trim($v, "'");
                                    //使用值
                                    $val_str = in_array($key, $cols_num_arr) ? intval($v) : "'" . self::escapeStr(trim($v)) . "'";
                                    $temp .= "{$val_str},";
                                }
                                $temp = rtrim($temp, ',');
                                //如果有值时
                                if ($temp) {
                                    $where .= " and {$key_str} {$type} ({$temp})";
                                } else {//查询失败
                                    $where .= " and 1=0";
                                }
                            } else {
                                $where .= " and {$key_str} {$type} ({$val_str})";
                            }
                            break;
                        case 'or':
                            $where .= " or (";
                            if (is_array($val)) {
                                //临时变量
                                $temp = '';
                                foreach ($val as $v) {
                                    //去掉左右双引号
                                    $v = trim($v, "'");

                                    //使用值
                                    $val_str = in_array($key, $cols_num_arr) ? intval($v) : "'" . self::escapeStr(trim($v)) . "'";
                                    $temp .= "{$key_str}={$val_str} or";
                                }
                                $temp = rtrim($temp, 'or');
                                $where .= $temp;
                            } else {
                                $where .= "{$key_str}={$val_str}";
                            }
                            $where .= ")";
                            break;
                        case '=':
                        case '!=':
                        case '<=':
                        case '<':
                        case '>':
                        case '>=':
                            $where .= " and {$key_str}{$type}{$val_str}";
                            break;
                        default:
                            $where .= " and {$key_str}={$val_str}";
                    }
                } else {
                    $where .= " and {$key_str}={$val_str}";
                }
            }
        }
        //多查询条件时,去掉第一个的 and ,如果有的话
        $where = trim($where);
        if ($where) {
            //去掉头部的and
            if (0 === strpos($where, 'and'))
                $where = trim(substr($where, 3));
            else if (0 === strpos($where, 'or'))
                $where = trim(substr($where, 2));

            if ($where) {
                $where = ' where ' . $where . ' ';
            }
        }
        return $where;
    }

    //过滤字符串,防止SQL注入
    public static function escapeStr($string = '') {
        if (!defined('MAGIC_OPEN')) {
            define('MAGIC_OPEN',get_magic_quotes_gpc());
        }
        if (!$string)
            return $string;

        //如果打开了
        if (MAGIC_OPEN) {
            $string = stripslashes($string); //去掉
        }
        //换成过滤
        $string = mysql_escape_string($string);

        return $string;
    }

}

