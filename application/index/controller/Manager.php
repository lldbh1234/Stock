<?php
namespace app\index\controller;

use app\index\logic\OrderLogic;
use think\Request;
use app\index\logic\UserLogic;

class Manager extends Base
{
    protected $_logic;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->_logic = new UserLogic();
    }
    public function manager()
    {
        $user = $this->_logic->userIncManager($this->user_id);
        if($user['is_manager'] == -1){
            if($user['has_one_manager']){
                if($user['has_one_manager']['state'] == 0){
                    // 待审核
                    $configs = cfgs();
                    $poundage = isset($configs['manager_poundage']) && $configs['manager_poundage'] ? $configs['manager_poundage'] : 88;
                    $this->assign("user", $user);
                    $this->assign("poundage", $poundage);
                    return view("manager/wait");
                }elseif ($user['has_one_manager']['state'] == 1){
                    // 审核通过
                    if(!file_exists('./upload/manager_qrcode/' . $this->user_id . '.png')){
                        self::createManagerQrcode($this->user_id);
                    }
                }elseif ($user['has_one_manager']['state'] == 2){
                    //审核未通过
                    $configs = cfgs();
                    $poundage = isset($configs['manager_poundage']) && $configs['manager_poundage'] ? $configs['manager_poundage'] : 88;
                    $this->assign("user", $user);
                    $this->assign("poundage", $poundage);
                    return view("manager/register");
                }
            }else{
                // 未申请
                $configs = cfgs();
                $poundage = isset($configs['manager_poundage']) && $configs['manager_poundage'] ? $configs['manager_poundage'] : 88;
                $this->assign("user", $user);
                $this->assign("poundage", $poundage);
                return view("manager/register");
            }
        }else{
            $childrenIds = $this->_logic->getUidsByParentId($this->user_id);
            $user['children'] = count($childrenIds);
            $orderLogic = new OrderLogic();
            //直属平仓
            $user['pingcang'] = $orderLogic->countBy(['user_id' => ['in', $childrenIds], 'state' => 2]);//抛出
            //直属持仓
            $user['chicang'] = $orderLogic->countBy(['user_id' => ['in', $childrenIds], 'state' => 3]);//持仓

            $this->assign("user", $user);
            return view("manager/home");
        }
        //异常
        $this->assign("user", $user);
        return view("manager/register");
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


    public function incomeLists()
    {
        $data = input('post.');
        $startDate = isset($data['startDate']) ? strtotime($data['startDate']) : '';
        $endDate = isset($data['endDate']) ? strtotime($data['endDate'])+86399 : '';
        $map = [
            'user_id' => $this->user_id,
            'type' => ['in', [3,8]],
        ];
        if($startDate && $endDate) $map["create_at"] = ['between', [$startDate, $endDate]];
        $lists = $this->_logic->recordList($map);
        $amount = $this->_logic->recordAmount($map);
        $search = [
            'startDate' => $startDate ? date('Y-m-d', $startDate) : date('Y-m-d'),
            'endDate' => $endDate ? date('Y-m-d', $endDate) : date('Y-m-d'),
        ];

        $this->assign('search', $search);
        $this->assign('amount', $amount);
        $this->assign('lists', $lists);
        return view();
    }
    public function children()
    {
        $userLogic = new UserLogic();
        $data = input('post.');
        $map = ['parent_id' => $this->user_id];
        isset($data['mobile']) ? $map['mobile'] = ['like', '%'. $data['mobile'] .'%'] :  '';
        $lists = $userLogic->getAllBy($map);
        $this->assign('lists', $lists);
        $search = ['mobile' => isset($data['mobile']) ? $data['mobile'] : ''];
        $this->assign('search', $search);
        return view();

    }
    public function followEvening()
    {
        $orderLogic = new OrderLogic();
        $userLogic = new UserLogic();

        $data = input('post.');
        $startDate = isset($data['startDate']) ? strtotime($data['startDate']) : '';
        $endDate = isset($data['endDate']) ? strtotime($data['endDate'])+86399 : '';

        $userIds = $userLogic->getUidsByParentId($this->user_id);

        $orderMap = ['user_id' => ['in', $userIds], 'state' => 2];
        if($startDate && $endDate) $orderMap["create_at"] = ['between', [$startDate, $endDate]];
        $lists =  $orderLogic->getAllBy($orderMap, ['create_at' => 'desc']);//抛出
        $search = [
            'startDate' => $startDate ? date('Y-m-d', $startDate) : date('Y-m-d'),
            'endDate' => $endDate ? date('Y-m-d', $endDate) : date('Y-m-d'),
        ];
        $this->assign('search', $search);
        $this->assign('lists', $lists);
        return view();
    }
    public function followPosition()
    {
        $orderLogic = new OrderLogic();
        $userLogic = new UserLogic();

        $data = input('post.');
        $startDate = isset($data['startDate']) ? strtotime($data['startDate']) : '';
        $endDate = isset($data['endDate']) ? strtotime($data['endDate'])+86399 : '';

        $userIds = $userLogic->getUidsByParentId($this->user_id);

        $orderMap = ['user_id' => ['in', $userIds], 'state' => 3];
        if($startDate && $endDate) $orderMap["create_at"] = ['between', [$startDate, $endDate]];
        $lists =  $orderLogic->getAllBy($orderMap, ['create_at' => 'desc']);//持仓
        $search = [
            'startDate' => $startDate ? date('Y-m-d', $startDate) : date('Y-m-d'),
            'endDate' => $endDate ? date('Y-m-d', $endDate) : date('Y-m-d'),
        ];
        $this->assign('search', $search);
        $this->assign('lists', $lists);
        return view();
    }
}