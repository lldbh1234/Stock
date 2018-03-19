<?php
/**
 * Created by PhpStorm.
 * User: bruce
 * Date: 18/3/5
 * Time: 上午4:25
 */

namespace app\index\command;
use app\admin\logic\OrderLogic;
use app\index\logic\StockLogic;
use app\index\model\System;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Queue;

/**
 * 计划任务 保证金和止盈止损的处理
 * @author bruce
 *
 */
class HandBond extends Command
{

    protected function configure(){
        $this->setName('HandBond')->setDescription('计划任务 保证金和止盈止损的处理');
    }

    protected function execute(Input $input, Output $output){
        $output->writeln('HandBond Crontab job begin at ...'.date('Y-m-d H:i:s'));
        /*** 这里写计划任务列表集 START ***/

        $this->work();


        /*** 这里写计划任务列表集 END ***/
        $output->writeln('HandBond Crontab job end...'.date('Y-m-d H:i:s'));
    }

    /**
     * @return bool
     */
    private function work(){
        $orderLogic = new OrderLogic();
        //调用方法判断是否执行
        if(!self::checkStockTradeTime()) return false;
        //查询所有持仓中的订单
        $position = $orderLogic->getAllBy(['state' => 3]);
        if(!$position) return false;
        //取出持仓订单code
        $codeArr = $orderLogic->getCodeBy(['state' => 3]);
        //取回持仓订单实时行情列表
        $lists = (new StockLogic())->simpleData($codeArr);
        //取出亏损订单
        foreach($position as $v)
        {
            $current_price      = $lists[$v['code']]['last_px'];//现价
            $stop_profit_price  = $v['stop_profit_price'];//止盈金额
            $stop_loss_price    = $v['stop_loss_price'];//止损金额
            $deposit            = $v['deposit'];
            $deposit70          = $deposit*0.7;
            $deposit50          = $deposit*0.5;
            //处理异常<-->拿不到实时行情
            if(isset($lists[$v['code']]['last_px']))
            {
                //计算爆仓状态订单
                if(($v['price']-$current_price)*$v['hand'] >= $v['deposit'])//只有跌了才有可能爆仓
                {
                    $blasting_loss = [
                        'user_id'       => $v['user_id'],
                        'order_id'      => $v['order_id'],
                        'sell_price'    => $current_price,
                        'sell_hand'     => $v['hand'],
                        'sell_deposit'  => $current_price*$v['hand'],
                        'profit'        => ($current_price-$v['price'])*$v['hand'],
                        'state'         => 6,
                        'force_type'    => 1,
                        'title'         => '订单ID【'. $v['order_id'] .'】已爆仓需强制平仓',
                        'content'       => '订单ID【'. $v['order_id'] .'】已爆仓需强制平仓',
                    ];
                    Queue::push('app\index\job\SystemClosePosition@doHandle', $blasting_loss, null);

                }else{

                    //保证金不足
                    if($v['price'] > $current_price)//如果亏损了
                    {
                        $profit = ($v['price']-$current_price)*$v['hand'];
                        if($profit >= $deposit50 && $profit < $deposit70)//保证金不足50%
                        {
                            //补充保证金
                            $loss_50 = [
                                'user_id'   => $v['user_id'],
                                'order_id'  => $v['order_id'],
                                'type'      => '50',
                                'title'     => '持仓股票【'. $v['name'] .'】的保证金已经不足50%,请及时补充保证金，以防强制平仓',
                                'content'   => '持仓股票【'. $v['name'] .'】的保证金已经不足50%,请及时补充保证金，以防强制平仓',
                            ];
                            Queue::push('app\index\job\UserNotice@systemNotice', $loss_50, null);
                        }
                        if($profit >= $deposit70 && $profit < $deposit)//保证金不足30%
                        {
                            //补充保证金
                            $loss_30 = [
                                'user_id'   => $v['user_id'],
                                'order_id'  => $v['order_id'],
                                'type'      => '30',
                                'title'     => '持仓股票【'. $v['name'] .'】的保证金已经不足30%,请及时补充保证金，以防强制平仓',
                                'content'   => '持仓股票【'. $v['name'] .'】的保证金已经不足30%,请及时补充保证金，以防强制平仓',
                            ];
                            Queue::push('app\index\job\UserNotice@systemNotice', $loss_30, null);
                        }
                    }

                    //止盈
                    if($current_price >= $stop_profit_price)//到达止盈金额
                    {
                        $stop_profit = [
                            'user_id'       => $v['user_id'],
                            'order_id'      => $v['order_id'],
                            'sell_price'    => $current_price,
                            'sell_hand'     => $v['hand'],
                            'sell_deposit'  => $current_price*$v['hand'],
                            'profit'        => ($current_price-$v['price'])*$v['hand'],
                            'state'         => 6,
                            'force_type'    => 2,
                            'title'         => '订单ID【'. $v['order_id'] .'】需止盈强制平仓',
                            'content'       => '订单ID【'. $v['order_id'] .'】需止盈强制平仓',
                        ];

                        Queue::push('app\index\job\SystemClosePosition@doHandle', $stop_profit, null);
                    }
                    //止损
                    if($current_price <= $stop_loss_price)//到达止损金额
                    {
                        $stop_loss = [
                            'user_id'       => $v['user_id'],
                            'order_id'      => $v['order_id'],
                            'sell_price'    => $current_price,
                            'sell_hand'     => $v['hand'],
                            'sell_deposit'  => $current_price*$v['hand'],
                            'profit'        => ($current_price-$v['price'])*$v['hand'],
                            'state'         => 6,
                            'force_type'    => 2,
                            'title'         => '订单ID【'. $v['order_id'] .'】需止损强制平仓',
                            'content'       => '订单ID【'. $v['order_id'] .'】需止损强制平仓',
                        ];
                        Queue::push('app\index\job\SystemClosePosition@doHandle', $stop_loss, null);
                    }

                }

            }

        }

    }

    private function checkStockTradeTime()
    {
        if(date('w') == 0){
            return false;
        }
        if(date('w') == 6){
            return false;
        }
        if(date('G') < 9 || (date('G') == 9 && date('i') < 30)){
            return false;
        }
        if(((date('G') == 11 && date('i') > 30) || date('G') > 11) && date('G') < 13){
            return false;
        }
        if(date('G') > 15){
            return false;
        }
//        $holiday = explode(',', cfgs()['holiday']);
        $holiday = explode(',', self::cf('holiday', ''));
        if(in_array(date("Y-m-d"), $holiday)){
            return false;
        }
        return true;
    }
    private function cf($alias, $default='')
    {
        $value = (new System())->where(["alias" => $alias])->value("val");
//        $value = model("System")->where(["alias" => $alias])->value("val");
        return is_null($value) ? $default : $value;
    }

}