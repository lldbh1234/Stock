<?php
namespace app\index\controller;

use think\Controller;

class Home extends Controller
{
    public function login()
    {
        if(request()->isPost()){

        }
        return view();
    }

    public function register()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('User');
            if(!$validate->scene('register')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{

            }
        }
        $pid = input("?pid") ? input("pid") : 0;
        return view();
    }
}