<?php
namespace app\admin\controller;

use app\admin\logic\AdminLogic;
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
}