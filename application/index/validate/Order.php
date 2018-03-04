<?php
namespace app\index\validate;

use think\Validate;

class Order extends Validate
{
    protected $rule = [
        'ids'   => "require|array|min:1|checkDatetime",
    ];

    protected $message = [
        'ids.require'   => "系统错误：非法操作！",
        'ids.array'     => "系统错误：非法操作！",
        'ids.min'       => "系统错误：非法操作！",
        'ids.checkDatetime' => "非交易时间段！",
    ];

    protected $scene = [
        "realPosition" => ["ids"]
    ];

    protected function checkDatetime($value, $rule, $data)
    {
        return true;
        return checkStockTradeTime();
    }
}