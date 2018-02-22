<?php
namespace app\admin\controller;

use app\admin\logic\AdminLogic;
use think\Request;

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
}