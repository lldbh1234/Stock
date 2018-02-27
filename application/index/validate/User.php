<?php
namespace app\index\validate;

use app\index\logic\SmsLogic;
use think\Validate;
use app\index\model\Admin;

class User extends Validate
{
    protected $rule = [
        'orgCode'   => 'require|checkOrgCode',
        'mobile'    => 'require|regex:/^[1][3,4,5,7,8][0-9]{9}$/|checkMobile',
        'password'	=> 'require|length:6,16',
        'rePassword' => 'confirm:password',
        'code'      => 'require|checkCode',
    ];

    protected $message = [
        'orgCode.require'   => '机构编码不能为空！',
        'orgCode.checkOrgCode' => '机构编码填写错误！',
        'mobile.require'    => '手机号码不能为空！',
        'mobile.regex'      => '手机号码格式错误！',
        'mobile.checkMobile' => '手机号码已注册！',
        'code.require'      => '短信验证码不能为空！',
        'code.checkCode'    => '短信验证码错误！',
        'password.require'  => '密码不能为空！',
        'password.length'   => '密码为6-16位字符！',
        'rePassword.confirm' => '俩次输入密码不一致！',
        'act.in'            => '系统提示：非法操作！',
    ];

    protected $scene = [
        'register'  => ['orgCode', 'mobile', 'password', 'rePassword', 'code'],
        'captcha'   => [
            'mobile' => 'require|regex:/^[1][3,4,5,7,8][0-9]{9}$/',
            'act' => "in:register"
        ],
    ];

    protected function checkOrgCode($value)
    {
        $_where = [
            "code"  => $value,
            "role"  => Admin::RING_ROLE_ID
        ];
        $admin = Admin::where($_where)->find();
        return $admin ? true : false;
    }

    protected function checkMobile($value, $rule, $data)
    {
        $ring = Admin::where(["code" => $data['orgCode']])->find();
        $ringAdminIds = Admin::where(["pid" => $ring['pid']])->column("admin_id");
        array_push($ringAdminIds, $ring['pid']);
        $user = \app\index\model\User::where(["username" => $value, "admin_id" => ["IN", $ringAdminIds]])->find();
        return $user ? false : true;
    }

    protected function checkCode($value, $rule, $data)
    {
        $mobile = $data['mobile'];
        return (new SmsLogic())->verify($mobile, $value, "register");
    }
}