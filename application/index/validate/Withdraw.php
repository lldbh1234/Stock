<?php
namespace app\index\validate;

use app\index\logic\BankLogic;
use app\index\logic\SmsLogic;
use app\index\logic\UserLogic;
use think\Validate;

class Withdraw extends Validate
{
    protected $rule = [
        'mobile' => 'checkDateTime',
        'money' => "require|float|gt:10|checkMoney",
        'card'  => "require|checkCard",
        'code'  => 'require|checkCode',
    ];

    protected $message = [
        'mobile.checkDateTime' => '不在规定提现时间内！',
        'money.require' => '提现金额不能为空！',
        'money.float'   => '提现金额必须为数字！',
        'money.gt'      => '提现金额必须大于10！',
        'money.checkMoney' => '账户余额不足！',
        'card.require'  => '请选择到账银行！',
        'card.checkCard' => '到账银行不存在！',
        'code.require'  => '短信验证码不能为空！',
        'code.checkCode' => '短信验证码错误！',
    ];

    protected $scene = [
        'do' => ['mobile', 'money', 'card', 'code'],
    ];

    public function checkDateTime($value)
    {
        if(date('w') == 0){
            return false;
        }
        if(date('w') == 6){
            return false;
        }
        if(date('G') < 9){
            return false;
        }
        if(date('G') > 17){
            return false;
        }
        if(date('G') == 17 && date('i') > 30){
            return false;
        }
        $holiday = explode(',', cf('holiday', ''));
        if(in_array(date("Y-m-d"), $holiday)){
            return false;
        }
        return true;
    }

    protected function checkCode($value, $rule, $data)
    {
        $user = uInfo();
        $mobile = $user['mobile'];
        $validate = (new SmsLogic())->verify($mobile, $value, "withdraw");
        if($validate){
            if($user['withdraw_state'] == 1){
                return "用户禁止提现，请联系管理员！";
            }
            return true;
        }
        return false;
    }

    protected function checkMoney($value)
    {
        $account = uInfo()['account'];
        return $account >= $value;
    }

    protected function checkCard($value)
    {
        $user = (new UserLogic())->userIncCard(isLogin());
        if($user['has_one_card']){
            return $user['has_one_card']['id'] == $value;
        }
        return false;
    }
}