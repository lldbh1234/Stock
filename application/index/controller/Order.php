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
        $field = "order_id,code,name,price,deposit,defer,hand,stop_loss_price,stop_profit_price,create_at";
        if(request()->isPost()){
            $orders = $this->_userLogic->pageUserOrder($this->user_id, $state = 3, $field);
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
            $response = ["orders" => $list, "total_page" => $last_page, "current_page" => $current_page];
            return $this->ok($response);
        }else{
            $capital = $this->_userCapital();
            $orders = $this->_userLogic->pageUserOrder($this->user_id, $state = 3, $field);
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
                return $this->ok($response);
            }
        }else{
            return $this->fail("系统提示：非法操作！");
        }
    }

    // 委托
    public function entrust()
    {
        $field = "order_id,code,name,deposit,defer,price,sell_price,sell_hand,stop_loss_price,stop_profit_price,state,create_at";
        if(request()->isPost()){
            $orders = $this->_userLogic->pageUserOrder($this->user_id, $state = [1, 4], $field);
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
            $response = ["orders" => $list, "total_page" => $last_page, "current_page" => $current_page];
            return $this->ok($response);
        }else{
            $capital = $this->_userCapital();
            $orders = $this->_userLogic->pageUserOrder($this->user_id, $state = [1, 4], $field);
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
    }

    // 平仓
    public function history()
    {
        $field = "order_id,code,name,price,sell_price,sell_hand,create_at,update_at";
        if(request()->isPost()){
            $orders = $this->_userLogic->pageUserOrder($this->user_id, $state = 2, $field);
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
            $response = ["orders" => $list, "total_page" => $last_page, "current_page" => $current_page];
            return $this->ok($response);
        }else{
            $capital = $this->_userCapital();
            $orders = $this->_userLogic->pageUserOrder($this->user_id, $state = 2, $field);
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

    public function cancel()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Order');
            if(!$validate->scene('cancel')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $orderId = input("post.id/d");
                $order = $this->_userLogic->userOrderById($this->user_id, $orderId, [1, 4]);
                $order = reset($order);
                if($order){
                    if($order['state'] == 1){
                        //建仓
                        $res = $this->_userLogic->cancelUserBuying($order);
                        if($res){
                            return $this->ok();
                        }else{
                            return $this->fail("撤销失败！");
                        }
                    }else{
                        //平仓
                        $res = $this->_userLogic->cancelUserSelling($order);
                        if($res){
                            return $this->ok();
                        }else{
                            return $this->fail("撤销失败！");
                        }
                    }
                }else{
                    return $this->fail("系统提示：非法操作！");
                }
            }
        }else{
            return $this->fail("系统提示：非法操作！");
        }
    }

    // 补充保证金
    public function deposit()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Order');
            if(!$validate->scene('deposit')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $orderId = input("post.id/d");
                $deposit = input("post.deposit/f");
                $res = $this->_userLogic->userOrderDepositSupply($this->user_id, $orderId, $deposit);
                if($res){
                    return $this->ok();
                }else{
                    return $this->fail("保证金补充失败！");
                }
            }
        }else{
            return $this->fail("系统提示：非法操作！");
        }
    }

    // 平仓申请
    public function selling()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Order');
            if(!$validate->scene('selling')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $orderId = input("post.id/d");
                $order = $this->_userLogic->userOrderById($this->user_id, $orderId, 3);
                $order = reset($order);
                if($order){
                    $res = $this->_userLogic->userOrderSelling($order);
                    if($res){
                        return $this->ok();
                    }else{
                        return $this->fail("平仓申请提交失败！");
                    }
                }else{
                    return $this->fail("系统提示：非法操作！");
                }
            }
        }else{
            return $this->fail("系统提示：非法操作！");
        }
    }
}