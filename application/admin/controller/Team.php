<?php
namespace app\admin\controller;

use think\Request;
use app\admin\logic\AdminLogic;

class Team extends Base
{
    protected $_logic;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->_logic = new AdminLogic();
    }

    public function settle()
    {
        $_res = $this->_logic->pageTeamLists("settle", input(""));
        $this->assign("datas", $_res['lists']);
        $this->assign("pages", $_res['pages']);
        $this->assign("search", input(""));
        return view();
    }

    public function operate()
    {
        $_res = $this->_logic->pageTeamLists("operate", input(""));
        $this->assign("datas", $_res['lists']);
        $this->assign("pages", $_res['pages']);
        $this->assign("search", input(""));
        return view();
    }

    public function member()
    {
        $_res = $this->_logic->pageTeamLists("member");
        dump($_res['lists']['data']);
    }

    public function ring()
    {
        $_res = $this->_logic->pageTeamLists("ring");
        dump($_res['lists']['data']);
    }

    public function createSettle()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Team');
            if(!$validate->scene('create')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $data = input("post.");
                unset($data['password2']);
                $data['role'] = \app\admin\model\Admin::SETTLE_ROLE_ID;
                $data['pid'] = $this->_logic->getAdminPid();
                $data['code'] = $this->_logic->getAdminCode($data['role']);
                $adminId = $this->_logic->adminCreate($data);
                if(0 < $adminId){
                    return $this->ok();
                } else {
                    return $this->fail("添加失败！");
                }
            }
        }
        return view();
    }

    public function modifySettle($id = null)
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Team');
            if(!$validate->scene('modify')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $data = input("post.");
                unset($data['username']);
                if(empty($data['password'])){
                    unset($data['password']);
                }
                $res = $this->_logic->adminUpdate($data);
                if($res){
                    return $this->ok();
                } else {
                    return $this->fail("编辑失败！");
                }
            }
        }
        $admin = $this->_logic->teamAdminById($id, "settle");
        if($admin){
            $this->assign("admin", $admin);
            return view();
        }else{
            return "非法操作！";
        }
    }

    public function createOperate()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Team');
            if(!$validate->scene('create')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $data = input("post.");
                unset($data['password2']);
                $data['role'] = \app\admin\model\Admin::OPERATE_ROLE_ID;
                $data['pid'] = $this->_logic->getAdminPid();
                $data['code'] = $this->_logic->getAdminCode($data['role']);
                $adminId = $this->_logic->adminCreate($data);
                if(0 < $adminId){
                    return $this->ok();
                } else {
                    return $this->fail("添加失败！");
                }
            }
        }
        return view();
    }

    public function modifyOperate($id = null)
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Team');
            if(!$validate->scene('modify')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $data = input("post.");
                unset($data['username']);
                if(empty($data['password'])){
                    unset($data['password']);
                }
                $res = $this->_logic->adminUpdate($data);
                if($res){
                    return $this->ok();
                } else {
                    return $this->fail("编辑失败！");
                }
            }
        }
        $admin = $this->_logic->teamAdminById($id, "operate");
        if($admin){
            $this->assign("admin", $admin);
            return view();
        }else{
            return "非法操作！";
        }
    }
}