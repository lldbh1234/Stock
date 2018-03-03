<?php
namespace app\admin\validate;

use think\Validate;

class UserWithDraw extends Validate
{
    protected $rule = [
        'id'        => 'require|min:1',
        'state'     => 'require|in:-1,1',
    ];

    protected $message = [
        'id.require'   => '系统提示：非法操作！',
        'id.min'       => '系统提示：非法操作！',
        'state.require'     => '系统提示：非法操作！',
        'state.in'          => '系统提示：非法操作！',
    ];

    protected $scene = [

        'user_withdraw' => ['id', 'state',],
    ];

}