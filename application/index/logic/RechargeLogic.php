<?php
namespace app\index\logic;

use app\index\model\User;
use app\index\model\UserRecharge;
use think\Db;

class RechargeLogic
{
    public function orderByTradeNo($tradeNo, $state = null)
    {
        $where['trade_no'] = $tradeNo;
        is_null($state) ? null : $where['state'] = $state;
        $order = UserRecharge::where($where)->find();
        return $order ? $order->toArray() : [];
    }

    public function createRechargeOrder($userId, $amount, $type)
    {
        $tradeNo = $type == 3 ? createStrategyShortSn() : createStrategySn(); // 3汇付天下，订单号最长20位
        $data = [
            "user_id" => $userId,
            "trade_no" => $tradeNo,
            "amount" => $amount,
            "type"  => $type,
        ];
        $res = UserRecharge::create($data);
        return $res ? $data['trade_no'] : false;
    }

    public function rechargeComplete($tradeNo, $amount, $userId, $outTradeNo)
    {
        Db::startTrans();
        try{
            // 修改订单状态
            $data = ["out_trade_no" => $outTradeNo, "state" => 1];
            $where = ["trade_no" => $tradeNo, "state" => 0];
            UserRecharge::update($data, $where);
            // 用户余额增加
            $user = User::find($userId);
            $user->setInc("account", $amount);
            // 用户资金明细增加
            $rData = [
                "type" => 5,
                "amount" => $amount,
                "account" => $user->account,
                "remark" => json_encode(['tradeNo' => $tradeNo]),
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
}