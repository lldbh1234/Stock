<?php
namespace app\admin\controller;

use think\Request;
use app\admin\logic\DepositLogic;


class Deposit extends Base
{
    protected $_logic;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->_logic = new DepositLogic();
    }

    public function index()
    {
        $_res = $this->_logic->pageDepositLists();
        $this->assign("datas", $_res['lists']);
        $this->assign("pages", $_res['pages']);
        return view();
    }

    public function create()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Deposit');
            if(!$validate->scene('create')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $modeId = $this->_logic->createDeposit(input("post."));
                if(0 < $modeId){
                    return $this->ok();
                } else {
                    return $this->fail("添加失败！");
                }
            }
        }
        return view();
    }

    public function modify($id = null)
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Deposit');
            if(!$validate->scene('modify')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $res = $this->_logic->updateDeposit(input("post."));
                if($res){
                    return $this->ok();
                } else {
                    return $this->fail("修改失败！");
                }
            }
        }
        $deposit = $this->_logic->depositById($id);
        if($deposit){
            $this->assign("deposit", $deposit);
            return view();
        }else{
            return "非法操作！";
        }
    }

    public function remove()
    {
        if(request()->isPost()){
            $act = input("act/s", "single");
            $validate = \think\Loader::validate('Deposit');
            if($act == "patch"){
                // 批量
                if(!$validate->scene('patch')->check(input("post."))){
                    return $this->fail($validate->getError());
                }else{
                    $res = $this->_logic->deleteDeposit(input("post.ids/a"));
                    if($res){
                        return $this->ok();
                    } else {
                        return $this->fail("删除失败！");
                    }
                }
            }else{
                if(!$validate->scene('remove')->check(input("post."))){
                    return $this->fail($validate->getError());
                }else{
                    $res = $this->_logic->deleteDeposit(input("post.id"));
                    if($res){
                        return $this->ok();
                    } else {
                        return $this->fail("删除失败！");
                    }
                }
            }
        }else{
            return $this->fail("非法操作！");
        }
    }
}