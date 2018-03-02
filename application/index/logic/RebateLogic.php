<?php
namespace app\index\logic;

use think\Db;
use app\index\model\User;
use app\index\model\Order;

class RebateLogic
{
    protected $_config;
    public function __construct()
    {
        $this->_config = cfgs();
    }

    // 处理跟买牛人订单返点 $niurenUserId-牛人用户ID $orderId-返点订单ID $money-盈利金额
    public function handleNiurenRebate($niurenUserId, $orderId, $money)
    {
        Db::startTrans();
        try{
            // 牛人返点率(%)
            $point = isset($this->_config['niuren_point']) ? floatval($this->_config['niuren_point']) : 5;
            $rebateMoney = sprintf("%.2f", substr(sprintf("%.3f", $money * $point / 100), 0, -1)); //分成金额
            // 牛人总收入增加
            $niuren = User::find($niurenUserId);
            $niuren->hasOneNiuren->setInc('income', $rebateMoney);
            // 牛人可转收入增加
            $niuren->hasOneNiuren->setInc('sure_income', $rebateMoney);
            // 牛人收入明细
            $rData = [
                "money" => $rebateMoney,
                "type"  => 0,
                "order_id" => $orderId,
            ];
            $niuren->hasManyNiurenRecord()->save($rData);
            // 订单标识为已结算订单
            Order::update(["order_id" => $orderId, "niuren_rebate" => 1]);
            Db::commit();
            return true;
        }catch (\Exception $e){
            dump($e->getMessage());
            Db::rollback();
            return false;
        }
    }
}