<?php
namespace app\index\validate;

use think\Validate;
use app\index\logic\AdminLogic;
use app\index\logic\SmsLogic;

class Manager extends Validate
{
    protected $rule = [
        'mobile'    => 'require|regex:/^[1][3,4,5,7,8,9][0-9]{9}$/',
        'realname'  => "require|max:32|chs",
        'coding'    => 'require|checkCoding',
        'code'      => 'require|checkCode|checkUserAmount',
    ];

    protected $message = [
        'mobile.require'    => '手机号码不能为空！',
        'mobile.regex'      => '手机号码格式错误！',
        'realname.require'  => '真实姓名不能为空！',
        'realname.max'      => '真实姓名最大16个字符！',
        'realname.chs'      => '真实姓名只能为汉字',
        'coding.require'    => '机构编码不能为空！',
        'coding.checkCoding' => '机构编码不存在！',
        'code.require'      => '短信验证码不能为空！',
        'code.checkCode'    => '短信验证码错误！',
        'code.checkUserAmount' => '账户余额不足，请充值！',
    ];

    protected $scene = [
        'register' => ['mobile', 'realname', 'coding', 'code'],
    ];

    protected function checkCoding($value)
    {
        $admin = (new AdminLogic())->adminByCode($value);
        return $admin ? true : false;
    }

    protected function checkCode($value, $rule, $data)
    {
        $mobile = $data['mobile'];
        return (new SmsLogic())->verify($mobile, $value, "manager");
    }

    protected function checkUserAmount($value, $rule, $data)
    {
        $poundage = cf('manager_poundage', 88);
        return uInfo()['account'] >= $poundage;
    }
}