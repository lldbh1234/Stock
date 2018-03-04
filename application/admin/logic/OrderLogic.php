<?php
namespace app\admin\logic;

use app\admin\model\Order;
use app\admin\model\User;
use think\Db;

class OrderLogic
{
    public function pageOrderLists($state = null, $where = [], $pageSize = null)
    {
        $state ? is_array($state) ? $where['state'] = ["IN", $state] : $where['state'] = $state : $where['state'] = ["NEQ", 5];
        $pageSize = $pageSize ? : config("page_size");
        $lists = Order::with("hasOneUser")->where($where)->order("order_id DESC")->paginate($pageSize);
        $records = $lists->toArray();
        $defer = [1 => '是', 0 => '否'];
        $follow = [1 => '是', 0 => '否'];
        $state = [1 => '委托建仓', 2 => '平仓', 3 => '持仓', 4 => '委托平仓', 5 => '作废'];
        array_filter($records['data'], function(&$item) use ($defer, $follow, $state){
            $item['is_defer_text'] = $defer[$item['is_defer']];
            $item['state_text'] = $state[$item['state']];
            $item['is_follow_text'] = $follow[$item['is_follow']];
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
}