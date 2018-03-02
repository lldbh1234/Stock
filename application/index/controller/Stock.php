<?php
namespace app\index\controller;

use think\Request;
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
                $modeId = input("post.mode/d");
                $depositId = input("post.deposit/d");
                $leverId = input("post.lever/d");
                $user = uInfo();
                $mode = (new ModeLogic())->modeById($modeId);
                $deposit = (new DepositLogic())->depositById($depositId);
                $lever = (new LeverLogic())->leverById($leverId);
                $jiancangTotal = ($deposit['money'] * $lever['multiple']) / 10000 * $mode['jiancang'];
                $moneyTotal = $deposit['money'] + $jiancangTotal;
                if($moneyTotal <= $user['account']){

                }else{
                    return $this->fail("您的余额不足，请充值！");
                }
            }
        }else{
            $stock = $this->_logic->stockByCode($code);
            if($stock){
                $quotation = $this->_logic->simpleData($code);
                if(isset($quotation[$code]) && !empty($quotation[$code])){
                    $modes = (new ModeLogic())->productModes();
                    $deposits = (new DepositLogic())->allDeposits();
                    $levers = (new LeverLogic())->allLevers();
                    $this->assign("stock", $quotation[$code]);
                    $this->assign("modes", $modes);
                    $this->assign("deposits", $deposits);
                    $this->assign("levers", $levers);
                    $this->assign("user", uInfo());
                    return view('buy');
                }else{
                    return view('public/error');
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
            $quotation = $this->_logic->realTimeData($code);
            if(isset($quotation[0]) && !empty($quotation[0])){
                $this->assign("quotation", $quotation[0]);
                return view();
            }else{
                return view('public/error');
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