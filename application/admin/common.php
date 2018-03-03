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
if(!function_exists("dd")){
    function dd($array)
    {
        if(empty($array)) return ['code' => 1, 'msg' => '请传打印参数'];
        echo "<pre>";
        var_dump($array);die();
    }
}
