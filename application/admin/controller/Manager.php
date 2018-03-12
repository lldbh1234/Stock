<?php
/**
 * Created by PhpStorm.
 * User: bruce
 * Date: 18/3/1
 * Time: 下午6:36
 */

namespace app\admin\controller;

use app\admin\logic\UserManagerLogic;
use think\Db;
use think\Request;
class Manager extends Base
{
    public $userManageLogic;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->userManageLogic = new UserManagerLogic();
    }

    public function lists()
    {
        $map = input('');
        !isset($map['state']) ? $map['state'] = 1 : '';
        $_res = $this->userManageLogic->pageManagerLists($map);
        $this->assign("datas", $_res['lists']);
        $this->assign("pages", $_res['pages']);
        $this->assign("search", input(""));
        return view();
    }

    public function auditLists()
    {
        $map = input('');
        $map['state'] = 0;
        $_res = $this->userManageLogic->pageManagerLists($map);
        $this->assign("datas", $_res['lists']);
        $this->assign("pages", $_res['pages']);
        $this->assign("search", input(""));
        return view();
    }

    public function point($id = null)
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('UserManager');
            if(!$validate->scene('point')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $data = [
                    "id" => input("post.id/d"),
                    "point" => input("post.point/f"),
                    "jiancang_point" => input("post.jiancang_point/f"),
                    "defer_point" => input("post.defer_point/f")
                ];
                $res = $this->userManageLogic->updateManager($data);
                if($res !== false){
                    return $this->ok();
                } else {
                    return $this->fail("操作失败！");
                }
            }
        }
        $manager = $this->userManageLogic->managerById($id);
        if($manager){
            $this->assign("manager", $manager);
            return view();
        }else{
            return "非法操作！";
        }
    }


    public function audit()
    {
        if(request()->isPost())
        {

            $validate = \think\Loader::validate('UserManager');
            if(!$validate->scene('user_audit')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $map = input('post.');
                $map['update_at'] = time();
                $map['update_by'] = isLogin();
                if($this->userManageLogic->updateState($map)){
                    if($map['state'] == 1) self::createManagerQrcode($map['user_id']);
                    return $this->ok();
                } else {
                    return $this->fail("系统提示：操作失败，请联系管理员");
                }
            }
        };
    }


}