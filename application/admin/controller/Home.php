<?php
namespace app\admin\controller;

use app\admin\logic\AdminLogic;
use app\admin\logic\SmsLogic;
use think\Request;
use think\Controller;
use app\admin\logic\LoginLogic;

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
                    $logic = new LoginLogic();
                    $adminId = $logic->login(input("post.username"), input("post.password"));
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
            session(config("admin_auth_key"), null);
            session('admin_info', null);
            session('admin_auth', null);
            session('admin_auth_sign', null);
            session('ACCESS_LIST', null);
            session('[destroy]');
            return $this->redirect(url('admin/Home/login'));
        } else {
            return $this->redirect(url('admin/Home/login'));
        }
    }
    public function verifySMS()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Login');
            if(!$validate->scene('verify_code')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $username = input("post.username/s");
                $act = 'admin_login';
                $admin = (new AdminLogic())->adminExistByUsername($username);
                $admin_login_num = 1;
                $ip = str_replace('.', '_', isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null);
                $sessKey = "ip_{$ip}_{$admin['mobile']}_{$act}";
                if(!$admin)
                {
                    return $this->fail("管理员账号信息错误或手机号未绑定！！！");
                }else{
                    if((new AdminLogic())->isProxy($admin['role']))//代理无需短信验证
                    {
                        session('admin_login_code', 9527);
                        session($sessKey, time()+60);
                        return $this->ok();
                    }
                }
                if (session($sessKey) && session($sessKey) >= time()) {
                    $admin_login_num +=1;
                    session($username.'login_num', $admin_login_num);
                    return $this->fail("短信已发送请在60秒后再次点击发送！");
                }
                if(session($username.'login_num') >= 5)//限制错误次数不能超过5次
                {
                    (new AdminLogic())->updateByUsername($username, ['status' => 1]);
                    return $this->fail("登陆次数太多,账户异常,请联系管理员解绑！");
                }
                list($res, $code) = (new SmsLogic())->adminLogin($admin['mobile'], $ip, $act);
                if($res){
                    session('admin_login_code', $code);
                    session($sessKey, time()+60);
                    return $this->ok();
                }else{
                    return $this->fail("发送失败{$code}！");
                }
            }
        }else{
            return $this->fail("非法操作！");
        }
    }
    public function giveAccountSms()
    {
        $admin = session('admin_info');
        $is_admin = (new AdminLogic())->isAdmin($admin['admin_id']);
        if(!$is_admin) return '非法请求';
        if(empty($admin['mobile'])) return '管理员未绑定手机号码';
        $act = 'admin_give';
        $ip = str_replace('.', '_', isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null);
        $sessKey = "ip_{$ip}_{$admin['mobile']}_{$act}";

        if (session($sessKey) && session($sessKey) >= time()) {
            return $this->fail("短信已发送请在60秒后再次点击发送！");
        }

        list($res, $code) = (new SmsLogic())->send($admin['mobile'], $act);
        if($res){
            session('admin_give_code', $code);
            session($sessKey, time()+60);
            return $this->ok();
        }else{
            return $this->fail("发送失败{$code}！");
        }
    }
}