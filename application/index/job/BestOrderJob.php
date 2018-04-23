<?php
namespace app\index\job;

use app\index\logic\BestLogic;
use app\index\logic\StockLogic;
use think\queue\Job;
use app\index\logic\OrderLogic;

class BestOrderJob
{
    // 自动递延
    public function handleBestOrder(Job $job, $orderId)
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

    public function handle($orderId)
    {
        $order = (new OrderLogic())->orderById($orderId);
        if($order['state'] == 3){
            while (true){
                $quotation = (new StockLogic())->quotationBySina($order['code']);
                if(isset($quotation[$order['code']])){
                    $last_px = $quotation[$order['code']]['last_px']; //现价
                    $buy_px = $quotation[$order['code']]['buy_px']; //买1价
                    $best = [
                        "order_id"  => $order['order_id'],
                        "user_id"   => $order['user_id'],
                        "mode_id"   => $order['mode_id'],
                        "lever"     => $order['lever'],
                        "code"      => $order['code'],
                        "name"      => $order['name'],
                        "full_code" => $order['full_code'],
                        "price"     => $order['price'],
                        "hand"      => $order['hand'],
                        "deposit"   => $order['deposit'],
                        "last_px"   => $last_px,
                        "buy_px"    => $buy_px,
                        "profit"    => ($buy_px - $order['price']) * $order['hand'],
                    ];
                    $res = (new BestLogic())->saveBest($best);
                    return $res ? true : false;
                    break;
                }else{
                    continue;
                }
            }
        }
        return true;
    }
}