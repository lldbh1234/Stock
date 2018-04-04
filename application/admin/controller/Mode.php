<?php
namespace app\admin\controller;

use app\admin\logic\DepositLogic;
use app\admin\logic\LeverLogic;
use think\Request;
use app\admin\logic\ModeLogic;
use app\admin\logic\ProductLogic;
use app\admin\logic\PluginsLogic;

class Mode extends Base
{
    protected $_logic;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->_logic = new ModeLogic();
    }

    public function index()
    {
        $_res = $this->_logic->pageModeLists();
        $this->assign("datas", $_res['lists']);
        $this->assign("pages", $_res['pages']);
        return view();
    }

    public function create()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Mode');
            if(!$validate->scene('create')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $modeId = $this->_logic->createMode(input("post."));
                if(0 < $modeId){
                    return $this->ok();
                } else {
                    return $this->fail("添加失败！");
                }
            }
        }
        $products = (new ProductLogic())->allEnableProducts();
        $plugins = (new PluginsLogic())->allEnableModePlugins();
        $this->assign("products", $products);
        $this->assign("plugins", $plugins);
        return view();
    }

    public function modify($id = null)
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Mode');
            if(!$validate->scene('modify')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $res = $this->_logic->updateMode(input("post."));
                if($res){
                    return $this->ok();
                } else {
                    return $this->fail("修改失败！");
                }
            }
        }
        $mode = $this->_logic->modeById($id);
        if($mode){
            $products = (new ProductLogic())->allEnableProducts();
            $plugins = (new PluginsLogic())->allEnableModePlugins();
            $this->assign("mode", $mode);
            $this->assign("products", $products);
            $this->assign("plugins", $plugins);
            return view();
        }else{
            return "非法操作！";
        }
    }

    public function remove()
    {
        if(request()->isPost()){
            $act = input("act/s", "single");
            $validate = \think\Loader::validate('Mode');
            if($act == "patch"){
                // 批量
                if(!$validate->scene('patch')->check(input("post."))){
                    return $this->fail($validate->getError());
                }else{
                    $res = $this->_logic->deleteMode(input("post.ids/a"));
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
                    $res = $this->_logic->deleteMode(input("post.id"));
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

    public function setDeposit($id = null)
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Mode');
            if(!$validate->scene('setDeposit')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $data = [
                    "mode_id" => input("post.id/d"),
                    "deposit" => input("post.deposit/a")
                ];
                $res = $this->_logic->updateMode($data);
                if($res){
                    return $this->ok();
                } else {
                    return $this->fail("操作失败！");
                }
            }
        }
        $mode = $this->_logic->modeById($id);
        if($mode){
            $deposit = (new DepositLogic())->depositLists();
            $this->assign("mode", $mode);
            $this->assign("deposit", $deposit);
            return view();
        }else{
            return "非法操作！";
        }
    }

    public function setLever($id = null)
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Mode');
            if(!$validate->scene('setLever')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $data = [
                    "mode_id" => input("post.id/d"),
                    "lever" => input("post.lever/a")
                ];
                $res = $this->_logic->updateMode($data);
                if($res){
                    return $this->ok();
                } else {
                    return $this->fail("操作失败！");
                }
            }
        }
        $mode = $this->_logic->modeById($id);
        if($mode){
            $lever = (new LeverLogic())->leverLists();
            $this->assign("mode", $mode);
            $this->assign("lever", $lever);
            return view();
        }else{
            return "非法操作！";
        }
    }

    public function deposit($id)
    {
        $res = $this->_logic->pageModeDeposits($id);
        $this->assign("datas", $res['lists']);
        $this->assign("pages", $res['pages']);
        $this->assign("mode_id", $id);
        $this->assign("mode_name", $res['name']);
        return view();
    }

    public function createDeposit()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('ModeDeposit');
            if(!$validate->scene('create')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $data = input("post.");
                $modeId = $data['mode_id'];
                unset($data['mode_id']);
                $depositId = $this->_logic->createModeDeposit($modeId, $data);
                if(0 < $depositId){
                    return $this->ok();
                } else {
                    return $this->fail("添加失败！");
                }
            }
        }
        return view();
    }

    public function modifyDeposit($mode_id = null, $id = null)
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('ModeDeposit');
            if(!$validate->scene('modify')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $id = input("post.id/d");
                $modeId = input("post.mode_id/d");
                $res = $this->_logic->updateModeDeposit($modeId, $id, input("post."));
                if($res){
                    return $this->ok();
                } else {
                    return $this->fail("修改失败！");
                }
            }
        }
        $deposit = $this->_logic->modeDepositById($mode_id, $id);
        if($deposit){
            $this->assign("deposit", $deposit);
            return view();
        }else{
            return "非法操作！";
        }
    }

    public function removeDeposit()
    {
        if(request()->isPost()){
            $act = input("act/s", "single");
            $validate = \think\Loader::validate('ModeDeposit');
            if($act == "patch"){
                // 批量
                if(!$validate->scene('patch')->check(input("post."))){
                    return $this->fail($validate->getError());
                }else{
                    $res = $this->_logic->deleteModeDeposit(input("post.mode_id"), input("post.ids/a"));
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
                    $res = $this->_logic->deleteModeDeposit(input("post.mode_id"), input("post.id"));
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

    public function lever($id)
    {
        $res = $this->_logic->pageModeLevers($id);
        $this->assign("datas", $res['lists']);
        $this->assign("pages", $res['pages']);
        $this->assign("mode_id", $id);
        $this->assign("mode_name", $res['name']);
        return view();
    }

    public function createLever()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('ModeLever');
            if(!$validate->scene('create')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $data = input("post.");
                $modeId = $data['mode_id'];
                unset($data['mode_id']);
                $leverId = $this->_logic->createModeLever($modeId, $data);
                if(0 < $leverId){
                    return $this->ok();
                } else {
                    return $this->fail("添加失败！");
                }
            }
        }
        return view();
    }

    public function modifyLever($mode_id = null, $id = null)
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('ModeLever');
            if(!$validate->scene('modify')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $id = input("post.id/d");
                $modeId = input("post.mode_id/d");
                $res = $this->_logic->updateModeLever($modeId, $id, input("post."));
                if($res){
                    return $this->ok();
                } else {
                    return $this->fail("修改失败！");
                }
            }
        }
        $lever = $this->_logic->modeLeverById($mode_id, $id);
        if($lever){
            $this->assign("lever", $lever);
            return view();
        }else{
            return "非法操作！";
        }
    }

    public function removeLever()
    {
        if(request()->isPost()){
            $act = input("act/s", "single");
            $validate = \think\Loader::validate('ModeLever');
            if($act == "patch"){
                // 批量
                if(!$validate->scene('patch')->check(input("post."))){
                    return $this->fail($validate->getError());
                }else{
                    $res = $this->_logic->deleteModeLever(input("post.mode_id"), input("post.ids/a"));
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
                    $res = $this->_logic->deleteModeLever(input("post.mode_id"), input("post.id"));
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