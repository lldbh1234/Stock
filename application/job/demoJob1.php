<?php
/**
 * Created by PhpStorm.
 * User: bruce
 * Date: 18/3/4
 * Time: 下午11:40
 */

namespace app\job;

use think\queue\Job;
//如果一个任务类里有多个小任务的话，如上面的例子二，需要用@+方法名app\lib\job\Job2@task1、app\lib\job\Job2@task2
class demoJob1{

    public function task1(Job $job, $data){


    }

    public function task2(Job $job, $data){


    }

    public function failed($data){


    }

}