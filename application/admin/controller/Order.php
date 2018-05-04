<?php
namespace app\admin\controller;

use app\admin\logic\AdminLogic;
use think\Request;
use app\admin\logic\OrderLogic;
use app\admin\logic\StockLogic;

class Order extends Base
{
    protected $_logic;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->_logic = new OrderLogic();
    }

    // 委托
    public function index()
    {
        $_res = $this->_logic->pageEntrustOrders(input(""));
        $this->assign("datas", $_res['lists']);
        $this->assign("pages", $_res['pages']);
        $this->assign("search", input(""));
        return view();
    }

    // 委托详情
    public function entrustDetail($id = null)
    {
        $order = $this->_logic->orderIncUserById($id, $state = 4);
        if($order){
            $state = [1 => '委托建仓', 2 => '平仓', 3 => '持仓', 4 => '委托平仓', 5 => '作废'];
            $order['state_text'] = $state[$order['state']];
            $this->assign("order", $order);
            return view();
        }
        return "非法操作！";
    }

    // 委托返点
    public function entrustRebate($id = null)
    {
        $order = $this->_logic->orderIncRecordById($id, $state = 4);
        if($order){
            $this->assign("order", $order);
            return view();
        }
        return "非法操作！";
    }

    // 历史
    public function history()
    {
        $_res = $this->_logic->pageHistoryOrders(input(""));
        $tableCols = (new AdminLogic())->tableColumnShow();
        $this->assign("datas", $_res['lists']);
        $this->assign("pages", $_res['pages']);
        $this->assign("totalProfit", $_res['totalProfit']);
        $this->assign("totalJiancang", $_res['totalJiancang']);
        $this->assign("totalDefer", $_res['totalDefer']);
        $this->assign("tableCols", $tableCols);
        $this->assign("search", input(""));
        return view();
    }

    // 平仓详情
    public function historyDetail($id = null)
    {
        $order = $this->_logic->orderIncUserById($id, $state = 2);
        if($order){
            $holiday = explode(',', cf('holiday', ''));
            $workDay = workDay($order['original_free'], $order['free_time'], $holiday);
            $forceType = [0 => '委托平仓', 1 => '爆仓', 2 => '止盈止损', 3 => '非自动递延', 4 => '余额不足'];
            $order['force_type_text'] = $forceType[$order['force_type']];
            $order['total_defer'] = $workDay * $order['defer'];
            $this->assign("order", $order);
            return view();
        }
        return "非法操作！";
    }

    // 平仓返点
    public function historyRebate($id = null)
    {
        $order = $this->_logic->orderIncRecordById($id, $state = 2);
        if($order){
            $this->assign("order", $order);
            return view();
        }
        return "非法操作！";
    }

    // 持仓
    public function position()
    {
        $_res = $this->_logic->pagePositionOrders(input(""));
        $tableCols = (new AdminLogic())->tableColumnShow();
        if($_res['lists']['data']){
            $codes = array_column($_res['lists']['data'], "code");
            $quotation = (new StockLogic())->stockQuotationBySina($codes);
            array_filter($_res['lists']['data'], function(&$item) use ($quotation){
                $item['last_px'] = isset($quotation[$item['code']]['last_px']) ? number_format($quotation[$item['code']]['last_px'], 2) : '-';
                $item['pl'] = isset($quotation[$item['code']]['last_px']) ? number_format(($item['last_px'] - $item['price']) * $item['hand'], 2) : "-";
            });
        }
        $pageDeposit = array_sum(collection($_res['lists']['data'])->column("deposit"));
        $pageJiancang = array_sum(collection($_res['lists']['data'])->column("jiancang_fee"));
        $pageDefer = array_sum(collection($_res['lists']['data'])->column("defer_total"));
        $this->assign("datas", $_res['lists']);
        $this->assign("pages", $_res['pages']);
        $this->assign("totalDeposit", $_res['totalDeposit']);
        $this->assign("totalJiancang", $_res['totalJiancang']);
        $this->assign("totalDefer", $_res['totalDefer']);
        $this->assign("pageDeposit", $pageDeposit);
        $this->assign("pageJiancang", $pageJiancang);
        $this->assign("pageDefer", $pageDefer);
        $this->assign("tableCols", $tableCols);
        $this->assign("search", input(""));
        return view();
    }

    // 强制平仓
    public function force()
    {
        $_res = $this->_logic->pageForceOrders(input(""));
        $this->assign("datas", $_res['lists']);
        $this->assign("pages", $_res['pages']);
        $this->assign("search", input(""));
        return view();
    }

    // 持仓详情
    public function positionDetail($id = null)
    {
        $order = $this->_logic->orderIncUserById($id, $state = 3);
        if($order){
            $hedging = [1 => '是', 0 => '否'];
            $holiday = explode(',', cf('holiday', ''));
            $workDay = workDay($order['original_free'], $order['free_time'], $holiday);
            $quotation = (new StockLogic())->stockQuotationBySina($order['code']);
            $order['is_hedging_text'] = $hedging[$order['is_hedging']];
            $order['last_px'] = isset($quotation[$order['code']]['last_px']) ? number_format($quotation[$order['code']]['last_px'], 2) : '-';
            $order['pl'] = isset($quotation[$order['code']]['last_px']) ? number_format(($order['last_px'] - $order['price']) * $order['hand'], 2) : "-";
            $order['total_defer'] = $workDay * $order['defer'];
            $this->assign("order", $order);
            return view();
        }
        return "非法操作！";
    }

    // 持仓返点
    public function positionRebate($id = null)
    {
        $order = $this->_logic->orderIncRecordById($id, $state = 3);
        if($order){
            $this->assign("order", $order);
            return view();
        }
        return "非法操作！";
    }

    /*public function buyOk()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Order');
            if(!$validate->scene('buyOk')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $data = [
                    "order_id" => input("post.id/d"),
                    "price" => input("post.price/f"),
                    "state" => 3
                ];
                $res = $this->_logic->updateOrder($data);
                if($res){
                    return $this->ok();
                }else{
                    return $this->fail("操作失败！");
                }
            }
        }else{
            return $this->fail("系统提示：非法操作！");
        }
    }*/

    // 持仓订单对冲
    public function hedging()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Order');
            if(!$validate->scene('hedging')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $data = [
                    "order_id" => input("post.id/d"),
                    "price" => input("post.price/f"),
                    "state" => 3,
                    "is_hedging" => 1,
                    "update_by" => isLogin()
                ];
                $res = $this->_logic->updateOrder($data);
                if($res){
                    return $this->ok();
                }else{
                    return $this->fail("操作失败！");
                }
            }
        }else{
            return $this->fail("系统提示：非法操作！");
        }
    }

    /*public function buyFail()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Order');
            if(!$validate->scene('buyFail')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $res = $this->_logic->buyFail(input("post.id/d"));
                if($res){
                    return $this->ok();
                }else{
                    return $this->fail("操作失败！");
                }
            }
        }else{
            return $this->fail("系统提示：非法操作！");
        }
    }*/

    public function sellOk()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Order');
            if(!$validate->scene('sell')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $res = $this->_logic->sellOk(input("post.id/d"));
                if($res){
                    return $this->ok();
                }else{
                    return $this->fail("操作失败！");
                }
            }
        }else{
            return $this->fail("系统提示：非法操作！");
        }
    }

    public function sellFail()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Order');
            if(!$validate->scene('sell')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $data = [
                    "order_id" => input("post.id/d"),
                    "sell_price" => 0,
                    "sell_hand" => 0,
                    "sell_deposit" => 0,
                    "profit"    => 0,
                    "state"     => 3
                ];
                $res = $this->_logic->updateOrder($data);
                if($res){
                    return $this->ok();
                }else{
                    return $this->fail("操作失败！");
                }
            }
        }else{
            return $this->fail("系统提示：非法操作！");
        }
    }

    // 强制平仓
    public function forceSell()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Order');
            if(!$validate->scene('force')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $orderId = input("post.id/d");
                $price = input("post.sell_price/f");
                $res = $this->_logic->forceSell($orderId, $price);
                if($res){
                    return $this->ok("平仓成功！");
                }else{
                    return $this->fail("强制平仓失败，请稍后重试！");
                }
            }
        }else{
            return $this->fail("系统提示：非法操作！");
        }
    }

    // 送股
    public function give($id = null)
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Order');
            if(!$validate->scene('give')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $orderId = input("post.id/d");
                $price = input("post.price/f");
                $order = $this->_logic->orderById($id);
                $data = [
                    "order_id"  => $orderId,
                    "price"     => $price,
                    "hand"      => input("post.hand/d"),
                    "stop_profit_price" => input("post.profit/f"),
                    "stop_profit_point" => round((input("post.profit/f") - $price) / $price * 100, 2),
                    "stop_loss_price" => input("post.loss/f"),
                    "stop_loss_point" => round(($price - input("post.loss/f")) / $price * 100, 2),
                    "sell_hand" => input("post.hand/d"),
                    "sell_deposit" => $order['sell_price'] * input("post.hand/d"),
                    "profit"    => ($order['sell_price'] - $price) * input("post.hand/d"),
                ];
                $res = $this->_logic->orderGive($data);
                if($res){
                    return $this->ok("操作成功！");
                }else{
                    return $this->fail("操作失败，请稍后重试！");
                }
            }
        }else{
            $order = $this->_logic->orderById($id, $state = 6);
            if($order){
                $this->assign("order", $order);
                return view();
            }else{
                return "非法操作！";
            }
        }
    }

    // 穿仓
    public function ware()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Order');
            if(!$validate->scene('ware')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $orderId = input("post.id/d");
                $price = input("post.sell/f");
                $res = $this->_logic->orderWare($orderId, $price);
                if($res){
                    return $this->ok("操作成功！");
                }else{
                    return $this->fail("操作失败，请稍后重试！");
                }
            }
        }else{
            return $this->fail("系统提示：非法操作！");
        }
    }

    // 订单转持仓
    public function toPosition()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Order');
            if(!$validate->scene('toPosition')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $orderId = input("post.id/d");
                $res = $this->_logic->orderToPosition($orderId);
                if($res){
                    return $this->ok("操作成功！");
                }else{
                    return $this->fail("操作失败，请稍后重试！");
                }
            }
        }else{
            return $this->fail("系统提示：非法操作！");
        }
    }
}