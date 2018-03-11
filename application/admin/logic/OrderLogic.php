<?php
namespace app\admin\logic;

use app\admin\model\Admin;
use app\admin\model\Order;
use app\admin\model\User;
use think\Db;

class OrderLogic
{
    public function pageOrderLists($state = null, $where = [], $pageSize = null)
    {
        $where = [];
        $hasWhere = [];
        $myUserIds = Admin::userIds();
        $myUserIds ? $where["stock_order.user_id"] = ["IN", $myUserIds] : null;
        $state ? is_array($state) ? $where['stock_order.state'] = ["IN", $state] : $where['stock_order.state'] = $state : $where['stock_order.state'] = ["NEQ", 5];
        $pageSize = $pageSize ? : config("page_size");
        $lists = Order::hasWhere("hasOneUser", $hasWhere)
                    ->with(["hasOneUser" => ["hasOneParent", "hasOneAdmin" => ["hasOneParent"]]])
                    ->where($where)
                    ->order("order_id DESC")
                    ->paginate($pageSize);
        $records = $lists->toArray();
        $defer = [1 => '是', 0 => '否'];
        $follow = [1 => '是', 0 => '否'];
        $hedging = [1 => '是', 0 => '否'];
        $state = [1 => '委托建仓', 2 => '平仓', 3 => '持仓', 4 => '委托平仓', 5 => '作废'];
        array_filter($records['data'], function(&$item) use ($defer, $follow, $state, $hedging){
            $item['is_defer_text'] = $defer[$item['is_defer']];
            $item['state_text'] = $state[$item['state']];
            $item['is_follow_text'] = $follow[$item['is_follow']];
            $item['is_hedging_text'] = $hedging[$item['is_hedging']];
        });
        return ["lists" => $records, "pages" => $lists->render()];
    }

    public function pagePositionOrders($filter = [], $pageSize = null)
    {
        $where = [];
        $hasWhere = [];
        $myUserIds = Admin::userIds();
        $myUserIds ? $where["stock_order.user_id"] = ["IN", $myUserIds] : null;
        $where['stock_order.state'] = 3;
        // 昵称
        if(isset($filter['nickname']) && !empty($filter['nickname'])){
            $_nickname = trim($filter['nickname']);
            $hasWhere["nickname"] = ["LIKE", "%{$_nickname}%"];
        }
        // 手机号
        if(isset($filter['mobile']) && !empty($filter['mobile'])){
            $hasWhere["mobile"] = trim($filter['mobile']);
        }
        // 股票代码
        if(isset($filter['code']) && !empty($filter['code'])){
            $where['stock_order.code'] = trim($filter['code']);
        }
        // 股票名称
        if(isset($filter['name']) && !empty($filter['name'])){
            $_name = trim($filter['name']);
            $hasWhere["stock_order.name"] = ["LIKE", "%{$_name}%"];
        }
        // 微圈
        if(isset($filter['ring']) && !empty($filter['ring'])){
            $_ring = trim($filter['ring']);
            $_where = ["username" => ["LIKE", "%{$_ring}%"]];
            $adminIds = Admin::where($_where)->column("admin_id");
            $hasWhere["admin_id"] = ["IN", $adminIds];
        }
        // 微会员
        if(isset($filter['member']) && !empty($filter['member'])){
            $_member = trim($filter['member']);
            $_where = ["username" => ["LIKE", "%{$_member}%"]];
            $memberAdminIds = Admin::where($_where)->column("admin_id") ? : [-1];
            $ringAdminIds = Admin::where(["pid" => ["IN", $memberAdminIds]])->column("admin_id") ? : [-1];
            $adminIds = array_unique(array_merge($memberAdminIds, $ringAdminIds));
            $adminIds = $adminIds ? : [-1];
            $userIds = User::where(["admin_id" => ["IN", $adminIds]])->column("user_id");
            if($myUserIds){
                $userIds = array_intersect($userIds, $myUserIds);
            }
            $where["stock_order.user_id"] = ["IN", $userIds];
        }
        // 经纪人
        if(isset($filter['manager']) && !empty($filter['manager'])){
            $_manager = trim($filter['manager']);
            $_where = ["username" => ["LIKE", "%{$_manager}%"]];
            $managerUserIds = User::where($_where)->column("user_id") ? : [-1];
            $hasWhere["parent_id"] = ["IN", $managerUserIds];
        }
        // 提交时间
        if(isset($filter['create_begin']) || isset($filter['create_end'])){
            if(!empty($filter['create_begin']) && !empty($filter['create_end'])){
                $_start = strtotime($filter['create_begin']);
                $_end = strtotime($filter['create_end']);
                $where['stock_order.create_at'] = ["BETWEEN", [$_start, $_end]];
            }elseif(!empty($filter['create_begin'])){
                $_start = strtotime($filter['create_begin']);
                $where['stock_order.create_at'] = ["EGT", $_start];
            }elseif(!empty($filter['create_end'])){
                $_end = strtotime($filter['create_end']);
                $where['stock_order.create_at'] = ["ELT", $_end];
            }
        }
        // 是否对冲
        if(isset($filter['is_hedging']) && is_numeric($filter['is_hedging'])){
            $hasWhere["stock_order.is_hedging"] = $filter['is_hedging'];
        }
        $pageSize = $pageSize ? : config("page_size");
        $lists = Order::hasWhere("hasOneUser", $hasWhere)
            ->with(["hasOneUser" => ["hasOneParent", "hasOneAdmin" => ["hasOneParent"]], "hasOneOperator"])
            ->where($where)
            ->order("order_id DESC")
            ->paginate($pageSize);
        $records = $lists->toArray();
        $hedging = [1 => '是', 0 => '否'];
        $state = [1 => '委托建仓', 2 => '平仓', 3 => '持仓', 4 => '委托平仓', 5 => '作废'];
        array_filter($records['data'], function(&$item) use ($state, $hedging){
            $item['state_text'] = $state[$item['state']];
            $item['is_hedging_text'] = $hedging[$item['is_hedging']];
        });
        return ["lists" => $records, "pages" => $lists->render()];
    }

