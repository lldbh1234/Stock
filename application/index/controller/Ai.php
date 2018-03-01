<?php
namespace app\index\controller;

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
        $lists = $this->_logic->aiTypeLists();
        $this->assign("datas", $lists);
        return view();
    }
}