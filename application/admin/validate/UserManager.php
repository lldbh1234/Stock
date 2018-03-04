<?php
namespace app\admin\validate;

use think\Validate;

class UserManager extends Validate
{
    protected $rule = [
        'id'        => 'require|min:1',
        'state'     => 'require|in:0,1,2',
        'user_id'   => 'require'
    ];

    protected $message = [
        'id.require'   => '系统提示：非法操作！',
        'id.min'       => '系统提示：非法操作！',
        'state.require'     => '系统提示：非法操作！',
        'state.in'          => '系统提示：非法操作！',
        'user_id.require'          => '系统提示：非法操作！',
    ];

    protected $scene = [

        'user_audit' => ['id', 'state','user_id'],
    ];

}