<?php
namespace app\index\logic;


use app\index\model\Deposit;

class DepositLogic
{
    public function allDeposits()
    {
        $deposits = Deposit::where(["status" => 0])->order("sort,id")->select();
        return $deposits ? collection($deposits)->toArray() : [];
    }

    public function depositById($id)
    {
        $deposit = Deposit::find($id);
        return $deposit ? $deposit->toArray() : [];
    }
}