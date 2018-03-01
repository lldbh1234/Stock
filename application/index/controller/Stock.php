<?php
namespace app\index\controller;

use think\Request;
use app\index\logic\StockLogic;

class Stock extends Base
{
    protected $_logic;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->_logic = new StockLogic();
    }

    public function stockBuy($code)
    {

    }

    public function info($code)
    {
        $stock = $this->_logic->stockByCode($code);
        if($stock){
            $quotation = $this->_logic->realTimeData($code);
            if(isset($quotation[0]) && !empty($quotation[0])){
                $this->assign("quotation", $quotation[0]);
                return view();
            }else{
                return "错误页面！";
            }
        }else{
            return view('public/error');
        }
    }

    public function real()
    {
        $code = input("code");
        if($code){
            $res = $this->_logic->realTimeData($code);
            if(request()->isPost()){
                return $this->ok($res);
            }else{
                return json($res);
            }
        }
        return json([]);
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
}