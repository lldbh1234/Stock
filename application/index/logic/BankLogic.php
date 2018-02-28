<?php
namespace app\index\logic;


use app\index\model\Bank;

class BankLogic
{
    public function bankLists()
    {
        $banks = Bank::where(["state" => 1])->select();
        return $banks ? collection($banks)->toArray() : [];
    }
}