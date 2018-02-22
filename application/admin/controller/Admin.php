<?php
namespace app\admin\controller;

use think\Request;
use app\admin\logic\AdminLogic;

class Admin extends Base
{
    protected $_logic;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->_logic = new AdminLogic();
    }

    public function roles()
    {
        $_res = $this->_logic->pageRoleLists();
        $this->assign("datas", $_res['lists']);
        $this->assign("pages", $_res['pages']);
        return view();
    }

    public function roleCreate()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Role');
            if(!$validate->scene('create')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $roleId = $this->_logic->roleCreate(input("post."));
                if(0 < $roleId){
                    return $this->ok();
                } else {
                    return $this->fail("添加失败！");
                }
            }
        }
        return view();
    }

    public function roleRemove()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Role');
            if(!$validate->scene('remove')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $res = $this->_logic->roleDelete(input("post.id"));
                if($res){
                    return $this->ok();
                } else {
                    return $this->fail("删除失败！");
                }
            }
        }else{
            return $this->fail("非法操作！");
        }
    }

    public function rolePatchRemove()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Role');
            if(!$validate->scene('patch')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $res = $this->_logic->roleDelete(input("post.ids/a"));
                if($res){
                    return $this->ok();
                } else {
                    return $this->fail("删除失败！");
                }
            }
        }else{
            return $this->fail("非法操作！");
        }
    }

    public function roleEdit($id = null)
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Role');
            if(!$validate->scene('modify')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $res = $this->_logic->roleUpdate(input("post."));
                if($res){
                    return $this->ok();
                } else {
                    return $this->fail("编辑失败！");
                }
            }
        }
        $role = $this->_logic->roleById($id);
        if($role){
            $this->assign("role", $role);
            return view();
        }else{
            return "非法操作！";
        }
    }

    public function lists()
    {
        $_res = $this->_logic->pageAdminLists(input(""));
        $roles = $this->_logic->allEnableRoles();
        $this->assign("datas", $_res['lists']);
        $this->assign("pages", $_res['pages']);
        $this->assign("roles", $roles);
        $this->assign("search", input(""));
        return view();
    }

    public function create()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Admin');
            if(!$validate->scene('create')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $data = input("post.");
                unset($data['password2']);
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
        $roles = $this->_logic->allEnableRoles();
        $this->assign("roles", $roles);
        return view('create');
    }
}