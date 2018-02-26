<?php
namespace app\admin\controller;

use app\admin\logic\StockLogic;
use think\Request;
use app\admin\logic\AiLogic;

class Ai extends Base
{
    protected $_logic;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->_logic = new AiLogic();
    }

    public function index()
    {
        $_res = $this->_logic->pageAiTypes();
        $this->assign("datas", $_res['lists']);
        $this->assign("pages", $_res['pages']);
        return view();
    }

    public function createType()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('AiType');
            if(!$validate->scene('create')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $typeId = $this->_logic->createAiType(input("post."));
                if(0 < $typeId){
                    return $this->ok();
                } else {
                    return $this->fail("添加失败！");
                }
            }
        }
        return view();
    }

    public function modifyType($id = null)
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('AiType');
            if(!$validate->scene('modify')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $res = $this->_logic->updateAiType(input("post."));
                if($res){
                    return $this->ok();
                } else {
                    return $this->fail("修改失败！");
                }
            }
        }
        $type = $this->_logic->aiTypeById($id);
        if($type){
            $this->assign("type", $type);
            return view();
        }else{
            return "非法操作！";
        }
    }

    public function removeType()
    {
        if(request()->isPost()){
            $act = input("act/s", "single");
            $validate = \think\Loader::validate('AiType');
            if($act == "patch"){
                // 批量
                if(!$validate->scene('patch')->check(input("post."))){
                    return $this->fail($validate->getError());
                }else{
                    $res = $this->_logic->deleteAiType(input("post.ids/a"));
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
                    $res = $this->_logic->deleteAiType(input("post.id"));
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

    public function stocks($id = null)
    {
        $_res = $this->_logic->pageAiTypeStocks($id);
        $this->assign("datas", $_res['lists']);
        $this->assign("pages", $_res['pages']);
        $this->assign("type_id", $id);
        $this->assign("type_name", $_res['name']);
        return view();
    }

    public function create()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Ai');
            if(!$validate->scene('create')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $data = input("post.");
                $code = $data["code"];
                $stock = (new StockLogic())->stockByCode($code);
                if($stock){
                    $data['name'] = $stock['name'];
                    $data['full_code'] = $stock['full_code'];
                    $aiId = $this->_logic->createAi($data);
                    if(0 < $aiId){
                        return $this->ok();
                    } else {
                        return $this->fail("添加失败！");
                    }
                }else{
                    return $this->fail("股票代码不存在！");
                }
            }
        }
        return view();
    }

    public function modify($id = null)
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Ai');
            if(!$validate->scene('modify')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $data = input("post.");
                $code = $data["code"];
                $stock = (new StockLogic())->stockByCode($code);
                if($stock){
                    $data['name'] = $stock['name'];
                    $data['full_code'] = $stock['full_code'];
                    $res = $this->_logic->updateAi($data);
                    if($res){
                        return $this->ok();
                    } else {
                        return $this->fail("修改失败！");
                    }
                }else{
                    return $this->fail("股票代码不存在！");
                }
            }
        }
        $ai = $this->_logic->aiById($id);
        if($ai){
            $this->assign("ai", $ai);
            return view();
        }else{
            return "非法操作！";
        }
    }

    public function remove()
    {
        if(request()->isPost()){
            $act = input("act/s", "single");
            $validate = \think\Loader::validate('Ai');
            if($act == "patch"){
                // 批量
                if(!$validate->scene('patch')->check(input("post."))){
                    return $this->fail($validate->getError());
                }else{
                    $res = $this->_logic->deleteAi(input("post.ids/a"));
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
                    $res = $this->_logic->deleteAi(input("post.id"));
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