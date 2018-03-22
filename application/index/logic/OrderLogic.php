<?php
namespace app\index\logic;

use app\index\model\Admin;
use app\index\model\DeferRecord;
use app\index\model\System;
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

    public function orderUpdate($data)
    {
        return Order::update($data);
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

    // 自动递延，扣除用户余额
    public function handleDeferByUserAccount($order, $managerId, $admins)
    {
        Db::startTrans();
        try{
            // 订单过期时间增加
            $holiday = cf("holiday", '');
            $timestamp = workTimestamp(1, explode(',', $holiday), $order["free_time"]);
            $data = [
                "order_id"  => $order['order_id'],
                "free_time" => $timestamp
            ];
            Order::update($data);
            // 用户余额减少
            $user = User::find($order["user_id"]);
            $user->setDec("account", $order['defer']);
            // 用户资金明细
            $rData = [
                "type" => 1,
                "amount" => $order['defer'],
                "remark" => json_encode(['orderId' => $order['order_id']]),
                "direction" => 2
            ];
            $user->hasManyRecord()->save($rData);
            // 经纪人返点
            if($managerId){
                $manager = User::find($managerId);
                $managerData = $manager->hasOneManager->toArray();
                if(isset($managerData['defer_point']) && $managerData['defer_point'] > 0){
                    if(isset($admins[$managerData['admin_id']])){
                        $ring = $admins[$managerData['admin_id']];
                        $realPoint = $ring['real_defer_point'] * $managerData['defer_point'] / 100;
                        $admins[$managerData['admin_id']]['real_defer_point'] -= $realPoint;
                        if($realPoint > 0){
                            //$rebateMoney = sprintf("%.2f", substr(sprintf("%.3f", $order['defer'] * $managerData['defer_point'] / 100), 0, -1)); //分成金额
                            $rebateMoney = round($order['defer'] * $realPoint, 2);
                            // 经纪人总收入增加
                            $manager->hasOneManager->setInc('income', $rebateMoney);
                            // 经纪人可转收入增加
                            $manager->hasOneManager->setInc('sure_income', $rebateMoney);
                            // 经纪人收入明细
                            $rData = [
                                "money" => $rebateMoney, //返点金额
                                "point" => $managerData['defer_point'], // 返点比例
                                "type"  => 2, // 收入类型：0-直属用户收益分成，1-建仓费分成，2-递延费分成
                                "order_id" => $order['order_id'],
                            ];
                            $manager->hasManyManagerRecord()->save($rData);
                        }
                    }
                }
            }
            // 代理商返点
            foreach ($admins as $admin){
                $realPoint = $admin["real_defer_point"];
                if($realPoint > 0){
                    //$rebateMoney = sprintf("%.2f", substr(sprintf("%.3f", $order['defer'] * $point / 100), 0, -1)); //分成金额
                    $rebateMoney = round($order['defer'] * $realPoint, 2);
                    $admin = Admin::find($admin['admin_id']);
                    // 代理商手续费增加
                    $admin->setInc('total_fee', $rebateMoney);
                    // 代理商收入明细
                    $rData = [
                        "money" => $rebateMoney, //返点金额
                        "point" => $admin['defer_point'], // 返点比例
                        "type"  => 2, // 收入类型：0-用户收益分成，1-建仓费分成，2-递延费分成
                        "order_id" => $order['order_id'],
                    ];
                    $admin->hasManyRecord()->save($rData);
                }
            }
            // 递延费扣除记录
            $rData = [
                "user_id" => $order["user_id"],
                "order_id" => $order['order_id'],
                "money" => $order['defer'],
                "type"  => 0, // 0-余额扣除，1-保证金扣除
            ];
            DeferRecord::create($rData);
            Db::commit();
            return true;
        }catch (\Exception $e){
            Db::rollback();
            return false;
        }
    }

    // 自动递延，扣除订单保证金
    public function handleDeferByDeposit($order, $managerId, $admins)
    {
        Db::startTrans();
        try{
            // 订单过期时间增加，保证金减少
            $holiday = cf("holiday", []);
            $timestamp = workTimestamp(1, explode(',', $holiday), $order["free_time"]);
            $data = [
                "order_id"  => $order['order_id'],
                "free_time" => $timestamp,
                "deposit"   => $order['deposit'] - $order['defer']
            ];
            Order::update($data);
            // 经纪人返点
            if($managerId){
                $manager = User::find($managerId);
                $managerData = $manager->hasOneManager->toArray();
                if(isset($managerData['defer_point']) && $managerData['defer_point'] > 0){
                    $rebateMoney = sprintf("%.2f", substr(sprintf("%.3f", $order['defer'] * $managerData['defer_point'] / 100), 0, -1)); //分成金额
                    // 经纪人总收入增加
                    $manager->hasOneManager->setInc('income', $rebateMoney);
                    // 经纪人可转收入增加
                    $manager->hasOneManager->setInc('sure_income', $rebateMoney);
                    // 经纪人收入明细
                    $rData = [
                        "money" => $rebateMoney,
                        "type"  => 2, // 收入类型：0-直属用户收益分成，1-建仓费分成，2-递延费分成
                        "order_id" => $order['order_id'],
                    ];
                    $manager->hasManyManagerRecord()->save($rData);
                }
            }
            // 代理商返点
            foreach ($admins as $admin){
                $point = $admin["defer_point"];
                if($point > 0){
                    $rebateMoney = sprintf("%.2f", substr(sprintf("%.3f", $order['defer'] * $point / 100), 0, -1)); //分成金额
                    $admin = Admin::find($admin['admin_id']);
                    // 代理商手续费增加
                    $admin->setInc('total_fee', $rebateMoney);
                    // 代理商收入明细
                    $rData = [
                        "money" => $rebateMoney,
                        "type"  => 2, // 收入类型：0-用户收益分成，1-建仓费分成，2-递延费分成
                        "order_id" => $order['order_id'],
                    ];
                    $admin->hasManyRecord()->save($rData);
                }
            }
            // 递延费扣除记录
            $rData = [
                "user_id" => $order["user_id"],
                "order_id" => $order['order_id'],
                "money" => $order['defer'],
                "type"  => 1, // 0-余额扣除，1-保证金扣除
            ];
            DeferRecord::create($rData);
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
        $orders = Order::with("belongsToMode")->where($where)->select();
        return $orders ? collection($orders)->toArray() : [];
    }

    // 今天需建仓费返点的所有订单（今天建仓的）
    public function todayJiancangRebateOrder()
    {
        $todayBegin = strtotime(date("Y-m-d 00:00:00"));
        $todayEnd = strtotime(date("Y-m-d 23:59:59"));
        $where["state"] = 3;
        $where["jiancang_rebate"] = 0;
        $where["create_at"] = ["BETWEEN", [$todayBegin, $todayEnd]];
        $orders = Order::where($where)->select();
        return $orders ? collection($orders)->toArray() : [];
    }
}