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