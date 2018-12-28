<?php
/**
 * Created by PhpStorm.
 * User: hemuhan
 * Date: 2017/11/21
 * Time: 下午3:15
 */
namespace Common\Library;
use Models\IdentifyMap;
use Models\Member;

/**
 * 公共函数库类，系统自动加载此类的信息，通过$this->util来访问系统
 * Class Util
 * @package Librarys
 */
class Util
{
    const MD5_PATTERN = '/^([a-f0-9]{32})$/is';
    const MOBILE_PATTERN = '/^(13[0-9]|14[57]|15[0-9]|166|17[0135678]|18[0-9]|19[89])\d{8}$/s';
    const EMAIL_PATTERN = '/^\w+(?:[\.\-_]\w+)*@\w+(?:\.(?:\w+[\-_]?\w+))+$/is';
    const NAME_PATTERN = '/^(([\x{4e00}-\x{9fa5}]{1,10}([·\.]{0,1}[\x{4e00}-\x{9fa5}]{1,10}){0,5})|[\x{4e00}-\x{9fa5}]{2,3}\/[\x{4e00}-\x{9fa5}]{2,3}|([a-zA-Z]{1,20}(\s{0,4}\.{0,1}\s{0,4}[a-zA-Z]{1,20}){0,5}))$/ius';

    /**
     * 判断key是否存在
     * @param $arr
     * @param $key
     * @param $def
     * @return mixed
     */
    public static function getA($arr, $key, $def=null)
    {
        $ak = (array)$key;
        foreach ($ak as $k) {
            if (isset($arr[$k])) {
                return $arr[$k];
            }
        }
        return $def;
    }

    /**
     * 驼峰转成下划线
     * @param $name
     * @return string
     */
    public function camelbackToUnderline($name)
    {
        $rn = '';
        $len = strlen($name);
        for ($i=0; $i < $len ; $i++) {
            $c = $name[$i];
            if ($c >= 'A' && $c <= 'Z') {
                $rn .= '_';
                $c = strtolower($c);
            }
            $rn .= $c;
        }

        return trim($rn, '_');
    }

    /**
     * 下划线转驼峰
     * @param $name
     * @return string
     */
    public function underlineToCamelback($name)
    {
        $parts = explode('_', $name);
        if (empty($parts)) {
            return '';
        }

        $nm = $parts[0];
        for ($i=1; $i < count($parts) ; $i++) {
            $np = $parts[$i];
            if (strlen($np) > 0) {
                $np[0] = strtoupper($np[0]);
                $nm .= $np;
            }
        }

        return $nm;
    }

    /**
     * 批量驼峰转下划线
     * @param $select
     * @return string
     */
    public function changeSelectKeyToUnderline($select)
    {
        $parts = explode(',', $select);
        $parts = array_map([$this, 'camelbackToUnderline'], $parts);

        return implode(',', $parts);
    }

    /**
     *  只处理 [{}, ...]  的模式
     */
    public function changeArrKeyToCamelback($indexArr)
    {
        $rets = [];
        foreach ($indexArr as $arr) {
            $ret = [];
            foreach ($arr as $key => $value) {
                $ret[$this->underlineToCamelback($key)] = $value;
            }
            $rets[] = $ret;
        }

        return $rets;
    }

