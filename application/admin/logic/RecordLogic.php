<?php
namespace app\admin\logic;


use app\admin\model\Admin;
use app\admin\model\AdminRecord;
use app\admin\model\DeferRecord;
use app\admin\model\User;
use app\admin\model\UserManagerRecord;
use app\admin\model\UserNiurenRecord;
use app\admin\model\UserRecharge;

class RecordLogic
{
    // 用户充值记录
    public function pageUserRechargeList($filter = [], $pageSize = null)
    {
        $where = [];
        $hasWhere = [];
        $where["stock_user_recharge.state"] = 1;
        $myUserIds = Admin::userIds();
        $myUserIds ? $where["stock_user_recharge.user_id"] = ["IN", $myUserIds] : null;
        // 订单号
        if(isset($filter['trade_no']) && !empty($filter['trade_no'])){
            $where['stock_user_recharge.trade_no'] = trim($filter['trade_no']);
        }
        // 充值人
        if(isset($filter['nickname']) && !empty($filter['nickname'])){
            $_nickname = trim($filter['nickname']);
            $hasWhere["nickname"] = ["LIKE", "%{$_nickname}%"];
        }
        // 手机号
        if(isset($filter['mobile']) && !empty($filter['mobile'])){
            $hasWhere["mobile"] = trim($filter['mobile']);
        }
        // 微圈
        if(isset($filter['ring']) && !empty($filter['ring'])){
            $_ring = trim($filter['ring']);
            $_where = ["username" => ["LIKE", "%{$_ring}%"]];
            $adminIds = Admin::where($_where)->column("admin_id");
            $hasWhere["admin_id"] = ["IN", $adminIds];
        }
        // 微会员
        if(isset($filter['member']) && !empty($filter['member'])){
            $_member = trim($filter['member']);
            $_where = ["username" => ["LIKE", "%{$_member}%"]];
            $memberAdminIds = Admin::where($_where)->column("admin_id") ? : [-1];
            $ringAdminIds = Admin::where(["pid" => ["IN", $memberAdminIds]])->column("admin_id") ? : [-1];
            $adminIds = array_unique(array_merge($memberAdminIds, $ringAdminIds));
            $adminIds = $adminIds ? : [-1];
            $userIds = User::where(["admin_id" => ["IN", $adminIds]])->column("user_id");
            if($myUserIds){
                $userIds = array_intersect($userIds, $myUserIds);
            }
            $where["stock_user_recharge.user_id"] = ["IN", $userIds];
        }
        // 经纪人
        if(isset($filter['manager']) && !empty($filter['manager'])){
            $_manager = trim($filter['manager']);
            $_where = ["username" => ["LIKE", "%{$_manager}%"]];
            $managerUserIds = User::where($_where)->column("user_id") ? : [-1];
            $hasWhere["parent_id"] = ["IN", $managerUserIds];
        }
        // 充值时间
        if(isset($filter['begin']) || isset($filter['end'])){
            if(!empty($filter['begin']) && !empty($filter['end'])){
                $_start = strtotime($filter['begin']);
                $_end = strtotime($filter['end']);
                $where['stock_user_recharge.create_at'] = ["BETWEEN", [$_start, $_end]];
            }elseif(!empty($filter['begin'])){
                $_start = strtotime($filter['begin']);
                $where['stock_user_recharge.create_at'] = ["EGT", $_start];
            }elseif(!empty($filter['end'])){
                $_end = strtotime($filter['end']);
                $where['stock_user_recharge.create_at'] = ["ELT", $_end];
            }
        }
        $pageSize = $pageSize ? : config("page_size");
        $totalAmount = UserRecharge::hasWhere("belongsToUser", $hasWhere)->where($where)->sum("amount");
        $totalActual = UserRecharge::hasWhere("belongsToUser", $hasWhere)->where($where)->sum("actual");
        $totalPoundage = UserRecharge::hasWhere("belongsToUser", $hasWhere)->where($where)->sum("poundage");
        $_lists = UserRecharge::hasWhere("belongsToUser", $hasWhere)
                    ->with(["belongsToUser" => ["hasOneParent", "hasOneAdmin" => ["hasOneParent"]]])
                    ->where($where)
                    ->order("id DESC")
                    ->paginate($pageSize, false, ['query'=>request()->param()]);
        $lists = $_lists->toArray();
        $pages = $_lists->render();
        return compact("lists", "pages", "totalAmount", "totalActual", "totalPoundage");
    }

