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
                    $adminId = model("Admin")->login(input("post.username"), input("post.password"));
                    if(0 < $adminId){ // 登录成功，$uid 为登录的 UID
                        //跳转到登录前页面
                        return $this->ok(['url' => url("admin/Index/index")]);
                    } else { //登录失败
                        switch($adminId) {
                            case -1: $error = '账户错误或已禁用！'; break; //系统级别禁用
                            case -2: $error = '账户或密码错误！'; break;
                            default: $error = '未知错误！'; break; // 0-接口参数错误
                        }
                        return $this->fail($error);
                    }
                }
            }
            return view("login");
        }
    }

    public function logout(){
        if(isLogin()){
            model('Admin')->logout();
            session('[destroy]');
            return $this->redirect(url('admin/Home/login'));
        } else {
            return $this->redirect(url('admin/Home/login'));
        }
    }

    public function demo()
    {
        dump(spPassword("123456"));
    }
}