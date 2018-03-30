<?php
namespace app\index\validate;

use think\Validate;

class Recharge extends Validate
{
    protected $rule = [
        'amount'    => 'require|float|gt:0',
    ];

    protected $message = [
        'amount.require' => '请选择充值金额！',
        'amount.float'   => '充值金额必须为数字！',
        'amount.gt'      => '充值金额必须大于10！',
    ];

    protected $scene = [
        'do' => ['amount'],
    ];
}