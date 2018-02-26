<?php
namespace app\admin\logic;

use app\admin\model\Stock;

class StockLogic
{
    public function stockByCode($code)
    {
        return Stock::where(['code' => $code])->find();
    }
}