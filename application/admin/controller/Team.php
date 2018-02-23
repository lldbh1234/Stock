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
        $_res = $this->_logic->pageTeamLists("settle");
        $this->assign("datas", $_res['lists']);
        $this->assign("pages", $_res['pages']);
        return view();
    }

    public function operate()
    {
        $_res = $this->_logic->pageTeamLists("operate");
        dump($_res['lists']['data']);
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

    public function createUser()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Team');
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
        $referer = $_SERVER['HTTP_REFERER'];
        if(strpos($referer, "settle") !== false){
            $roleId = \app\admin\model\Admin::SETTLE_ROLE_ID;
        }elseif(strpos($referer, "operate") !== false){
            $roleId = \app\admin\model\Admin::OPERATE_ROLE_ID;
        }elseif(strpos($referer, "member") !== false){
            $roleId = \app\admin\model\Admin::MEMBER_ROLE_ID;
        }elseif(strpos($referer, "ring") !== false){
            $roleId = \app\admin\model\Admin::RING_ROLE_ID;
        }else{
            return "非法操作！";
        }
        $this->assign("role", $roleId);
        return view('create');
    }
}