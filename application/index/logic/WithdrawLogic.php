<?php
namespace app\index\logic;

use app\index\model\UserWithdraw;

class WithdrawLogic
{
    public function orderByTradeNo($tradeNo, $state = null)
    {
        $where['out_sn'] = $tradeNo;
        is_null($state) ? null : $where['state'] = $state;
        $order = UserWithdraw::where($where)->find();
        return $order ? $order->toArray() : [];
    }

    public function updateByTradeNo($tradeNo, $data)
    {
        $where = ["out_sn" => $tradeNo];
        return UserWithdraw::update($data, $where);
    }
}