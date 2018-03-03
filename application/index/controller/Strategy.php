<?php
namespace app\index\controller;

use think\Request;
use app\index\logic\HotLogic;
use app\index\logic\StockLogic;

class Strategy extends Base
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
    }

    public function index()
    {
        $hots = (new HotLogic())->allHots();
        $codes = array_column($hots, "code");
        $quotation = (new StockLogic())->simpleData($codes);
        array_filter($hots, function(&$item) use ($quotation){
            $item['last_px'] = $quotation[$item['code']]['last_px'];
            $item['px_change'] = $quotation[$item['code']]['px_change'];
            $item['px_change_rate'] = $quotation[$item['code']]['px_change_rate'];
        });
        $this->assign("hots", $hots);
        return view();
    }
}