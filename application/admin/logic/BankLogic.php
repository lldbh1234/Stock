<?php
namespace app\admin\logic;


use app\admin\model\Bank;

class BankLogic
{
    public function bankLists()
    {
        $banks = Bank::where(["state" => 1])->select();
        return $banks ? collection($banks)->toArray() : [];
    }

    public function bankByNumber($number)
    {
        $bank = Bank::where(["number" => $number])->find();
        return $bank ? $bank->toArray() : [];
    }

    public function bankByName($name)
    {
        $bank = Bank::where(["name" => $name])->find();
        return $bank ? $bank->toArray() : [];
    }
}