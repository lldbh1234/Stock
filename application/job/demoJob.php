<?php
/**
 * Created by PhpStorm.
 * User: bruce
 * Date: 18/3/4
 * Time: 下午11:40
 */

namespace app\job;

use think\Db;
use think\queue\Job;

class demoJob{
    /**
     * 任务类不需继承任何类，如果这个类只有一个任务，那么就只需要提供一个fire方法就可以了，如果有多个小任务，就写多个方法，
     * 每个方法会传入两个参数 think\queue\Job $job（当前的任务对象） 和 $data（发布任务时自定义的数据）
     * 还有个可选的任务失败执行的方法 failed 传入的参数为$data（发布任务时自定义的数据）
     * @param Job $job
     * @param $data
     */
    public function fire(Job $job, $data)
    {
        $isJobDone = $this->send($data);
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
     * 根据消息中的数据进行实际的业务处理
     * @param array|mixed    $data     发布任务时自定义的数据
     * @return boolean                 任务执行的结果
     */
    private function send($data)
    {
        $data = json_decode($data, true);
        $result =  Db::name('test')->insert([
            'name' => $data['name']
        ]);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

}