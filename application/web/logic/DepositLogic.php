<?php
namespace app\web\logic;


use app\web\model\Deposit;

class DepositLogic
{
    public function allDeposits()
    {
        $deposits = Deposit::where(["status" => 0])->order("sort")->limit(8)->select();
        return $deposits ? collection($deposits)->toArray() : [];
    }

    public function depositById($id)
    {
        $deposit = Deposit::find($id);
        return $deposit ? $deposit->toArray() : [];
    }
}