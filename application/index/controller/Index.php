<?php
namespace app\index\controller;

use app\index\logic\OrderLogic;
use app\index\logic\UserFollowLogic;
use app\index\logic\UserLogic;
use app\index\logic\UserNoticeLogic;
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
        $userNoticeLogic = new UserNoticeLogic();
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

        $followIds = $userFollowLogic->getFollowIdByUid($this->user_id);
        $bestStrategyList =  $orderLogic->getAllBy(['state' => 3, 'profit' => ['>', 0]]);
        foreach($bestStrategyList as $k => $v)
        {
            $bestStrategyList[$k] = array_merge($v, $userLogic->userDetail($v['user_id'], ['state' => 3]));//持仓
        }
        $bestStrategyList = collection($bestStrategyList)->sort(function ($a, $b){
            return $b['strategy_yield'] - $a['strategy_yield'];
        })->slice(0,5)->toArray();//排序
        $userNotice = $userNoticeLogic->getAllBy(['user_id' => $this->user_id, 'read' => 1]);
        $userNotice = count($userNotice);

        $this->assign('bestUserList', $bestUserList);
        $this->assign('bestStrategyList', $bestStrategyList);
        $this->assign('followIds', $followIds);
        $this->assign('userNotice', $userNotice);
        return view();
    }
}
