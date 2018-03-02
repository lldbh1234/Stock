<?php
namespace app\admin\validate;

use think\Validate;

class User extends Validate
{
    protected $rule = [
        'user_id'   => 'require|min:1',
        'nickname'  => 'max:32',
        'password'	=> 'require|length:6,16',
        'state'     => 'require|in:0,1',
        'parent_id' => 'is_manager',
    ];

    protected $message = [
        'user_id.require'   => '系统提示：非法操作！',
        'user_id.min'       => '系统提示：非法操作！',
        'password.require'  => '密码不能为空！',
        'password.length'   => '密码为6-16位字符！',
        'nickname.max'      => '昵称最大32位字符！',
        'state.require'     => '系统提示：非法操作！',
        'state.in'          => '系统提示：非法操作！',
        'parent_id.is_manager' => '系统提示：当前填写邀请人不是经纪人',
    ];

    protected $scene = [
        'modify'  => [
            'user_id',
            'nickname',
            'state',
            'parent_id',
        ],
        'modify_pwd' => [
            'user_id',
            'password' => "length:6,16",
        ],
    ];
    public function is_manager($value)
    {
        if(empty($value)) return true;
        $user_info = \app\admin\model\User::where(['user_id' => $value])->find()->toArray();

        return $user_info && $user_info['is_manager']!='-1' ? true : false;
    }

}