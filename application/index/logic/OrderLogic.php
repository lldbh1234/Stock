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

    public function getAllBy($where=[])
    {
        $map = [];
        if(!empty($where) && is_array($where))
        {
            foreach($where as $k => $v)
            {
                $map[$k] = $v;
            }
        }
        return Order::where($map)->select();

    }

}