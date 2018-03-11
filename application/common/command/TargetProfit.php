<?php
/**
 * Created by PhpStorm.
 * User: bruce
 * Date: 18/3/5
 * Time: 上午4:25
 */

namespace app\common\command;
use app\admin\logic\OrderLogic;
use app\index\logic\StockLogic;
use think\console\Command;
use think\console\Input;
use think\console\Output;
/**
 * 计划任务 止盈止损处理
 * @author bruce
 *
 */
class TargetProfit extends Command
{

    protected function configure(){
        $this->setName('TargetProfit')->setDescription('计划任务 -- 止盈止损 -- 保证金处理');
    }

    protected function execute(Input $input, Output $output){
        $output->writeln('TargetProfit Crontab job start...');
        /*** 这里写计划任务列表集 START ***/

        $this->work();


        /*** 这里写计划任务列表集 END ***/
        $output->writeln('TargetProfit Crontab job end...');
    }

    /**
     * @return bool
     */
    private function work(){
        $orderLogic = new OrderLogic();
        //调用方法判断是否执行
        if(!self::checkStockTradeTime()) return true;
        $data = [];
        //查询所有持仓中的订单
        $position = $orderLogic->getAllBy(['state' => 3]);
        //取出持仓订单code
        $codeArr = $orderLogic->getCodeBy(['state' => 3]);
        //取回实时行情
        $lists = (new StockLogic())->simpleData($codeArr);
        $price_desc = 0.1;
        foreach($position as $v)
        {
            $current_price = $lists[$v['code']]['last_px'];
            $stop_profit_price = $v['stop_profit_price'];//止盈金额
            $stop_loss_price = $v['stop_loss_price'];//止损金额

            if($current_price)
            {
                if($stop_profit_price-$current_price > $price_desc)//止盈平仓
                {
                    $data[] = [
                        'user_id' => $v['user_id'],
                        'order_id' => $v['order_id'],
                        'kui' => ($v['price']-$current_price)*$v['hand'],
                        'deposit' => $v['deposit'],
                        'current'   => $current_price,
                        'hand'      => $v['hand'],
                        'price'     => $v['price'],
                        'name' => $v['name'],
                        'type' => 1,
                        'title' => '订单ID【'. $v['order_id'] .'】需止盈强制平仓',
                        'content' => '订单ID【'. $v['order_id'] .'】需止盈强制平仓',
                    ];

                }
                if($current_price-$stop_loss_price < $price_desc)//止损平仓
                {
                    $data[] = [
                        'user_id' => $v['user_id'],
                        'order_id' => $v['order_id'],
                        'kui' => ($v['price']-$current_price)*$v['hand'],
                        'deposit' => $v['deposit'],
                        'current'   => $current_price,
                        'hand'      => $v['hand'],
                        'price'     => $v['price'],
                        'name' => $v['name'],
                        'type' => 2,
                        'title' => '订单ID【'. $v['order_id'] .'】需止损强制平仓',
                        'content' => '订单ID【'. $v['order_id'] .'】需止损强制平仓',
                    ];
                }

            }
        }

        self::doHandle($data);

    }

    private function doHandle($data = [])
    {
//        !empty($data) ? cache('pingcang', json_encode($data)) : '';
        if(!empty($data))
        {
            $orderLogic = new OrderLogic();
            cache('pingcang', json_encode($data));
            foreach ($data as $v)
            {

                $orderLogic->updateOrder([
                    'order_id' => $v['order_id'],
                    'state' => 6,
                    'sell_price'   => $v['current'],
                    'sell_hand' => $v['hand'],
                    'sell_deposit' => $v['current']*$v['hand'],
                    'profit' => ($v['current']-$v['price'])*$v['hand'],
                ]);

            }
        }

    }

    function checkStockTradeTime()
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
        $holiday = explode(',', cfgs()['holiday']);
        if(in_array(date("Y-m-d"), $holiday)){
            return false;
        }
        return true;
    }
}