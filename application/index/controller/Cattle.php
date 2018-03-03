<?php
namespace app\index\controller;

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
        ];
        //策略数不达标
        if($userDetail['pulish_strategy'] < $pulish_strategy) {
            $applyInfo['pulish_strategy'] = $userDetail['pulish_strategy'];
            $applyInfo['status'] = 1;
        }
        if($userDetail['strategy_win'] < $strategy_win) {
            $applyInfo['strategy_win'] = $userDetail['strategy_win'];
            $applyInfo['status'] = 1;
        }
        if($userDetail['strategy_yield'] < $strategy_yield) {
            $applyInfo['strategy_yield'] = $userDetail['strategy_yield'];
            $applyInfo['status'] = 1;
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
}