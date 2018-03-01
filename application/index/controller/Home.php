<?php
namespace app\index\controller;

use app\index\logic\AdminLogic;
use app\index\logic\LoginLogic;
use app\index\logic\UserLogic;
use think\Controller;
use app\index\logic\SmsLogic;

class Home extends Controller
{
    public function login()
    {
        if(isLogin()){
            return $this->redirect(url("index/Index/index"));
            exit;
        }else{
            if(request()->isPost()){
                $validate = \think\Loader::validate('User');
                if(!$validate->scene('login')->check(input("post."))){
                    return $this->fail($validate->getError());
                }else{
                    $username = input("post.username/s");
                    $password = input("post.password/s");
                    $member = input("post.institution/d");
                    $userId = (new LoginLogic())->login($username, $password, $member);
                    if(0 < $userId){ // 登录成功，$uid 为登录的 UID
                        //跳转到登录前页面
                        return $this->ok(['url' => url("index/Index/index")]);
                    } else { //登录失败
                        switch($userId) {
                            case -1: $error = '账户不存在或已禁用！'; break; //系统级别禁用
                            case -2: $error = '账户或密码错误！'; break;
                            default: $error = '未知错误！'; break; // 0-接口参数错误
                        }
                        return $this->fail($error);
                    }
                }
            }
            $members = (new AdminLogic())->allMemberLists();
            $this->assign("members", $members);
            return view();
        }
    }

    public function register()
    {
        if(isLogin()){
            return $this->redirect(url("index/Index/index"));
            exit;
        }else{
            if(request()->isPost()){
                $validate = \think\Loader::validate('User');
                if(!$validate->scene('register')->check(input("post."))){
                    return $this->fail($validate->getError());
                }else{
                    $data = input("post.");
                    $admin = (new AdminLogic())->adminByCode($data['orgCode']);
                    if($admin){
                        $data['username'] = $data["mobile"];
                        $data['nickname'] = $data["mobile"];
                        $data['face'] = config("default_face");
                        $data['admin_id'] = $admin['admin_id'];
                        $data['parent_id'] = input("post.pid/d", 0);
                        $userId = (new UserLogic())->createUser($data);
                        if($userId > 0){
                            $user = (new UserLogic())->userById($userId);
                            (new LoginLogic())->autoLogin($user);
                            $url = url('index/Index/index');
                            return $this->ok(['url' => $url]);
                        }else{
                            return $this->fail("注册失败！");
                        }
                    }else{
                        return $this->fail("机构编码不存在！");
                    }
                }
            }
            $pid = input("?pid") ? input("pid") : 0;
            return view();
        }
    }

    public function forget()
    {
        if(isLogin()){
            return $this->redirect(url("index/Index/index"));
            exit;
        }else{
            if(request()->isPost()){
                $validate = \think\Loader::validate('User');
                if(!$validate->scene('forget')->check(input("post."))){
                    return $this->fail($validate->getError());
                }else{
                    $mobile = input("post.mobile");
                    $password = input("post.password/s");
                    $member = input("post.institution/d");
                    $res = (new LoginLogic())->forgetPassword($mobile, $password, $member);
                    if($res !== false){
                        $url = url("index/Home/login");
                        return $this->ok(["url" => $url]);
                    }else{
                        return $this->fail("密码找回失败！");
                    }
                }
            }
            $members = (new AdminLogic())->allMemberLists();
            $this->assign("members", $members);
            return view();
        }
    }

    public function logout(){
        if(isLogin()){
            session("user_id", null);
            session('user_info', null);
            session('user_auth', null);
            session('user_auth_sign', null);
            session('[destroy]');
            return $this->redirect(url('index/Home/login'));
        } else {
            return $this->redirect(url('index/Home/login'));
        }
    }

    public function captcha()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('User');
            if(!$validate->scene('captcha')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $mobile = input("post.mobile/s");
                $act = input("post.act/s");
                list($res, $code) = (new SmsLogic())->send($mobile, $act);
                if($res){
                    return $this->ok(['code' => $code]);
                }else{
                    return $this->fail("发送失败！");
                }
            }
        }else{
            return $this->fail("非法操作！");
        }
    }
}