<?php
namespace app\web\logic;

use think\Db;
use app\web\model\Order;
use app\web\model\User;

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

    public function orderByState($state = 1)
    {
        $where["state"] = is_array($state) ? ["IN", $state] : $state;
        $orders = Order::where($where)->select();
        return $orders ? collection($orders)->toArray() : [];
    }

    // 所有需处理递延的订单（持仓并且过期的）
    public function allDeferOrders()
    {
        $where["state"] = 3;
        $where["free_time"] = ["LT", time()];
        $orders = Order::where($where)->select();
        return $orders ? collection($orders)->toArray() : [];
    }

    // 处理订单自动递延费用
    public function reduceOrderDefer($order)
    {
        Db::startTrans();
        try{
            // 订单过期时间增加
            $data = [
                "order_id"  => $order['order_id'],
                "free_time" => $order["free_time"] + 86400
            ];
            Order::update($data);
            // 用户余额减少
            $user = User::find($order["user_id"]);
            if($user['account'] >= $order['defer']){
                // 余额充足
                $user->setDec("account", $order['defer']);
            }else{
                // 余额不足
                Order::where(["order_id" => $order["user_id"]])->setDec("deposit", $order['defer']);
                $user->setDec("blocked_account", $order['defer']);
            }
            // 资金明细
            $rData = [
                "type" => 1,
                "amount" => $order['defer'],
                "remark" => json_encode(['orderId' => $order['order_id']]),
                "direction" => 2
            ];
            $user->hasManyRecord()->save($rData);
            Db::commit();
            return true;
        }catch (\Exception $e){
            Db::rollback();
            return false;
        }
    }

    // 今天需给牛人返点的所有订单（平仓并盈利的）
    public function todayNiurenRebateOrder(){
        $todayBegin = strtotime(date("Y-m-d 00:00:00"));
        $todayEnd = strtotime(date("Y-m-d 23:59:59"));
        $where["state"] = 2;
        $where["profit"] = ["GT", 0];
        $where["niuren_rebate"] = 0;
        $where["update_at"] = ["BETWEEN", [$todayBegin, $todayEnd]];
        $orders = Order::where($where)->select();
        return $orders ? collection($orders)->toArray() : [];
    }

    // 今天需给代理商返点的所有订单（平仓并盈利的）
    public function todayProxyRebateOrder(){
        $todayBegin = strtotime(date("Y-m-d 00:00:00"));
        $todayEnd = strtotime(date("Y-m-d 23:59:59"));
        $where["state"] = 2;
        $where["profit"] = ["GT", 0];
        $where["proxy_rebate"] = 0;
        $where["update_at"] = ["BETWEEN", [$todayBegin, $todayEnd]];
        $orders = Order::where($where)->select();
        return $orders ? collection($orders)->toArray() : [];
    }
}