<?php
namespace app\admin\controller;

use think\Request;

class Test extends Base
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
    }

    public function test()
    {
        self::checkAuth();
    }

}