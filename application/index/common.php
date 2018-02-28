<?php
/**
 * 是否登录
 */
if(!function_exists("isLogin")){
    function isLogin()
    {
        $user = session('user_auth');
        if (empty($user)) {
            return 0;
        } else {
            return session('user_auth_sign') == dataAuthSign($user) ? $user['user_id'] : 0;
        }
    }
}

if(!function_exists("uInfo")){
    function uInfo()
    {
        $user = session("user_info");
        if(!$user){
            $user = model("User")->find(isLogin());
            $user = $user ? $user->toArray() : [];
        }
        return $user;
    }
}