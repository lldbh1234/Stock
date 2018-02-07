<?php
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