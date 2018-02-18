<?php
namespace app\admin\controller;

use think\Request;
use app\admin\logic\ProductLogic;

class Product extends Base
{
    private $_logic;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->_logic = new ProductLogic();
    }

    public function index()
    {
        $lists = $this->_logic->pageProductLists();
        $this->assign("data", $lists['lists']);
        $this->assign("pages", $lists['pages']);
        return view();
    }

    public function add()
    {
        if(request()->isPost()){
            return "add action";
        }
        return view();
    }
}