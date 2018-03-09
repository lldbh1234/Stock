<?php
namespace app\admin\logic;


use app\admin\model\Admin;
use app\admin\model\User;
use app\admin\model\UserRecharge;

class RecordLogic
{
    // 用户充值记录
    public function pageUserRechargeList($filter = [], $pageSize = null)
    {
        $where = Admin::manager();
        $where["state"] = 1;
        // 订单号
        if(isset($filter['trade_no']) && !empty($filter['trade_no'])){
            $where['trade_no'] = $filter['trade_no'];
        }
        // 充值人
        if(isset($filter['nickname']) && !empty($filter['nickname'])){
            $_nickname = trim($filter['nickname']);
            $_where = ["nickname" => ["LIKE", "%{$_nickname}%"]];
            $userIds = User::where($_where)->column("user_id");
            $where["user_id"] = ["IN", $userIds];
        }
        // 手机号
        if(isset($filter['mobile']) && !empty($filter['mobile'])){
            $_where = ["mobile" => trim($filter['mobile'])];
            $userIds = User::where($_where)->column("user_id");
            $where["user_id"] = ["IN", $userIds];
        }
        // 微圈
        if(isset($filter['ring']) && !empty($filter['ring'])){
            $_ring = trim($filter['ring']);
            $_where = ["username" => ["LIKE", "%{$_ring}%"]];
            $adminIds = Admin::where($_where)->column("admin_id");
            $tempUserIds = User::where(["admin_id" => ["IN", $adminIds]])->column("user_id");
            if(isset($userIds)){
                $userIds = array_intersect($userIds, $tempUserIds);
                $where["user_id"] = ["IN", $userIds];
            }else{
                $userIds = $tempUserIds;
                $where["user_id"] = ["IN", $userIds];
            }
        }
        // 微会员
        if(isset($filter['member']) && !empty($filter['member'])){
            $_member = trim($filter['member']);
            $_where = ["username" => ["LIKE", "%{$_member}%"]];
            $memberAdminIds = Admin::where($_where)->column("admin_id") ? : [-1];
            $ringAdminIds = Admin::where(["pid" => ["IN", $memberAdminIds]])->column("admin_id") ? : [-1];
            $adminIds = array_merge($memberAdminIds, $ringAdminIds);
            $adminIds = $adminIds ? : [-1];
            $tempUserIds = User::where(["admin_id" => ["IN", $adminIds]])->column("user_id");
            if(isset($userIds)){
                $userIds = array_intersect($userIds, $tempUserIds);
                $where["user_id"] = ["IN", $userIds];
            }else{
                $userIds = $tempUserIds;
                $where["user_id"] = ["IN", $userIds];
            }
        }
        // 经纪人
        if(isset($filter['manager']) && !empty($filter['manager'])){
            $_manager = trim($filter['manager']);
            $_where = ["username" => ["LIKE", "%{$_manager}%"]];
            $managerUserIds = User::where($_where)->column("user_id") ? : [-1];
            $tempUserIds = User::where(["parent_id" => ["IN", $managerUserIds]])->column("user_id");
            if(isset($userIds)){
                $userIds = array_intersect($userIds, $tempUserIds);
                $where["user_id"] = ["IN", $userIds];
            }else{
                $userIds = $tempUserIds;
                $where["user_id"] = ["IN", $userIds];
            }
        }
        // 充值时间
        if(isset($filter['begin']) || isset($filter['end'])){
            if(!empty($filter['begin']) && !empty($filter['end'])){
                $_start = strtotime($filter['begin']);
                $_end = strtotime($filter['end']);
                $where['create_at'] = ["BETWEEN", [$_start, $_end]];
            }elseif(!empty($filter['begin'])){
                $_start = strtotime($filter['begin']);
                $where['create_at'] = ["EGT", $_start];
            }elseif(!empty($filter['end'])){
                $_end = strtotime($filter['end']);
                $where['create_at'] = ["ELT", $_end];
            }
        }
        $pageSize = $pageSize ? : config("page_size");
        $totalAmount = UserRecharge::where($where)->sum("amount");
        $totalActual = UserRecharge::where($where)->sum("actual");
        $totalPoundage = UserRecharge::where($where)->sum("poundage");
        $_lists = UserRecharge::with(["belongsToUser" => ["hasOneParent", "hasOneAdmin" => ["hasOneParent"]]])
                    ->where($where)
                    ->order("id DESC")
                    ->paginate($pageSize, false, ['query'=>request()->param()]);
        $lists = $_lists->toArray();
        $pages = $_lists->render();
        return compact("lists", "pages", "totalAmount", "totalActual", "totalPoundage");
    }
}