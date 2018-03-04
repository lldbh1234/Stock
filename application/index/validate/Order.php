<?php
namespace app\index\validate;

use app\index\logic\UserLogic;
use think\Validate;

class Order extends Validate
{
    protected $rule = [
        'ids'   => "require|array|min:1|checkDatetime",
        'id'    => "require|checkDatetime|checkOrder"
    ];

    protected $message = [
        'ids.require'   => "系统错误：非法操作！",
        'ids.array'     => "系统错误：非法操作！",
        'ids.min'       => "系统错误：非法操作！",
        'ids.checkDatetime' => "非交易时间段！",
        'id.require'    => "系统错误：非法操作！",
        'id.checkDatetime'  => "非交易时间段！",
        'id.checkOrder' => "系统错误：非法操作！",
    ];

    protected $scene = [
        "realPosition"  => ["ids"],
        "cancel"        => ["id"]
    ];

    protected function checkDatetime($value, $rule, $data)
    {
        return checkStockTradeTime();
    }

    protected function checkOrder($value, $rule, $data)
    {
        $order = (new UserLogic())->userOrderById(isLogin(), $value, [1, 4]);
        return $order ? true : false;
    }
}