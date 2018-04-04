<?php
namespace app\index\job;

use app\index\logic\OrderLogic;
use app\index\logic\StockLogic;
use think\queue\Job;

class SellJob
{
    // 自动递延
    public function handleSellOrder(Job $job, $orderId)
    {
        $isJobDone = $this->handleSell($orderId);
        if ($isJobDone) {
            //成功删除任务
            $job->delete();
        } else {
            //任务轮询4次后删除
            if ($job->attempts() > 3) {
                // 第1种处理方式：重新发布任务,该任务延迟10秒后再执行
                //$job->release(10);
                // 第2种处理方式：原任务的基础上1分钟执行一次并增加尝试次数
                //$job->failed();
                // 第3种处理方式：删除任务
                $job->delete();
            }
        }
    }

    public function handleSell($orderId)
    {
        $order = (new OrderLogic())->orderById($orderId);
        if($order['state'] == 3){
            $quotation = (new StockLogic())->quotationBySina($order['code']);
            if(isset($quotation[$order['code']])){
                $lastPx = $quotation[$order['code']]['last_px']; //最新价
                $lossTotal = ($order['price'] - $lastPx) * $order['hand']; //损失总金额
                if($lossTotal >= $order['deposit']){
                    // 爆仓
                    $data = [
                        "order_id"  => $order["order_id"],
                        "sell_price" => $lastPx,
                        "sell_hand" => $order["hand"],
                        "sell_deposit" => $lastPx * $order["hand"],
                        "profit"    => ($lastPx - $order["price"]) * $order["hand"],
                        "state"     => 6,
                        "force_type" => 1 // 强制平仓类型；1-爆仓，2-到达止盈止损，3-非自动递延，4-递延费无法扣除
                    ];
                    $res = (new OrderLogic())->orderUpdate($data);
                    return $res ? true : false;
                }else{
                    if($lastPx >= $order['stop_profit_price']){
                        // 到达止盈
                        //$sellPrice = $order['stop_profit_price'];
                        $sellPrice = $lastPx;
                        $data = [
                            "order_id"  => $order["order_id"],
                            "sell_price" => $sellPrice,
                            "sell_hand" => $order["hand"],
                            "sell_deposit" => $sellPrice * $order["hand"],
                            "profit"    => ($sellPrice - $order["price"]) * $order["hand"],
                            "state"     => 6,
                            "force_type" => 2 // 强制平仓类型；1-爆仓，2-到达止盈止损，3-非自动递延，4-递延费无法扣除
                        ];
                        $res = (new OrderLogic())->orderUpdate($data);
                        return $res ? true : false;
                    }elseif ($lastPx <= $order['stop_loss_price']){
                        // 到达止损
                        //$sellPrice = $order['stop_loss_price'];
                        $sellPrice = $lastPx;
                        $data = [
                            "order_id"  => $order["order_id"],
                            "sell_price" => $sellPrice,
                            "sell_hand" => $order["hand"],
                            "sell_deposit" => $sellPrice * $order["hand"],
                            "profit"    => ($sellPrice - $order["price"]) * $order["hand"],
                            "state"     => 6,
                            "force_type" => 2 // 强制平仓类型；1-爆仓，2-到达止盈止损，3-非自动递延，4-递延费无法扣除
                        ];
                        $res = (new OrderLogic())->orderUpdate($data);
                        return $res ? true : false;
                    }else{
                        return true;
                    }
                }
            }else{
                return false;
            }
        }
        return true;
    }
}