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

if(!function_exists("cfgs"))
{
    function cfgs()
    {
        return model("System")->column("val", "alias");
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

if(!function_exists("dd")){
    function dd($array)
    {
        if(empty($array)) return ['code' => 1, 'msg' => '请传打印参数'];
        echo "<pre>";
        var_dump($array);die();
    }
}
