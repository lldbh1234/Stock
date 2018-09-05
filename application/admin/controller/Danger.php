<?php
namespace app\admin\controller;

use app\admin\logic\DangerLogic;
use app\admin\logic\StockLogic;
use think\Request;

class Danger extends Base
{
    protected $_logic;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->_logic = new DangerLogic();
    }

    public function index()
    {
        $res = $this->_logic->pageDangerList(input(""));
        $this->assign("datas", $res['lists']);
        $this->assign("pages", $res['pages']);
        $this->assign("search", input(""));
        return view();
    }

    public function create()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Danger');
            if(!$validate->scene('create')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $data = input("post.");
                $code = $data["code"];
                $stock = (new StockLogic())->stockByCode($code);
                if($stock){
                    $data['name'] = $stock['name'];
                    $data['symbol'] = $stock['full_code'];
                    $data['state'] = 1;
                    $dangerId = $this->_logic->createDanger($data);
                    if($dangerId){
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

    public function remove()
    {
        if(request()->isPost()){
            $act = input("act/s", "single");
            /*$validate = \think\Loader::validate('Danger');
            if(!$validate->scene('remove')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{*/
                $res = $this->_logic->deleteByCode(input("post.code"));
                if($res){
                    return $this->ok();
                } else {
                    return $this->fail("删除失败！");
                }
            /*}*/
        }else{
            return $this->fail("非法操作！");
        }
    }
}