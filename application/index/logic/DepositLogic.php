<?php
namespace app\index\logic;


use app\index\model\Deposit;

class DepositLogic
{
    public function allDeposits()
    {
        $deposits = Deposit::where(["status" => 0])->order("sort")->limit(8)->select();
        return $deposits ? collection($deposits)->toArray() : [];
    }
}