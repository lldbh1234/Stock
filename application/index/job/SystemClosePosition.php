<?php
/**
 * 系统平仓
 */
namespace app\index\job;

use app\admin\logic\OrderLogic;
use think\queue\Job;

class SystemClosePosition
{
    /**
     * 系统平仓
     */
    public function doHandle(Job $job, $data){
        $isJobDone = $this->systemClosePosition($data);
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

    public function systemClosePosition($data)
    {
        if(!empty($data))
        {
            $orderLogic = new OrderLogic();
            $updateArr = [
                'user_id'       => $data['user_id'],
                'order_id'      => $data['order_id'],
                'sell_price'    => $data['sell_price'],
                'sell_hand'     => $data['hand'],
                'sell_deposit'  => $data['sell_deposit'],
                'profit'        => $data['profit'],
                'state'         => $data['state'],
                'force_type'    => $data['force_type'],
//                'title'         => '订单ID【'. $data['order_id'] .'】需止盈强制平仓',
//                'content'       => '订单ID【'. $data['order_id'] .'】需止盈强制平仓',
            ];

            $orderLogic->updateOrder($updateArr);

        }
        return true;
    }

}