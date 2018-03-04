<?php
namespace app\index\logic;

use think\Db;
use app\index\model\Order;
use app\index\model\User;

class OrderLogic
{
    public function createOrder($data)
    {
        Db::startTrans();
        try{
            $res = Order::create($data);
            $pk = model("Order")->getPk();
            $user = User::find($data['user_id']);
            $user->setDec("account", $data['jiancang_fee'] + $data['deposit']);
            $user->setInc("blocked_account", $data['deposit']);
            $rData = [
                "type" => 4,
                "amount" => $data['deposit'],
                "remark" => json_encode(['orderId' => $res->$pk]),
                "direction" => 2
            ];
            $user->hasManyRecord()->save($rData);
            $rData = [
                "type" => 0,
                "amount" => $data['jiancang_fee'],
                "remark" => json_encode(['orderId' => $res->$pk]),
                "direction" => 2
            ];
            $user->hasManyRecord()->save($rData);
            Db::commit();
            return $res->$pk;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return 0;
        }
    }

    public function getAllBy($where=[], $order = [], $limit=0)
    {
        $map = [];
        if(!empty($where) && is_array($where))
        {
            foreach($where as $k => $v)
            {
                $map[$k] = $v;
            }
        }
        if(!empty($order))
        {
            $data = Order::with(['hasOneUser'])->where($map)->order($order)->select();
        }else{
            $data = Order::with(['hasOneUser'])->where($map)->select();
        }


        return collection($data)->toArray();

    }
    public function getLimit($where=[], $limit=['limit' => 2])
    {
        $map = [];
        if(!empty($where) && is_array($where))
        {
            foreach($where as $k => $v)
            {
                $map[$k] = $v;
            }
        }

        $data = Order::with(['hasOneUser'])->where($map)->order(['create_at' => 'desc'])->limit($limit['limit'])->select();

        return collection($data)->toArray();

    }

    public function getCodesBy($where=[])
    {
        $map = [];
        if(!empty($where) && is_array($where))
        {
            foreach($where as $k => $v)
            {
                $map[$k] = $v;
            }
        }

        return Order::where($map)->column('code');


        return collection($data)->toArray();
    }

    public function countBy($where=[])
    {
        $map = [];
        if(!empty($where) && is_array($where))
        {
            foreach($where as $k => $v)
            {
                $map[$k] = $v;
            }
        }

        return Order::where($map)->count();

    }
    public function orderIdsByUid($uid)
    {
        return Order::where(['user_id' => $uid])->column('order_id');

    }

    public function orderById($orderId)
    {
        $order = Order::find($orderId);
        return $order ? $order->toArray() : [];
    }
}