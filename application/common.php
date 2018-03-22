<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
/**
 * 密码加密方法
 * @param string $pw 要加密的字符串
 * @param string $key 加密密钥
 * @return string
 */
if(!function_exists("spPassword")){
    function spPassword($pw, $key = '')
    {
        if(empty($key)){
            $key = config("pwd_auth_key");
        }
        $password = "###" . md5(md5("{$key}{$pw}"));
        return $password;
    }
}

/**
 * 密码比较方法,所有涉及密码比较的地方都用这个方法
 * @param string $password 要比较的密码
 * @param string $password_in_db 数据库保存的已经加密过的密码
 * @return boolean 密码相同，返回true
 */
if(!function_exists("spComparePassword")) {
    function spComparePassword($password, $password_in_db)
    {
        return spPassword($password) == $password_in_db;
    }
}

/**
 * 产生随机字符串
 * @param $length 字符串长度
 * @param $num 是否纯数字
 * @return string
 */
if(!function_exists("randomString")) {
    function randomString($length, $num = false)
    {
        if ($num) {
            $code_array = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9");
        } else {
            //"0","1","l","i","o","L","I","O",
            $code_array = array(
                "2", "3", "4", "5", "6", "7", "8", "9",
                "a", "b", "c", "d", "e", "f", "g", "h",
                "j", "k", "m", "n", "p", "q", "r", "s",
                "t", "u", "v", "w", "x", "y", "z",
                "A", "B", "C", "D", "E", "F", "G", "H",
                "J", "K", "M", "N", "P", "Q", "R", "S",
                "T", "U", "V", "W", "X", "Y", "Z"
            );
        }
        $code_length = count($code_array) - 1;
        $code = "";
        for ($i = 0; $i < $length; $i++) {
            $code .= $code_array[mt_rand(0, $code_length)];
        }
        return $code;
    }
}

/**
 * 数据签名认证
 * @param  array  $data 被认证的数据
 * @return string       签名
 */
if(!function_exists("dataAuthSign")){
    function dataAuthSign($data)
    {
        //数据类型检测
        if(!is_array($data)){
            $data = (array)$data;
        }
        ksort($data); //排序
        $code = http_build_query($data); //url编码并生成query字符串
        $sign = sha1($code); //生成签名
        return $sign;
    }
}

if(!function_exists("mobileHide")){
    function mobileHide($mobile)
    {
        return substr_replace($mobile,'****',3,4);
    }
}

if(!function_exists("numberFormat")){
    function numberFormat($number)
    {
        $b=1000;
        $c=10000;
        $d=100000000;
        if ($number>=$b && $number<$c){
            return round($number/$b, 2).'千';
        }else if ($number>=$c && $number<$d){
            return round($number/$c, 2).'万';
        }else {
            return round($number/$d, 2).'亿';
        }
    }
}
if(!function_exists("timeAgo")) {
    /**
     * @param $the_time
     * @return false|string
     * 时间标签
     */
    function timeAgo($the_time)
    {
        $now_time = time();
        $show_time = $the_time;
        $dur = $now_time - $show_time;
        if ($dur < 0) return $the_time;
        if ($dur < 60) return $dur . '秒前';
        if ($dur < 3600) return floor($dur / 60) . '分钟前';
        if ($dur < 86400) return floor($dur / 3600) . '小时前';
        if ($dur < 604800) return floor($dur / 86400) . '天前';//7天前
        return date('Y-m-d', $the_time);
    }
}

if(!function_exists("cf"))
{
    function cf($alias, $default='')
    {
        $value = model("System")->where(["alias" => $alias])->value("val");
        return is_null($value) ? $default : $value;
    }
}

if(!function_exists("workTimestamp")){
    function workTimestamp($length, $holiday = [], $time = null)
    {
        $realLength = 1;
        $time = $time ? : time();
        for($i = 1; $i <= $length;){
            $timestamp = strtotime("+{$realLength}day", $time);
            $realLength++;
            $week = date("w", $timestamp);
            $date = date("Y-m-d", $timestamp);
            if($week == 0 || $week == 6){
                // 周末
                continue;
            }
            if(in_array($date, $holiday)){
                // 节假日
                continue;
            }
            $i++;
        }
        return $timestamp;
    }
}