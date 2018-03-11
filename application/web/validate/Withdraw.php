<?php
namespace app\web\validate;

use app\web\logic\BankLogic;
use app\web\logic\SmsLogic;
use think\Validate;

class Withdraw extends Validate
{
    protected $rule = [
        'mobile'    => 'checkDateTime',
        'money'     => "require|float|gt:10|checkMoney",
        'bank'      => 'require|checkBank',
        'card'      => 'require|max:19',
        'realname'  => 'require|max:16',
        'address'	=> 'require|max:64',
        'code'      => 'require|checkCode',
    ];

    protected $message = [
        'mobile.checkDateTime' => '不在规定提现时间内！',
        'money.require' => '提现金额不能为空！',
        'money.float'   => '提现金额必须为数字！',
        'money.gt'      => '提现金额必须大于10！',
        'money.checkMoney' => '账户余额不足！',
        'bank.require'  => '请选择到账银行！',
        'bank.checkBank' => '到账银行错误！',
        'card.require'  => '银行卡号不能为空！',
        'card.max'      => '银行卡号最大19个字符！',
        'realname.require' => '持卡人姓名不能为空！',
        'realname.max'  => '持卡人姓名最大16个字符！',
        'address.require' => '开卡行地址不能为空！',
        'address.max'   => '开卡行地址最大64个字符！',
        'code.require'  => '短信验证码不能为空！',
        'code.checkCode' => '短信验证码错误！',
    ];

    protected $scene = [
        'do' => ['mobile', 'money', 'bank', 'card', 'realname', 'address', 'code'],
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
        $holiday = explode(',', cfgs()['holiday']);
        if(in_array(date("Y-m-d"), $holiday)){
            return false;
        }
        return true;
    }

    protected function checkBank($value)
    {
        $bank = (new BankLogic())->bankByNumber($value);
        return $bank ? true : false;
    }

    protected function checkCode($value, $rule, $data)
    {
        $mobile = uInfo()['mobile'];
        return (new SmsLogic())->verify($mobile, $value, "withdraw");
    }

    protected function checkMoney($value)
    {
        $account = uInfo()['account'];
        return $account >= $value;
    }
}