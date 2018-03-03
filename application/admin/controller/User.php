<?php
/**
 * Created by PhpStorm.
 * User: bruce
 * Date: 18/3/1
 * Time: 下午6:36
 */

namespace app\admin\controller;

use app\admin\logic\UserGiveLogic;
use app\admin\logic\UserLogic;
use app\admin\logic\UserWithdrawLogic;
use think\Db;
use think\Request;
class User extends Base
{
    public $userLogic;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->userLogic = new UserLogic();
    }

    public function lists()
    {
        $_res = $this->userLogic->pageUserLists(input(''));

        $this->assign("datas", $_res['lists']);
        $this->assign("pages", $_res['pages']);
        $this->assign("search", input(""));
        return view();

    }

    public function modify()
    {
        if(request()->isPost())
        {
            $validate = \think\Loader::validate('User');
            if(!$validate->scene('modify')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{

                if($this->userLogic->update(input("post."))){
                    return $this->ok();
                } else {
                    return $this->fail("修改失败！");
                }
            }
        }
        $id = input('user_id');
        $data = $this->userLogic->getOne($id);
        $this->assign('data', $data);
        $this->assign('id', $id);
        return view();
    }

    public function modifyPwd()
    {
        if(request()->isPost())
        {
            $validate = \think\Loader::validate('User');
            if(!$validate->scene('modify_pwd')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{

                if($this->userLogic->update(input("post."))){
                    return $this->ok();
                } else {
                    return $this->fail("修改失败！");
                }
            }
        };
    }
    public function giveLists()
    {
        $_res = $this->userLogic->pageUserLists(input(''));

        $this->assign("datas", $_res['lists']);
        $this->assign("pages", $_res['pages']);
        $this->assign("search", input(""));
        return view();

    }
    public function giveAccount()
    {
        if(request()->isPost())
        {
            $validate = \think\Loader::validate('User');
            if(!$validate->scene('give')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{

                if($this->userLogic->setInc(input("post."))){
                    return $this->ok();
                } else {
                    return $this->fail("操作失败！");
                }
            }
        };

    }

    public function giveLog()
    {
        $_res = (new UserGiveLogic())->pageUserGiveLists(input(''));

        $this->assign("datas", $_res['lists']);
        $this->assign("pages", $_res['pages']);
        $this->assign("search", input(""));
        return view();

    }

    public function withdrawLists()
    {
        $_res = (new UserWithdrawLogic())->pageUserWithdrawLists(input(''));

        $this->assign("datas", $_res['lists']);
        $this->assign("pages", $_res['pages']);
        $this->assign("search", input(""));
        return view();

    }

    public function withdraw()
    {
        if(request()->isPost())
        {

            $validate = \think\Loader::validate('UserWithdraw');
            if(!$validate->scene('user_withdraw')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{

                $ret = (new UserWithdrawLogic())->withdrawById(input('post.'));
                if($ret['code'] == 0){
                    return $this->ok();
                } else {
                    return $this->fail([$ret['msg']]);
                }
            }
        };
    }


}