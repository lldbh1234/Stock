<?php
namespace app\admin\controller;

use think\Request;
use app\admin\logic\AdminLogic;

class Team extends Base
{
    protected $_logic;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->_logic = new AdminLogic();
    }

    public function settle()
    {
        $_res = $this->_logic->pageTeamLists("settle");
        dump($_res['lists']['data']);
    }

    public function operate()
    {
        $_res = $this->_logic->pageTeamLists("operate");
        dump($_res['lists']['data']);
    }

    public function member()
    {
        $_res = $this->_logic->pageTeamLists("member");
        dump($_res['lists']['data']);
    }

    public function ring()
    {
        $_res = $this->_logic->pageTeamLists("ring");
        dump($_res['lists']['data']);
    }
}