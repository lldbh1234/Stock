<?php
namespace app\index\controller;

use app\index\logic\OrderLogic;
use app\index\logic\StockLogic;
use app\index\logic\UserFollowLogic;
use app\index\logic\UserNiurenLogic;
use app\index\logic\UserRecordLogic;
use app\index\model\UserNiuren;
use think\Request;
use app\index\logic\UserLogic;

class Cattle extends Base
{
    protected $_logic;
    protected $conf;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->_logic = new UserLogic();
        $this->conf = cfgs();
    }

    public function index(){
        $userInfo = $this->_logic->userById($this->user_id);

        if($userInfo['is_niuren'] == 1)
        {
            $orderLogic = new OrderLogic();
            $niurenInfo = $this->_logic->getNiuStaticByUid($this->user_id);
            $userInfo = array_merge($niurenInfo, $userInfo);
            //查询牛人订单
            $niuOrderIds = $orderLogic->orderIdsByUid($this->user_id);
            $map = [
                'is_follow' => 1,
                'follow_id' => ['in', $niuOrderIds],
                'state' => [
                    'in', [2,3],
                ],
            ];
            //跟单用户Id
            $childOrderLists = $orderLogic->getAllBy($map);
            $allCodes = $orderLogic->getCodesBy([
                'is_follow' => 1,
                'follow_id' => ['in', $niuOrderIds],
                'state' => 3,
            ]);
            $codeInfo = [];
            if($allCodes) $codeInfo = (new StockLogic())->simpleData($allCodes);

            $userInfo['evening']    = 0;//跟单平仓
            $userInfo['position']   = 0;//跟单持仓
            $userInfo['realtime_income']   = 0;//实时收入
            $userInfo['not_income']   = 0;//未结收入
            $niuren_point = $this->conf['niuren_point']/100;//牛人返点

            foreach($childOrderLists as $v)
            {
                if($v['state'] == 2 && $v['niuren_rebate'] == 0) {
                    $userInfo['evening'] += 1;
                    //未结收入 --平仓未结算
                    if($v['profit'] > 0)
                    {
                        $money = sprintf("%.2f", substr(sprintf("%.3f", $v['profit']*$niuren_point / 100), 0, -1));
                        $userInfo['not_income'] += $money;
                    }


                }
                if($v['state'] == 3) {

                    $userInfo['position'] += 1;
                    //实时收入 -持仓中
                    $sell_price = isset($codeInfo[$v['code']]['last_px']) ? $codeInfo[$v['code']]['last_px'] : 0;
                    $profit = $sell_price - $v['price'];
                    if($profit > 0)
                    {
                        $money_ = sprintf("%.2f", substr(sprintf("%.3f", $v['profit']*$niuren_point / 100), 0, -1));
                        $userInfo['realtime_income'] += $money_;
                    }

                }

            }

            $this->assign('userInfo', $userInfo);
            return view();
        }

        //不是牛人
        $userDetail         = $this->_logic->userDetail($this->user_id);
        $pulish_strategy    = $this->conf['pulish_strategy'];//发布策略次数
        $strategy_win       = $this->conf['strategy_win'];//策略胜算
        $strategy_yield     = $this->conf['strategy_yield'];//策略收益
        $applyInfo = [
            'pulish_strategy'   => $pulish_strategy,
            'strategy_win'      => $strategy_win,
            'strategy_yield'    => $strategy_yield,
            'status'            => 0,
            'enough'            => 0,
        ];
        //策略数不达标
        if($userDetail['pulish_strategy'] < $pulish_strategy) {
            $applyInfo['pulish_strategy'] = $userDetail['pulish_strategy'];
            $applyInfo['status'] = 1;
            $applyInfo['enough'] = 1;
        }
        if($userDetail['strategy_win'] < $strategy_win) {
            $applyInfo['strategy_win'] = $userDetail['strategy_win'];
            $applyInfo['status'] = 1;
            $applyInfo['enough'] = 1;
        }
        if($userDetail['strategy_yield'] < $strategy_yield) {
            $applyInfo['strategy_yield'] = $userDetail['strategy_yield'];
            $applyInfo['status'] = 1;
            $applyInfo['enough'] = 1;
        }

        $this->assign('applyInfo', $applyInfo);
        $this->assign('conf', $this->conf);
        return view('beCattle');
    }
    public function apply()
    {
        $userInfo = $this->_logic->userById($this->user_id);
        if($userInfo['is_niuren'] == 1) return $this->fail('系统提示：您已经是牛人啦！');
        //判断满足条件
        $userDetail         = $this->_logic->userDetail($this->user_id);
        $pulish_strategy    = $this->conf['pulish_strategy'];//发布策略次数
        $strategy_win       = $this->conf['strategy_win'];//策略胜算
        $strategy_yield     = $this->conf['strategy_yield'];//策略收益
        if($userDetail['pulish_strategy'] < $pulish_strategy) return $this->fail('系统提示：发布策略数不满足申请条件');
        if($userDetail['strategy_win'] < $strategy_win) return $this->fail('系统提示：策略胜算率不满足申请条件');
        if($userDetail['strategy_yield'] < $strategy_yield) return $this->fail('系统提示：策略收益率不满足申请条件');
        //满足条件
        if($this->_logic->updateUser(['user_id' => $this->user_id, 'is_niuren' => 1]))
        {
            (new UserNiuren())->saveAll(['user_id' => $this->user_id, ]);
            return $this->ok();
        }
        return $this->fail('系统提示：申请失败');
    }
    public function follow()
    {
        if(request()->isPost())
        {
            $id = input('post.user_id/d');
            $type = input('post.type/d');
            if($id == $this->user_id) return $this->fail('系统提示：无法关注自己');
            $userFollowLogic = new UserFollowLogic();
            if(intval($id) > 0 && $type > 0){
                $user = $this->_logic->userById($id);
                if($user){
                    $map = [
                        'follow_id' => $id,
                        'fans_id' => $this->user_id,
                    ];
                    if($type == 1){
                        if($userFollowLogic->add($map))
                        {
                            return $this->ok();
                        }
                    }
                    if($type == 2){
                        if($userFollowLogic->delBy($map))
                        {
                            return $this->ok();
                        }
                    }

                }
            }

        }
        return $this->fail('系统提示：非法操作');
    }

    public function moreMaster()
    {
        //$userLogic = new UserLogic();
        $userFollowLogic = new UserFollowLogic();

        //$map = ['is_niuren' => 1];
        //$orderMap = ['state' => 2];//抛出
        $type = !empty(input('type') && in_array(input('type'), [1,2,3])) ? input('type'): 1;
        $filter = [];
        switch ($type){
            case '1':
                break;
            case '2':
                // 日
                $filter['begin'] = date('Y-m-d 00:00:00');
                $filter['end'] = date('Y-m-d 23:59:59');
                break;
            case '3':
                //月
                $filter['begin'] = date('Y-m-01 00:00:00');
                $filter['end'] = date("Y-m-t 23:59:59");
                break;
            default:
                break;
        }
        $bestUserList = (new OrderLogic())->allYieldOrders($filter, 10);
/*//        if($type == 1){
//
//        }
        if($type == 2){//日
            $orderMap['update_at'] = ['between', [strtotime(date('Y-m-d')), strtotime(date('Y-m-d'))+86399]];
        }
        if($type == 3){//月
            $endDay = strtotime(date('Y-m') . "+1 month -1 day") + 86399;
            $orderMap['update_at'] = ['between', [strtotime(date('Y-m')), $endDay]];
        }
        $bestUserList =  $userLogic->getAllBy($map);
        foreach($bestUserList as $k => $v)
        {
            $bestUserList[$k] = array_merge($v, $userLogic->userDetail($v['user_id'], $orderMap));
        }
        $bestUserList = collection($bestUserList)->sort(function ($a, $b){
            return $b['strategy_yield'] - $a['strategy_yield'];
        })->toArray();//排序*/
        $followIds = $userFollowLogic->getFollowIdByUid($this->user_id);
        $this->assign('type', $type);
        $this->assign('followIds', $followIds);
        $this->assign('bestUserList', $bestUserList);
        return view();
    }

    public function moreStrategy()
    {
        $orderLogic = new OrderLogic();

        $bestStrategyList =  $orderLogic->getAllBy(['state' => 3]);

        $codes = $orderLogic->getCodesBy(['state' => 3]);
        $codeInfo = [];
        if($codes) $codeInfo = (new StockLogic())->simpleData($codes);
        foreach($bestStrategyList as $k => $v)
        {

            $sell_price = isset($codeInfo[$v['code']]['last_px']) ? $codeInfo[$v['code']]['last_px'] : $v['price'];
            $bestStrategyList[$k]['strategy_yield'] = round(($sell_price-$v['price'])/$v['price']*100, 2);
            $bestStrategyList[$k]['profit'] = round(($sell_price-$v['price'])*$v['hand'], 2);

        }

        $bestStrategyList = collection($bestStrategyList)->sort(function ($a, $b){
            return $b['strategy_yield'] - $a['strategy_yield'];
        })->toArray();//排序
        $this->assign('bestStrategyList', $bestStrategyList);
        return view();
    }
    public function myIncome()
    {
        $data = input('post.');
        $startDate = isset($data['startDate']) ? strtotime($data['startDate']) : '';
        $endDate = isset($data['endDate']) ? strtotime($data['endDate'])+86399 : '';
        $map = [
            'user_id' => $this->user_id,
            'type' => 0,
        ];
        if($startDate && $endDate) $map["create_at"] = ['between', [$startDate, $endDate]];
        $lists = $this->_logic->recordList($map);
        $amount = $this->_logic->recordAmount($map);
        $search = [
            'startDate' => $startDate ? date('Y-m-d', $startDate) : date('Y-m-d'),
            'endDate' => $endDate ? date('Y-m-d', $endDate) : date('Y-m-d'),
        ];

        $this->assign('search', $search);
        $this->assign('amount', $amount);
        $this->assign('lists', $lists);
        return view();
    }
    public function strategyEvening()
    {
        $orderLogic = new OrderLogic();

        $data = input('post.');
        $startDate = isset($data['startDate']) ? strtotime($data['startDate']) : '';
        $endDate = isset($data['endDate']) ? strtotime($data['endDate'])+86399 : '';
        //计算牛人订单号
        $niuOrderIds = $orderLogic->orderIdsByUid($this->user_id);
        $map = [
            'is_follow' => 1,
            'follow_id' => ['in', $niuOrderIds],
            'state' => 2,
        ];
        if($startDate && $endDate) $map["create_at"] = ['between', [$startDate, $endDate]];

        $lists =  $orderLogic->getAllBy($map, ['create_at' => 'desc']);//抛出
        $search = [
            'startDate' => $startDate ? date('Y-m-d', $startDate) : date('Y-m-d'),
            'endDate' => $endDate ? date('Y-m-d', $endDate) : date('Y-m-d'),
        ];
        $this->assign('search', $search);
        $this->assign('lists', $lists);
        return view();
    }
    public function strategyPosition()
    {
        $orderLogic = new OrderLogic();

        $data = input('post.');
        $startDate = isset($data['startDate']) ? strtotime($data['startDate']) : '';
        $endDate = isset($data['endDate']) ? strtotime($data['endDate'])+86399 : '';
        //计算牛人订单号
        $niuOrderIds = $orderLogic->orderIdsByUid($this->user_id);
        $map = [
            'is_follow' => 1,
            'follow_id' => ['in', $niuOrderIds],
            'state' => 3,
        ];
        if($startDate && $endDate) $map["create_at"] = ['between', [$startDate, $endDate]];

        $lists =  $orderLogic->getAllBy($map, ['create_at' => 'desc']);//持仓
        $profit = 0.00;
        foreach($lists as $v)
        {
            $profit += $v['profit'];
        }
        $search = [
            'startDate' => $startDate ? date('Y-m-d', $startDate) : date('Y-m-d'),
            'endDate' => $endDate ? date('Y-m-d', $endDate) : date('Y-m-d'),
        ];
        $this->assign('profit', $profit);
        $this->assign('search', $search);
        $this->assign('lists', $lists);
        return view();
    }
    public function niurenDetail()
    {
        $userFollowLogic = new UserFollowLogic();
        $uid = input('uid/d');
        if(!$uid) return $this->redirect('index/Attention/index');

        $userInfo = $this->_logic->userById($uid);
        if($userInfo && $userInfo['is_niuren'] == 1)
        {
            $orderLogic = new OrderLogic();
            $userDetail = $this->_logic->userDetail($uid);
            $userStatic = $this->_logic->userStatic($uid);
            $userInfo = array_merge($userStatic, $userInfo, $userDetail);
            $fansIds = $userFollowLogic->getFansIdByUid($uid);
            $follow = 0;
            if(in_array($this->user_id, $fansIds)){
                $follow = 1;
            }

            //最新
            $newList = $orderLogic->getLimit(['user_id' => $uid, 'state' => ['in', [2,3]]], ['limit' => '2']);
            $codes = $orderLogic->getCodesBy(['user_id' => $uid]);
            $codeInfo = [];
            if($codes) $codeInfo = (new StockLogic())->simpleData($codes);
            foreach($newList as $k => $v)
            {
                if($v['state'] == 2)//抛出
                {
                    $newList[$k]['shouyi'] = round(($v['sell_price']-$v['price'])/$v['price']*100, 2);
                }else{
                    $sell_price = isset($codeInfo[$v['code']]['last_px']) ? $codeInfo[$v['code']]['last_px'] : $v['price'];
                    $newList[$k]['shouyi'] = round(($sell_price-$v['price'])/$v['price']*100, 2);
                }

            }
            //当前持仓
            $currentList = $orderLogic->getLimit(['user_id' => $uid, 'state' => 3], ['limit' => '2']);
            foreach($currentList as $k => $v)
            {
                $sell_price = isset($codeInfo[$v['code']]['last_px']) ? $codeInfo[$v['code']]['last_px'] : $v['price'];
                $currentList[$k]['shouyi'] = round(($sell_price-$v['price'])/$v['price']*100, 2);
            }
            $this->assign('userInfo', $userInfo);
            $this->assign('follow', $follow);
            $this->assign('fansIds', $fansIds);
            $this->assign('newList', $newList);
            $this->assign('currentList', $currentList);
//            dump($userInfo);die();
            return view();
        }else{
            return $this->redirect('index/Attention/index');
        }
    }
    public function moreEvening()
    {
        $uid = input('uid/d');
        if(!$uid) return $this->redirect('index/Attention/index');
        $orderLogic = new OrderLogic();
        $lists = $orderLogic->getAllBy(['user_id' => $uid, 'state' => ['in', [2,3]]], ['create_at' => 'desc']);
        $codes = $orderLogic->getCodesBy(['user_id' => $uid]);
        $codeInfo = [];
        if($codes) $codeInfo = (new StockLogic())->simpleData($codes);
        foreach($lists as $k => $v)
        {
            if($v['state'] == 2)//抛出
            {
                $lists[$k]['shouyi'] = round(($v['sell_price']-$v['price'])/$v['price']*100, 2);
            }else{
                $sell_price = isset($codeInfo[$v['code']]['last_px']) ? $codeInfo[$v['code']]['last_px'] : $v['price'];
                $lists[$k]['shouyi'] = round(($sell_price-$v['price'])/$v['price']*100, 2);
            }

        }
        $this->assign('uid', $uid);
        $this->assign('lists', $lists);
        return view();

    }
    public function morePosition()
    {
        $uid = input('uid/d');
        if(!$uid) return $this->redirect('index/Attention/index');
        $orderLogic = new OrderLogic();
        //当前持仓
        $lists = $orderLogic->getAllBy(['user_id' => $uid, 'state' => 3], ['create_at' => 'desc']);
        $codes = $orderLogic->getCodesBy(['user_id' => $uid]);
        $codeInfo = [];
            if($codes) $codeInfo = (new StockLogic())->simpleData($codes);
        foreach($lists as $k => $v)
        {
            $sell_price = isset($codeInfo[$v['code']]['last_px']) ? $codeInfo[$v['code']]['last_px'] : $v['price'];
            $lists[$k]['shouyi'] = round(($sell_price-$v['price'])/$v['price']*100, 2);
        }
        $this->assign('uid', $uid);
        $this->assign('lists', $lists);
        return view();
    }
    public function removeCapital()
    {
        //查询当前用户是否属于牛人
        if(uInfo()['is_niuren'] == 1 /*&& uInfo()['manager_state'] == 2*/)
        {
            //查询当前用户可转收入
            $niurenLogic = new UserNiurenLogic();
            $niurenInfo = $niurenLogic->getInfoByUid($this->user_id);
            if($niurenInfo && $niurenInfo['sure_income'] > 0)
            {
                $amount = $niurenInfo['sure_income'];
                //转出
                $updateArr = [
                    'id'                => $niurenInfo['id'],
                    'sure_income'       => 0,
                    'already_income'    => $niurenInfo['sure_income']+$niurenInfo['already_income'],
                ];
                if($niurenLogic->updateManager($updateArr))
                {
                    $userRecordLogic = new UserRecordLogic();
                    //记录日志
                    $userRecordLogic->insert([
                        'user_id' => $this->user_id,
                        'type'      => 9,
                        'amount'    => $amount,
                        'direction' => 1,
                        'create_at' => time(),
                    ]);
                    return $this->ok([], '系统提示：转出成功！');
                }


            }else{
                return $this->fail("系统提示：当前用户无可转收入！");
            }
        }
        return $this->fail("系统提示：非法操作！");


    }
}