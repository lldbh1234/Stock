<?php
namespace app\admin\validate;

use think\Validate;

class Admin extends Validate
{
    protected $rule = [
        'id'        => 'require|min:1',
        'ids'       => 'require|array|checkIds',
        'admin_id'  => 'require|min:1',
        'username'  => 'require|unique:admin|length:4,32',
        'password'	=> 'require|length:6,16',
        'password2' => 'confirm:password',
        'nickname'  => 'max:32',
        'mobile'    => 'require|unique:admin|regex:/^[1][3,4,5,7,8][0-9]{9}$/',
        'role'      => 'require|checkRole',
        'status'    => 'require|in:0,1',
    ];

    protected $message = [
        'id.require'        => '系统提示：非法操作！',
        'id.min'            => '系统提示：非法操作！',
        'ids.require'       => '请选择要操作的数据！',
        'ids.array'         => '请选择要操作的数据！',
        'ids.checkIds'      => '请选择要操作的数据！',
        'admin_id.require'  => '系统提示：非法操作！',
        'admin_id.min'      => '系统提示：非法操作！',
        'username.require'  => '登录名不能为空！',
        'username.unique'   => '登录名已经存在！',
        'username.length'   => '登录名为4-32位字符！',
        'password.require'  => '初始密码不能为空！',
        'password.length'   => '初始密码为6-16位字符！',
        'password2.confirm' => '俩次输入密码不一致！',
        'nickname.max'      => '昵称最大32位字符！',
        'mobile.require'    => '手机不能为空！',
        'mobile.unique'     => '手机已经存在！',
        'mobile.regex'      => '手机格式错误！',
        'role.require'      => '请选择所属角色！',
        'role.checkRole'    => '所属角色不存在！',
        'status.require'    => '系统提示：非法操作！',
        'status.in'         => '系统提示：非法操作！',
    ];

    protected $scene = [
        'create'  => ['username', 'password', 'password2', 'nickname', 'mobile', 'role', 'status'],
        'modify'  => [
            'admin_id',
            'password' => "length:6,16",
            'nickname',
            'mobile' => 'require|unique:admin,mobile^admin_id|regex:/^[1][3,4,5,7,8][0-9]{9}$/',
            'role',
            'status'
        ],
        'remove' => ['id'],
        'patch'  => ['ids'],
    ];

    public function checkRole($value)
    {
        $role = \app\admin\model\Role::find($value);
        return $role ? true : false;
    }

    protected function checkIds($value)
    {
        return count($value) > 0;
    }
}