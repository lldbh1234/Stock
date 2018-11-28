<?php
namespace app\index\controller;

use think\Queue;
use think\Request;
use app\index\logic\OrderLogic;
use app\index\logic\DepositLogic;
use app\index\logic\LeverLogic;
use app\index\logic\ModeLogic;
use app\index\logic\StockLogic;

class Stock extends Base
{
    protected $_logic;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->_logic = new StockLogic();
    }

    public function stockBuy($code = null)
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Stock');
            if(!$validate->scene('buy')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $blackUser = [1172, 969, 974, 1051, 1103, 1861, 2192, 3400, 4057, 4965, ];
                if(in_array($this->user_id, $blackUser)){
                    return $this->fail("资金账户已用尽！");
                }
                $code = input("post.code/s");
                $modeId = input("post.mode/d");
                $depositId = input("post.deposit/d");
                $leverId = input("post.lever/d");
                $price = input("post.price/f");
                $followId = input("post.follow_id/d", 0);
                while(true){
                    $quotation = $this->_logic->quotationBySina($code);
                    if(isset($quotation[$code])){
                        $last_px = $quotation[$code]['last_px'];
                        $sell_px = $quotation[$code]['sell_px'];
                        $sell_px = $sell_px > 0 ? $sell_px : $last_px; // 卖1如果没拿到，用现价建仓
                        $price = $last_px - $sell_px > 0.02 ? $sell_px + 0.02 : $sell_px; //卖1如果比股票报价低，超过0.02 就上浮，反之不上调，等值也不上调
                        $stock = $this->_logic->stockByCode($code);
                        $mode = (new ModeLogic())->modeIncPluginsById($modeId);
                        $deposit = (new DepositLogic())->depositById($depositId);
                        $lever = (new LeverLogic())->leverById($leverId);
                        $plugins = $mode['has_one_plugins'];
                        require_once request()->root() . "../plugins/{$plugins['type']}/{$plugins['code']}.php";
                        $obj = new $plugins['code'];
                        $trade = $obj->getTradeInfo($price, cf("capital_usage", 95), $deposit['money'], $lever['multiple'], $mode['jiancang'], $mode['defer']);
                        if($trade["hand"] < 100){
                            return $this->fail("建仓数量最低100股起！");
                        }else{
                            if(uInfo()['account'] >= $deposit['money'] + $trade["jiancang"]){
                                $holiday = explode(',', cf("holiday", ""));
                                $timestamp = workTimestamp($mode['free'], $holiday, strtotime(date("Y-m-d 14:40", request()->time())));
                                $order = [
                                    "order_sn" => createStrategySn(),
                                    "user_id" => $this->user_id,
                                    "product_id" => $mode['product_id'],
                                    "mode_id" => $modeId,
                                    "lever" => $lever['multiple'],
                                    "code"  => $code,
                                    "name"  => $stock['name'],
                                    "full_code" => $stock['full_code'],
                                    "price" => $price,
                                    "hand"  => $trade["hand"],
                                    "jiancang_fee" => $trade["jiancang"],
                                    "defer" => $trade["defer"],
                                    "free_time" => $timestamp,
                                    "original_free" => $timestamp,
                                    "is_defer" => input("post.defer/d"),
                                    "stop_profit_price" => input("post.profit/f"),
                                    "stop_profit_point" => round((input("post.profit/f") - $price) / $price * 100, 2),
                                    "stop_loss_price" => input("post.loss/f"),
                                    "stop_loss_point" => round(($price - input("post.loss/f")) / $price * 100, 2),
                                    "deposit"   => $deposit['money'],
                                    "original_deposit" => $deposit['money'],
                                    "state"     => 3, // 下单即持仓
                                    "is_follow" => $followId ? 1 : 0,
                                    "follow_id" => $followId,
                                    "is_hedging" => 0, // 持仓单默认未对冲
                                ];
                                $orderId = (new OrderLogic())->createOrder($order);
                                if($orderId > 0){
                                    $url = url("index/Order/position");
                                    // 队列
                                    $smsNoticeData = $sysNoticeData = ["niurenId" => $this->user_id];
                                    Queue::push('app\index\job\UserNotice@systemNotice', $sysNoticeData, null);
                                    Queue::push('app\index\job\UserNotice@smsNotice', $smsNoticeData, null);
                                    return $this->ok(["url" => $url]);
                                }else{
                                    return $this->fail("创建策略失败！");
                                }
                            }else{
                                return $this->fail("您的余额不足，请充值！");
                            }
                            break;
                        }
                    }else{
                        continue;
                    }
                }
            }
        }else{
            $stock = $this->_logic->stockByCode($code);
            if($stock){
                while (true){
                    //$quotation = $this->_logic->simpleData($code);
                    $quotation = $this->_logic->quotationBySina($code);
                    if(isset($quotation[$code]) && !empty($quotation[$code])){
                        $modes = (new ModeLogic())->productModes();
                        $deposits = (new DepositLogic())->allDeposits();
                        $levers = (new LeverLogic())->allLevers();
                        $this->assign("stock", $quotation[$code]);
                        $this->assign("modes", $modes);
                        $this->assign("deposits", $deposits);
                        $this->assign("levers", $levers);
                        $this->assign("user", uInfo());
                        $this->assign("usage", cf('capital_usage', 95));
                        $this->assign("follow_id", input("?follow_id") ? input("follow_id") : 0);
                        return view('buy');
                        break;
                    }else{
                        continue;
                    }
                }
            }else{
                return view('public/error');
            }
        }
    }

    public function info($code = null)
    {
        $stock = $this->_logic->stockByCode($code);
        if($stock){
            while (true){
                $quotation = $this->_logic->realTimeDataByTencent($code);
                if(isset($quotation[$code]) && !empty($quotation[$code])){
                    $this->assign("quotation", $quotation[$code]);
                    return view();
                    break;
                }else{
                    continue;
                }
            }
        }else{
            return view('public/error');
        }
    }

    public function real()
    {
        $code = input("code");
        if($code){
            $res = $this->_logic->realData($code);
            if(request()->isPost()){
                return $this->ok($res);
            }else{
                return json($res);
            }
        }
        return json([]);
    }

    public function incReal()
    {
        $code = input("code");
        $cnc = input("cnc");
        $min = input("min");
        $res = [];
        if(checkStockTradeTime() && $code){
            $res = $this->_logic->realData($code, $cnc, $min);
        }
        if(request()->isPost()){
            return $this->ok($res);
        }else{
            return json($res);
        }
    }

    public function simple()
    {
        $code = input("code");
        if($code){
            $res = $this->_logic->simpleData($code);
            if(request()->isPost()){
                return $this->ok($res);
            }else{
                return json($res);
            }
        }
        return json([]);
    }

    public function kline()
    {
        $code = input("code");
        if($code){
            $period = input("period", 6);
            $count = input("count", 50);
            $res = $this->_logic->klineData($code, $period, $count);
            if(request()->isPost()){
                return $this->ok($res);
            }else{
                return json($res);
            }
        }
        return json([]);
    }
}