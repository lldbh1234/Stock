<?php
namespace app\admin\validate;

use app\admin\logic\AdminLogic;
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
        'pid'       => 'require|gt:0|checkParent',
        'nickname'  => 'max:32',
        'mobile'    => 'require|unique:admin|regex:/^[1][3,4,5,7,8][0-9]{9}$/',
        //'role'      => 'require|checkRole',
        'status'    => 'require|in:0,1',
        'id'        => 'require|min:1|checkId',
        'number'    => 'require|float|gt:0',
        'point'     => 'require|float|max:100',
        'jiancang_point' => 'require|float|max:100',
        'defer_point' => 'require|float|max:100',
        'name'      => 'require|max:64',
        'domain'    => [
            'require',
            'regex' => '/^([a-z0-9]+([a-z0-9-]*(?:[a-z0-9]+))?.)?[a-z0-9]+([a-z0-9-]*(?:[a-z0-9]+))?(\.us|\.tv|\.org\.cn|\.org|\.net\.cn|\.net|\.mobi|\.me|\.la|\.info|\.hk|\.gov\.cn|\.edu|\.com\.cn|\.com|\.co\.jp|\.co|\.cn|\.cc|\.biz)$/i',
            'max:64'
        ],
        'appid'     => 'require|alphaNum|max:64',
        'appsecret' => 'require|alphaNum|max:64',
        'token'     => 'alphaNum|max:64',
        'sign_name' => 'max:64',
        'sms_username' => 'alphaNum|max:64',
        'sms_password' => 'alphaNum|max:64',
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
        'pid.require'       => '请选择上级！',
        'pid.gt'            => '请选择上级！',
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
        'point.require'     => '请输入盈利返点比率！',
        'point.float'       => '盈利返点比率为数字！',
        'point.max'         => '盈利返点比率最大为100！',
        'jiancang_point.require' => '请输入建仓费返点比率！',
        'jiancang_point.float' => '建仓费返点比率为数字！',
        'jiancang_point.max' => '建仓费返点比率最大为100！',
        'defer_point.require' => '请输入递延费返点比率！',
        'defer_point.float' => '递延费返点比率为数字！',
        'defer_point.max'   => '递延费返点比率最大为100！',
        'name.require'      => '微圈名不能为空！',
        'name.max'          => '微圈名最大64个字符！',
        'domain.require'    => '域名不能为空！',
        'domain.regex'      => '域名不是有效域名！',
        'domain.max'        => '域名最大64个字符！',
        'appid.require'     => '公众号APPID不能为空！',
        'appid.alphaNum'    => '公众号APPID格式错误！',
        'appid.max'         => '公众号APPID最大64个字符！',
        'appsecret.require' => '公众号秘钥不能为空！',
        'appsecret.alphaNum' => '公众号秘钥格式错误！',
        'appsecret.max'     => '公众号秘钥最大64个字符！',
        'token.alphaNum'    => 'TOKEN格式错误！',
        'token.max'         => 'TOKEN最大64个字符！',
        'sign_name.max'     => '签名最大64个字符！',
        'sms_username.alphaNum' => '短信用户名格式错误！',
        'sms_username.max'  => '短信用户名最大64个字符！',
        'sms_password.alphaNum' => '短信密码格式错误！',
        'sms_password.max'  => '短信密码最大64个字符！',
    ];

    protected $scene = [
        //'create'  => ['username', 'password', 'password2', 'nickname', 'mobile', 'role', 'status'],
        'create'  => ['username', 'password', 'password2', 'nickname', 'mobile', 'status'],
        'createTeam' => ['username', 'password', 'password2', 'pid', 'nickname', 'mobile', 'status'],
        'modify'  => [
            'admin_id',
            'password' => "length:6,16",
            'nickname',
            'mobile' => 'require|unique:admin,mobile^admin_id|regex:/^[1][3,4,5,7,8][0-9]{9}$/',
            'status'
        ],
        'modifyTeam' => [
            'admin_id',
            'password' => "length:6,16",
            'pid',
            'nickname',
            'mobile' => 'require|unique:admin,mobile^admin_id|regex:/^[1][3,4,5,7,8][0-9]{9}$/',
            'status'
        ],
        'recharge' => ['id', 'number'],
        'wechat' => ['id', 'name', 'domain', 'appid', 'appsecret', 'token', 'sign_name', 'sms_username', 'sms_password'],
        'rebate' => ['id', 'point'],
        'point' => ['id', 'point', 'jiancang_point', 'defer_point'],
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

    public function checkParent($value)
    {
        $referer = $_SERVER['HTTP_REFERER'];
        if(strpos($referer, "settle") !== false){
            return false;
        }elseif(strpos($referer, "operate") !== false){
            $_role = "settle";
        }elseif(strpos($referer, "member") !== false){
            $_role = "operate";
        }elseif(strpos($referer, "ring") !== false){
            $_role = "member";
        }else{
            return false;
        }
        $parents = (new AdminLogic())->teamAdminsByRole($_role);
        $parentIds = array_column($parents, "admin_id");
        return in_array($value, $parentIds) ? true : false;
    }
}