    public function updateOrder($data)
    {
        return Order::update($data);
    }

    public function buyFail($orderId)
    {
        Db::startTrans();
        try{
            $order = Order::find($orderId)->toArray();
            $data = [
                "order_id" => $order["order_id"],
                "state" => 5
            ];
            Order::update($data);
            // 用户资金
            $user = User::find($order['user_id']);
            $user->setInc("account", $order['jiancang_fee'] + $order['deposit']);
            // 冻结资金
            $user->setDec("blocked_account", $order['deposit']);
            // 资金明细(保证金)
            $rData = [
                "type" => 4,
                "amount" => $order['deposit'],
                "remark" => json_encode(['orderId' => $order["order_id"]]),
                "direction" => 1
            ];
            $user->hasManyRecord()->save($rData);
            // 资金明细(建仓费)
            $rData = [
                "type" => 0,
                "amount" => $order['jiancang_fee'],
                "remark" => json_encode(['orderId' => $order["order_id"]]),
                "direction" => 1
            ];
            $user->hasManyRecord()->save($rData);
            Db::commit();
            return true;
        } catch (\Exception $e){
            Db::rollback();
            return false;
        }
    }

    public function sellOk($orderId)
    {
        Db::startTrans();
        try{
            $order = Order::find($orderId)->toArray();
            $data = [
                "order_id" => $order["order_id"],
                "state" => 2
            ];
            Order::update($data);
            if($order["profit"] > 0){
                // 盈利
                $configs = cfgs();
                $bonus_rate = isset($configs['bonus_rate']) && !empty($configs['bonus_rate']) ? $configs['bonus_rate'] : 90;
                $bonus = round($order["profit"] * $bonus_rate / 100, 2);
                // 用户资金
                $user = User::find($order['user_id']);
                $user->setInc("account", $order['deposit'] + $bonus);
                // 冻结资金
                $user->setDec("blocked_account", $order['deposit']);
                // 资金明细(保证金)
                $rData = [
                    "type" => 4,
                    "amount" => $order['deposit'],
                    "remark" => json_encode(['orderId' => $order["order_id"]]),
                    "direction" => 1
                ];
                $user->hasManyRecord()->save($rData);
                // 资金明细(分红)
                $rData = [
                    "type" => 7,
                    "amount" => $bonus,
                    "remark" => json_encode(['orderId' => $order["order_id"]]),
                    "direction" => 1
                ];
                $user->hasManyRecord()->save($rData);
            }else{
                // 亏损
                // 用户资金
                $user = User::find($order['user_id']);
                $user->setInc("account", $order['deposit'] + $order["profit"]);
                // 冻结资金
                $user->setDec("blocked_account", $order['deposit']);
                // 资金明细(保证金)
                $rData = [
                    "type" => 4,
                    "amount" => $order['deposit'] + $order["profit"],
                    "remark" => json_encode(['orderId' => $order["order_id"]]),
                    "direction" => 1
                ];
                $user->hasManyRecord()->save($rData);
            }
            Db::commit();
            return true;
        } catch (\Exception $e){
            Db::rollback();
            return false;
        }
    }