    public function makeChineseSemi($str)
    {
        $arr = array('０' => '0', '１' => '1', '２' => '2', '３' => '3', '４' => '4',
            '５' => '5', '６' => '6', '７' => '7', '８' => '8', '９' => '9',
            'Ａ' => 'A', 'Ｂ' => 'B', 'Ｃ' => 'C', 'Ｄ' => 'D', 'Ｅ' => 'E',
            'Ｆ' => 'F', 'Ｇ' => 'G', 'Ｈ' => 'H', 'Ｉ' => 'I', 'Ｊ' => 'J',
            'Ｋ' => 'K', 'Ｌ' => 'L', 'Ｍ' => 'M', 'Ｎ' => 'N', 'Ｏ' => 'O',
            'Ｐ' => 'P', 'Ｑ' => 'Q', 'Ｒ' => 'R', 'Ｓ' => 'S', 'Ｔ' => 'T',
            'Ｕ' => 'U', 'Ｖ' => 'V', 'Ｗ' => 'W', 'Ｘ' => 'X', 'Ｙ' => 'Y',
            'Ｚ' => 'Z', 'ａ' => 'a', 'ｂ' => 'b', 'ｃ' => 'c', 'ｄ' => 'd',
            'ｅ' => 'e', 'ｆ' => 'f', 'ｇ' => 'g', 'ｈ' => 'h', 'ｉ' => 'i',
            'ｊ' => 'j', 'ｋ' => 'k', 'ｌ' => 'l', 'ｍ' => 'm', 'ｎ' => 'n',
            'ｏ' => 'o', 'ｐ' => 'p', 'ｑ' => 'q', 'ｒ' => 'r', 'ｓ' => 's',
            'ｔ' => 't', 'ｕ' => 'u', 'ｖ' => 'v', 'ｗ' => 'w', 'ｘ' => 'x',
            'ｙ' => 'y', 'ｚ' => 'z',
            '（' => '(', '）' => ')', '〔' => '[', '〕' => ']', '【' => '[',
            '】' => ']', '〖' => '[', '〗' => ']', '“' => '[', '”' => ']',
            '‘' => '[', '’' => ']', '｛' => '{', '｝' => '}', '《' => '<',
            '》' => '>',
            '％' => '%', '＋' => '+', '—' => '-', '－' => '-', '～' => '-',
            '：' => ':', '。' => '.', '、' => ',', '，' => '.', '、' => '.',
            '；' => ',', '？' => '?', '！' => '!', '…' => '-', '‖' => '|',
            '”' => '"', '’' => '`', '‘' => '`', '｜' => '|', '〃' => '"',
            '　' => ' ','＄'=>'$','＠'=>'@','＃'=>'#','＾'=>'^','＆'=>'&','＊'=>'*',
            '＂'=>'"'
        );

        return strtr($str, $arr);
    }

    /**
     * 判断有联|无联
     * @param $name
     * @param $mobile
     * @return string
     */
    public function getContactStatus($name, $mobile)
    {
        if (!$name || !$mobile) {
            return 'n';
        } elseif (strpos($name, '*') || strpos($mobile, '*')) {  //如果name或 mobile 含有 '*'号
            return 'p';
        } else {
            return 'y';
        }
    }

    /**
     * 正则匹配
     *
     * @param $str
     * @return bool
     */
    public static function matchPattern($pattern, $str)
    {
        if (!preg_match($pattern, $str)) {
            return false;
        }
        return true;
    }

