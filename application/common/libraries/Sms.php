<?php
namespace app\common\libraries;

class Sms
{
    public function send($mobile, $code, $act = "register")
    {
        $sessKey = "{$mobile}_{$act}";
        session($sessKey, $code);
        return true;
    }
}