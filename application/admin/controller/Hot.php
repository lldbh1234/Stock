<?php
namespace app\admin\controller;

use app\admin\logic\StockLogic;
use think\Request;
use app\admin\logic\HotLogic;

class Hot extends Base
{
    protected $_logic;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->_logic = new HotLogic();
    }

    public function index()
    {
        $res = $this->_logic->pageHotLists();
        $this->assign("datas", $res['lists']);
        $this->assign("pages", $res['pages']);
        return view();
    }

    public function create()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Hot');
            if(!$validate->scene('create')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $data = input("post.");
                $code = $data["code"];
                $stock = (new StockLogic())->stockByCode($code);
                if($stock){
                    $data['name'] = $stock['name'];
                    $data['full_code'] = $stock['full_code'];
                    $hotId = $this->_logic->createHot($data);
                    if(0 < $hotId){
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
            $validate = \think\Loader::validate('Hot');
            if(!$validate->scene('modify')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $data = input("post.");
                $code = $data["code"];
                $stock = (new StockLogic())->stockByCode($code);
                if($stock){
                    $data['name'] = $stock['name'];
                    $data['full_code'] = $stock['full_code'];
                    $res = $this->_logic->updateHot($data);
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
        $hot = $this->_logic->hotById($id);
        if($hot){
            $this->assign("hot", $hot);
            return view();
        }else{
            return "非法操作！";
        }
    }

    public function remove()
    {
        if(request()->isPost()){
            $act = input("act/s", "single");
            $validate = \think\Loader::validate('Hot');
            if($act == "patch"){
                // 批量
                if(!$validate->scene('patch')->check(input("post."))){
                    return $this->fail($validate->getError());
                }else{
                    $res = $this->_logic->deleteHot(input("post.ids/a"));
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
                    $res = $this->_logic->deleteHot(input("post.id"));
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