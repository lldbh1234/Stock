<?php
namespace app\admin\logic;


use app\admin\model\Admin;
use app\admin\model\AdminRecord;
use app\admin\model\AdminWithdraw;
use app\admin\model\DeferRecord;
use app\admin\model\User;
use app\admin\model\UserManagerRecord;
use app\admin\model\UserNiurenRecord;
use app\admin\model\UserRecharge;
use app\common\payment\paymentLLpay;
use think\Db;

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
        $tableCols = Admin::tableColumnShow();
        if($tableCols['settle'] == 1){
            $with = ["belongsToUser" => ["hasOneParent", "hasOneAdmin" => ["hasOneParent" => ["hasOneParent" => ["hasOneParent"]]]]];
        }elseif($tableCols['operate'] == 1){
            $with = ["belongsToUser" => ["hasOneParent", "hasOneAdmin" => ["hasOneParent" => ["hasOneParent"]]]];
        }elseif($tableCols['member'] == 1){
            $with = ["belongsToUser" => ["hasOneParent", "hasOneAdmin" => ["hasOneParent"]]];
        }elseif ($tableCols['ring'] == 1){
            $with = ["belongsToUser" => ["hasOneParent", "hasOneAdmin"]];
        }else{
            $with = ["belongsToUser" => ["hasOneParent"]];
        }
        $_lists = UserRecharge::hasWhere("belongsToUser", $hasWhere)
                    ->with($with)
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

    // 经纪人返点记录
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
                    ->order("id DESC")
                    ->paginate($pageSize, false, ['query'=>request()->param()]);
        $lists = $_lists->toArray();
        $pages = $_lists->render();
        return compact("lists", "pages", "totalMoney");
    }

    // 代理商个人返点记录
    public function pageSelfRecordById($adminId, $filter = [], $pageSize = null)
    {
        $where = ["admin_id" => $adminId];
        // 结算时间
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
        // 分成类型
        if(isset($filter['type']) && is_numeric($filter['type'])){
            $where["type"] = $filter['type'];
        }
        $totalMoney = AdminRecord::where($where)->sum("money");
        $_lists = AdminRecord::where($where)
            ->paginate($pageSize, false, ['query'=>request()->param()]);
        $lists = $_lists->toArray();
        $pages = $_lists->render();
        return compact("lists", "pages", "totalMoney");
    }

    // 代理商返点记录
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

    // 代理商出金记录
    public function pageProxyWithdrawLists($filter = [], $pageSize = null)
    {
        $where = Admin::manager();
        $hasWhere = [];
        if(isset($where['admin_id'])){
            $where['stock_admin_withdraw.admin_id'] = $where['admin_id'];
            unset($where['admin_id']);
        }
        // 订单号
        if(isset($filter['trade_no']) && is_numeric($filter['trade_no'])){
            $where["stock_admin_withdraw.out_sn"] = $filter['trade_no'];
        }
        // 代理商
        if(isset($filter['proxy']) && is_numeric($filter['proxy'])){
            $_proxy = trim($filter['proxy']);
            $hasWhere["nickname|username"] = ["LIKE", "%{$_proxy}%"];
        }
        // 手机
        if(isset($filter['mobile']) && is_numeric($filter['mobile'])){
            $hasWhere["mobile"] = trim($filter['mobile']);
        }
        // 状态
        if(isset($filter['state']) && is_numeric($filter['state']) && in_array($filter['state'], [0,1,2,-1])){//状态
            $where['stock_admin_withdraw.state'] = $filter['state'];
        }
        // 申请时间
        if(isset($filter['create_begin']) || isset($filter['create_end'])){
            if(!empty($filter['create_begin']) && !empty($filter['create_end'])){
                $_start = strtotime($filter['create_begin']);
                $_end = strtotime($filter['create_end']);
                $where['stock_admin_withdraw.create_at'] = ["BETWEEN", [$_start, $_end]];
            }elseif(!empty($filter['create_begin'])){
                $_start = strtotime($filter['create_begin']);
                $where['stock_admin_withdraw.create_at'] = ["EGT", $_start];
            }elseif(!empty($filter['create_end'])){
                $_end = strtotime($filter['create_end']);
                $where['stock_admin_withdraw.create_at'] = ["ELT", $_end];
            }
        }
        // 审核时间
        if(isset($filter['update_begin']) || isset($filter['update_end'])){
            if(!empty($filter['update_begin']) && !empty($filter['update_end'])){
                $_start = strtotime($filter['update_begin']);
                $_end = strtotime($filter['update_end']);
                $where['stock_admin_withdraw.update_at'] = ["BETWEEN", [$_start, $_end]];
            }elseif(!empty($filter['update_begin'])){
                $_start = strtotime($filter['update_begin']);
                $where['stock_admin_withdraw.update_at'] = ["EGT", $_start];
            }elseif(!empty($filter['update_end'])){
                $_end = strtotime($filter['update_end']);
                $where['stock_admin_withdraw.update_at'] = ["ELT", $_end];
            }
        }
        $pageSize = $pageSize ? : config("page_size");
        $totalAmount = AdminWithdraw::hasWhere("belongsToAdmin", $hasWhere)->where($where)->sum("amount");
        $totalActual = AdminWithdraw::hasWhere("belongsToAdmin", $hasWhere)->where($where)->sum("actual");
        $totalPoundage = AdminWithdraw::hasWhere("belongsToAdmin", $hasWhere)->where($where)->sum("poundage");
        $_lists = AdminWithdraw::hasWhere("belongsToAdmin", $hasWhere)
                    ->with(["belongsToAdmin", "hasOneUpdateBy"])
                    ->where($where)
                    ->order("id DESC")
                    ->paginate($pageSize, false, ['query'=>request()->param()]);
        $lists = $_lists->toArray();
        $pages = $_lists->render();
        return compact("lists", "pages", "totalAmount", "totalActual", "totalPoundage");
    }

    // 代理商出金详情
    public function proxyWithdrawById($id)
    {
        $withdraw = AdminWithdraw::with("belongsToAdmin,hasOneUpdateBy")->find($id);
        return $withdraw ? $withdraw->toArray() : [];
    }

    public function doProxyWithdraw($id, $state)
    {
        Db::startTrans();
        try{
            Db::rollback();
            $withdraw = AdminWithdraw::find($id);
            if($state == 1){
                // 审核通过
                // 代付接口
                $withdrawData = [
                    "tradeNo" => $withdraw->out_sn,
                    "amount" => $withdraw->actual,
                    "createAt" => $withdraw->create_at,
                    "name" => $withdraw->remark["name"],
                    "card" => $withdraw->remark["card"],
                    "info"  => "58好策略代理商佣金提现",
                    "notify" => url("index/Notify/proxyPayment", "", true, true)
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
                    AdminWithdraw::update($data);
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
                AdminWithdraw::update($data);
                // 用户余额回退
                $admin = Admin::find($withdraw->admin_id);
                $admin->setInc("total_fee", $withdraw->amount);
            }
            Db::commit();
            return [true, '操作成功！'];
        }catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return [false, '系统提示：异常错误！'];
        }
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
        $tableCols = Admin::tableColumnShow();
        if($tableCols['settle'] == 1){
            $with = ["belongsToUser" => ["hasOneAdmin" => ["hasOneParent" => ["hasOneParent" => ["hasOneParent"]]]], "belongsToOrder" => ["belongsToMode"]];
        }elseif($tableCols['operate'] == 1){
            $with = ["belongsToUser" => ["hasOneAdmin" => ["hasOneParent" => ["hasOneParent"]]], "belongsToOrder" => ["belongsToMode"]];
        }elseif($tableCols['member'] == 1){
            $with = ["belongsToUser" => ["hasOneAdmin" => ["hasOneParent"]], "belongsToOrder" => ["belongsToMode"]];
        }elseif ($tableCols['ring'] == 1){
            $with = ["belongsToUser" => ["hasOneAdmin"], "belongsToOrder" => ["belongsToMode"]];
        }else{
            $with = ["belongsToUser", "belongsToOrder" => ["belongsToMode"]];
        }
        $pageSize = $pageSize ? : config("page_size");
        $totalMoney = DeferRecord::hasWhere("belongsToUser", $hasWhere)->where($where)->sum("money");
        $_lists = DeferRecord::hasWhere("belongsToUser", $hasWhere)
                        ->with($with)
                        ->where($where)
                        ->order("id DESC")
                        ->paginate($pageSize, false, ['query'=>request()->param()]);
        $lists = $_lists->toArray();
        $pages = $_lists->render();
        return compact("lists", "pages", "totalMoney");
    }
}