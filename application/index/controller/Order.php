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
        $capital = $this->_userCapital();
        $orders = $this->_userLogic->pageUserOrder($this->user_id, $state = 3, 1);
        if($orders['data']){
            $codes = array_column($orders['data'], "code");
            $quotation = (new StockLogic())->simpleData($codes);
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
        $this->assign("capital", $capital);
        $this->assign("orders", $list);
        $this->assign("totalPage", $last_page);
        $this->assign("currentPage", $current_page);
        return view();
    }

    public function ajaxPosition()
    {
        if(request()->isPost() && request()->isAjax()){
            $validate = \think\Loader::validate('Order');
            if(!$validate->scene('realPosition')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $lists = []; //股票列表
                $capital = $this->_userCapital(); // 资金情况
                $ids = input("post.ids/a");
                array_filter($capital, function(&$item){
                    $item = number_format($item, 2);
                });
                $orders = $this->_userLogic->userOrderById($this->user_id, $ids, 3);
                if($orders){
                    $codes = array_column($orders, "code");
                    $quotation = (new StockLogic())->simpleData($codes);
                    array_filter($orders, function($item) use ($quotation, &$lists){
                        $_lastPx = $quotation[$item['code']]['last_px'];
                        $lists[] = [
                            "id" => $item["order_id"],
                            "code" => $item["code"],
                            "last_px" => number_format($_lastPx, 2), //现价
                            "market_value" => number_format($_lastPx * $item['hand'], 2), //市值
                            "yield_rate" => number_format(round(($_lastPx - $item['price']) / $item['price'] * 100, 2), 2), //收益率
                            "total_pl"  => number_format(($_lastPx - $item['price']) * $item['hand'], 2), //盈亏
                        ];
                    });
                }
                $response = ["capital" => $capital, "orders" => $lists];
                return $this->ok($response, '', 'json');
            }
        }else{
            return $this->fail("系统提示：非法操作！");
        }
    }

    // 委托
    public function entrust()
    {
        $capital = $this->_userCapital();
        $orders = $this->_userLogic->pageUserOrder($this->user_id, $state = [1, 4]);
        if($orders['data']){
            array_filter($orders['data'], function (&$item){
                $item['market_value'] = $item['sell_price'] * $item['sell_hand']; //市值
                $item['yield_rate'] = round(($item['sell_price'] - $item['price']) / $item['price'] * 100, 2); //收益率
                $item['total_pl'] = ($item['sell_price'] - $item['price']) * $item['sell_hand']; //盈亏
            });
            $list = $orders['data'];
            $last_page = $orders['last_page'];
            $current_page = $orders['current_page'];
        }else{
            $list = [];
            $last_page= 1;
            $current_page = 1;
        }
        $this->assign("capital", $capital);
        $this->assign("orders", $list);
        $this->assign("totalPage", $last_page);
        $this->assign("currentPage", $current_page);
        return view();
    }

    // 平仓
    public function history()
    {
        $capital = $this->_userCapital();
        $orders = $this->_userLogic->pageUserOrder($this->user_id, $state = 2);
        if($orders['data']){
            array_filter($orders['data'], function (&$item){
                $item['market_value'] = $item['sell_price'] * $item['sell_hand']; //市值
                $item['yield_rate'] = round(($item['sell_price'] - $item['price']) / $item['price'] * 100, 2); //收益率
                $item['total_pl'] = ($item['sell_price'] - $item['price']) * $item['sell_hand']; //盈亏
            });
            $list = $orders['data'];
            $last_page = $orders['last_page'];
            $current_page = $orders['current_page'];
        }else{
            $list = [];
            $last_page= 1;
            $current_page = 1;
        }
        $this->assign("capital", $capital);
        $this->assign("orders", $list);
        $this->assign("totalPage", $last_page);
        $this->assign("currentPage", $current_page);
        return view();
    }

    // 资金详情
    private function _userCapital()
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
        }
        $capital['expendableFund'] = $user['account']; //可用资金
        $capital['netAssets'] = $capital['expendableFund'] + $user['blocked_account'] + $capital['floatPL']; //净资产
        return $capital;
    }
}