<?php
namespace app\index\validate;

use app\index\logic\OrderLogic;
use app\index\logic\StockLogic;
use app\index\logic\UserLogic;
use think\Validate;

class Order extends Validate
{
    protected $rule = [
        'ids'   => "require|array|min:1|checkDatetime",
        'id'    => "require|checkDatetime|checkOrder",
        'deposit' => "require|float|gt:0|checkUserAccount",
        "profit" => "require|float|gt:0",
        "loss"  => "require|float|gt:0|checkLossValue"
    ];

    protected $message = [
        'ids.require'   => "系统错误：非法操作！",
        'ids.array'     => "系统错误：非法操作！",
        'ids.min'       => "系统错误：非法操作！",
        'ids.checkDatetime' => "非交易时间段！",
        'id.require'    => "系统错误：非法操作！",
        'id.checkDatetime'  => "非交易时间段！",
        'id.checkOrder' => "系统错误：非法操作！",
        'id.canSelling' => "系统错误：非法操作！",
        'id.canModifyPl' => "系统错误：非法操作！",
        'deposit.require' => "请输入补充金额！",
        'deposit.float' => "补充金额必须为数字！",
        'deposit.gt'    => "补充金额必须大于0！",
        'deposit.checkUserAccount' => "账户余额不足，请充值！",
        "profit.require" => "止盈金额不能为空！",
        "profit.float"  => "止盈金额必须为数字！",
        "profit.gt"     => "止盈金额必须大于0！",
        "loss.require"  => "止损金额不能为空！",
        "loss.float"    => "止损金额必须为数字！",
        "loss.gt"       => "止损金额必须大于0！",
        "loss.checkLoss" => "止损金额输入错误！",
    ];

    protected $scene = [
        "realPosition"  => ["ids"],
        "cancel"    => ["id"],
        "selling"   => [
            "id" => "require|checkDatetime|canSelling"
        ],
        "deposit"   => [
            "id" => "require|checkDatetime|checkDeposit",
            "deposit"
        ],
        "modifyPl"  => [
            "id" => "require|canModifyPl",
            "profit",
            "loss" => "require|float|gt:0|checkLossValue"
        ]
    ];

    protected function checkDatetime($value, $rule, $data)
    {
        return checkStockTradeTime();
    }

    protected function checkOrder($value, $rule, $data)
    {
        $order = (new UserLogic())->userOrderById(isLogin(), $value, [1, 4]);
        return $order ? true : false;
    }

    protected function canSelling($value, $rule, $data){
        $order = (new UserLogic())->userOrderById(isLogin(), $value, 3);
        if($order){
            $order = reset($order);
            $holiday = explode(',', cf('holiday', ''));
            $timestamp = workTimestamp(1, $holiday, $order['create_at']);
            $timestamp = strtotime(date("Y-m-d", $timestamp));
            if($timestamp > request()->time()){
                return "建仓未满1个交易日，无法平仓！";
            }else{
                return true;
                /*$suspension = explode(",", cf("suspension", ""));
                if(in_array($order['code'], $suspension)){
                    return "股票无法平仓！";
                }else{
                    return true;
                }*/
            }
        }
        return false;
    }

    protected function canModifyPl($value, $rule, $data){
        $order = (new UserLogic())->userOrderById(isLogin(), $value, 3);
        return $order ? true : false;
    }

    protected function checkDeposit($value, $rule, $data)
    {
        $order = (new UserLogic())->userOrderById(isLogin(), $value, 3);
        return $order ? true : false;
    }

    protected function checkUserAccount($value, $rule, $data){
        return $value <= uInfo()['account'];
    }

    protected function checkLoss($value, $rule, $data)
    {
        $order = (new UserLogic())->userOrderById(isLogin(), $data['id'], 3);
        if($order){
            $_profit = $data['profit'];
            $order = reset($order);
            $price = $order["price"]; //买入价
            if($_profit > $price){
                if($value < $price){
                    $deposit = $order["deposit"]; //保证金
                    $_deposit = ($price - $value) * $order['hand']; //调整后的保证金
                    if($_deposit > $deposit){
                        // 需要增加保证金
                        $diff = $_deposit - $deposit;
                        if(uInfo()['account'] >= $diff){
                            return true;
                        }else{
                            return "您的余额不足，请充值！";
                        }
                    }else{
                        return true;
                    }
                }else{
                    return "止损金额必须小于买入价！";
                }
            }else{
                return "止盈金额必须大于买入价！";
            }
        }else{
            return "系统错误：非法操作！";
        }
    }

    protected function checkLossValue($value, $rule, $data)
    {
        $order = (new UserLogic())->userOrderById(isLogin(), $data['id'], 3);
        if($order){
            $order = reset($order);
            while (true){
                $quotation = (new StockLogic())->simpleData($order['code']);
                if(isset($quotation[$order['code']]) && !empty($quotation[$order['code']])){
                    $last_px = $quotation[$order['code']]['last_px']; // 最新价
                    if($value >= $last_px){
                        return "止损价必须小于现价！";
                    }else{
                        $_profit = $data['profit'];
                        $price = $order["price"]; //买入价
                        if($_profit > $price){
                            if($value < $price){
                                //新止损点
                                $lossPoint = round((($price - $value) / $price * 100), 2);
                                //补充保证金起始止损点
                                $originalLossPoint = $order['stop_loss_point'] >= 8 ? $order['stop_loss_point'] : 8;
                                // 需补充的止损比例
                                $lossDiffPoint = $lossPoint - $originalLossPoint;
                                if($lossPoint > 8 && $lossDiffPoint > 0){
                                    // 需要补充保证金
                                    // 所需补充的金额
                                    $_deposit = $order["original_deposit"] * $order['lever'] * $lossDiffPoint / 100;
                                    if($_deposit > 0 && uInfo()['account'] >= $_deposit){
                                        return true;
                                    }else{
                                        return "您的余额不足，请充值！";
                                    }
                                }else{
                                    // 不需要补充保证金
                                    return true;
                                }
                            }else{
                                return "止损金额必须小于买入价！";
                            }
                        }else{
                            return "止盈金额必须大于买入价！";
                        }
                    }
                    break;
                }else{
                    continue;
                }
            }
        }else{
            return "系统错误：非法操作！";
        }
    }
}