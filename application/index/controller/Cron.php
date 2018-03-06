<?php
namespace app\index\controller;

use app\index\logic\OrderLogic;
use think\Controller;
use think\Db;
use think\Queue;

class Cron extends Controller
{
    // 半小时
    public function grabStockLists()
    {
        set_time_limit(0);
        if(checkStockTradeTime()){
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
                //@file_put_contents($_jsPath, $_jsText);
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

    // 5分钟一次
    public function scanOrderDefer()
    {
        if(checkStockTradeTime()){
            $orders = (new OrderLogic())->allDeferOrders();
            if($orders){
                foreach ($orders as $order){
                    if($order['is_defer']){
                        // 自动递延
                        Queue::push('app\index\job\DeferJob@handleDeferOrder', $order["order_id"], null);
                    }else{
                        // 非自动递延,强制平仓
                        $Coercions = cache("Coercion_Selling");
                        $Coercions[] = $order["order_id"];
                        cache("Coercion_Selling", $Coercions);
                    }
                }
            }
        }
    }

    // 牛人返点-每天停盘后的时间段 17-23点
    public function handleNiurenRebate()
    {
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

    // 代理商返点-每天停盘后的时间段 17-23点
    public function handleProxyRebate()
    {
        if(checkSettleTime()){
            $orders = (new OrderLogic())->todayProxyRebateOrder();
            if($orders){
                foreach ($orders as $order){
                    $rebateData = [
                        "money" => $order["profit"],
                        "user_id" => $order["user_id"]
                    ];
                    Queue::push('app\index\job\RebateJob@handleProxyRebate', $rebateData, null);
                }
            }
        }
    }
}