<?php
namespace app\index\controller;

use app\index\logic\OrderLogic;
use app\index\logic\StockLogic;
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
                    $poundage = cf("manager_poundage", 88);
                    $this->assign("user", $user);
                    $this->assign("poundage", $poundage);
                    return view("manager/wait");
                }elseif ($user['has_one_manager']['state'] == 1){
                    // 审核通过
//                    if(!file_exists('./upload/manager_qrcode/' . $this->user_id . '.png')){
//                        self::createManagerQrcode($this->user_id);
//                    }
                }elseif ($user['has_one_manager']['state'] == 2){
                    //审核未通过
                    $poundage = cf("manager_poundage", 88);
                    $this->assign("user", $user);
                    $this->assign("poundage", $poundage);
                    return view("manager/register");
                }
            }else{
                // 未申请
                $poundage = cf("manager_poundage", 88);
                $this->assign("user", $user);
                $this->assign("poundage", $poundage);
                return view("manager/register");
            }
        }else{

            if(!file_exists('./upload/manager_qrcode/' . $this->user_id . '.png')){
                self::createManagerQrcode($this->user_id);
            }
            //经纪人下的用户
            $childrenIds = $this->_logic->getUidsByParentId($this->user_id);
            $user['children'] = count($childrenIds);
            $map = [
                'user_id' => ['in', $childrenIds],
                'state' => [
                    'in', [2,3],
                ],
            ];
            $orderLogic = new OrderLogic();
            //跟单用户Id
            $childOrderLists = $orderLogic->getAllBy($map);
            $allCodes = $orderLogic->getCodesBy([
                'user_id' => ['in', $childrenIds],
                'state' => 3,
            ]);
            $codeInfo = [];
            if($allCodes) $codeInfo = (new StockLogic())->simpleData($allCodes);

            $user['evening']    = 0;//跟单平仓
            $user['position']   = 0;//跟单持仓
            $user['realtime_income']   = 0;//实时收入
            $user['not_income']   = 0;//未结收入
            $niuren_point = $user['has_one_manager']['point']/100;//牛人返点


            foreach($childOrderLists as $v)
            {
                if($v['state'] == 2 && $v['proxy_rebate'] == 0) {
                    $user['evening'] += 1;//直属平仓
                    //未结收入 --平仓未结算
                    if($v['profit'] > 0)
                    {
                        $money = sprintf("%.2f", substr(sprintf("%.3f", $v['profit']*$niuren_point / 100), 0, -1));
                        $user['not_income'] += $money;
                    }


                }
                if($v['state'] == 3) {

                    $user['position'] += 1;//直属持仓
                    //实时收入 -持仓中
                    $sell_price = isset($codeInfo[$v['code']]['last_px']) ? $codeInfo[$v['code']]['last_px'] : 0;
                    $profit = $sell_price - $v['price'];
                    if($profit > 0)
                    {
                        $money_ = sprintf("%.2f", substr(sprintf("%.3f", $v['profit']*$niuren_point / 100), 0, -1));
                        $user['realtime_income'] += $money_;
                    }

                }

            }

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
            'type' => 0,
        ];
        if($startDate && $endDate) $map["create_at"] = ['between', [$startDate, $endDate]];
        $lists = $this->_logic->manageRecordList($map);
        $amount = $this->_logic->manageRecordAmount($map);
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

    /**
     * 直属平仓
     * @return \think\response\View
     */
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

    /**
     * 直属持仓
     * @return \think\response\View
     */
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