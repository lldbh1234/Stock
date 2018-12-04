<?php
namespace app\admin\controller;

use app\admin\logic\OrderLogic;
use app\admin\logic\StockLogic;
use app\admin\model\UserGive;
use app\admin\model\UserRecord;
use app\admin\model\UserWithdraw;
use app\common\libraries\api51;
use app\common\libraries\apiYiyuan;
use app\common\libraries\Sms;
use app\common\model\AdminCard;
use app\common\payment\authRbPay;
use app\common\payment\paymentLLpay;
use app\common\quotation\sina;
use app\common\quotation\tencent;
use app\index\job\DeferJob;
use app\index\job\RebateJob;
use app\index\job\SellJob;
use app\index\logic\AdminLogic;
use app\index\logic\RebateLogic;
use app\index\logic\RechargeLogic;
use app\index\logic\UserLogic;
use llpay\payment\pay\LLpaySubmit;
use think\Controller;
use think\Db;
use think\Queue;
use think\Request;

class Test extends Controller
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
    }

    public function test($order_id = null, $mobile=null, $admin_mobile=null)
    {
        /*header("Content-type:text/html;charset=utf-8");
        $withdrawData = [
            "tradeNo" => createStrategySn(),
            "amount" => 100,
            "createAt" => time(),
            "name" => "梁健",
            "card" => "6217004220033901731",
        ];
        dump($withdrawData);
        $response = (new paymentLLpay())->payment($withdrawData);
        dump($response);
        exit;*/
        /*
        $nickname = cf('nickname_prefix', config("nickname_prefix"));
        $_logic = new \app\admin\logic\UserLogic();
        for($i = 1; $i <= 50; $i++){
            $mobile = "10880000051";
            $virtual = [];
            $virtual['mobile'] = strval($mobile + $i);
            $virtual['username'] = $virtual['mobile'];
            $virtual['password'] = "123456";
            $virtual['nickname'] = $nickname . substr($virtual["mobile"], -4);
            $virtual['face'] = config("default_face");
            $virtual['admin_id'] = 526;
            $virtual['state'] = 0;
            $virtual['is_virtual'] = 1;
            $user_id = $_logic->createUser($virtual);
            dump($user_id);
            if($user_id){
                $give = $_logic->giveMoney($user_id, 1000000, "虚拟用户赠金");
                dump($give);
            }
        }*/
        //exit();
        if($order_id)
        {
            $job = new DeferJob();
            $res = $job->handle($order_id);
            dump($res);
        }elseif ($mobile)
        {
            header("Content-type: text/html; charset=utf-8");
            $user = (new UserLogic())->userBy(['mobile' => $mobile]);
            $res = (new \app\admin\logic\UserLogic())->unbindCard($user['user_id']);
            dump($res);
        }elseif($admin_mobile)
        {
            header("Content-type: text/html; charset=utf-8");
            $adminModel = new \app\admin\logic\AdminLogic();
            $admin = $adminModel->adminByMobile($admin_mobile);
            dump($admin);exit;
            $res = (new AdminCard())->where(['admin_id' => $admin['admin_id']])->delete();
            dump($res);
        }

        exit;
        $order = (new UserLogic())->userOrderById('4574', '47148', 3);
        $order = reset($order);
        if($order){
            $order['buy_px'] = 54.22; //买1如果比股票报价低，超过0.02 就上浮，反之不上调，等值也不上调
            $res = (new UserLogic())->userOrderSelling($order);
            dump($res);
        }else{
            return $this->fail("系统提示：非法操作！");
        }
        exit;
        return $this->huigun();
        exit();
        set_time_limit(0);
        $lists = UserRecord::group("user_id")->column("user_id");
        foreach ($lists as $value){
            if(in_array($value, [297, 374])){
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

    public function huigun()
    {
        //proxy
//        $sql1 = "SELECT sum(`money`) as money_count, admin_id FROM `stock_admin_record` where create_at >= '1526464800' group by `admin_id`";
//        $adminRecord = collection(Db::query($sql1))->toArray();
////        $updateMoneySql = "";
//        foreach($adminRecord as $k => $v)
//        {
//            $money = $v['money_count'];
//            $admin_id = $v['admin_id'];
//            echo "UPDATE `stock_admin` SET `total_fee` = `total_fee`-$money , `total_income` = `total_income`-$money WHERE `admin_id` = $admin_id;"; echo "<br />";
//
//        }
//        $sqlDel = "DELETE FROM `stock_admin_record` WHERE create_at >= '1526464800'";
//        echo $sqlDel;die();
        //user
//        dump($updateMoneySql);
//        $sql1 = "SELECT sum(`amount`) as `amount`, `user_id` FROM `stock_user_record` where create_at >= '1526464800' and type=10 group by user_id";
//        $adminRecord = collection(Db::query($sql1))->toArray();
//        foreach($adminRecord as $k => $v)
//        {
//            $money = $v['amount'];
//            $admin_id = $v['user_id'];
//            echo "UPDATE `stock_user` SET `account` = `account`-$money  WHERE `user_id` = $admin_id;"; echo "<br />";
//
//        }
//        foreach($adminRecord as $k => $v)
//        {
//            $money = $v['amount'];
//            $admin_id = $v['user_id'];
//            echo "UPDATE `stock_user_manager` SET `already_income` = `already_income`-$money, `sure_income` = `sure_income` + $money  WHERE `user_id` = $admin_id;"; echo "<br />";
//
//        }
//        echo "DELETE FROM `stock_user_record` WHERE create_at >= '1526464800'";
//        die();
        //manager
//        $sql1 = "SELECT sum(`money`) as money_count, user_id FROM `stock_user_manager_record` where create_at >= '1526464800' group by `user_id`";
//        $adminRecord = collection(Db::query($sql1))->toArray();
////        $updateMoneySql = "";
//        foreach($adminRecord as $k => $v)
//        {
//            $money = $v['money_count'];
//            $admin_id = $v['user_id'];
//            echo "UPDATE `stock_user_manager` SET `income` = `income`-$money , `sure_income` = `sure_income`-$money WHERE `user_id` = $admin_id;"; echo "<br />";
//
//        }
//        $sqlDel = "DELETE FROM `stock_user_manager_record` WHERE create_at >= '1526464800'";
//        echo $sqlDel;
//        die();
        //order
//        $sql1 = "SELECT * from `stock_order` WHERE `state` = 3 AND `create_at` >= '1526400000' ";
        $sql2 = "UPDATE `stock_order` SET `jiancang_rebate` = 0 WHERE `state` = 3 AND `create_at` >= '1526400000' ;";
//        $sql3 = "SELECT * from `stock_order` WHERE `state` = 2 AND `update_at` >= '1526400000' ";
        $sql4 = "UPDATE `stock_order` SET `niuren_rebate` = 0,`proxy_rebate` = 0 WHERE `state` = 2 AND `update_at` >= '1526400000' ;";
//        echo $sql1;
        echo "<br />";
        echo $sql2;
        echo "<br />";
//        echo $sql3;
        echo "<br />";
        echo $sql4;
//        $adminRecord = collection(Db::query($sql1))->toArray();
//        foreach($adminRecord as $k => $v)
//        {
//            $money = $v['money_count'];
//            $admin_id = $v['user_id'];
//            echo "UPDATE `stock_user_manager` SET `income` = `income`-$money , `sure_income` = `sure_income`-$money WHERE `user_id` = $admin_id;"; echo "<br />";
//
//        }
//        $sqll = "UPDATE `stock_admin_record` SET `create_at` = '1526371200' where create_at >= '1526371200'";

    }

}
