<?php
namespace app\admin\controller;

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
}