    /**
     * 工资格式化
     * @param  string $raw_salary 工资原文
     * @return array              格式化后的结果
     */
    public static function salaryFormat($raw_salary)
    {
        $raw_salary = str_replace(array('&#x20;', '&nbsp;', ' ', "\r", "\n"), '', trim($raw_salary));
        $salary = strtolower($raw_salary);
        $delimiter = '-';
        $delimiters = array('~', '—');
        foreach ($delimiters as $item) {
            if (false !== strpos($salary, $item)) {
                $delimiter = $item;
                break;
            }
        }

        // 薪资所有的都换算成月薪
        $salary_from = 0;
        $salary_to   = 0;
        $salary_arr = explode('/', $salary);
        $salary_arr = array_map('trim', $salary_arr);
        $units = array(
            '时' => 1,
            '日' => 2,
            '月' => 3,
            '年' => 4,
        );
        $unit = '月';
        // 未找到时间的单位，直接都当成0
        if (isset($salary_arr[1])) {
            switch ($salary_arr[1]) {
                case '年':
                    if (strpos($salary, '万以下元/年')) {
                        $salary_from = 0;
                        $salary_to   = (int)$salary;
                        $salary_to   = floor((10000 * $salary_to) / 12);
                    } elseif (false !== strpos($salary, '万以上元/年')) {
                        $salary_from = (int)$salary;
                        $salary_from = floor((10000 * $salary_from) / 12);
                        $salary_to   = 0;
                    } elseif (false !== strpos($salary, '万元/年') || false !== strpos($salary, '万/年')) {
                        $salary_temp = explode($delimiter, $salary, 2);
                        $salary_from = (int)$salary_temp[0];
                        $salary_to   = isset($salary_temp[1]) ? (int)$salary_temp[1] : 0;
                        $salary_from = floor((10000 * $salary_from) / 12);
                        $salary_to   = floor((10000 * $salary_to) / 12);
                    } elseif (false !== strpos($salary, '元/年')) {
                        $salary_temp = explode($delimiter, $salary, 2);
                        $salary_from = (int)$salary_temp[0];
                        $salary_to   = isset($salary_temp[1]) ? (int)$salary_temp[1] : 0;
                        $salary_from = floor($salary_from / 12);
                        $salary_to   = floor($salary_to / 12);
                    }
                    break;
                case '月以下':
                case '月以上':
                case '月':
                case 'month':
                    if (false !== strpos($salary, '千以下/月')) {
                        $salary_from = 0;
                        $salary_to   = (float)$salary * 1000;
                    } elseif (false !== strpos($salary, '以下元/月') || false !== strpos($salary, '以下/月') || false !== strpos($salary, '元/月以下')) {
                        $salary_from = 0;
                        $salary_to   = (float)$salary;
                    } elseif (false !== strpos($salary, '万以上/月')) {
                        $salary_from = (float)$salary * 10000;
                        $salary_to   = 0;
                    } elseif (false !== strpos($salary, '以上元/月') || false !== strpos($salary, '以上/月') || false !== strpos($salary, '元/月以上')) {
                        $salary_from = (int)$salary;
                        $salary_to   = 0;
                    } elseif (false !== strpos($salary, '万元/月') || false !== strpos($salary, '万/月')) {
                        $salary_temp = explode($delimiter, $salary, 2);
                        $salary_from = (float)$salary_temp[0];
                        $salary_to   = isset($salary_temp[1]) ? (float)$salary_temp[1] : 0;
                        $salary_from *= 10000;
                        $salary_to *= 10000;
                    } elseif (false !== strpos($salary, '千元/月') || false !== strpos($salary, '千/月') || false !== strpos($salary, 'k/月')) {
                        $salary_temp = explode($delimiter, $salary, 2);
                        $salary_from = (float)$salary_temp[0];
                        $salary_to   = isset($salary_temp[1]) ? (float)$salary_temp[1] : 0;
                        $salary_from *= 1000;
                        $salary_to *= 1000;
                    } elseif (false !== strpos($salary, '元/月') || false !== strpos($salary, 'rmb/month')) {
                        $salary_temp = explode($delimiter, $salary, 2);
                        $salary_from = (int)$salary_temp[0];
                        $salary_to   = isset($salary_temp[1]) ? (int)$salary_temp[1] : 0;
                    }
                    break;
                case '日':
                case '天':
                    $salary_from = (int)$salary;
                    $salary_from = $salary_from * 30;
                    $salary_to   = $salary_from;
                    break;
                case '时':
                case '小时':
                    $salary_from = (int)$salary;
                    $salary_from = $salary_from * 240;
                    $salary_to   = $salary_from;
                    break;
                default:
                    // Do nothing
            }
        } else {
            // BOSS: 20K-40K
            if (false !== strpos($salary, '以上')) {
                $salary_from = (float)$salary;
                $salary_to = 0;
            } elseif (false !== strpos($salary, '以下')) {
                $salary_from = 0;
                $salary_to = (float)$salary;
            } else {
                if (strpos($salary, '万') !== false) {
                    $salary = (int)$salary * 10000;
                }
                $salary_temp = explode($delimiter, $salary);
                $salary_from = (int)$salary_temp[0];
                $salary_to   = isset($salary_temp[1]) ? (int)$salary_temp[1] : 0;
            }

            if (false !== strpos($salary, 'k')) {
                $salary_from *= 1000;
                $salary_to *= 1000;
            }
        }

        $data = array(
            'salary' => $raw_salary,
            'from' => $salary_from,
            'to' => $salary_to > 0 ? $salary_to : -1,
            'unit' => isset($units[$unit]) ? $units[$unit] : 0,
        );

        return $data;
    }