    public function pageNiurenRecord($filter = [], $pageSize = null)
    {
        $where = [];
        $hasWhere["is_niuren"] = 1;
        $myUserIds = Admin::userIds();
        $myUserIds ? $where["stock_user_niuren_record.user_id"] = ["IN", $myUserIds] : null;
        // 牛人
        if(isset($filter['nickname']) && !empty($filter['nickname'])){
            $_nickname = trim($filter['nickname']);
            $hasWhere["nickname"] = ["LIKE", "%{$_nickname}%"];
        }
        // 手机号
        if(isset($filter['mobile']) && !empty($filter['mobile'])){
            $hasWhere["mobile"] = trim($filter['mobile']);
        }
        // 微圈
        if(isset($filter['ring']) && !empty($filter['ring'])){
            $_ring = trim($filter['ring']);
            $_where = ["username" => ["LIKE", "%{$_ring}%"]];
            $adminIds = Admin::where($_where)->column("admin_id");
            $hasWhere["admin_id"] = ["IN", $adminIds];
        }
        // 微会员
        if(isset($filter['member']) && !empty($filter['member'])){
            $_member = trim($filter['member']);
            $_where = ["username" => ["LIKE", "%{$_member}%"]];
            $memberAdminIds = Admin::where($_where)->column("admin_id") ? : [-1];
            $ringAdminIds = Admin::where(["pid" => ["IN", $memberAdminIds]])->column("admin_id") ? : [-1];
            $adminIds = array_unique(array_merge($memberAdminIds, $ringAdminIds));
            $adminIds = $adminIds ? : [-1];
            $userIds = User::where(["admin_id" => ["IN", $adminIds]])->column("user_id");
            if($myUserIds){
                $userIds = array_intersect($userIds, $myUserIds);
            }
            $where["stock_user_niuren_record.user_id"] = ["IN", $userIds];
        }
        // 结算时间
        if(isset($filter['begin']) || isset($filter['end'])){
            if(!empty($filter['begin']) && !empty($filter['end'])){
                $_start = strtotime($filter['begin']);
                $_end = strtotime($filter['end']);
                $where['stock_user_niuren_record.create_at'] = ["BETWEEN", [$_start, $_end]];
            }elseif(!empty($filter['begin'])){
                $_start = strtotime($filter['begin']);
                $where['stock_user_niuren_record.create_at'] = ["EGT", $_start];
            }elseif(!empty($filter['end'])){
                $_end = strtotime($filter['end']);
                $where['stock_user_niuren_record.create_at'] = ["ELT", $_end];
            }
        }
        // 分成类型
        if(isset($filter['type']) && is_numeric($filter['type'])){
            $where["stock_user_niuren_record.type"] = $filter['type'];
        }
        $pageSize = $pageSize ? : config("page_size");
        $totalMoney = UserNiurenRecord::hasWhere("belongsToNiuren", $hasWhere)->where($where)->sum("money");
        $_lists = UserNiurenRecord::hasWhere("belongsToNiuren", $hasWhere)
                        ->with(["belongsToNiuren" => ["hasOneAdmin" => ["hasOneParent"]], "belongsToOrder"])
                        ->where($where)
                        ->paginate($pageSize, false, ['query'=>request()->param()]);
        $lists = $_lists->toArray();
        $pages = $_lists->render();
        return compact("lists", "pages", "totalMoney");
    }

