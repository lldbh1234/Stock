<?php
namespace app\admin\controller;

use app\admin\logic\AdminLogic;
use app\admin\logic\BankLogic;
use app\admin\logic\RegionLogic;
use app\admin\logic\SmsLogic;
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
        $isProxy = (new AdminLogic())->isProxy(manager()['role']);
        if($isProxy){
            return $this->redirect(url("admin/Index/userinfo"));
            exit;
        }
        return view();
    }

    // 个人信息
    public function userinfo()
    {
        if(request()->isPost()){

        }
        $admin = (new AdminLogic())->adminIncRole($this->adminId);
        list($jiangcang, $defer, $profit) = (new AdminLogic())->myPoint($this->adminId);
        $this->assign("admin", $admin);
        $this->assign("jiangcang", $jiangcang);
        $this->assign("defer", $defer);
        $this->assign("profit", $profit);
        return view();
    }

    // 修改密码
    public function password()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Index');
            if(!$validate->scene('password')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $data = [
                    "admin_id" => $this->adminId,
                    "password" => input("post.new/s")
                ];
                $res = (new AdminLogic())->adminUpdate($data);
                if($res){
                    $url = url('admin/Home/logout');
                    return $this->ok(['url' => $url]);
                }else{
                    return $this->fail("修改失败！");
                }
            }
        }
        return view();
    }

    //绑定银行卡
    public function myCard()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Card');
            if(!$validate->scene('modify')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $data = input("post.");
                $res = (new AdminLogic())->saveAdminCard($this->adminId, $data);
                if($res){
                    return $this->ok();
                }else{
                    return $this->fail("绑定银行卡失败，请稍后重试！");
                }
            }
        }
        $admin = (new AdminLogic())->adminIncCard($this->adminId);
        $banks = (new BankLogic())->bankLists();
        $_regionLogic = new RegionLogic();
        if($admin['has_one_card']){
            $provinces = $_regionLogic->regionByParentId();
            $citys = $_regionLogic->regionByParentId($admin['has_one_card']['bank_province']);
        }else{
            $provinces = $_regionLogic->regionByParentId();
            $citys = $_regionLogic->regionByParentId($provinces[0]['id']);
        }
        $callback = input("?get.callback") ? base64_decode(input("get.callback")) : "";
        $this->assign("admin", $admin);
        $this->assign("banks", $banks);
        $this->assign("provinces", $provinces);
        $this->assign("citys", $citys);
        $this->assign("callback", $callback);
        return view("card");
    }

    public function withdraw()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Withdraw');
            if(!$validate->scene('do')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $_adminLogic = new AdminLogic();
                $money = input("post.money/f");
                $admin = $_adminLogic->adminIncCard($this->adminId);
                $remark = [
                    "bank" => $admin['has_one_card']['bank_name'],
                    "card" => $admin['has_one_card']['bank_card'],
                    "name" => $admin['has_one_card']['bank_user'],
                    //"addr" => $admin['has_one_card']['bank_address'],
                ];
                $withdrawId = $_adminLogic->createAdminWithdraw($this->adminId, $money, $remark);
                if($withdrawId > 0){
                    return $this->ok();
                }else{
                    return $this->fail("提现申请失败！");
                }
            }
        }
        $admin = (new AdminLogic())->adminIncCard($this->adminId);
        $bind = $admin['has_one_card'] ? 1 : 0;
        $callback = url("admin/Index/withdraw");
        $redirect = url("admin/Index/myCard", ["callback" => base64_encode($callback)]);
        $this->assign("bind", $bind);
        $this->assign("admin", $admin);
        $this->assign("redirect", $redirect);
        return view();
    }

    public function withdrawList()
    {
        $res = (new AdminLogic())->pageAdminWithdraws($this->adminId, input(""));
        $this->assign("datas", $res['lists']);
        $this->assign("pages", $res['pages']);
        $this->assign("search", input(""));
        return view("withdrawLists");
    }

    public function getRegion()
    {
        if(request()->isPost()){
            $id = input("post.id", null);
            if(!is_null($id)){
                $citys = (new RegionLogic())->regionByParentId($id);
                return $this->ok($citys);
            }else {
                return $this->fail("非法操作");
            }
        }
        return $this->fail("非法操作");
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
                $mobile = (new AdminLogic())->adminExistByUsername($username);
                if(!$mobile)
                {
                    return $this->fail("管理员账号信息错误或手机号未绑定！！！");
                }
                $admin_login_num = 1;
                $ip = str_replace('.', '_', isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null);
                $sessKey = "ip_{$ip}_{$mobile}_{$act}";
                if (session($sessKey) && session($sessKey) >= time()) {
                    $admin_login_num +=1;
                    session($username.'login_num', $admin_login_num);
                    return $this->fail("短信已发送请在60秒后再次点击发送！");
                }
                if(session($username.'login_num') >= 5)//限制错误次数不能超过5次
                {
                    (new AdminLogic())->adminUpdate(['username' => $username, 'status' => 1]);
                    return $this->fail("登陆次数太多,账户异常,请联系管理员解绑！");
                }
                list($res, $code) = (new SmsLogic())->adminLogin($mobile, $ip, $act);
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
}