<?php
namespace app\index\controller;

use app\index\logic\BestLogic;
use app\index\logic\OrderLogic;
use app\index\logic\RegionLogic;
use app\index\logic\StockLogic;
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
        $lists = [];

        // 自选
        $stocks = $userLogic->userOptional($this->user_id);
        if($stocks){
            $codes = array_column($stocks, "code");
            $lists = (new StockLogic())->simpleData($codes);
            foreach ($stocks as $key => &$item){
                if(isset($lists[$item['code']])){
                    $item['quotation'] = $lists[$item['code']];
                }else{
                    unset($stocks[$key]);
                }
            }
        }

        // 最牛达人
        /*$bestUserList =  $userLogic->getAllBy(['is_niuren' => 1]);
        foreach($bestUserList as $k => $v)
        {
            $bestUserList[$k] = array_merge($v, $userLogic->userDetail($v['user_id'], ['state' => 2]));//抛出
        }
        $bestUserList = collection($bestUserList)->sort(function ($a, $b){
            return $b['strategy_yield'] - $a['strategy_yield'];
        })->slice(0,5)->toArray();*/
        // 最牛达人 modify by liang
        //$bestUserList = $orderLogic->allYieldOrders();
        $bestUserList = [];

        $followIds = $userFollowLogic->getFollowIdByUid($this->user_id);
        // 最优持仓
        /*$bestStrategyList =  $orderLogic->getAllBy(['state' => 3]);
        $codes = $orderLogic->getCodesBy(['state' => 3]);//持仓
        $codeInfo = [];
        if($codes){
            $codeInfo = (new StockLogic())->simpleData($codes);
        }
        foreach($bestStrategyList as $k => $v)
        {
            $sell_price = isset($codeInfo[$v['code']]['last_px']) ? $codeInfo[$v['code']]['last_px'] : $v['price'];
            $bestStrategyList[$k]['strategy_yield'] = round(($sell_price-$v['price'])/$v['price']*100, 2);
            $bestStrategyList[$k]['profit'] = round(($sell_price-$v['price'])*$v['hand'], 2);
        }
        $bestStrategyList = collection($bestStrategyList)->sort(function ($a, $b){
            return $b['strategy_yield'] - $a['strategy_yield'];
        })->slice(0,5)->toArray();//排序*/
        // 最优持仓
        $bestStrategyList = (new BestLogic())->bestPositions();
        $userNotice = $userNoticeLogic->getAllBy(['user_id' => $this->user_id, 'read' => 1]);
        $userNotice = count($userNotice);

        $this->assign('bestUserList', $bestUserList);
        $this->assign('bestStrategyList', $bestStrategyList);
        $this->assign('followIds', $followIds);
        $this->assign('userNotice', $userNotice);
        $this->assign("stocks", $stocks);
        return view();
    }

    public function getRegion()
    {
        if(request()->isPost()){
            $id = input("post.id", null);
            if(!is_null($id)){
                $citys = (new RegionLogic())->regionByParentId($id);
                return $this->ok($citys);
            }else {
                return $this->fail("非法操作");
            }
        }
        return $this->fail("非法操作");
    }

    public function help()
    {
        return view();
    }
}
