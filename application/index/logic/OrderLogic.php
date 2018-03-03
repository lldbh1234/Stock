<?php
namespace app\index\logic;


use app\index\model\Order;

class OrderLogic
{
    public function createOrder($data)
    {
        $res = Order::create($data);
        $pk = model("AiType")->getPk();
        return $res ? $res->$pk : 0;
    }
}