<?php
namespace app\admin\controller;

use think\Request;

class Index extends Base
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
    }

    public function index()
    {
        return "admin index";
    }
}