    /**
     * PHP时间段获取
     * @param type currentMonth、lastMonth、currentWeek、lastWeek
     */
    public static function dateRange($type, $time = false) {
        $currentMonthFirst  = date('Y-m-01', time());
        $nextMonthFirst     = date('Y-m-01', strtotime('+1 month'));

        $res = [];
        switch ($type) {
            case 'currentMonth' :
                $res['start'] = $currentMonthFirst;
                $res['end'] = date('Y-m-d', strtotime($nextMonthFirst)-1);
                break;
            case 'currentWeek':
                $res['start'] = date('Y-m-d', strtotime('this week'));
                $res['end'] = date('Y-m-d', strtotime('next week -1 day'));
                break;
        }

        if ($time) {
            $res['start'] = $res['start'] . ' 00:00:00';
            $res['end'] = $res['end'] . ' 23:59:59';
        }

        return $res;
    }

    /**
     * 获取区间内所有时间节点
     * @param string $type
     * @return array
     */
    public static function getDateByRange($type = 'week')
    {
        switch ($type) {
            case 'week':
                $dateTypeRange = self::dateRange('currentWeek');
                break;
            case 'month':
                $dateTypeRange = self::dateRange('currentMonth');
                break;
        }

        $rangeDate = [];

        $dt  = strtotime($dateTypeRange['start']);
        $end = strtotime($dateTypeRange['end']);
        while ($dt <= $end) {
            $rangeDate[] = date('Y-m-d', $dt);
            $dt = strtotime("+1 day", $dt);
        }

        return $rangeDate;
    }

    /**
     * 日期数组补全,如获取近一周数据,数组正常需要有7个元素,如果查询出的数据不足则补全缺失的日期
     * @param $dateArr 原始日期数组
     * @param string $dateColumn 提取出日期数组中日期列
     * @param string $type 近一周或者近一月(week|month)
     */
    public static function mergeDate(&$dateArr, $type = 'week', $dateColumn = 'login_date', $extraColumn = ['user_cnt' => 0])
    {
        $columnArr = array_column($dateArr, $dateColumn);

        $rangeDate = self::getDateByRange($type);

        foreach ($rangeDate as $d) {
            if ($key = array_search($d, $columnArr) === false) {
                if (empty($extraColumn)) {
                    break;
                } else {
                    $extraColumn[$dateColumn] = $d;
                    $dateArr[] = $extraColumn;
                }
            }
        }

        foreach ($dateArr as $k => $v) {
            $column[$k] = $v[$dateColumn];
        }

        array_multisort($column, SORT_ASC, $dateArr);
    }

    /**
     * 获取客户端IP
     *
     * @return string
     */
    public static function getClientIp()
    {
        // 优先使用真实IP
        if (getenv('REMOTE_ADDR')) {
            $ip = getenv('REMOTE_ADDR');
        } elseif (getenv('HTTP_CLIENT_IP')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } else {
            $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
        }
        return $ip;
    }

    /**
     * Aes转换算法
     *
     * @param $data
     * @param $ssl_key
     * @param $ssl_iv
     * @return string
     */
    public static function encryptByAes($data,$ssl_key,$ssl_iv)
    {
       return openssl_encrypt($data, 'aes-256-cfb', $ssl_key, 0, $ssl_iv);
    }
}