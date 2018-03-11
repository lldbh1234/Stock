<?php
namespace app\web\controller;

use think\Queue;
use think\Request;
use app\web\logic\OrderLogic;
use app\web\logic\DepositLogic;
use app\web\logic\LeverLogic;
use app\web\logic\ModeLogic;
use app\web\logic\StockLogic;

class Stock extends Base
{
    protected $_logic;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->_logic = new StockLogic();
    }

    public function stockBuy()
    {
        return view();
    }
    public function stockSell()
    {
        return view();
    }
    public function stockHistory()
    {
        return view();
    }
    public function stockDetail()
    {
        return view();
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