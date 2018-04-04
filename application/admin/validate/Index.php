<?php
namespace app\admin\validate;

use app\admin\logic\AdminLogic;
use think\Validate;

class Index extends Validate
{
    protected $rule = [
        'old'   => 'require|checkPassword',
        'new'   => 'require|length:6,16',
        "reNew" => "confirm:new",
    ];

    protected $message = [
        'old.require'   => '请输入原密码！',
        'old.checkPassword' => '原密码不正确！',
        'new.require'   => '请输入新密码！',
        'new.length'    => '新密码为6-16位字符！',
        'reNew.confirm' => '新密码输入不一致！',
    ];

    protected $scene = [
        "password" => ["old", "new", "reNew"],
    ];

    public function checkPassword($value)
    {
        $manager = (new AdminLogic())->adminById(isLogin());
        return spComparePassword($value, $manager['password']);
    }
}