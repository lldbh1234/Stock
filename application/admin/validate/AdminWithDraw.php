<?php
namespace app\admin\validate;

use app\admin\logic\RecordLogic;
use think\Validate;

class AdminWithDraw extends Validate
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
        'do' => ['id', 'state', 'password'],
    ];

    protected function canWithDraw($value)
    {
        $withdraw = (new RecordLogic())->proxyWithdrawById($value);
        if($withdraw){
            return $withdraw['state']['value'] == 0;
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