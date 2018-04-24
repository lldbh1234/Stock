<?php
namespace app\index\logic;

use app\index\model\Best;

class BestLogic
{
    public function saveBest($data)
    {
        $best = Best::find($data['order_id']);
        if($best){
            return Best::update($data);
        }else{
            return Best::create($data);
        }
    }

    // 删除已平仓的订单
    // $orderIds 所有持仓ID
    public function deleteClosedOrders($orderIds)
    {
        return Best::where(["order_id" => ["NOT IN", $orderIds]])->delete();
    }

    public function bestPositions($limit = 5)
    {
        $order = Best::with("hasOneUser")
                ->field(["*", "`profit`/`deposit`" => "yield"])
                ->order(["yield" => "DESC"])
                ->limit($limit)
                ->select();
        return $order ? collection($order)->toArray() : [];
    }
}