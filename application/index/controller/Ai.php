<?php
namespace app\index\controller;

use app\index\logic\StockLogic;
use think\Request;
use app\index\logic\AiLogic;

class Ai extends Base
{
    protected $_logic;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->_logic = new AiLogic();
    }

    public function index()
    {
        $codes = [];
        $lists = $this->_logic->aiTypeLists();
        array_filter($lists, function(&$item) use (&$codes){
            foreach ($item['has_many_ai'] as $value){
                $codes[] = $value['code'];
            }
        });
        $simple = (new StockLogic())->simpleData($codes);
        array_filter($lists, function(&$item) use ($simple){
            foreach ($item['has_many_ai'] as &$value){
                $value['last_px'] = $simple[$value['code']]['last_px'];
                $value['rate'] = round((($value['last_px'] - $value['income']) / $value['income'] * 100), 2);
            }
        });
        $this->assign("datas", $lists);
        return view();
    }
}