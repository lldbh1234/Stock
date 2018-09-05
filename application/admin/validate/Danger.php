<?php
namespace app\admin\validate;

use app\admin\logic\DangerLogic;
use think\Validate;
use app\admin\logic\StockLogic;

class Danger extends Validate
{
    protected $rule = [
        "code"  => "require|checkCode|checkUnique",
    ];

    protected $message = [
        "code.require"  => "股票代码不能为空！",
        "code.checkCode"  => "股票代码不存在！",
        "code.checkUnique" => "股票代码已经添加！",
    ];

    protected $scene = [
        "create" => ["code"],
    ];

    protected function checkCode($value)
    {
        $stock = (new StockLogic())->stockByCode($value);
        return $stock ? true : false;
    }

    protected function checkUnique($value)
    {
        $danger = (new DangerLogic())->dangerByCode($value);
        return $danger ? false : true;
    }
}