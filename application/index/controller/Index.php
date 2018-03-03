<?php
namespace app\index\controller;

use app\index\logic\OrderLogic;
use app\index\logic\UserFollowLogic;
use app\index\logic\UserLogic;
use think\Request;

class Index extends Base
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
    }

    public function index()
    {
        $userLogic = new UserLogic();
        $orderLogic = new OrderLogic();
        $userFollowLogic = new UserFollowLogic();
        $bestUserList =  $userLogic->getAllBy(['is_niuren' => 1]);
        foreach($bestUserList as $k => $v)
        {
            $bestUserList[$k] = array_merge($v, $userLogic->userDetail($v['user_id'], ['state' => 2]));//抛出

        }
        $bestUserList = collection($bestUserList)->sort(function ($a, $b){
            return $b['strategy_yield'] - $a['strategy_yield'];
        })->slice(0,5)->toArray();//排序
//        $bestUserList = collection($bestUserList)->sort(function($a,$b)
//        {
//            if ($a['strategy_yield']==$b['strategy_yield']) return 0;
//            return ($a['strategy_yield']>$b['strategy_yield'])?-1:1;
//        })->toArray();//排序

        $followIds = $userFollowLogic->getFansIdByUid($this->user_id);
        $bestStrategyList =  $orderLogic->getAllBy(['profit' => ['>', 0]]);
        foreach($bestStrategyList as $k => $v)
        {
            $bestStrategyList[$k] = array_merge($v, $userLogic->userDetail($v['user_id'], ['state' => 3]));//持仓
//            $bestStrategyList[$k]['strategy_yield'] = empty($v['price']) ? 0 : round(($v['sell_price']-$v['price'])/$v['price']/100, 2);
        }
        $bestStrategyList = collection($bestStrategyList)->sort(function ($a, $b){
            return $b['strategy_yield'] - $a['strategy_yield'];
        })->slice(0,5)->toArray();//排序

        $this->assign('bestUserList', $bestUserList);
        $this->assign('bestStrategyList', $bestStrategyList);
        $this->assign('followIds', $followIds);
        return view();
    }
}
