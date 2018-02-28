<?php
namespace app\index\validate;

use app\index\logic\BankLogic;
use app\index\logic\SmsLogic;
use think\Validate;

class Withdraw extends Validate
{
    protected $rule = [
        'money'     => "checkTime|require|float|gt:10",
        'bank'      => 'require|checkBank',
        'card'      => 'require|max:19',
        'realname'  => 'require|max:16',
        'address'	=> 'require|max:64',
        'code'      => 'require|checkCode',
    ];

    protected $message = [
        'money.require' => '提现金额不能为空！',
        'money.float'   => '提现金额必须为数字！',
        'money.gt'      => '提现金额必须大于10！',
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
        'do' => ['money', 'bank', 'card', 'realname', 'address', 'code'],
    ];

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
}