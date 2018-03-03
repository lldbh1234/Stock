<?php
namespace app\index\controller;

use think\Request;
use app\index\logic\OrderLogic;
use app\index\logic\UserLogic;
use app\index\logic\StockLogic;

class Order extends Base
{
    protected $_logic;
    protected $_userLogic;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->_logic = new OrderLogic();
        $this->_userLogic = new UserLogic();
    }

    // 持仓
    public function position()
    {
        $capital = [
            "netAssets" => 0, //净资产
            "expendableFund" => 0, //可用资金
            "floatPL" => 0, //浮动盈亏
            "marketValue" => 0, //持仓市值
        ];
        $user = $this->_userLogic->userIncOrder($this->user_id, $state = 3);
        $codes = array_column($user["has_many_order"], "code");
        if($codes){
            $quotation = (new StockLogic())->simpleData($codes);
            array_filter($user["has_many_order"], function($item) use ($quotation, &$capital){
                $floatPL = ($quotation[$item['code']]['last_px'] - $item['price']) * $item['hand'];
                $marketValue = $item['hand'] * $quotation[$item['code']]['last_px'];
                $capital['floatPL'] += $floatPL;
                $capital['marketValue'] += $marketValue;
            });
            $orders = $this->_userLogic->pageUserOrder($this->user_id, $state = 3);
            array_filter($orders['data'], function (&$item) use ($quotation){
                $item['last_px'] = $quotation[$item['code']]['last_px']; //现价
                $item['market_value'] = $item['last_px'] * $item['hand']; //市值
                $item['yield_rate'] = round(($item['last_px'] - $item['price']) / $item['price'] * 100, 2); //收益率
                $item['total_pl'] = ($item['last_px'] - $item['price']) * $item['hand']; //盈亏
            });
            $list = $orders['data'];
            $last_page = $orders['last_page'];
            $current_page = $orders['current_page'];
        }else{
            $list = [];
            $last_page= 1;
            $current_page = 1;
        }
        $capital['expendableFund'] = $user['account']; //可用资金
        $capital['netAssets'] = $capital['expendableFund'] + $user['blocked_account'] + $capital['floatPL']; //净资产
        $this->assign("capital", $capital);
        $this->assign("orders", $list);
        $this->assign("totalPage", $last_page);
        $this->assign("currentPage", $current_page);
        return view();
    }

    public function entrust()
    {
        return view();
    }

    public function history()
    {
        return view();
    }
}