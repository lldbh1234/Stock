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
            $bestUserList[$k]['user_desc'] = $userLogic->userDetail($v['user_id']);

        }
        $followIds = $userFollowLogic->getFansIdByUid($this->user_id);
        $bestStrategyList =  $orderLogic->getAllBy(['profit' => ['>', 0]]);
        foreach($bestStrategyList as $k => $v)
        {
            $bestStrategyList[$k]['strategy_yield'] = empty($v['price']) ? 0 : round(($v['sell_price']-$v['price'])/$v['price']/100, 2);
        }

        $this->assign('bestUserList', $bestUserList);
        $this->assign('bestStrategyList', $bestStrategyList);
        $this->assign('followIds', $followIds);
        return view();
    }
}
