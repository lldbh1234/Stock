<?php
namespace app\index\validate;

use app\index\logic\StockLogic;
use think\Validate;

class Stock extends Validate
{
    protected $rule = [
        'code'  => 'require|checkCode|checkTradeTime',
        'mode'  => 'require|checkMode',
        'deposit' => 'require|checkDeposit',
        'lever' => 'require|checkLever',
        'profit' => 'require|float',
        'loss'  => 'require|float',
        'defer' => 'require|in:1,2',
    ];

    protected $message = [
        'code.require'  => '系统提示:非法操作！',
        'code.checkCode' => '股票不存在！',
        'code.checkTradeTime' => '非交易时间，不可购买！',
        'mode.require'  => '请选择策略模式！',
        'mode.checkMode' => '非交易时间，不可购买！',
    ];

    protected $scene = [
        'create' => ['code'],
    ];

    protected function checkCode($value, $rule, $data)
    {
        $stock = (new StockLogic())->stockByCode($value);
        return $stock ? true : false;
    }

    protected function checkTradeTime($value, $rule, $data)
    {
        return checkStockTradeTime();
    }

    protected function checkMode()
    {

    }
}