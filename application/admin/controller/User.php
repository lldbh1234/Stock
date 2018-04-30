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
        $pageAccount = array_sum(collection($_res['lists']['data'])->column("account"));
        $this->assign("datas", $_res['lists']);
        $this->assign("pages", $_res['pages']);
        $this->assign("totalAccount", $_res['totalAccount']);
        $this->assign("pageAccount", $pageAccount);
        $this->assign("search", input(""));
        return view();
    }

    public function modify()
    {
        if(request()->isPost()) {
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

    public function detail($id = null, $type = 1)
    {
        switch ($type){
            case '1':
                // 基本信息
                $user = $this->userLogic->userIncFamily($id);
                if($user){
                    $this->assign("user", $user);
                    return view("userDetail1");
                }else{
                    return "非法操作！";
                }
                break;
            case '2':
                //当前持仓
                break;
            case '3':
                //历史交易
                $_res = $this->userLogic->pageUserOrderByUserId($id, 2, input(""), 10);
                if($_res){
                    $pageProfit = array_sum(collection($_res['lists']['data'])->column("profit"));
                    $pageDeposit = array_sum(collection($_res['lists']['data'])->column("deposit"));
                    $pageJiancang = array_sum(collection($_res['lists']['data'])->column("jiancang_fee"));
                    $this->assign("datas", $_res['lists']);
                    $this->assign("pages", $_res['pages']);
                    $this->assign("pageProfit", $pageProfit);
                    $this->assign("pageDeposit", $pageDeposit);
                    $this->assign("pageJiancang", $pageJiancang);
                    $this->assign("totalProfit", $_res['totalProfit']);
                    $this->assign("totalDeposit", $_res['totalDeposit']);
                    $this->assign("totalJiancang", $_res['totalJiancang']);
                    $this->assign("totalDefer", $_res['totalDefer']);
                    $this->assign("search", input(""));
                    return view("userDetail3");
                }else{
                    return "非法操作！";
                }
                break;
            case '4':
                //出金记录
                $_res = $this->userLogic->pageUserWithdrawByUserId($id, input(""), 10);
                if($_res){
                    $pageAmount = array_sum(collection($_res['lists']['data'])->column("amount"));
                    $pageActual = array_sum(collection($_res['lists']['data'])->column("actual"));
                    $pagePoundage = array_sum(collection($_res['lists']['data'])->column("poundage"));
                    $state = [0 => '待审核', '1' => '代付中', 2=> '已到账', -1 => '已拒绝'];
                    array_filter($_res['lists']['data'], function(&$_item) use ($state){
                        $_item['state_text'] = $state[$_item['state']];
                        $_item['remark'] = json_decode($_item['remark'], true);
                    });
                    $this->assign("datas", $_res['lists']);
                    $this->assign("pages", $_res['pages']);
                    $this->assign("pageAmount", $pageAmount);
                    $this->assign("pageActual", $pageActual);
                    $this->assign("pagePoundage", $pagePoundage);
                    $this->assign("totalAmount", $_res['totalAmount']);
                    $this->assign("totalActual", $_res['totalActual']);
                    $this->assign("totalPoundage", $_res['totalPoundage']);
                    $this->assign("search", input(""));
                    return view("userDetail4");
                }else{
                    return "非法操作！";
                }
                break;
            case '5':
                // 入金记录
                $_res = $this->userLogic->pageUserRechargeByUserId($id, input(""), 10);
                if($_res){
                    $pageAmount = array_sum(collection($_res['lists']['data'])->column("amount"));
                    $type = [0 => "支付宝", 1 => "微信", 2 => "连连支付"];
                    array_filter($_res['lists']['data'], function(&$_item) use ($type){
                        $_item['type_text'] = $type[$_item['type']];
                    });
                    $this->assign("datas", $_res['lists']);
                    $this->assign("pages", $_res['pages']);
                    $this->assign("totalAmount", $_res['totalAmount']);
                    //$this->assign("totalActual", $_res['totalActual']);
                    //$this->assign("totalPoundage", $_res['totalPoundage']);
                    $this->assign("pageAmount", $pageAmount);
                    $this->assign("search", input(""));
                    return view("userDetail5");
                }else{
                    return "非法操作！";
                }
                break;
            case '6':
                // 资金记录
                $_res = $this->userLogic->pageUserRecordByUserId($id, input(""), 10);
                if($_res){
                    $type = config("user_record_type");
                    array_filter($_res['lists']['data'], function(&$_item) use ($type){
                        $_item['type_text'] = $type["{$_item['type']}_{$_item['direction']}"];
                        $_item['remark'] = json_decode($_item['remark'], true) ? json_decode($_item['remark'], true) : $_item['remark'];
                    });
                    $this->assign("datas", $_res['lists']);
                    $this->assign("pages", $_res['pages']);
                    return view("userDetail6");
                }else{
                    return "非法操作！";
                }
                break;
            default:
                return "非法操作！";
                break;
        }
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
        }
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
        $this->assign("totalAmount", $_res['totalAmount']);
        $this->assign("totalActual", $_res['totalActual']);
        $this->assign("totalPoundage", $_res['totalPoundage']);
        $this->assign("search", input(""));
        return view();
    }

    public function withdrawDetail($id = null)
    {
        $withdraw = (new UserWithdrawLogic())->getWithdrawById($id);
        if($withdraw){
            $state = [0=>"待审核", 1=>"审核通过",-1=>"审核拒绝",2=>"已到账"];
            $withdraw['remark'] = json_decode($withdraw['remark'], true);
            $withdraw['state_text'] = $state[$withdraw['state']];
            $this->assign("withdraw", $withdraw);
            return view();
        }else{
            return "非法操作！";
        }
    }

    public function withdraw()
    {
        if(request()->isPost())
        {
            $validate = \think\Loader::validate('UserWithDraw');
            if(!$validate->scene('user_withdraw')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $id = input('post.id/d');
                $state = input('post.state/d');
                list($res, $msg) = (new UserWithdrawLogic())->doWithdraw($id, $state);
                if($res){
                    return $this->ok();
                } else {
                    return $this->fail($msg);
                }
            }
        }
    }
}