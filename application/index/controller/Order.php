<?php
namespace app\index\controller;


use think\Request;

class Order extends Base
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
    }

    public function position()
    {
        return "持仓单";
    }
}