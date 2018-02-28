<?php
namespace app\index\controller;


use think\Request;

class Stock extends Base
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
    }

    public function stockBuy($code)
    {

    }
}