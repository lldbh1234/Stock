<?php
namespace app\admin\controller;

use app\admin\logic\StockLogic;
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
        $pageAmount = array_sum(collection($res['lists']['data'])->column("amount"));
        $pageActual = array_sum(collection($res['lists']['data'])->column("actual"));
        $pagePoundage = array_sum(collection($res['lists']['data'])->column("poundage"));
        $this->assign("datas", $res['lists']);
        $this->assign("pages", $res['pages']);
        $this->assign("totalAmount", $res['totalAmount']);
        $this->assign("totalActual", $res['totalActual']);
        $this->assign("totalPoundage", $res['totalPoundage']);
        $this->assign("pageAmount", $pageAmount);
        $this->assign("pageActual", $pageActual);
        $this->assign("pagePoundage", $pagePoundage);
        $this->assign("search", input(""));
        return view();
    }

    // 牛人返点
    public function niuren()
    {
        $codes = [
            "600000",
            "600004",
            "600007",
        ];
        $res = (new StockLogic())->stockQuotationBySina($codes);
        dump($res);
        exit;
        $res = $this->_logic->pageNiurenRecord(input(""));
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