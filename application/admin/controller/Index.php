<?php
namespace app\admin\controller;

use app\admin\logic\AdminLogic;
use app\admin\logic\BankLogic;
use app\admin\logic\RegionLogic;
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
        $this->assign("admin", $admin);
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
        $this->assign("admin", $admin);
        $this->assign("banks", $banks);
        $this->assign("provinces", $provinces);
        $this->assign("citys", $citys);
        return view("card");
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
}