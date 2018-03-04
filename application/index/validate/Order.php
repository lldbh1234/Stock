<?php
namespace app\index\validate;

use app\index\logic\UserLogic;
use think\Validate;

class Order extends Validate
{
    protected $rule = [
        'ids'   => "require|array|min:1|checkDatetime",
        'id'    => "require|checkDatetime|checkOrder",
        'deposit' => "require|float|gt:0|checkUserAccount",
    ];

    protected $message = [
        'ids.require'   => "系统错误：非法操作！",
        'ids.array'     => "系统错误：非法操作！",
        'ids.min'       => "系统错误：非法操作！",
        'ids.checkDatetime' => "非交易时间段！",
        'id.require'    => "系统错误：非法操作！",
        'id.checkDatetime'  => "非交易时间段！",
        'id.checkOrder' => "系统错误：非法操作！",
        'id.canSelling' => "系统错误：非法操作！",
        'deposit.require' => "请输入补充金额！",
        'deposit.float' => "补充金额必须为数字！",
        'deposit.gt'    => "补充金额必须大于0！",
        'deposit.checkUserAccount' => "账户余额不足，请充值！"
    ];

    protected $scene = [
        "realPosition"  => ["ids"],
        "cancel"    => ["id"],
        "selling"   => [
            "id" => "require|checkDatetime|canSelling"
        ],
        "deposit"   => [
            "id" => "require|checkDatetime|checkDeposit",
            "deposit"
        ],
    ];

    protected function checkDatetime($value, $rule, $data)
    {
        return true;
        return checkStockTradeTime();
    }

    protected function checkOrder($value, $rule, $data)
    {
        $order = (new UserLogic())->userOrderById(isLogin(), $value, [1, 4]);
        return $order ? true : false;
    }

    protected function canSelling($value, $rule, $data){
        $order = (new UserLogic())->userOrderById(isLogin(), $value, 3);
        return $order ? true : false;
    }

    protected function checkDeposit($value, $rule, $data)
    {
        $order = (new UserLogic())->userOrderById(isLogin(), $value, 3);
        return $order ? true : false;
    }

    protected function checkUserAccount($value, $rule, $data){
        return $value <= uInfo()['account'];
    }
}