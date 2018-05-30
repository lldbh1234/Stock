<?php
namespace app\index\job;

use app\index\logic\AdminLogic;
use app\index\logic\OrderLogic;
use app\index\logic\StockLogic;
use app\index\logic\UserLogic;
use think\queue\Job;

class DeferJob
{
    // 自动递延
    public function handleDeferOrder(Job $job, $orderId)
    {
        $isJobDone = $this->handle($orderId);
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

    // 非自动递延（强制平仓）
    public function handleNonAutoDeferOrder(Job $job, $order)
    {
        $isJobDone = $this->handleNonAuto($order);
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

    // 自动递延
    public function handle($orderId)
    {
        $order = (new OrderLogic())->orderById($orderId);
        if($order['is_defer'] && $order['free_time'] < time() && $order['state'] == 3){
            // 停牌股不扣费处理
            $i = 0;
            $halt = false; //未停牌
            while (true){
                $quotation = (new StockLogic())->quotationBySina($order['code']);
                if(isset($quotation[$order['code']]) && !empty($quotation[$order['code']])){
                    $last_px = $quotation[$order['code']]['last_px']; // 最新价
                    $buy_px = $quotation[$order['code']]['buy_px']; // 竞买价，即“买一”报价
                    $sell_px = $quotation[$order['code']]['sell_px']; // 竞卖价，即“卖一”报价
                    if($buy_px > 0 || $sell_px > 0){
                        // 未停牌
                        $halt = false; //未停牌
                        break;
                    }else{
                        // 有可能停牌
                        if($i >= 1){
                            // 重试一次，现价依旧为0，股票停牌
                            $halt = true;
                            break;
                        }else{
                            // 极有可能停牌重试一次
                            $i++;
                            continue;
                        }
                    }
                }else{
                    continue;
                }
            }
            if($halt){
                // 股票停牌，直接递延，不扣递延费
                $holiday = cf("holiday", '');
                $timestamp = workTimestamp(1, explode(',', $holiday), $order["free_time"]);
                $data = [
                    "order_id"  => $order["order_id"],
                    "free_time" => $timestamp,
                ];
                $res = (new OrderLogic())->orderUpdate($data);
                return $res ? true : false;
            }else{
                // 股票未停牌，扣除递延费
                $user = (new UserLogic())->userById($order['user_id']);
                if($user){
                    $managerUserId = $user["parent_id"];
                    $adminId = $user["admin_id"];
                    $adminIds = (new AdminLogic())->ringFamilyTree($adminId);
                    if($user['account'] >= $order['defer']){
                        // 用户余额充足
                        $handleRes = (new OrderLogic())->handleDeferByUserAccount($order, $managerUserId, $adminIds);
                        return $handleRes ? true : false;
                    }/*else if($order['deposit'] >= $order['defer']){ // 取消余额不足，扣除保证金功能
                        // 订单保证金充足
                        $handleRes = (new OrderLogic())->handleDeferByDeposit($order, $managerUserId, $adminIds);
                        return $handleRes ? true : false;
                    }*/else{
                        // 余额不足，无法扣除
                        while (true){
                            $quotation = (new StockLogic())->quotationBySina($order['code']);
                            if(isset($quotation[$order['code']]) && !empty($quotation[$order['code']])){
                                $last_px = $quotation[$order['code']]['last_px']; // 最新价
                                $buy_px = $quotation[$order['code']]['buy_px']; // 平仓按买1价处理
                                if($buy_px > 0) {
                                    $sell_price = $last_px - $buy_px > 0.02 ? $buy_px + 0.02 : $buy_px;//买1如果比股票报价低，超过0.02 就上浮，反之不上调，等值也不上调
                                    $data = [
                                        "order_id" => $order["order_id"],
                                        "sell_price" => $sell_price,
                                        "sell_hand" => $order["hand"],
                                        "sell_deposit" => $sell_price * $order["hand"],
                                        "profit" => ($sell_price - $order["price"]) * $order["hand"],
                                        "state" => 6,
                                        "force_type" => 4, // 强制平仓类型；1-爆仓，2-到达止盈止损，3-非自动递延，4-递延费无法扣除
                                        "update_at" => time()
                                    ];
                                    $res = (new OrderLogic())->orderUpdate($data);
                                    return $res ? true : false;
                                    break;
                                }else{
                                    continue;
                                }
                            }else{
                                continue;
                            }
                        }
                    }
                }
            }
        }
        return true;
    }

    // 非自动递延
    public function handleNonAuto($order)
    {
        if($order['is_defer'] == 0){
            $quotation = (new StockLogic())->quotationBySina($order['code']);
            if(isset($quotation[$order['code']])){
                $data = [
                    "order_id"  => $order["order_id"],
                    "sell_price" => $quotation[$order['code']]['last_px'],
                    "sell_hand" => $order["hand"],
                    "sell_deposit" => $quotation[$order['code']]['last_px'] * $order["hand"],
                    "profit"    => ($quotation[$order['code']]['last_px'] - $order["price"]) * $order["hand"],
                    "state"     => 6,
                    "force_type" => 3 //强制平仓类型；1-爆仓，2-到达止盈止损，3-非自动递延，4-递延费无法扣除
                ];
                $res = (new OrderLogic())->orderUpdate($data);
                return $res ? true : false;
            }else{
                return false;
            }
        }
        return true;
    }
}