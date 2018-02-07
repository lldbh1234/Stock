<?php
namespace app\admin\validate;

use think\Validate;

class Login extends Validate
{
    protected $rule = [
        'username'  =>  'require',
        'password'	=>  'require',
        'code'	    =>	'require|captcha',
    ];

    protected $message = [
        'username.require'  =>	'账户不能为空！',
        'password.require'  =>	'密码不能为空！',
        'code.require'      =>	'验证码不能为空！',
        'code.captcha'      =>	'验证码输入错误！',
    ];

    protected $scene = [
        'login'  =>  ['username', 'password', 'code'],
    ];
}