    // 代理商个人返点记录
    public function pageSelfRecordById($adminId, $pageSize = null)
    {

    }

    public function pageManagerRecord($filter = [], $pageSize = null)
    {
        $where = [];
        $hasWhere["is_manager"] = 1;
        $myUserIds = Admin::userIds();
        $myUserIds ? $where["stock_user_manager_record.user_id"] = ["IN", $myUserIds] : null;
        // 经纪人
        if(isset($filter['nickname']) && !empty($filter['nickname'])){
            $_nickname = trim($filter['nickname']);
            $hasWhere["nickname"] = ["LIKE", "%{$_nickname}%"];
        }
        // 手机号
        if(isset($filter['mobile']) && !empty($filter['mobile'])){
            $hasWhere["mobile"] = trim($filter['mobile']);
        }
        // 微圈
        if(isset($filter['ring']) && !empty($filter['ring'])){
            $_ring = trim($filter['ring']);
            $_where = ["username" => ["LIKE", "%{$_ring}%"]];
            $adminIds = Admin::where($_where)->column("admin_id");
            $hasWhere["admin_id"] = ["IN", $adminIds];
        }
        // 微会员
        if(isset($filter['member']) && !empty($filter['member'])){
            $_member = trim($filter['member']);
            $_where = ["username" => ["LIKE", "%{$_member}%"]];
            $memberAdminIds = Admin::where($_where)->column("admin_id") ? : [-1];
            $ringAdminIds = Admin::where(["pid" => ["IN", $memberAdminIds]])->column("admin_id") ? : [-1];
            $adminIds = array_unique(array_merge($memberAdminIds, $ringAdminIds));
            $adminIds = $adminIds ? : [-1];
            $userIds = User::where(["admin_id" => ["IN", $adminIds]])->column("user_id");
            if($myUserIds){
                $userIds = array_intersect($userIds, $myUserIds);
            }
            $where["stock_user_manager_record.user_id"] = ["IN", $userIds];
        }
        // 结算时间
        if(isset($filter['begin']) || isset($filter['end'])){
            if(!empty($filter['begin']) && !empty($filter['end'])){
                $_start = strtotime($filter['begin']);
                $_end = strtotime($filter['end']);
                $where['stock_user_manager_record.create_at'] = ["BETWEEN", [$_start, $_end]];
            }elseif(!empty($filter['begin'])){
                $_start = strtotime($filter['begin']);
                $where['stock_user_manager_record.create_at'] = ["EGT", $_start];
            }elseif(!empty($filter['end'])){
                $_end = strtotime($filter['end']);
                $where['stock_user_manager_record.create_at'] = ["ELT", $_end];
            }
        }
        // 分成类型
        if(isset($filter['type']) && is_numeric($filter['type'])){
            $where["stock_user_manager_record.type"] = $filter['type'];
        }
        $pageSize = $pageSize ? : config("page_size");
        $totalMoney = UserManagerRecord::hasWhere("belongsToManager", $hasWhere)->where($where)->sum("money");
        $_lists = UserManagerRecord::hasWhere("belongsToManager", $hasWhere)
                    ->with(["belongsToManager" => ["hasOneAdmin" => ["hasOneParent"]], "belongsToOrder"])
                    ->where($where)
                    ->paginate($pageSize, false, ['query'=>request()->param()]);
        $lists = $_lists->toArray();
        $pages = $_lists->render();
        return compact("lists", "pages", "totalMoney");
    }

