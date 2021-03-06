<?php
namespace app\index\controller;

use app\index\logic\BestLogic;
use app\index\logic\DangerLogic;
use app\index\logic\UserLogic;
use think\Controller;
use think\Db;
use think\Queue;
use app\index\logic\OrderLogic;

class Cron extends Controller
{
    // php think queue:work --daemon
    // php think queue:work --queue SellOrderQueue --daemon
    // 抓取板块行情指数
    public function grabPlateIndex()
    {
        exit;
        set_time_limit(0);
        if(checkStockTradeTime()){
            $jsonArray = [];
            $jsonPath = "./plate.json";
            $url = 'http://hq.sinajs.cn/rn=1520407404627&list=s_sh000001,s_sz399001,s_sz399006';
            $html = file_get_contents($url);
            $html = str_replace(["\r\n", "\n", "\r", " "], "", $html);
            $plates = explode(';', $html);
            if($plates){
                foreach ($plates as $plate){
                    if($plate){
                        $plate = iconv("GB2312", "UTF-8", $plate);
                        preg_match('/^varhq_str_s_([sh|sz]{2})(\d{6})="(.*)"/i', $plate, $match);
                        if($match[3]){
                            $_data = explode(",", $match[3]);
                            $jsonArray[] = [
                                "plate_name" => $_data[0],
                                "last_px"   => $_data[1],
                                "px_change" => $_data[2],
                                "px_change_rate" => $_data[3]
                            ];
                        }
                    }
                }
            }
            if($jsonArray){
                @file_put_contents($jsonPath, json_encode($jsonArray, JSON_UNESCAPED_UNICODE));
                echo "ok";
            }
        }
    }

    // 半小时 股票列表
    public function grabStockLists()
    {
        set_time_limit(0);
        if(checkStockTradeDate() && input("get.bCJALo") == "system"){
            $_arrays = [];
            $_jsTextIndex = 0;
            $_jsTextArrays = [];
            $_jsText = "var stocks=new Array();";
            $_jsPath = "./static/js/stock.js";
            $url = 'http://money.finance.sina.com.cn/d/api/openapi_proxy.php/?__s=[["hq","hs_a","",0,1,80]]&callback=FDC_DC.theTableData';
            $html = file_get_contents($url);
            $json = substr($html, 70, -3);
            $array = json_decode($json, true);
            $total = $array["count"];
            $count = ceil($total / 80);
            foreach ($array['items'] as $item){
                $_arrays[] = [
                    "full_code"	=> $item[0],
                    "code"  => $item[1],
                    "name"  => str_replace(' ', '', $item[2]),
                ];
                $_jsTextArrays[] = "stocks[". $_jsTextIndex ."]=new Array('','" . $item[1] . "','" . str_replace(' ', '', $item[2]) . "','" . $item[0] . "'); ";
                $_jsTextIndex++;
            }
            for ($i = 2; $i <= $count; $i++){
                $_url = 'http://money.finance.sina.com.cn/d/api/openapi_proxy.php/?__s=[["hq","hs_a","",0,'.$i.',80]]&callback=FDC_DC.theTableData';
                $_html = file_get_contents($_url);
                $_json = substr($_html, 70, -3);
                $_array = json_decode($_json, true);
                foreach ($_array['items'] as $_item){
                    $_arrays[] = [
                        "full_code"	=> $_item[0],
                        "code"  => $_item[1],
                        "name"  => str_replace(' ', '', $_item[2]),
                    ];
                    $_jsTextArrays[] = "stocks[". $_jsTextIndex ."]=new Array('','" . $_item[1] . "','" . str_replace(' ', '', $_item[2]) . "','" . $_item[0] . "'); ";
                    $_jsTextIndex++;
                }
            }
            $_jsText .= implode('', $_jsTextArrays);
            Db::startTrans();
            try{
                model("Lists")->query("truncate table stock_list");
                model("Lists")->saveAll($_arrays);
                @file_put_contents($_jsPath, $_jsText);
                // 提交事务
                Db::commit();
                echo "ok";
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                echo "false";
            }
        }
    }

