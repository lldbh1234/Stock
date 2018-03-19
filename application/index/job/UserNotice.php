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
        $saveData = [];
        if(isset($data["has_many_fans"]))
        {

            $niurenId = $data["niurenId"];
            $niuren = (new UserLogic())->userIncFans($niurenId);
            if($niuren['has_many_fans']){

                foreach ($niuren['has_many_fans'] as $fans){
                    $saveData[] = [
                        "user_id" => $fans["fans_id"],
                        "title" => "牛人操盘动向",
                        "content" => "您关注的牛人“{$niuren['username']}”有新的操盘动向，请注意查看！",
                    ];
                }
//                model("UserNotice")->saveAll($saveData);
            }
        }else{

                $readyKey = $data['order_id'].'_'.$data['type'];
                if(self::checkNotice($readyKey))
                {
                    $saveData[] = [
                        "user_id"   => $data["user_id"],
                        "title"     => $data["title"],
                        "content"   => $data["content"],
                    ];
                }


        }
        model("UserNotice")->saveAll($saveData);
        return true;
    }
    public function checkNotice($option)
    {
//        $readyKey = $v['order_id'].'_'.$v['type'];
        $readyKey = $option;
        //通知过的不再通知
        if(!cache($readyKey))
        {
            cache($readyKey, 1, ['expire' => 86400]);
            return true;
        }
        return false;
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