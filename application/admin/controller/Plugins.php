<?php
namespace app\admin\controller;

use think\Request;
use app\admin\logic\PluginsLogic;

class Plugins extends Base
{
    private $_logic;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->_logic = new PluginsLogic();
    }

    public function lists()
    {
        $lists = $this->_logic->allPluginsLists();
        $this->assign("data", $lists);
        return view();
    }
}