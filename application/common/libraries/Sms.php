<?php
namespace app\common\libraries;

use chuanglan\ChuanglanSMS;

class Sms
{
    public function send($mobile, $code, $act = "register")
    {
        $engine = new ChuanglanSMS();
        $result = $engine->sendSMS($mobile, '【58好策略】您好，您的验证码是' . $code);
        $result = $engine->execResult($result);
        if (isset($result[1]) && $result[1] == 0) {
            $sessKey = "{$mobile}_{$act}";
            session($sessKey, $code);
            return [true, $code];
        } else {
            return [false, $result[1]];
        }
    }
}