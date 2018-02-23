<?php
namespace app\admin\validate;

use think\Validate;
use app\admin\model\Role;
use app\admin\model\Admin;

class Team extends Validate
{
    protected $rule = [
        'admin_id'  => 'require|min:1',
        'username'  => 'require|unique:admin|length:2,32',
        'password'	=> 'require|length:6,16',
        'password2' => 'confirm:password',
        'nickname'  => 'max:32',
        'mobile'    => 'require|unique:admin|regex:/^[1][3,4,5,7,8][0-9]{9}$/',
        //'role'      => 'require|checkRole',
        'status'    => 'require|in:0,1',
        'id'        => 'require|min:1|checkId',
        'number'    => 'require|float|gt:0',
    ];

    protected $message = [
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
        'id.require'        => '系统提示：非法操作！',
        'id.min'            => '系统提示：非法操作！',
        'id.checkId'        => '系统提示：非法操作！',
        'number.require'    => '请输入充值金额！',
        'number.float'      => '充值金额为数字！',
        'number.gt'         => '充值金额必须大于0！',
    ];

    protected $scene = [
        //'create'  => ['username', 'password', 'password2', 'nickname', 'mobile', 'role', 'status'],
        'create'  => ['username', 'password', 'password2', 'nickname', 'mobile', 'status'],
        'modify'  => [
            'admin_id',
            'password' => "length:6,16",
            'nickname',
            'mobile' => 'require|unique:admin,mobile^admin_id|regex:/^[1][3,4,5,7,8][0-9]{9}$/',
            'status'
        ],
        'recharge' => ['id', 'number'],
    ];

    public function checkRole($value)
    {
        $role = Role::find($value);
        return $role ? true : false;
    }

    public function checkId($value)
    {
        $_where = [];
        $referer = $_SERVER['HTTP_REFERER'];
        if(strpos($referer, "settle") !== false){
            $_where['role'] = Admin::SETTLE_ROLE_ID;
        }elseif(strpos($referer, "operate") !== false){
            $_where['role'] = Admin::OPERATE_ROLE_ID;
        }elseif(strpos($referer, "member") !== false){
            $_where['role'] = Admin::MEMBER_ROLE_ID;
        }elseif(strpos($referer, "ring") !== false){
            $_where['role'] = Admin::RING_ROLE_ID;
        }else{
            return false;
        }
        $_where['admin_id'] = $value;
        //$_where['pid'] = manager()['admin_id'];
        $admin = Admin::where($_where)->find();
        return $admin ? true : false;
    }
}