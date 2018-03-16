<?php
namespace app\index\validate;

use app\index\logic\DepositLogic;
use app\index\logic\LeverLogic;
use app\index\logic\ModeLogic;
use app\index\logic\OrderLogic;
use app\index\logic\StockLogic;
use think\Validate;

class Stock extends Validate
{
    protected $rule = [
        'follow_id' => 'number|checkFollowId',
        'price' => 'require|float|gt:0|checkTradeTime',
        'code'  => 'require|checkCode',
        'mode'  => 'require|checkMode',
        'deposit' => 'require|checkDeposit',
        'lever' => 'require|checkLever',
        'profit' => 'require|float|checkProfit',
        'loss'  => 'require|float|checkLoss',
        'defer' => 'require|in:1,0',
    ];

    protected $message = [
        'follow_id.number'  => '系统提示:非法操作！',
        'follow_id.checkFollowId' => '系统提示:非法操作！',
        'price.require'     => '系统提示:非法操作！',
        'price.float'       => '系统提示:非法操作！',
        'price.gt'          => '系统提示:非法操作！',
        'price.checkTradeTime' => '非交易时间，不可购买！',
        'code.require'      => '系统提示:非法操作！',
        'code.checkCode'    => '股票不存在！',
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
        'buy' => ['price', 'code', 'mode', 'deposit', 'lever', 'profit', 'loss', 'defer'],
    ];

    protected function checkCode($value, $rule, $data)
    {
        $stock = (new StockLogic())->stockByCode($value);
        if($stock){
            $quotation = (new StockLogic())->simpleData($value);
            if(isset($quotation[$value]) && !empty($quotation[$value])){
                $changeRate = $quotation[$value]["px_change_rate"];
                $profitRate = cf('max_profit_rate', 9.5);
                if($changeRate >= $profitRate){
                    return "最大可购买涨幅为{$profitRate}的股票！";
                }else{
                    return true;
                }
            }
            return true;
        }
        return false;
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
        if($value > $data['price']){
            $mode = (new ModeLogic())->modeById($data['mode']);
            $min = round($data['price'] * (1 + $mode['profit'] / 100), 2);
            return $value < $min ? "止盈最小可设置为" . number_format($min, 2) : true;
        }else{
            return "止盈金额不能小于策略委托价！";
        }
    }

    protected function checkLoss($value, $rule, $data)
    {
        if($value < $data['price']){
            $mode = (new ModeLogic())->modeById($data['mode']);
            $max = round($data['price'] * (1 - $mode['loss'] / 100), 2);
            if($value > $max){
                return "止损金额最大可设置为" . number_format($max, 2);
            }else{
                $configs = cfgs();
                $usage = isset($configs["capital_usage"]) && !$configs["capital_usage"] ? $configs["capital_usage"] : 95;
                $deposit = (new DepositLogic())->depositById($data["deposit"]);
                $lever = (new LeverLogic())->leverById($data["lever"]);
                $total = $deposit["money"] * $lever["multiple"]; // 申请总配资款 = 保证金 * 杠杆倍数
                $realTotal = $total * $usage / 100; // 实际可使用最大配资款(95%)
                $hand = floor($realTotal / $data['price'] / 100) * 100; // 买入股数(整百)
                $min = $data['price'] - ($deposit["money"] / $hand); // (买入价-止损价)*买入手数=损失总金额 so====> 最小止损价=买入价-(保证金/买入手数)
                return $value < $min ? "止损金额最小可设置为" . number_format($max, 2) : true;
            }
        }else{
            return "止损金额必须小于策略委托价！";
        }
    }

    protected function checkFollowId($value, $rule, $data)
    {
        if($value){
            $order = (new OrderLogic())->orderById($value);
            
            if($order){
                if($order['user_id'] == isLogin()){
                    return "不可跟买自己的策略！";
                }else{
                    if($data['code'] == $order['code']){
                        return true;
                    }else{
                        return false;
                    }
                }
            }
            return false;
        }
        return true;
    }
}