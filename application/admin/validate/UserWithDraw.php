<?php
namespace app\admin\validate;

use app\admin\logic\UserWithdrawLogic;
use think\Validate;

class UserWithDraw extends Validate
{
    protected $rule = [
        'id'        => 'require|canWithDraw',
        'state'     => 'require|in:-1,1',
        'password'  => 'require|checkConfirm'
    ];

    protected $message = [
        'id.require'   => '系统提示：非法操作！',
        'id.canWithDraw'    => '系统提示：非法操作！',
        'state.require'     => '系统提示：非法操作！',
        'state.in'          => '系统提示：非法操作！',
        'password.require'  => '系统提示:请输入密钥!',
    ];

    protected $scene = [
        'user_withdraw' => ['id', 'state', 'password'],
    ];

    protected function canWithDraw($value)
    {
        $withdraw = (new UserWithdrawLogic())->withdrawById($value);
        if($withdraw){
            return $withdraw['state'] == 0;
        }
        return false;
    }
    public function checkConfirm($value)
    {
        if(spPassword($value) == config('withdraw_pwd'))
        {
            return true;
        }
        return '请输入正确的密钥';

    }
}