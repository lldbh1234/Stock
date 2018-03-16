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
        /*$user = session("user_info");
        if(!$user){
            $user = model("User")->find(isLogin());
            $user = $user ? $user->toArray() : [];
            session('user_info', $user);
        }
        return $user;
        */
        $user = model("User")->find(isLogin());
        return $user ? $user->toArray() : [];
    }
}

if(!function_exists("createOrderSn")){
    function createOrderSn()
    {
        //return date("YmdHis") . isLogin() . randomString(6, true);
        return uniqid() . randomString(4, true);
    }
}

if(!function_exists("checkStockTradeTime"))
{
    function checkStockTradeTime()
    {
        if(date('w') == 0){
            return false;
        }
        if(date('w') == 6){
            return false;
        }
        if(date('G') < 9 || (date('G') == 9 && date('i') < 30)){
            return false;
        }
        if(((date('G') == 11 && date('i') > 30) || date('G') > 11) && date('G') < 13){
            return false;
        }
        if(date('G') >= 15){
            return false;
        }
        $holiday = explode(',', cf('holiday', ''));
        if(in_array(date("Y-m-d"), $holiday)){
            return false;
        }
        return true;
    }
}

if(!function_exists("checkSettleTime"))
{
    // 结算时间 工作日 17-23点
    function checkSettleTime()
    {
        if(date('w') == 0){
            return false;
        }
        if(date('w') == 6){
            return false;
        }
        if(date('G') < 16){
            return false;
        }
        if(date('G') > 23){
            return false;
        }
        $holiday = explode(',', cf('holiday', ''));
        if(in_array(date("Y-m-d"), $holiday)){
            return false;
        }
        return true;
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