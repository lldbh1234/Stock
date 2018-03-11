<?php
namespace app\web\logic;

use app\common\libraries\Sms;

class SmsLogic
{
    protected $smsLib;
    public function __construct()
    {
        $this->smsLib = new Sms();
    }

    public function send($mobile, $act = "register")
    {
        $code = randomString($length = 4, $num = true);
        $res = $this->smsLib->send($mobile, $code, $act);
        return [$res, $code];
    }

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
}