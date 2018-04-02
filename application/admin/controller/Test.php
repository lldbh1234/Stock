<?php
namespace app\admin\controller;

use app\admin\logic\OrderLogic;
use app\admin\logic\StockLogic;
use app\common\payment\paymentLLpay;
use app\index\job\DeferJob;
use app\index\job\RebateJob;
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
        $withdraw = [
            "tradeNo" => "20180402214505",
            "amount" => 30,
            "name"  => "梁健",
            "card" => "6217004220033901731"
        ];
        $html = (new paymentLLpay())->payment($withdraw);
        dump($html);
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