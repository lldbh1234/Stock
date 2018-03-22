<?php
namespace app\index\logic;

use app\index\model\Admin;
use think\Db;
use app\index\model\User;
use app\index\model\Order;

class RebateLogic
{
    // 处理跟买牛人订单返点 $niurenUserId-牛人用户ID $orderId-返点订单ID $money-盈利金额
    public function handleNiurenRebate($niurenUserId, $orderId, $money)
    {
        Db::startTrans();
        try{
            // 牛人返点率(%)
            $point = cf("niuren_point", 5);
            $rebateMoney = sprintf("%.2f", substr(sprintf("%.3f", $money * $point / 100), 0, -1)); //分成金额
            // 牛人总收入增加
            $niuren = User::find($niurenUserId);
            $niuren->hasOneNiuren->setInc('income', $rebateMoney);
            // 牛人可转收入增加
            $niuren->hasOneNiuren->setInc('sure_income', $rebateMoney);
            // 牛人收入明细
            $rData = [
                "money" => $rebateMoney,
                "type"  => 0,
                "order_id" => $orderId,
            ];
            $niuren->hasManyNiurenRecord()->save($rData);
            // 订单标识为已结算订单
            Order::update(["order_id" => $orderId, "niuren_rebate" => 1]);
            Db::commit();
            return true;
        }catch (\Exception $e){
            Db::rollback();
            return false;
        }
    }

    // $money-系统抽成总金额
    public function handleProxyRebate($managerId, $admins, $orderId, $money)
    {
        Db::startTrans();
        try{
            if($managerId){
                $manager = User::find($managerId);
                $managerData = $manager->hasOneManager->toArray();
                if(isset($managerData['point']) && $managerData['point'] > 0) {
                    if(isset($admins[$managerData['admin_id']])){
                        $ring = $admins[$managerData['admin_id']];
                        $realPoint = $ring['real_point'] * $managerData['point'] / 100;
                        $admins[$managerData['admin_id']]['real_point'] -= $realPoint;
                        if($realPoint > 0){
                            //$rebateMoney = sprintf("%.2f", substr(sprintf("%.3f", $money * $realPoint), 0, -1)); //分成金额
                            $rebateMoney = round($money * $realPoint, 2);
                            // 经纪人总收入增加
                            $manager->hasOneManager->setInc('income', $rebateMoney);
                            // 经纪人可转收入增加
                            $manager->hasOneManager->setInc('sure_income', $rebateMoney);
                            // 经纪人收入明细
                            $rData = [
                                "money" => $rebateMoney, //返点金额
                                "point" => $managerData['point'], // 返点比例
                                "type" => 0, //0-直属用户收益分成, 1-建仓费分成，2-递延费分成
                                "order_id" => $orderId,
                            ];
                            $manager->hasManyManagerRecord()->save($rData);
                        }
                    }
                }
            }
            // 各级代理商返点
            foreach ($admins as $admin){
                $realPoint = $admin["real_point"];
                if($realPoint > 0){
                    //$rebateMoney = sprintf("%.2f", substr(sprintf("%.3f", $money * $realPoint), 0, -1)); //分成金额
                    $rebateMoney = round($money * $realPoint, 2);
                    $admin = Admin::find($admin['admin_id']);
                    // 代理商手续费增加
                    $admin->setInc('total_fee', $rebateMoney);
                    // 代理商收入明细
                    $rData = [
                        "money" => $rebateMoney, //返点金额
                        "point" => $admin['point'], // 返点比例
                        "type"  => 0, // 收入类型：0-用户收益分成，1-建仓费分成，2-递延费分成
                        "order_id" => $orderId,
                    ];
                    $admin->hasManyRecord()->save($rData);
                }
            }
            // 订单标识为已结算订单
            Order::update(["order_id" => $orderId, "proxy_rebate" => 1]);
            Db::commit();
            return true;
        }catch (\Exception $e){
            Db::rollback();
            return false;
        }
    }

    // 建仓费返点
    public function handleJiancangRebate($managerId, $admins, $orderId, $fee)
    {
        Db::startTrans();
        try{
            // 经纪人返点
            if($managerId){
                $manager = User::find($managerId);
                $managerData = $manager->hasOneManager->toArray();
                if(isset($managerData['jiancang_point']) && $managerData['jiancang_point'] > 0){
                    if(isset($admins[$managerData['admin_id']])){
                        $ring = $admins[$managerData['admin_id']];
                        $realPoint = $ring['real_jiancang_point'] * $managerData['jiancang_point'] / 100;
                        $admins[$managerData['admin_id']]['real_jiancang_point'] -= $realPoint;
                        if($realPoint > 0){
                            //$rebateMoney = sprintf("%.2f", substr(sprintf("%.3f", $fee * $managerData['jiancang_point'] / 100), 0, -1)); //分成金额
                            $rebateMoney = round($fee * $realPoint, 2);
                            // 经纪人总收入增加
                            $manager->hasOneManager->setInc('income', $rebateMoney);
                            // 经纪人可转收入增加
                            $manager->hasOneManager->setInc('sure_income', $rebateMoney);
                            // 经纪人收入明细
                            $rData = [
                                "money" => $rebateMoney, //返点金额
                                "point" => $managerData['jiancang_point'], // 返点比例
                                "type"  => 1, // 收入类型：0-直属用户收益分成，1-建仓费分成，2-递延费分成
                                "order_id" => $orderId,
                            ];
                            $manager->hasManyManagerRecord()->save($rData);
                        }
                    }
                }
            }
            // 各级代理商返点
            foreach ($admins as $admin){
                $realPoint = $admin["real_jiancang_point"];
                if($realPoint > 0){
                    //$rebateMoney = sprintf("%.2f", substr(sprintf("%.3f", $fee * $point / 100), 0, -1)); //分成金额
                    $rebateMoney = round($fee * $realPoint, 2);
                    $admin = Admin::find($admin['admin_id']);
                    // 代理商手续费增加
                    $admin->setInc('total_fee', $rebateMoney);
                    // 代理商收入明细
                    $rData = [
                        "money" => $rebateMoney, //返点金额
                        "point" => $admin['jiancang_point'], // 返点比例
                        "type"  => 1, // 收入类型：0-用户收益分成，1-建仓费分成，2-递延费分成
                        "order_id" => $orderId,
                    ];
                    $admin->hasManyRecord()->save($rData);
                }
            }
            // 订单标识建仓费返点已结算
            Order::update(["order_id" => $orderId, "jiancang_rebate" => 1]);
            Db::commit();
            return true;
        }catch (\Exception $e){
            Db::rollback();
            return false;
        }
    }
}