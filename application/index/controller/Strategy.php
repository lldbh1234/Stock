<?php
namespace app\index\controller;


use think\Request;

class Strategy extends Base
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
    }

    public function index()
    {
        return view();
    }
}