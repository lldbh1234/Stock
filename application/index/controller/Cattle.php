<?php
namespace app\index\controller;

use app\index\logic\OrderLogic;
use app\index\logic\UserFollowLogic;
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
            $children = $this->_logic->getAllBy(['parent_id' => $this->user_id]);
            $children = count($children);
            $this->assign('userInfo', $userInfo);
            $this->assign('children', $children);
            return view();
        }

        $userDetail = $this->_logic->userDetail($this->user_id);
        $pulish_strategy = $this->conf['pulish_strategy'];//发布策略次数
        $strategy_win = $this->conf['strategy_win'];//策略胜算
        $strategy_yield = $this->conf['strategy_yield'];//策略收益
        $applyInfo = [
            'pulish_strategy' => $pulish_strategy,
            'strategy_win' => $strategy_win,
            'strategy_yield' => $strategy_yield,
            'status' => 0,
            'enough' => 0,
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
        $userDetail = $this->_logic->userDetail($this->user_id);
        $pulish_strategy = $this->conf['pulish_strategy'];//发布策略次数
        $strategy_win = $this->conf['strategy_win'];//策略胜算
        $strategy_yield = $this->conf['strategy_yield'];//策略收益
        if($userDetail['pulish_strategy'] < $pulish_strategy) return $this->fail('系统提示：发布策略数不满足申请条件');
        if($userDetail['strategy_win'] < $strategy_win) return $this->fail('系统提示：策略胜算率不满足申请条件');
        if($userDetail['strategy_yield'] < $strategy_yield) return $this->fail('系统提示：策略收益率不满足申请条件');
        //满足条件
        if($this->_logic->updateUser(['user_id' => $this->user_id, 'is_niuren' => 1]))
        {
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
        $userLogic = new UserLogic();
        $userFollowLogic = new UserFollowLogic();

        $map = ['is_niuren' => 1];
        $orderMap = ['state' => 2];//抛出
        $type = !empty(input('type') && in_array(input('type'), [1,2,3])) ? input('type'): 1;
//        if($type == 1){
//
//        }
        if($type == 2){
            $orderMap['update_at'] = ['between', [strtotime(date('Y-m-d')), strtotime(date('Y-m-d'))+86399]];
        }
        if($type == 3){
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
        })->toArray();//排序
        $followIds = $userFollowLogic->getFansIdByUid($this->user_id);
        $this->assign('type', $type);
        $this->assign('followIds', $followIds);
        $this->assign('bestUserList', $bestUserList);
        return view();
    }
    public function moreStrategy()
    {
        $orderLogic = new OrderLogic();
        $userLogic = new UserLogic();

        $bestStrategyList =  $orderLogic->getAllBy(['profit' => ['>', 0]]);
        foreach($bestStrategyList as $k => $v)
        {
            $bestStrategyList[$k]['strategy_yield'] = array_merge($v, $userLogic->userDetail($v['user_id'], ['state' => 3]));//持仓
//            $bestStrategyList[$k]['strategy_yield'] = empty($v['price']) ? 0 : round(($v['sell_price']-$v['price'])/$v['price']/100, 2);
        }

        $bestStrategyList = collection($bestStrategyList)->sort(function ($a, $b){
            return $b['strategy_yield'] - $a['strategy_yield'];
        })->toArray();//排序
        $this->assign('bestStrategyList', $bestStrategyList);
        return view();
    }
}