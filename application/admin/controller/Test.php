<?php
namespace app\admin\controller;

use app\admin\logic\OrderLogic;
use app\admin\logic\StockLogic;
use app\common\libraries\Sms;
use app\common\payment\paymentLLpay;
use app\common\quotation\sina;
use app\index\job\DeferJob;
use app\index\job\RebateJob;
use app\index\job\SellJob;
use app\index\logic\AdminLogic;
use app\index\logic\RebateLogic;
use app\index\logic\UserLogic;
use llpay\payment\pay\LLpaySubmit;
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
        $job = new SellJob();
        $res = $job->handleSell(307);
        dump($res);
        exit;
        $managerUserId = 0;
        $adminId = 6;
        $adminIds = (new AdminLogic())->ringFamilyTree($adminId);
        $handleRes = (new RebateLogic())->handleProxyRebate($managerUserId, $adminIds, 999, 500);
        dump($handleRes);
        exit;
        Queue::push('app\index\job\SellJob@handleTestJob', 104, null);
        exit;
        $res = cache("test_loss");
        dump($res);
        exit;
        $_logic = new \app\index\logic\StockLogic();
        $res = $_logic->quotationBySina("600000");
        dump($res);
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