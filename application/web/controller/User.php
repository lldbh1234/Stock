<?php
namespace app\web\controller;

use app\web\logic\OrderLogic;
use app\web\logic\UserNoticeLogic;
use think\Request;
use app\web\logic\UserLogic;
use app\web\logic\BankLogic;
use app\web\logic\StockLogic;

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
        return view();
    }
    public function bankCards()
    {
        return view();
    }
    public function payMent()
    {
        return view();
    }
    public function authPayment()
    {
        return view();
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
                    $url = url("web/User/setting");
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
                    $url = url("web/User/index");
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

    public function avatar()
    {
        if(request()->isPost()){
            $file = request()->file('avatar');
            if(empty($file)) return $this->fail('系统提示:非法操作');
            $path = './upload/avatar/';
            $res = $file->move($path, 'user_id_'.$this->user_id.'.png');
            if($res){
                $file_name = $res->getFilename();
                $path = trim($path, '.');
                $ret = $this->_logic->updateUser(['user_id' => $this->user_id, 'face' => $path.$file_name]);
                if($ret)
                {
                    return $this->ok(['avatar' => $path.$file_name]);
                }
                return $this->fail('系统提示:头像上传失败');

            }else{
                return $this->fail($file->getError());
            }
        }

    }
    public function nickEdit()
    {
        if(request()->isPost()){
            $data = input('post.');
            $validate = \think\Loader::validate('User');
            if(!$validate->scene('update_nick')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{
                $updateArr = ['user_id' => $this->user_id];
                isset($data['nickname']) ? $updateArr['nickname'] = $data['nickname'] : '';
                $userLogic = new UserLogic();
                if($userLogic->updateUser($updateArr))
                {
                    return $this->ok();
                }
            }

            return $this->fail('系统提示:操作失败');
        }
    }

}