    // 强制平仓
    public function forceSell($orderId, $price)
    {
        Db::startTrans();
        try{
            $order = Order::find($orderId)->toArray();
            // 订单更改
            $data = [
                "order_id" => $orderId,
                "sell_price" => $price,
                "sell_hand" => $order["hand"],
                "sell_deposit" => $order["hand"] * $price,
                "profit" => ($price - $order["price"]) * $order["hand"],
                "state" => 2,
                "update_by" => isLogin()
            ];
            Order::update($data);
            // 分成
            if($data["profit"] > 0){
                // 盈利
                $configs = cfgs();
                $bonus_rate = isset($configs['bonus_rate']) && !empty($configs['bonus_rate']) ? $configs['bonus_rate'] : 90;
                $bonus = round($order["profit"] * $bonus_rate / 100, 2);
                // 用户资金
                $user = User::find($order['user_id']);
                $user->setInc("account", $order['deposit'] + $bonus);
                // 冻结资金
                $user->setDec("blocked_account", $order['deposit']);
                // 资金明细(保证金)
                $rData = [
                    "type" => 4,
                    "amount" => $order['deposit'],
                    "remark" => json_encode(['orderId' => $order["order_id"]]),
                    "direction" => 1
                ];
                $user->hasManyRecord()->save($rData);
                // 资金明细(分红)
                $rData = [
                    "type" => 7,
                    "amount" => $bonus,
                    "remark" => json_encode(['orderId' => $order["order_id"]]),
                    "direction" => 1
                ];
                $user->hasManyRecord()->save($rData);
            }else{
                // 亏损
                // 用户资金
                $user = User::find($order['user_id']);
                $user->setInc("account", $order['deposit'] + $order["profit"]);
                // 冻结资金
                $user->setDec("blocked_account", $order['deposit']);
                // 资金明细(保证金)
                $rData = [
                    "type" => 4,
                    "amount" => $order['deposit'] + $order["profit"],
                    "remark" => json_encode(['orderId' => $order["order_id"]]),
                    "direction" => 1
                ];
                $user->hasManyRecord()->save($rData);
            }
            Db::commit();
            return true;
        }catch(\Exception $e){
            Db::rollback();
            return false;
        }
    }

    public function getAllBy($where=[])
    {
        $map = [];
        if(!empty($where) && is_array($where))
        {
            foreach($where as $k => $v)
            {
                $map[$k] = $v;
            }
        }
        $data = Order::where($map)->select();
        return collection($data)->toArray();
    }
    public function getCodeBy($where=[])
    {
        $map = [];
        if(!empty($where) && is_array($where))
        {
            foreach($where as $k => $v)
            {
                $map[$k] = $v;
            }
        }
        return Order::where($map)->column('code');
    }
}