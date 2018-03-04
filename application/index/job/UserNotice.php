<?php
namespace app\index\job;

use app\index\logic\UserLogic;
use think\queue\Job;

class UserNotice
{
    /**
     * 系统内通知
     */
    public function systemNotice(Job $job, $data){
        $isJobDone = $this->sendSystem($data);
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

    /**
     * 短信通知
     */
    public function smsNotice(Job $job, $data){
        $isJobDone = $this->sendSms($data);
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

    public function sendSystem($data)
    {
        $niurenId = $data["niurenId"];
        $niuren = (new UserLogic())->userIncFans($niurenId);
        if($niuren['has_many_fans']){
            $saveData = [];
            foreach ($niuren['has_many_fans'] as $fans){
                $saveData[] = [
                    "user_id" => $fans["fans_id"],
                    "title" => "牛人操盘动向",
                    "content" => "您关注的牛人“{$niuren['username']}”有新的操盘动向，请注意查看！",
                ];
            }
            model("UserNotice")->saveAll($saveData);
        }
        return true;
    }

    /**
     * 发送短信
     * @param $data
     * @return bool
     */
    public function sendSms($data)
    {
        return true;
    }
}