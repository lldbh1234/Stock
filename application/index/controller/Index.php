<?php
namespace app\index\controller;

use app\index\logic\OrderLogic;
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
        $bestUserList =  $userLogic->getAllBy(['is_niuren' => 1]);
        foreach($userLogic as $k => $v)
        {
            $bestUserList[$k]['user_desc'] = $userLogic->userDetail($v['user_id']);
        }
        $bestStrategyList =  $orderLogic->getAllBy();
        $this->assign('bestUserList', $bestUserList);
        $this->assign('bestStrategyList', $bestStrategyList);
        return view();
    }
}
