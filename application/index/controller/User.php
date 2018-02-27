<?php
namespace app\index\controller;

use think\Request;
use app\index\logic\UserLogic;

class User extends Base
{
    protected $_logic;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->_logic = new UserLogic();
    }

    public function index()
    {
        return view();
    }
}