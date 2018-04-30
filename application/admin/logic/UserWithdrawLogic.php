<?php
namespace app\admin\logic;

use app\admin\model\Admin;
use app\admin\model\User;
use app\admin\model\UserWithdraw;
use app\common\payment\paymentLLpay;
use think\Db;

class UserWithdrawLogic
{
    public function getWithdrawById($id)
    {
        $withdraw = UserWithdraw::with("hasOneUser,hasOneAdmin")->find($id);
        return $withdraw ? $withdraw->toArray() : [];
    }

    public function pageUserWithdrawLists($filter = [], $pageSize = null)
    {
        $where = [];
        $hasWhere = [];
        $myUserIds = Admin::userIds();
        $myUserIds ? $where["stock_user_withdraw.user_id"] = ["IN", $myUserIds] : null;
        if(isset($filter['username']) && !empty($filter['username'])){//用户
            $_username = trim($filter['username']);
            $hasWhere["username"] = ["LIKE", "%{$_username}%"];
        }

        if(isset($filter['mobile']) && !empty($filter['mobile'])){//用户
            $hasWhere["mobile"] = trim($filter['mobile']);
        }

        if(isset($filter['state']) && is_numeric($filter['state']) && in_array($filter['state'], [0,1,-1,2])){//状态
            $where['stock_user_withdraw.state'] = $filter['state'];
        }

        // 提现时间
        if(isset($filter['begin']) || isset($filter['end'])){
            if(!empty($filter['begin']) && !empty($filter['end'])){
                $_start = strtotime($filter['begin']);
                $_end = strtotime($filter['end']);
                $where['stock_user_withdraw.create_at'] = ["BETWEEN", [$_start, $_end]];
            }elseif(!empty($filter['begin'])){
                $_start = strtotime($filter['begin']);
                $where['stock_user_withdraw.create_at'] = ["EGT", $_start];
            }elseif(!empty($filter['end'])){
                $_end = strtotime($filter['end']);
                $where['stock_user_withdraw.create_at'] = ["ELT", $_end];
            }
        }

        $pageSize = $pageSize ? : config("page_size");
        $totalAmount = UserWithdraw::hasWhere("hasOneUser", $hasWhere)->where($where)->sum("amount");
        $totalActual = UserWithdraw::hasWhere("hasOneUser", $hasWhere)->where($where)->sum("actual");
        $totalPoundage = UserWithdraw::hasWhere("hasOneUser", $hasWhere)->where($where)->sum("poundage");
        //推荐人-微圈-微会员
        $_lists = UserWithdraw::hasWhere("hasOneUser", $hasWhere)
                ->with(['hasOneUser', 'hasOneAdmin'])
                ->where($where)
                ->order("id DESC")
                ->paginate($pageSize, false, ['query'=>request()->param()]);
        $lists = $_lists->toArray();
        $pages = $_lists->render();
        return compact("lists", "pages", "totalAmount", "totalActual", "totalPoundage");
    }

    public function withdrawById($id)
    {
        $withdraw = UserWithdraw::find($id);
        return $withdraw ? $withdraw->toArray() : [];
    }

    public function doWithdraw($id, $state)
    {
        Db::startTrans();
        try{
            $withdraw = UserWithdraw::find($id);
            if($state == 1){
                // 审核通过
                // 代付接口
                $remark = json_decode($withdraw->remark, true);
                $withdrawData = [
                    "tradeNo" => $withdraw->out_sn,
                    "amount" => $withdraw->actual,
                    //"amount" => 0.2,
                    "createAt" => $withdraw->create_at,
                    "name" => $remark["name"],
                    "card" => $remark["card"],
                ];
                $response = (new paymentLLpay())->payment($withdrawData);
                if($response['ret_code'] == '0000'){
                    // 代付申请成功
                    // 订单状态更改
                    $data = [
                        "id" => $id,
                        "state" => $state,
                        "update_by" => isLogin()
                    ];
                    UserWithdraw::update($data);
                }else{
                    // 代付申请失败
                    Db::rollback();
                    return [false, "代付平台错误：{$response['ret_msg']}！"];
                }
            }elseif($state == -1){
                // 审核拒绝
                // 订单状态更改
                $data = [
                    "id" => $id,
                    "state" => $state,
                    "update_by" => isLogin()
                ];
                UserWithdraw::update($data);
                // 用户余额回退
                $user = User::find($withdraw->user_id);
                $user->setInc("account", $withdraw->amount);
                // 资金明细
                $rData = [
                    "type" => 6,
                    "amount" => $withdraw->amount,
					"account" => $user->account,
                    "remark" => json_encode(['tradeNo' => $withdraw->out_sn]),
                    "direction" => 1
                ];
                $user->hasManyRecord()->save($rData);
            }
            Db::commit();
            return [true, '操作成功！'];
        }catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return [false, '系统提示：异常错误！'];
        }
    }

    /*public function withdrawById($param)
    {
        $data = UserWithdraw::find($param['id']);
        if(!$data) return ['code' => 1, 'msg' => '系统提示：非法操作！'];
        $data = $data->toArray();
        if($data['state'] == 1) return ['code' => 1, 'msg' => '系统提示：出金已审核，请勿重复操作！'];
        if($data['state'] == -1) return ['code' => 1, 'msg' => '系统提示：出金审核失败，请勿重复操作！'];
        $updateArr = [];
        $userUpdateArr = [];
        if($param['state'] == 1)//审核通过
        {
            $updateArr = [
                'id'    => $param['id'],
                'state' => $param['state'],
                'update_by' => isLogin(),
            ];
        }
        if($param['state'] == -1)//审核失败
        {
            //审核失败返还 用户金额
            $updateArr = [
                'id'    => $param['id'],
                'state' => $param['state'],
                'update_by' => isLogin(),
            ];
            $userUpdateArr = [
                'user_id' => $data['user_id'],
                'account' => ['exp', 'account + '.$data['amount']]
            ];
        }
        Db::startTrans();
        try{
            if($updateArr) UserWithdraw::update($updateArr);
            if($userUpdateArr) User::update($userUpdateArr);
            // 提交事务
            Db::commit();
            return ['code' => 0, '操作成功'];
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return ['code' => 1, '系统提示：操作异常！'];
        }
    }*/

}