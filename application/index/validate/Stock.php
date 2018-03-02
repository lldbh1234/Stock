<?php
namespace app\index\validate;

use app\index\logic\DepositLogic;
use app\index\logic\LeverLogic;
use app\index\logic\ModeLogic;
use app\index\logic\StockLogic;
use think\Validate;

class Stock extends Validate
{
    protected $rule = [
        'code'  => 'require|checkCode|checkTradeTime',
        'mode'  => 'require|checkMode',
        'deposit' => 'require|checkDeposit',
        'lever' => 'require|checkLever',
        'profit' => 'require|float|checkProfit',
        'loss'  => 'require|float|checkLoss',
        'defer' => 'require|in:1,0',
    ];

    protected $message = [
        'code.require'      => '系统提示:非法操作！',
        'code.checkCode'    => '股票不存在！',
        'code.checkTradeTime' => '非交易时间，不可购买！',
        'mode.require'      => '请选择策略模式！',
        'mode.checkMode'    => '策略模式不存在！',
        'deposit.require'   => '请选择信用金！',
        'deposit.checkDeposit' => '信用金选择不正确！',
        'lever.require'     => '请选择策略匹配！',
        'lever.checkLever'  => '策略匹配选择不正确！',
        'profit.require'    => '请输入止盈价格！',
        'profit.float'      => '止盈价格必须为数字！',
        'profit.checkProfit' => '止盈价格错误！',
        'loss.require'      => '请输入止损价格！',
        'loss.float'        => '止损价格必须为数字！',
        'loss.checkLoss'    => '止损价格错误！',
        'defer.require'     => '请选择是否自动递延！',
        'defer.in'          => '自动递延选择错误！',
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

    protected function checkMode($value, $rule, $data)
    {
        $mode = (new ModeLogic())->modeById($value);
        return $mode ? true : false;
    }

    protected function checkDeposit($value, $rule, $data)
    {
        $deposit = (new DepositLogic())->depositById($value);
        return $deposit ? true : false;
    }

    protected function checkLever($value, $rule, $data)
    {
        $lever = (new LeverLogic())->leverById($value);
        return $lever ? true : false;
    }

    protected function checkProfit($value, $rule, $data)
    {
        $stock = (new StockLogic())->simpleData($data['code']);
        $stock = $stock[$data['code']];
        $mode = (new ModeLogic())->modeById($data['mode']);
        $max = $stock['last_px'] * (1 + $mode['profit'] / 100);
        return $value > $max ? "止盈最大可设置为" . number_format($max, 2) : true;
    }

    protected function checkLoss($value, $rule, $data)
    {
        $stock = (new StockLogic())->simpleData($data['code']);
        $stock = $stock[$data['code']];
        $mode = (new ModeLogic())->modeById($data['mode']);
        $min = $stock['last_px'] * (1 - $mode['profit'] / 100);
        return $value < $min ? "止损最小可设置为" . number_format($min, 2) : true;
    }
}