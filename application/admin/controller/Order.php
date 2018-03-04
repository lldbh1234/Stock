<?php
namespace app\admin\controller;

use think\Request;
use app\admin\logic\OrderLogic;

class Order extends Base
{
    protected $_logic;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->_logic = new OrderLogic();
    }

    public function index()
    {
        $_res = $this->_logic->pageOrderLists([1, 4], input(""));
        $this->assign("datas", $_res['lists']);
        $this->assign("pages", $_res['pages']);
        return view();
    }

    public function history()
    {
        $_res = $this->_logic->pageOrderLists(2, input(""));
        $this->assign("datas", $_res['lists']);
        $this->assign("pages", $_res['pages']);
        return view();
    }

    public function position()
    {
        $_res = $this->_logic->pageOrderLists(3, input(""));
        $this->assign("datas", $_res['lists']);
        $this->assign("pages", $_res['pages']);
        return view();
    }

    public function buyOk()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Order');
            if(!$validate->scene('buyOk')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $data = [
                    "order_id" => input("post.id/d"),
                    "price" => input("post.price/f"),
                    "state" => 3
                ];
                $res = $this->_logic->updateOrder($data);
                if($res){
                    return $this->ok();
                }else{
                    return $this->fail("操作失败！");
                }
            }
        }else{
            return $this->fail("系统提示：非法操作！");
        }
    }

    public function buyFail()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Order');
            if(!$validate->scene('buyFail')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $res = $this->_logic->buyFail(input("post.id/d"));
                if($res){
                    return $this->ok();
                }else{
                    return $this->fail("操作失败！");
                }
            }
        }else{
            return $this->fail("系统提示：非法操作！");
        }
    }

}