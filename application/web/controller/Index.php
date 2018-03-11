<?php
namespace app\web\controller;

use app\web\logic\OrderLogic;
use app\web\logic\StockLogic;
use app\web\logic\UserFollowLogic;
use app\web\logic\UserLogic;
use app\web\logic\UserNoticeLogic;
use think\Request;

class Index extends Base
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
    }

    public function index()
    {
        $orderLogic = new OrderLogic();
        //最新购买记录
        $orderLists = $orderLogic->getLimit([/*'state' => 3*/], 10);
        foreach($orderLists as $k => $v)
        {
            $orderLists[$k]['has_one_user']['mobile'] = mobileHide($v['has_one_user']['mobile']);
            $orderLists[$k]['time'] = timeAgo($v['create_at']);
        }
        $this->assign('orderLists', $orderLists);
        $this->assign('type', 1);
        return view();
    }
}
