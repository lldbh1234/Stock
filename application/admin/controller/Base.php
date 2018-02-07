<?php
namespace app\admin\controller;

use think\Controller;
use think\Request;

class Base extends Controller
{
    protected $adminId;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->adminId = isLogin();
        if(!$this->adminId){// 还没登录 跳转到登录页面
            return $this->redirect(url("admin/Home/login"));
            exit;
        }
    }
}