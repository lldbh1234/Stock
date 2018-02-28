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
        $menu = self::leftMenu();
        $this->assign('menu', $menu);
        return view();
    }

    public function welcome()
    {
        return view();
    }
}