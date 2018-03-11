<?php
namespace app\web\logic;

use app\web\model\Admin;
use think\Db;
use app\web\model\User;
use app\web\model\Order;

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
            Db::rollback();
            return false;
        }
    }

    public function handleProxyRebate($managerId, $admins, $orderId, $money)
    {
        Db::startTrans();
        try{
            if($managerId){
                $manager = User::find($managerId);
                $managerData = $manager->hasOneManager->toArray();
                $rebateMoney = sprintf("%.2f", substr(sprintf("%.3f", $money * $managerData['point'] / 100), 0, -1)); //分成金额
                // 经纪人总收入增加
                $manager->hasOneManager->setInc('income', $rebateMoney);
                // 经纪人可转收入增加
                $manager->hasOneManager->setInc('sure_income', $rebateMoney);
                // 经纪人收入明细
                $rData = [
                    "money" => $rebateMoney,
                    "type"  => 0, //0-直属用户收益分成
                    "order_id" => $orderId,
                ];
                $manager->hasManyManagerRecord()->save($rData);
            }
            // 各级代理商返点
            foreach ($admins as $admin){
                $point = $admin["point"];
                if($point > 0){
                    $rebateMoney = sprintf("%.2f", substr(sprintf("%.3f", $money * $point / 100), 0, -1)); //分成金额
                    Admin::find($admin['admin_id'])->setInc('total_fee', $rebateMoney);;
                }
            }
            // 订单标识为已结算订单
            Order::update(["order_id" => $orderId, "proxy_rebate" => 1]);
            Db::commit();
            return true;
        }catch (\Exception $e){
            Db::rollback();
            return false;
        }
    }
}