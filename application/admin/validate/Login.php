<?php
namespace app\admin\validate;

use think\Validate;

class Login extends Validate
{
    protected $rule = [
        'username'  =>  'require|alphaNum',
        'password'	=>  'require',
        'code'	    =>	'require|verify_code',
    ];

    protected $message = [
        'username.require'  =>	'账户不能为空！',
        'username.alphaNum' =>  '登录名格式不正确',//'登录名只能为数字和字母！',
        'password.require'  =>	'密码不能为空！',
        'code.require'      =>	'验证码不能为空！',
    ];

    protected $scene = [
        'login'  =>  ['username', 'password', 'code'],
        'verify_code' => ['username', ],
    ];
    public function verify_code($value)
    {
        if(session('admin_login_code'))
        {
            if(session('admin_login_code') == $value)
            {
                return true;
            }else{
                return '验证码错误！';
            }
        }
        return '验证码已过期，请重新获取！';
    }
}