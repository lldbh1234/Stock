<?php
namespace app\index\job;

use app\index\logic\OrderLogic;
use think\queue\Job;

class DeferJob
{
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

    public function handle($orderId)
    {
        $order = (new OrderLogic())->orderById($orderId);
        if($order['is_defer'] && $order['free_time'] < time()){
            $handle = (new OrderLogic())->reduceOrderDefer($order);
            if(!$handle){
                $Coercions = cache("Coercion_Selling");
                $Coercions[] = $order["order_id"];
                cache("Coercion_Selling", $Coercions);
            }
        }
        return true;
    }
}