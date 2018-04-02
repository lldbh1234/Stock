<?php
namespace app\admin\controller;

use app\admin\logic\OrderLogic;
use app\admin\logic\StockLogic;
use app\index\job\DeferJob;
use app\index\job\RebateJob;
use app\index\logic\AdminLogic;
use app\index\logic\RebateLogic;
use app\index\logic\UserLogic;
use think\Controller;
use think\Queue;
use think\Request;

class Test extends Controller
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
    }

    public function test()
    {
        $quotation = (new StockLogic())->stockQuotationBySina("600000");
        dump($quotation);
        exit;
        $order = (new \app\index\logic\OrderLogic())->orderById(4);
        $job = new DeferJob();
        $job->handle($order);
        exit;
        $admins = (new AdminLogic())->ringFamilyTree(6);
        dump($admins);
        $money = 100;
        foreach ($admins as $val){
            dump($money * $val['real_point']);
        }
        exit;
        $order = (new OrderLogic())->getAllBy();
        $c70 = [];
        $c50 = [];
        foreach($order as $k => $v)
        {
            if(cache($v['order_id'].'_70'))
            {
                $c70[] = cache($v['order_id'].'_70');
            }
            if(cache($v['order_id'].'_50'))
            {
                $c50[] = cache($v['order_id'].'_50');
            }
        }
        dump($c70);
        dump($c50);die();
        //两个方法，前者是立即执行，后者是在$delay秒后执行
        //php think queue:listen
        //php think queue:work --daemon（不加--daemon为执行单个任务）
        //php think queue:work --queue helloJobQueue
        $data = json_encode(['name' => 'test']);
        $queue = null;

        Queue::push('app\job\demoJob@fire', $data, $queue);
        echo 'ok';
    }

}