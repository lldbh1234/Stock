<?php
namespace app\admin\controller;

use think\captcha\Captcha;
use think\Controller;
use think\Request;

class Home extends Controller
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
    }

    public function login()
    {
        if(isLogin()){
            return $this->redirect(url("admin/Index/index"));
            exit;
        }else{
            if(request()->isPost()){
                $validate = \think\Loader::validate('Login');
                if(!$validate->scene('login')->check(input("post."))){
                    return $this->fail($validate->getError());
                }else{

                }
            }
            return view("login");
        }
    }

    public function demo()
    {
        $captcha = new Captcha();
        $result = $captcha->check("dhwnd");
        dump($result);
    }
}