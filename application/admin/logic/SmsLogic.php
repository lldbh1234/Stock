<?php
namespace app\admin\logic;

use app\common\libraries\Sms;

class SmsLogic
{
    protected $smsLib;
    public function __construct()
    {
        $this->smsLib = new Sms();
    }

    // 验证码短信
    public function adminLogin($mobile, $ip, $act = "admin_login")
    {
        $code = randomString($length = 4, $num = true);
        return $this->smsLib->adminLogin($mobile, $code, $ip, $act);
    }
    // 验证码短信
    public function send($mobile, $act = "admin_get_k")
    {
        $code = randomString($length = 4, $num = true);
        return $this->smsLib->send($mobile, $code, $act);
    }

    // 校验验证码
    public function verify($mobile, $code, $act = "register")
    {
        $sessKey = "{$mobile}_{$act}";
        $sessCode = session($sessKey);
        if($code == $sessCode){
            session($sessKey, null);
            return true;
        }
        return false;
    }

    public function notice($mobile, $vars, $act = "loss")
    {
        if($act == "loss"){
            return $this->smsLib->sendLoss($mobile, $vars);
        }elseif($act == "nonAuto"){
            return $this->smsLib->sendNonAuto($mobile, $vars);
        }elseif($act == "balance"){
            return $this->smsLib->sendBalance($mobile, $vars);
        }
    }
}