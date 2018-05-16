<?php
namespace app\common\libraries;

use chuanglan\ChuanglanSMS;

class Sms
{
    const SING_NAME = "58好策略";

    public function send($mobile, $code, $act = "register")
    {
        $engine = new ChuanglanSMS();
        $result = $engine->sendSMS($mobile, '【' . self::SING_NAME . '】您好，您的验证码是' . $code);
        $result = $engine->execResult($result);
        if (isset($result[1]) && $result[1] == 0) {
            $sessKey = "{$mobile}_{$act}";
            session($sessKey, $code);
            return [true, $code];
        } else {
            return [false, $result[1]];
        }
    }

    // 止损短信提醒
    public function sendLoss($mobile, $vars)
    {
        $engine = new ChuanglanSMS();
        $msg = '【' . self::SING_NAME . '】您的策略{$var}即将达到止损，请即时处理！';
        $vars = is_array($vars) ? implode(',', $vars) : $vars;
        $params = "{$mobile},{$vars}";
        $result = $engine->sendVariableSMS($msg, $params);
        if(!is_null(json_decode($result))){
            $output=json_decode($result,true);
            if(isset($output['code'])  && $output['code']=='0'){
                return [true, true];
            }else{
                return [false, $output['errorMsg']];
            }
        }else{
            return [false, false];
        }
    }

    // 未开启自动递延，短信提醒
    public function sendNonAuto($mobile, $vars)
    {
        $engine = new ChuanglanSMS();
        $msg = '【' . self::SING_NAME . '】您的策略{$var}由于未开启自动递延将于14:40强制平仓，请即时处理！';
        $vars = is_array($vars) ? implode(',', $vars) : $vars;
        $params = "{$mobile},{$vars}";
        $result = $engine->sendVariableSMS($msg, $params);
        if(!is_null(json_decode($result))){
            $output=json_decode($result,true);
            if(isset($output['code'])  && $output['code']=='0'){
                return [true, true];
            }else{
                return [false, $output['errorMsg']];
            }
        }else{
            return [false, false];
        }
    }

    // 自动递延余额不足，短信提醒
    public function sendBalance($mobile, $vars)
    {
        $engine = new ChuanglanSMS();
        $msg = '【' . self::SING_NAME . '】您的策略{$var}由于账户余额不足无法自动递延，将于14:40强制平仓，请即时处理！';
        $vars = is_array($vars) ? implode(',', $vars) : $vars;
        $params = "{$mobile},{$vars}";
        $result = $engine->sendVariableSMS($msg, $params);
        if(!is_null(json_decode($result))){
            $output=json_decode($result,true);
            if(isset($output['code'])  && $output['code']=='0'){
                return [true, true];
            }else{
                return [false, $output['errorMsg']];
            }
        }else{
            return [false, false];
        }
    }
}