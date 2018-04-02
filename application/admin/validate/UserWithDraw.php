<?php
namespace app\admin\validate;

use app\admin\logic\UserWithdrawLogic;
use think\Validate;

class UserWithDraw extends Validate
{
    protected $rule = [
        'id'        => 'require|canWithDraw',
        'state'     => 'require|in:-1,1',
    ];

    protected $message = [
        'id.require'   => '系统提示：非法操作！',
        'id.canWithDraw'    => '系统提示：非法操作！',
        'state.require'     => '系统提示：非法操作！',
        'state.in'          => '系统提示：非法操作！',
    ];

    protected $scene = [
        'user_withdraw' => ['id', 'state'],
    ];

    protected function canWithDraw($value)
    {
        $withdraw = (new UserWithdrawLogic())->withdrawById($value);
        if($withdraw){
            return $withdraw['state'] == 0;
        }
        return false;
    }
}