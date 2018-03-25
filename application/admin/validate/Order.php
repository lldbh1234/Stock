<?php
namespace app\admin\validate;

use app\admin\logic\DepositLogic;
use app\admin\logic\LeverLogic;
use app\admin\logic\ModeLogic;
use app\admin\logic\OrderLogic;
use think\Validate;

class Order extends Validate
{
    protected $rule = [
        'id'    => 'require|gt:0|canBuy',
        'price' => 'require|float|gt:0|checkPrice',
        'hand'  => 'require|number|gt:0|checkHand',
        'profit' => 'require|float|gt:0|checkProfit',
        'loss'  => 'require|float|gt:0|checkLoss',
        'sell'  => "require|float|gt:0",
        'sell_price' => "require|float|gt:0"
    ];

    protected $message = [
        'id.require'    => '系统提示：非法操作！',
        'id.gt'         => '系统提示：非法操作！',
        'id.canBuy'     => '系统提示：非法操作！',
        'id.canSell'    => '系统提示：非法操作！',
        'id.canForce'   => '系统提示：非法操作！',
        'id.canHedging' => '系统提示：非法操作！',
        'id.canGive'    => '系统提示：非法操作！',
        'id.canWare'    => '系统提示：非法操作！',
        'id.canPosition' => '系统提示：非法操作！',
        'price.require' => '请输入买入价！',
        'price.float'   => '买入价必须为数字！',
        'price.gt'      => '买入价必须大于0！',
        "price.checkPrice" => "实际买入价与委托买入价相差不得超过0.02",
        'hand.require'  => '请输入买入数量！',
        'hand.number'   => '买入数量必须为整数！',
        'hand.gt'       => '买入数量必须大于0！',
        "hand.checkHand" => "买入数量必须为100的倍数！",
        'profit.require' => '请输入止盈金额！',
        'profit.float'  => '止盈金额必须为数字！',
        'profit.gt'     => '止盈金额必须大于0！',
        "profit.checkProfit" => "止盈金额输入错误！",
        'loss.require'  => '请输入止损金额！',
        'loss.float'    => '止损金额必须为数字！',
        'loss.gt'       => '止损金额必须大于0！',
        "loss.checkLoss" => "止损金额输入错误！",
        'sell.require'  => '请输入开盘跌停价！',
        'sell.float'    => '开盘跌停价必须为数字！',
        'sell.gt'       => '开盘跌停价必须大于0！',
        'sell_price.require'  => '请输入实际平仓价！',
        'sell_price.float'    => '实际平仓价必须为数字！',
        'sell_price.gt'       => '实际平仓价必须大于0！',
    ];

    protected $scene = [
        "buyOk" => ["id", "price"],
        "buyFail" => ["id"],
        "sell"  => ["id" => "require|gt:0|canSell"],
        "force" => [
            "id" => "require|gt:0|canForce",
            "sell_price"
        ],
        "hedging" => [
            "id" => "require|gt:0|canHedging",
            "price"
        ],
        "give"  => [
            'id'    => 'require|gt:0|canGive',
            "price" => 'require|float|gt:0',
            "hand",
            "profit",
            "loss"
        ],
        "ware"  => [
            'id'    => 'require|gt:0|canWare',
            "sell"
        ],
        "toPosition" => [
            'id'    => 'require|gt:0|canPosition',
        ],
    ];

    protected function canBuy($value)
    {
        $where = ["order_id" => $value, "state" => 1];
        $order = \app\admin\model\Order::where($where)->find();
        return $order ? true : false;
    }

    protected function canSell($value)
    {
        $where = ["order_id" => $value, "state" => 4];
        $order = \app\admin\model\Order::where($where)->find();
        return $order ? true : false;
    }

    protected function canForce($value)
    {
        $where = ["order_id" => $value, "state" => 6];
        $order = \app\admin\model\Order::where($where)->find();
        return $order ? true : false;
    }

    protected function canHedging($value)
    {
        $myUserIds = \app\admin\model\Admin::userIds();
        $where = ["order_id" => $value, "state" => 3, "is_hedging" => 0];
        $myUserIds ? $where['user_id'] = ["IN", $myUserIds] : null;
        $order = \app\admin\model\Order::where($where)->find();
        return $order ? true : false;
    }

    protected function checkPrice($value, $rule, $data)
    {
        $where = ["order_id" => $data['id'], "state" => 3];
        $order = \app\admin\model\Order::where($where)->find();
        if($order){
            $order = $order->toArray();
            return abs($value - $order['price']) > 0.02 ? false : true;
        }
        return false;
    }

    protected function canGive($value)
    {
        $order = (new OrderLogic())->orderById($value, $state = 6);
        if($order){
            return $order["force_type"] == 1 || $order["force_type"] == 2;
        }
        return false;
    }

    protected function checkHand($value)
    {
        return $value % 100 == 0;
    }

    protected function checkProfit($value, $rule, $data)
    {
        if($value > $data['price']){
            return true;
        }else{
            return "止盈金额不能小于买入价！";
        }
    }

    protected function checkLoss($value, $rule, $data)
    {
        if($value < $data['price']){
            return true;
        }else{
            return "止损金额必须小于买入价！";
        }
    }

    protected function canWare($value)
    {
        $order = (new OrderLogic())->orderById($value, $state = 6);
        if($order){
            return $order["force_type"] == 1 || $order["force_type"] == 2;
        }
        return false;
    }

    protected function canPosition($value)
    {
        $order = (new OrderLogic())->orderById($value, $state = 6);
        return $order ? true : false;
    }
}