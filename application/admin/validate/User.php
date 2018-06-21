<?php
namespace app\admin\validate;

use think\Validate;
use app\admin\model\Admin;
use app\admin\model\User as userModel;
use app\admin\logic\AdminLogic;

class User extends Validate
{
    protected $rule = [
        'mobile'    => 'require|regex:/^10[0-9]{9}$/|checkMobile',
        'user_id'   => 'require|min:1',
        'nickname'  => 'max:32',
        'password'	=> 'require|length:6,16',
        'rePassword' => 'confirm:password',
        'state'     => 'require|in:0,1',
        'parent_id' => 'is_manager',
        'admin_id'  => 'require|checkAdminId',
        'money'     => 'require|float|gt:0',
        'remark'    => 'max:255'
    ];

    protected $message = [
        'mobile.require'    => '手机号码不能为空！',
        'mobile.regex'      => '手机号码格式错误！',
        'mobile.checkMobile' => '手机号码已注册！',
        'user_id.require'   => '系统提示：非法操作！',
        'user_id.min'       => '系统提示：非法操作！',
        'password.require'  => '密码不能为空！',
        'password.length'   => '密码为6-16位字符！',
        'rePassword.confirm' => '俩次输入密码不一致！',
        'nickname.max'      => '昵称最大32位字符！',
        'state.require'     => '系统提示：非法操作！',
        'state.in'          => '系统提示：非法操作！',
        'parent_id.is_manager' => '系统提示：当前填写邀请人不是经纪人',
        'admin_id.require'  => '请选择上级微圈！',
        'admin_id.checkAdminId' => '上级微圈选择错误！',
        'money.require'     => '请输入赠送金额！',
        'money.float'       => '赠送金额为数字！',
        'money.gt'          => '赠送金额必须大于0！',
        'remark.max'        => '备注最大255个字符！',
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
            'password',
        ],
        'give' => [
            'user_id',
            'money' => 'require|float',
            'remark'
        ],
        'create_virtual'  => ['mobile', 'password', 'rePassword', 'admin_id', 'state'],
        'modify_virtual'  => [
            'user_id',
            'password' => "length:6,16",
            'nickname',
            'admin_id',
            'state'
        ],
    ];

    public function is_manager($value)
    {
        if(empty($value)) return true;
        $user_info = userModel::where(['user_id' => $value])->find()->toArray();
        return $user_info && $user_info['is_manager']!='-1' ? true : false;
    }

    public function checkMobile($value, $rule, $data)
    {
        $_adminLogic = new AdminLogic();
        $ring = $_adminLogic->adminById($data['admin_id']);
        $ringAdminIds = Admin::where(["pid" => $ring['pid']])->column("admin_id");
        array_push($ringAdminIds, $ring['pid']);
        $user = userModel::where(["username" => $value, "admin_id" => ["IN", $ringAdminIds]])->find();
        return $user ? false : true;
    }

    public function checkAdminId($value)
    {
        $_adminLogic = new AdminLogic();
        $ring = $_adminLogic->adminById($value);
        return $ring && $ring['role'] == Admin::RING_ROLE_ID;
    }
}