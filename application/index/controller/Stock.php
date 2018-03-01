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

    public function real()
    {
        $code = input("code");
        if($code){
            $codes = $this->_logic->fullCodeByCodes($code);
            $res = $this->_logic->realTimeData($codes);
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
            $codes = $this->_logic->fullCodeByCodes($code);
            $res = $this->_logic->simpleData($codes);
            if(request()->isPost()){
                return $this->ok($res);
            }else{
                return json($res);
            }
        }
        return json([]);
    }
}