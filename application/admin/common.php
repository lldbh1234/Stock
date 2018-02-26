<?php
/**
 * 是否登录
 */
if(!function_exists("isLogin")){
    function isLogin()
    {
        $user = session('admin_auth');
        if (empty($user)) {
            return 0;
        } else {
            return session('admin_auth_sign') == dataAuthSign($user) ? $user['admin_id'] : 0;
        }
    }
}

if(!function_exists("manager")){
    function manager()
    {
        $manager = session("admin_info");
        if(!$manager){
            $manager = model("Admin")->find(isLogin());
        }
        return $manager;
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