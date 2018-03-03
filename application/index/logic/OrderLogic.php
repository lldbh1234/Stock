<?php
namespace app\index\logic;

use think\Db;
use app\index\model\Order;
use app\index\model\User;

class OrderLogic
{
    public function createOrder($data)
    {
        Db::startTrans();
        try{
            $res = Order::create($data);
            $pk = model("Order")->getPk();
            $user = User::find($data['user_id']);
            $user->setDec("account", $data['jiancang_fee'] + $data['deposit']);
            $user->setInc("blocked_account", $data['deposit']);
            $rData = [
                "type" => 4,
                "amount" => $data['deposit'],
                "remark" => json_encode(['orderId' => $res->$pk]),
                "direction" => 1
            ];
            $user->hasManyRecord()->save($rData);
            $rData = [
                "type" => 0,
                "amount" => $data['jiancang_fee'],
                "remark" => json_encode(['orderId' => $res->$pk]),
                "direction" => 1
            ];
            $user->hasManyRecord()->save($rData);
            Db::commit();
            return $res->$pk;
        } catch (\Exception $e) {
            dump($e->getMessage());
            // 回滚事务
            Db::rollback();
            return 0;
        }
    }
}