    // 高危股票列表
    public function dangerList()
    {
        if(input("get.K5Ue5QYl") == "system"){
            set_time_limit(0);
            $page = 1;
            $list = [];
            $amount = input("get.time", "am") == "am" ? 3000000 : 7000000; // 高危股票交易额
            $_logic = new DangerLogic();
            $dangerCodes = $_logic->dangerCodesByState($state = 1);
            while (true){
                $url = 'http://money.finance.sina.com.cn/d/api/openapi_proxy.php/?__s=[["hq","hs_a","amount",1,' . $page . ',80]]';
                $response = file_get_contents($url);
                if($response){
                    $continue = false;
                    $response = ltrim($response, '[');
                    $response = rtrim($response, ']');
                    $response = json_decode($response, true);
                    foreach ($response['items'] as $val){
                        if($val[13] > 0 && $val[13] <= $amount){
                            if(!in_array($val[1], $dangerCodes)){
                                $list[$val[1]] = [
                                    "code" => $val[1],
                                    "name" => $val[2],
                                    "symbol" => $val[0],
                                    "amount" => $val[13],
                                    "state" => 0,
                                    "create_at" => time()
                                ];
                            }
                        }else{
                            if($val[13] > $amount){
                                $continue = true;
                                break;
                            }
                        }
                    }
                    $page++;
                    if($continue){
                        break;
                    }
                }else{
                    continue;
                }
            }
            if($list){
                $_logic->updateDangerList($list);
            }
        }
    }

    // 未开启递延短信提醒
    public function scanDeferNotice()
    {
        set_time_limit(0);
        if(checkStockTradeTime()){
            //$orders = (new OrderLogic())->allPositionOrders(null, ['is_defer' => 0], $virtual = 0); //$virtual = 0真实用户订单
            $where = ['is_defer' => 0, 'free_time' => ["ELT", strtotime(date("Y-m-d 14:40:00"))]];
            $orders = (new OrderLogic())->allPositionOrders(null, $where, $virtual = 0); //$virtual = 0真实用户订单
            if($orders){
                foreach ($orders as $order){
                    if($order['is_defer'] == 0){
                        // 未开启自动递延
                        $smsData = [
                            "Act"   => "NonAuto",
                            "Code"  => $order['code'],
                            "UserId" => $order['user_id'],
                            "OrderId" => $order['order_id'],
                        ];
                        Queue::push('app\index\job\UserNotice@smsNotice', $smsData, null);
                    }
                }
            }
        }
    }

    // 自动递延
    public function scanBalanceNotice()
    {
        set_time_limit(0);
        if(checkStockTradeTime()){
            $lists = (new OrderLogic())->allPositionTotalDefer();
            if($lists){
                $userLogic = new UserLogic();
                foreach ($lists as $vo){
                    $user = $userLogic->userById($vo['user_id']);
                    if($user['is_virtual'] == 1){
                        // 虚拟用户订单不处理
                        continue;
                    }
                    if($user['account'] < $vo['total_defer']){
                        // 未开启自动递延
                        $smsData = [
                            "Act"   => "Balance",
                            "Code"  => $vo['code'],
                            "UserId" => $vo['user_id'],
                        ];
                        Queue::push('app\index\job\UserNotice@smsNotice', $smsData, null);
                    }
                }

            }
        }
    }

    // 递延费扣除
    public function scanOrderDefer()
    {
        set_time_limit(0);
        if(checkStockTradeTime()){
            $orders = (new OrderLogic())->allDeferOrders();
            if($orders){
                foreach ($orders as $order){
                    if($order['is_defer']){
                        // 自动递延
                        Queue::push('app\index\job\DeferJob@handleDeferOrder', $order["order_id"], null);
                    }else{
                        // 非自动递延,强制平仓
                        Queue::push('app\index\job\DeferJob@handleNonAutoDeferOrder', $order, null);
                    }
                }
            }
        }
    }

