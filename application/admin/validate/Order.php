<?php
namespace app\admin\validate;

use think\Validate;

class Order extends Validate
{
    protected $rule = [
        'id'    => 'require|gt:0|canBuy',
        'price' => 'require|float|gt:0|checkPrice',
        'code'  => 'require|forceSell',
    ];

    protected $message = [
        'id.require'    => '系统提示：非法操作！',
        'id.gt'         => '系统提示：非法操作！',
        'id.canBuy'     => '系统提示：非法操作！',
        'id.canSell'    => '系统提示：非法操作！',
        'price.require' => '请输入实际买入价！',
        'price.float'   => '实际买入价必须为数字！',
        'price.gt'      => '实际买入价必须大于0！',
        "price.checkPrice" => "实际买入价与委托买入价相差不得超过0.02",
        'code.require'  => '系统提示：非法操作！',
        'code.forceSell' => '系统提示：非法操作！',
    ];

    protected $scene = [
        "buyOk" => ["id", "price"],
        "buyFail" => ["id"],
        "sell"  => ["id" => "require|gt:0|canSell"],
        "force" => [
            "id" => "require|gt:0",
            "code"
        ],
        "hedging" => [
            "id" => "require|gt:0|canHedging",
            "price"
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

    protected function forceSell($value, $rule, $data)
    {
        $orderId = $data['id'];
        $where = ["order_id" => $orderId, "code" => $value, "state" => 3];
        $order = \app\admin\model\Order::where($where)->find();
        return $order ? true : false;
    }
}