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

    // 首页
    public function welcome()
    {
        return view();
    }

    // 个人信息
    public function userinfo()
    {

    }

    // 修改密码
    public function password()
    {
        if(request()->isPost()){

        }
        return view();
    }
}