<?php
namespace app\index\job;

use app\index\logic\OrderLogic;
use app\index\logic\RebateLogic;

class RebateJob
{
    protected $_logic;
    public function __construct()
    {
        $this->_logic = new RebateLogic();
    }

    // 跟买订单
    public function handleFollowOrder(Job $job, $data)
    {
        $isJobDone = $this->handle($data);
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

    public function handleFollow($data)
    {
        $profit = $data['money'];
        $orderId = $data['order_id'];
        $niurenOrderId = $data['follow_id'];
        $order = (new OrderLogic())->orderById($niurenOrderId);
        $niurenUserId = $order['user_id'];
        $handleRes = $this->_logic->handleNiurenRebate($niurenUserId, $orderId, $profit);
        return $handleRes ? true : false;
    }
}