<?php
namespace app\admin\validate;

use app\admin\logic\AdminLogic;
use app\index\logic\BankLogic;
use app\index\logic\SmsLogic;
use app\index\logic\UserLogic;
use think\Validate;

class Withdraw extends Validate
{
    protected $rule = [
        'money' => "require|float|gt:100|checkMoney|checkDateTime",
        'card'  => "require|checkCard",
    ];

    protected $message = [
        'money.checkDateTime' => '不在规定提现时间内！',
        'money.require' => '提现金额不能为空！',
        'money.float'   => '提现金额必须为数字！',
        'money.gt'      => '提现金额必须大于100！',
        'money.checkMoney' => '账户余额不足！',
        'card.require'  => '请选择到账银行！',
        'card.checkCard' => '到账银行不存在！',
    ];

    protected $scene = [
        'do' => ['money', 'card'],
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

    protected function checkMoney($value)
    {
        $admin = (new AdminLogic())->adminById(isLogin());
        return $admin['total_fee'] >= $value;
    }

    protected function checkCard($value)
    {
        $admin = (new AdminLogic())->adminIncCard(isLogin());
        if($admin['has_one_card']){
            return $admin['has_one_card']['id'] == $value;
        }
        return false;
    }
}