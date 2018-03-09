<?php
namespace app\admin\controller;

use think\Request;
use app\admin\logic\RecordLogic;

class Record extends Base
{
    protected $_logic;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->_logic = new RecordLogic();
    }

    // 充值记录
    public function recharge()
    {
        $res = $this->_logic->pageUserRechargeList(input(""));
        $this->assign("datas", $res['lists']);
        $this->assign("pages", $res['pages']);
        $this->assign("totalAmount", $res['totalAmount']);
        $this->assign("totalActual", $res['totalActual']);
        $this->assign("totalPoundage", $res['totalPoundage']);
        $this->assign("search", input(""));
        return view();
    }

    // 牛人返点
    public function niuren()
    {

    }

    // 经纪人返点
    public function manager()
    {

    }

    // 代理商返点
    public function proxy()
    {

    }

    // 递延费扣除记录
    public function defer()
    {

    }
}