    public function pageAdminRecord($filter = [], $pageSize = null)
    {
        $where = Admin::manager();
        $hasWhere = [];
        if(isset($where['admin_id'])){
            $where['stock_admin_record.admin_id'] = $where['admin_id'];
            unset($where['admin_id']);
        }
        // 代理商
        if(isset($filter['nickname']) && !empty($filter['nickname'])){
            $_nickname = trim($filter['nickname']);
            $hasWhere["nickname|username"] = ["LIKE", "%{$_nickname}%"];
        }
        // 代理商类型
        if(isset($filter['role']) && is_numeric($filter['role'])){
            $hasWhere["role"] = $filter['role'];
        }
        // 结算时间
        if(isset($filter['begin']) || isset($filter['end'])){
            if(!empty($filter['begin']) && !empty($filter['end'])){
                $_start = strtotime($filter['begin']);
                $_end = strtotime($filter['end']);
                $where['stock_admin_record.create_at'] = ["BETWEEN", [$_start, $_end]];
            }elseif(!empty($filter['begin'])){
                $_start = strtotime($filter['begin']);
                $where['stock_admin_record.create_at'] = ["EGT", $_start];
            }elseif(!empty($filter['end'])){
                $_end = strtotime($filter['end']);
                $where['stock_admin_record.create_at'] = ["ELT", $_end];
            }
        }
        // 分成类型
        if(isset($filter['type']) && is_numeric($filter['type'])){
            $where["type"] = $filter['type'];
        }
        $pageSize = $pageSize ? : config("page_size");
        $totalMoney = AdminRecord::hasWhere("belongsToAdmin", $hasWhere)->where($where)->sum("money");
        $_lists = AdminRecord::hasWhere("belongsToAdmin", $hasWhere)
                        ->with(["belongsToAdmin", "belongsToOrder"])
                        ->where($where)
                        ->order("id DESC")
                        ->paginate($pageSize, false, ['query'=>request()->param()]);
        $lists = $_lists->toArray();
        $pages = $_lists->render();
        return compact("lists", "pages", "totalMoney");
    }

    public function pageDeferRecord($filter = [], $pageSize = null)
    {
        $where = [];
        $hasWhere = [];
        $myUserIds = Admin::userIds();
        $myUserIds ? $where["stock_defer_record.user_id"] = ["IN", $myUserIds] : null;
        // 昵称
        if(isset($filter['nickname']) && !empty($filter['nickname'])){
            $_nickname = trim($filter['nickname']);
            $hasWhere["nickname"] = ["LIKE", "%{$_nickname}%"];
        }
        // 手机号
        if(isset($filter['mobile']) && !empty($filter['mobile'])){
            $hasWhere["mobile"] = trim($filter['mobile']);
        }
        // 策略ID
        if(isset($filter['orderId']) && !empty($filter['orderId'])){
            $where["stock_defer_record.order_id"] = trim($filter['orderId']);
        }
        // 结算时间
        if(isset($filter['begin']) || isset($filter['end'])){
            if(!empty($filter['begin']) && !empty($filter['end'])){
                $_start = strtotime($filter['begin']);
                $_end = strtotime($filter['end']);
                $where['stock_defer_record.create_at'] = ["BETWEEN", [$_start, $_end]];
            }elseif(!empty($filter['begin'])){
                $_start = strtotime($filter['begin']);
                $where['stock_defer_record.create_at'] = ["EGT", $_start];
            }elseif(!empty($filter['end'])){
                $_end = strtotime($filter['end']);
                $where['stock_defer_record.create_at'] = ["ELT", $_end];
            }
        }
        // 扣除方式
        if(isset($filter['type']) && is_numeric($filter['type'])){
            $where["stock_defer_record.type"] = $filter['type'];
        }
        $pageSize = $pageSize ? : config("page_size");
        $totalMoney = DeferRecord::hasWhere("belongsToUser", $hasWhere)->where($where)->sum("money");
        $_lists = DeferRecord::hasWhere("belongsToUser", $hasWhere)
                        ->with(["belongsToUser"])
                        ->where($where)
                        ->order("id DESC")
                        ->paginate($pageSize, false, ['query'=>request()->param()]);
        $lists = $_lists->toArray();
        $pages = $_lists->render();
        return compact("lists", "pages", "totalMoney");
    }
}