    // 爆仓、止盈、止损，交易时间段、每2秒一次
    public function scanOrderSell()
    {
        set_time_limit(0);
        if(checkStockTradeTime()){
            $orders = (new OrderLogic())->allSellOrders();
            if($orders){
                foreach ($orders as $order){
                    /*$sellData = [
                        "order_id"  => $order["order_id"], //订单ID
                        "code"      => $order["code"], // 股票code
                        "price"     => $order["price"], // 买入价
                        "hand"      => $order["hand"], // 买入手数
                        "stop_profit" => $order["stop_profit_price"], // 止盈
                        "stop_loss" => $order["stop_loss_price"], // 止损
                        "deposit"   => $order["deposit"], // 保证金
                    ];*/
                    //Queue::push('app\index\job\SellJob@handleSellOrder', $sellData, null);
                    Queue::push('app\index\job\SellJob@handleSellOrder', $order["order_id"], "SellOrderQueue");
                }
            }
        }
    }

    // 盈利牛人返点-每天停盘后的时间段 16-23点
    public function handleNiurenRebate()
    {
        set_time_limit(0);
        if(checkSettleTime()){
            $orders = (new OrderLogic())->todayNiurenRebateOrder();
            if($orders){
                foreach ($orders as $order){
                    if($order['is_follow'] == 1){
                        // 跟买
                        $followData = [
                            "money" => $order["profit"], //盈利额
                            "order_id" => $order["order_id"], //订单ID
                            "follow_id" => $order["follow_id"] //跟买订单ID
                        ];
                        Queue::push('app\index\job\RebateJob@handleFollowOrder', $followData, null);
                    }
                }
            }
        }
    }

    // 盈利代理商返点-每天停盘后的时间段 16-23点
    public function handleProxyRebate()
    {
        set_time_limit(0);
        if(checkSettleTime()){
            $orders = (new OrderLogic())->todayProxyRebateOrder();
            if($orders){
                foreach ($orders as $order){
                    $rebateData = [
                        "money" => $order["profit"] * $order['belongs_to_mode']['point'] / 100, //系统抽成金额
                        "order_id" => $order["order_id"], //订单ID
                        "user_id" => $order["user_id"],
                    ];
                    Queue::push('app\index\job\RebateJob@handleProxyRebate', $rebateData, null);
                }
            }
        }
    }

    // 建仓费返点-每天停盘后的时间段 16-23点
    public function handleJiancangRebate()
    {
        set_time_limit(0);
        if(checkSettleTime()){
            $orders = (new OrderLogic())->todayJiancangRebateOrder();
            if($orders){
                foreach ($orders as $order){
                    $rebateData = [
                        "money" => $order["jiancang_fee"],
                        "order_id" => $order["order_id"], //订单ID
                        "user_id" => $order["user_id"]
                    ];
                    Queue::push('app\index\job\RebateJob@handleJiancangRebate', $rebateData, null);
                }
            }
        }
    }

    // 最优持仓数据统计,2小时运行一次
    public function scanBestOrders()
    {
        set_time_limit(0);
        if(checkStockTradeTime()){
            $orders = (new OrderLogic())->allPositionOrders("order_id");
            if($orders){
                foreach ($orders as $orderId){
                    Queue::push('app\index\job\BestOrderJob@handleBestOrder', $orderId, null);
                }
            }
        }
    }

    // 删除最优持仓中已平仓的策略，交易时间段10分钟运行一次
    public function scanClearBest()
    {
        set_time_limit(0);
        if(checkStockTradeTime()){
            $orderIds = (new OrderLogic())->allPositionOrders("order_id");
            if($orderIds){
                (new BestLogic())->deleteClosedOrders($orderIds);
            }
        }
    }

    // 清除队列表数据，每晚23:50执行一次
    public function clearJobTable()
    {
        Db::name("stock_jobs")->query("truncate table stock_jobs");
    }
}
