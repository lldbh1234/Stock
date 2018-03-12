<?php
namespace app\admin\validate;

use think\Validate;
use app\admin\model\Admin;

class UserManager extends Validate
{
    protected $rule = [
        'id'        => 'require|min:1',
        'state'     => 'require|in:0,1,2',
        'user_id'   => 'require',
        'point'     => 'require|float|max:100',
        'jiancang_point' => 'require|float|max:100',
        'defer_point' => 'require|float|max:100',
    ];

    protected $message = [
        'id.require'    => '系统提示：非法操作！',
        'id.min'        => '系统提示：非法操作！',
        'id.gt'         => '系统提示：非法操作！',
        'state.require' => '系统提示：非法操作！',
        'state.in'      => '系统提示：非法操作！',
        'user_id.require' => '系统提示：非法操作！',
        'point.require' => '请输入盈利返点比率！',
        'point.float'   => '盈利返点比率为数字！',
        'point.max'     => '盈利返点比率最大为100！',
        'jiancang_point.require' => '请输入建仓费返点比率！',
        'jiancang_point.float'  => '建仓费返点比率为数字！',
        'jiancang_point.max'    => '建仓费返点比率最大为100！',
        'defer_point.require'   => '请输入递延费返点比率！',
        'defer_point.float'     => '递延费返点比率为数字！',
        'defer_point.max'       => '递延费返点比率最大为100！',
    ];

    protected $scene = [
        'user_audit' => ['id', 'state','user_id'],
        'point' => [
            'id' => 'require|gt:0|checkId',
            'point',
            'jiancang_point',
            'defer_point'
        ]
    ];

    protected function checkId($value)
    {
        $where = ["id" => $value];
        $myUserIds = Admin::userIds();
        $myUserIds ? $where['user_id'] = ["IN", $myUserIds] : null;
        $manager = \app\admin\model\UserManager::where($where)->find();
        return $manager ? true : false;
    }
}