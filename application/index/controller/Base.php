<?php
namespace app\index\controller;

use think\Request;
use think\Controller;

class Base extends Controller
{
    protected $user_id;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->user_id = isLogin();
        if(!$this->user_id){// 还没登录 跳转到登录页面
            return $this->redirect(url("index/Home/login"));
            exit;
        }
    }
}