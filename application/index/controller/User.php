<?php
namespace app\index\controller;

use think\Request;
use app\index\logic\UserLogic;
use app\index\logic\BankLogic;
use app\index\logic\StockLogic;

class User extends Base
{
    protected $_logic;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->_logic = new UserLogic();
    }

    public function index()
    {
        $userStatic = $this->_logic->userStatic($this->user_id);
        $this->assign("user", array_merge(uInfo(), $userStatic));
        return view();
    }

    public function setting()
    {
        $this->assign("user", uInfo());
        return view();
    }

    public function optional()
    {
        $stocks = $this->_logic->userOptional($this->user_id);
        if($stocks){
            $codes = array_column($stocks, "code");
            $lists = (new StockLogic())->simpleData($codes);
            array_filter($stocks, function(&$item) use ($lists){
                $item['quotation'] = $lists[$item['code']];
            });
        }
        $this->assign("stocks", $stocks);
        return view();
    }

    public function createOptional()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Optional');
            if(!$validate->scene('create')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $code = input("post.code/s");
                $stock = (new StockLogic())->stockByCode($code);
                $optionalId = $this->_logic->createUserOptional($this->user_id, $stock);
                if($optionalId > 0){
                    return $this->ok();
                }else{
                    return $this->fail("自选股票添加失败！");
                }
            }
        }
        return $this->fail("系统提示：非法操作！");
    }

    public function removeOptional()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Optional');
            if(!$validate->scene('remove')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $ids = input("post.ids/a");
                $res = $this->_logic->removeUserOptional($this->user_id, $ids);
                if($res){
                    return $this->ok();
                }else{
                    return $this->fail("删除失败！");
                }
            }
        }
        return $this->fail("系统提示：非法操作！");
    }

    public function password()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('User');
            if(!$validate->scene('password')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $data = [
                    "user_id"   => $this->user_id,
                    "password"  => input("post.newPassword/s")
                ];
                $res = $this->_logic->updateUser($data);
                if($res){
                    session("user_info", null);
                    $url = url("index/User/setting");
                    return $this->ok(['url' => $url]);
                }else{
                    return $this->fail("修改失败！");
                }
            }
        }
        return view();
    }

    public function recharge()
    {
        if(request()->isPost()){
            exit;
        }
        return view();
    }

    public function withdraw()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Withdraw');
            if(!$validate->scene('do')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $money = input("post.money/f");
                $bank = (new BankLogic())->bankByNumber(input("post.bank"));
                $remark = [
                    "bank" => $bank['name'],
                    "card" => input("post.card/s"),
                    "name" => input("post.realname/s"),
                    "addr" => input("post.address/s"),
                ];
                $withdrawId = $this->_logic->createUserWithdraw($this->user_id, $money, $remark);
                if($withdrawId > 0){
                    $url = url("index/User/index");
                    return $this->ok(['url' => $url]);
                }else{
                    return $this->fail("提现申请失败！");
                }
            }
        }
        $banks = (new BankLogic())->bankLists();
        $this->assign("user", uInfo());
        $this->assign("banks", $banks);
        return view();
    }

    public function manager()
    {
        $user = $this->_logic->userIncManager($this->user_id);
        if($user['is_manager'] == -1){
            if($user['has_one_manager']){
                if($user['has_one_manager']['state'] == 0){
                    // 待审核
                    $this->assign("user", $user);
                    return view("manager/wait");
                }elseif ($user['has_one_manager']['state'] == 1){
                    // 审核通过
                }else{

                }
            }else{
                // 未申请
                $this->assign("user", $user);
                return view("manager/register");
            }
        }else{
            $this->assign("user", $user);
            return view("manager/home");
        }
    }

    public function RegisterManager()
    {
        if(request()->isPost()){
            $validate = \think\Loader::validate('Manager');
            if(!$validate->scene('register')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $res = $this->_logic->saveUserManager($this->user_id, input("post."));
                if($res){
                    return $this->ok();
                }else{
                    return $this->fail("经纪人申请失败！");
                }
            }
        }
        return $this->fail("系统提示：非法操作！");
    }
}