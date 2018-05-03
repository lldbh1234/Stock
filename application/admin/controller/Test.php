<?php
namespace app\admin\controller;

use app\admin\logic\OrderLogic;
use app\admin\logic\StockLogic;
use app\admin\model\UserGive;
use app\admin\model\UserRecord;
use app\admin\model\UserWithdraw;
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
        $stocks = (new UserLogic())->userOptional(19);
        if($stocks){
            $codes = array_column($stocks, "code");
            $lists = (new \app\index\logic\StockLogic())->simpleData($codes);
            array_filter($stocks, function(&$item) use ($lists){
                $item['quotation'] = isset($lists[$item['code']]) ? $lists[$item['code']] : 0;
            });
        }
        dump($stocks);
        exit;
        set_time_limit(0);
        $lists = UserRecord::group("user_id")->column("user_id");
        foreach ($lists as $value){
            $_lists = UserRecord::where(["user_id" => $value])->order(["create_at" => "ASC"])->select();
            $_lists = collection($_lists)->toArray();
            foreach ($_lists as $key => $vo){
                if($key == 0){
                    $account = $vo['direction'] == 1 ? $vo['amount'] : -$vo['amount'];
                }else{
                    $_vo= UserRecord::find($_lists[$key-1]['id']);
                    $account = $vo['direction'] == 1 ? $_vo->account + $vo['amount'] : $_vo->account - $vo['amount'];
                }
                $data = [
                    "id" => $vo['id'],
                    "account" => $account,
                ];
                UserRecord::update($data);
            }
        }
        exit;
        $lists = UserWithdraw::select();
        $lists = collection($lists)->toArray();
        $sql = "INSERT INTO `stock_user_record` (`user_id`, `type`, `amount`, `remark`, `direction`, `create_at`) VALUES ";
        foreach ($lists as $vo){
            $remark = json_encode(["tradeNo" => $vo['out_sn']]);
            $sql .= "({$vo['user_id']}, 6, {$vo['amount']}, '{$remark}', 2, {$vo['create_at']}),";
        }
        $sql = rtrim($sql, ',') . ';';
        echo $sql;
        exit;
        $order = \app\admin\model\Order::select();
        $order = collection($order)->toArray();
        $holiday = explode(',', cf("holiday", ""));
        foreach ($order as $vo){
            $day = workDay($vo['original_free'], $vo['free_time'], $holiday);
            $defer_total = $day * $vo['defer'];
            $data = [
                "order_id" => $vo["order_id"],
                "defer_total" => $defer_total
            ];
            $res = model("Order")->isUpdate(true)->save($data) ? true : false;
            dump($res);
        }
        exit;
        /*$job = new SellJob();
        $res = $job->handleSell(12);
        dump($res);
        exit;
        $managerUserId = 0;
        $adminId = 6;
        $adminIds = (new AdminLogic())->ringFamilyTree($adminId);
        $handleRes = (new RebateLogic())->handleProxyRebate($managerUserId, $adminIds, 999, 500);
        dump($handleRes);
        exit;*/
        Queue::push('app\index\job\SellJob@handleSellOrder', 12, "helloJobQueue");
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
