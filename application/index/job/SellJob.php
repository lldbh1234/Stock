<?php
namespace app\index\job;

use app\index\logic\OrderLogic;
use app\index\logic\SmsLogic;
use app\index\logic\StockLogic;
use app\index\logic\UserLogic;
use think\Queue;
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
                $buyPx = $quotation[$order['code']]['buy_px']; //买1价
                $sellPx = $quotation[$order['code']]['sell_px']; //卖1价
                $buyPx = $buyPx == 0 ? $lastPx : $buyPx; //买1价为0时按现价处理
                if($buyPx > 0 && $sellPx > 0){
                    //$lossTotal = ($order['price'] - $lastPx) * $order['hand']; //损失总金额
                    $lossTotal = ($order['price'] - $buyPx) * $order['hand']; //止损按买1
                    if($lossTotal >= $order['deposit']){
                        // 爆仓
                        $data = [
                            "order_id"  => $order["order_id"],
                            "sell_price" => $buyPx,
                            "sell_hand" => $order["hand"],
                            "sell_deposit" => $buyPx * $order["hand"],
                            "profit"    => ($buyPx - $order["price"]) * $order["hand"],
                            "state"     => 6,
                            "force_type" => 1, // 强制平仓类型；1-爆仓，2-到达止盈止损，3-非自动递延，4-递延费无法扣除
                            "update_at" => time()
                        ];
                        $res = (new OrderLogic())->orderUpdate($data);
                        return $res ? true : false;
                    }else{
                        //$lastPx >= $order['stop_profit_price']
                        if($sellPx >= $order['stop_profit_price']){ //止盈按卖1
                            // 到达止盈
                            //$sellPrice = $order['stop_profit_price'];
                            //$sellPrice = $lastPx;
                            $sellPrice = $sellPx; //止盈按卖1
                            $data = [
                                "order_id"  => $order["order_id"],
                                "sell_price" => $sellPrice,
                                "sell_hand" => $order["hand"],
                                "sell_deposit" => $sellPrice * $order["hand"],
                                "profit"    => ($sellPrice - $order["price"]) * $order["hand"],
                                "state"     => 6,
                                "force_type" => 2, // 强制平仓类型；1-爆仓，2-到达止盈止损，3-非自动递延，4-递延费无法扣除
                                "update_at" => time()
                            ];
                            $res = (new OrderLogic())->orderUpdate($data);
                            return $res ? true : false;
                        }elseif ($buyPx <= $order['stop_loss_price']){ //止损按买1
                            // 到达止损
                            //$sellPrice = $order['stop_loss_price'];
                            //$sellPrice = $lastPx;
                            $sellPrice = $buyPx; //止损按买1
                            $data = [
                                "order_id"  => $order["order_id"],
                                "sell_price" => $sellPrice,
                                "sell_hand" => $order["hand"],
                                "sell_deposit" => $sellPrice * $order["hand"],
                                "profit"    => ($sellPrice - $order["price"]) * $order["hand"],
                                "state"     => 6,
                                "force_type" => 2, // 强制平仓类型；1-爆仓，2-到达止盈止损，3-非自动递延，4-递延费无法扣除
                                "update_at" => time()
                            ];
                            $res = (new OrderLogic())->orderUpdate($data);
                            return $res ? true : false;
                        }else{
                            // 止损提醒
                            $realLoss = $order['price'] - $buyPx; // 实际损失的钱
                            $planLoss = $order['price'] - $order['stop_loss_price']; //预计损失的钱
                            $lossRate = $realLoss / $planLoss * 100;
                            if($lossRate >= 70){ //实际损失达到预计损失的70%短信提醒
                                $smsData = [
                                    "Act"   => "Loss",
                                    "Code"  => $order['code'],
                                    "UserId" => $order['user_id'],
                                    "OrderId" => $order['order_id'],
                                ];
                                Queue::push('app\index\job\UserNotice@smsNotice', $smsData, null);
                            }
                            return true;
                        }
                    }
                }
                return true;
            }else{
                return false;
            }
        }
        return true;
    }
}