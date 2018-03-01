<?php
namespace app\index\controller;

use think\Request;
use app\index\logic\UserLogic;

class Attention extends Base
{
    protected $_logic;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->_logic = new UserLogic();
    }

    public function index(){
        $res = $this->_logic->userIncAttention($this->user_id);
        dump($res);
    }
}