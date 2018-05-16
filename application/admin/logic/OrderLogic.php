<?php
namespace app\admin\logic;

use app\admin\model\Admin;
use app\admin\model\Order;
use app\admin\model\User;
use think\Db;

class OrderLogic
{
    public function pageOrderLists($state = null, $where = [], $pageSize = null)
    {
        $where = [];
        $hasWhere = [];
        $myUserIds = Admin::userIds();
        $myUserIds ? $where["stock_order.user_id"] = ["IN", $myUserIds] : null;
        $state ? is_array($state) ? $where['stock_order.state'] = ["IN", $state] : $where['stock_order.state'] = $state : $where['stock_order.state'] = ["NEQ", 5];
        $pageSize = $pageSize ? : config("page_size");
        $lists = Order::hasWhere("hasOneUser", $hasWhere)
                    ->with(["hasOneUser" => ["hasOneParent", "hasOneAdmin" => ["hasOneParent"]], "belongsToMode"])
                    ->where($where)
                    ->order("order_id DESC")
                    ->paginate($pageSize);
        $records = $lists->toArray();
        $defer = [1 => '是', 0 => '否'];
        $follow = [1 => '是', 0 => '否'];
        $hedging = [1 => '是', 0 => '否'];
        $state = [1 => '委托建仓', 2 => '平仓', 3 => '持仓', 4 => '委托平仓', 5 => '作废', 6=> '强制平仓'];
        array_filter($records['data'], function(&$item) use ($defer, $follow, $state, $hedging){
            $item['is_defer_text'] = $defer[$item['is_defer']];
            $item['state_text'] = $state[$item['state']];
            $item['is_follow_text'] = $follow[$item['is_follow']];
            $item['is_hedging_text'] = $hedging[$item['is_hedging']];
        });
        return ["lists" => $records, "pages" => $lists->render()];
    }

    // 委托订单
    public function pageEntrustOrders($filter = [], $pageSize = null)
    {
        $where = [];
        $hasWhere = [];
        $myUserIds = Admin::userIds();
        $myUserIds ? $where["stock_order.user_id"] = ["IN", $myUserIds] : null;
        //$where['stock_order.state'] = ["IN", [1, 4]];
        $where['stock_order.state'] = 4;
        // 策略ID
        if(isset($filter['id']) && !empty($filter['id']) && is_numeric($filter['id'])){
            $where["stock_order.order_id"] = trim($filter['id']);
        }
        // 昵称
        if(isset($filter['nickname']) && !empty($filter['nickname'])){
            $_nickname = trim($filter['nickname']);
            $hasWhere["nickname"] = ["LIKE", "%{$_nickname}%"];
        }
        // 手机号
        if(isset($filter['mobile']) && !empty($filter['mobile'])){
            $hasWhere["mobile"] = trim($filter['mobile']);
        }
        // 股票代码
        if(isset($filter['code']) && !empty($filter['code'])){
            $where['stock_order.code'] = trim($filter['code']);
        }
        // 股票名称
        if(isset($filter['name']) && !empty($filter['name'])){
            $_name = trim($filter['name']);
            $where["stock_order.name"] = ["LIKE", "%{$_name}%"];
        }
        // 微圈
        /*if(isset($filter['ring']) && !empty($filter['ring'])){
            $_ring = trim($filter['ring']);
            $_where = ["username" => ["LIKE", "%{$_ring}%"]];
            $adminIds = Admin::where($_where)->column("admin_id");
            $hasWhere["admin_id"] = ["IN", $adminIds];
        }*/
        // 微会员
        /*if(isset($filter['member']) && !empty($filter['member'])){
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
            $where["stock_order.user_id"] = ["IN", $userIds];
        }*/
        // 经纪人
        /*if(isset($filter['manager']) && !empty($filter['manager'])){
            $_manager = trim($filter['manager']);
            $_where = ["username" => ["LIKE", "%{$_manager}%"]];
            $managerUserIds = User::where($_where)->column("user_id") ? : [-1];
            $hasWhere["parent_id"] = ["IN", $managerUserIds];
        }*/
        // 委托时间
        if(isset($filter['begin']) || isset($filter['end'])){
            if(!empty($filter['begin']) && !empty($filter['end'])){
                $_start = strtotime($filter['begin']);
                $_end = strtotime($filter['end']);
                $where['stock_order.update_at'] = ["BETWEEN", [$_start, $_end]];
            }elseif(!empty($filter['begin'])){
                $_start = strtotime($filter['begin']);
                $where['stock_order.update_at'] = ["EGT", $_start];
            }elseif(!empty($filter['end'])){
                $_end = strtotime($filter['end']);
                $where['stock_order.update_at'] = ["ELT", $_end];
            }
        }
        $pageSize = $pageSize ? : config("page_size");
        /*$lists = Order::hasWhere("hasOneUser", $hasWhere)
            ->with(["hasOneUser" => ["hasOneParent", "hasOneAdmin" => ["hasOneParent"]], "hasOneOperator"])
            ->where($where)
            ->order("order_id DESC")
            ->paginate($pageSize, false, ['query'=>request()->param()]);*/
        $lists = Order::hasWhere("hasOneUser", $hasWhere)
            ->with(["hasOneUser", "hasOneOperator", "belongsToMode"])
            ->where($where)
            ->order("order_id DESC")
            ->paginate($pageSize, false, ['query'=>request()->param()]);
        $records = $lists->toArray();
        $state = [1 => '委托建仓', 2 => '平仓', 3 => '持仓', 4 => '委托平仓', 5 => '作废'];
        array_filter($records['data'], function(&$item) use ($state){
            $item['state_text'] = $state[$item['state']];
        });
        return ["lists" => $records, "pages" => $lists->render()];
    }

    // 平仓订单
    public function pageHistoryOrders($filter = [], $pageSize = null)
    {
        $where = [];
        $hasWhere = [];
        $myUserIds = Admin::userIds();
        $myUserIds ? $where["stock_order.user_id"] = ["IN", $myUserIds] : null;
        $where['stock_order.state'] = 2;
        // 策略ID
        if(isset($filter['id']) && !empty($filter['id']) && is_numeric($filter['id'])){
            $where["stock_order.order_id"] = trim($filter['id']);
        }
        // 昵称
        if(isset($filter['nickname']) && !empty($filter['nickname'])){
            $_nickname = trim($filter['nickname']);
            $hasWhere["nickname"] = ["LIKE", "%{$_nickname}%"];
        }
        // 手机号
        if(isset($filter['mobile']) && !empty($filter['mobile'])){
            $hasWhere["mobile"] = trim($filter['mobile']);
        }
        // 股票代码
        if(isset($filter['code']) && !empty($filter['code'])){
            $where['stock_order.code'] = trim($filter['code']);
        }
        // 股票名称
        if(isset($filter['name']) && !empty($filter['name'])){
            $_name = trim($filter['name']);
            $where["stock_order.name"] = ["LIKE", "%{$_name}%"];
        }
        // 结算中心
        if(isset($filter['settle']) && !empty($filter['settle'])){
            $_where = [
                "username" => trim($filter['settle']),
                "role" => Admin::SETTLE_ROLE_ID
            ];
            $settleId = Admin::where($_where)->value("admin_id");
            $ringIds = Admin::childrenAdminIds($settleId);
            $hasWhere["admin_id"] = ["IN", $ringIds];
        }
        // 运营中心
        if(isset($filter['operate']) && !empty($filter['operate'])){
            $_where = [
                "username" => trim($filter['operate']),
                "role" => Admin::OPERATE_ROLE_ID
            ];
            $operateId = Admin::where($_where)->value("admin_id");
            $tempRingIds = Admin::childrenAdminIds($operateId);
            $ringIds = isset($ringIds) ? array_intersect($ringIds, $tempRingIds) : $tempRingIds;
            $hasWhere["admin_id"] = ["IN", $ringIds];
        }
        // 微会员
        if(isset($filter['member']) && !empty($filter['member'])){
            $_where = [
                "username" => trim($filter['member']),
                "role" => Admin::MEMBER_ROLE_ID
            ];
            $memberId = Admin::where($_where)->value("admin_id");
            $tempRingIds = Admin::childrenAdminIds($memberId);
            $ringIds = isset($ringIds) ? array_intersect($ringIds, $tempRingIds) : $tempRingIds;
            $hasWhere["admin_id"] = ["IN", $ringIds];
        }
        // 微圈
        if(isset($filter['ring']) && !empty($filter['ring'])){
            $_where = [
                "username" => trim($filter['ring']),
                "role" => Admin::RING_ROLE_ID
            ];
            $tempRingIds = Admin::where($_where)->column("admin_id");
            $ringIds = isset($ringIds) ? array_intersect($ringIds, $tempRingIds) : $tempRingIds;
            $hasWhere["admin_id"] = ["IN", $ringIds];
        }
        // 经纪人
        /*if(isset($filter['manager']) && !empty($filter['manager'])){
            $_manager = trim($filter['manager']);
            $_where = ["username" => ["LIKE", "%{$_manager}%"]];
            $managerUserIds = User::where($_where)->column("user_id") ? : [-1];
            $hasWhere["parent_id"] = ["IN", $managerUserIds];
        }*/
        // 买入时间
        if(isset($filter['create_begin']) || isset($filter['create_end'])){
            if(!empty($filter['create_begin']) && !empty($filter['create_end'])){
                $_start = strtotime($filter['create_begin']);
                $_end = strtotime($filter['create_end']);
                $where['stock_order.create_at'] = ["BETWEEN", [$_start, $_end]];
            }elseif(!empty($filter['create_begin'])){
                $_start = strtotime($filter['create_begin']);
                $where['stock_order.create_at'] = ["EGT", $_start];
            }elseif(!empty($filter['create_end'])){
                $_end = strtotime($filter['create_end']);
                $where['stock_order.create_at'] = ["ELT", $_end];
            }
        }
        // 卖出时间
        if(isset($filter['sell_begin']) || isset($filter['sell_end'])){
            if(!empty($filter['sell_begin']) && !empty($filter['sell_end'])){
                $_start = strtotime($filter['sell_begin']);
                $_end = strtotime($filter['sell_end']);
                $where['stock_order.update_at'] = ["BETWEEN", [$_start, $_end]];
            }elseif(!empty($filter['sell_begin'])){
                $_start = strtotime($filter['sell_begin']);
                $where['stock_order.update_at'] = ["EGT", $_start];
            }elseif(!empty($filter['sell_end'])){
                $_end = strtotime($filter['sell_end']);
                $where['stock_order.update_at'] = ["ELT", $_end];
            }
        }
        $pageSize = $pageSize ? : config("page_size");
        $totalProfit = Order::hasWhere("hasOneUser", $hasWhere)->where($where)->sum("profit");
        $totalDeposit = Order::hasWhere("hasOneUser", $hasWhere)->where($where)->sum("deposit");
        $totalJiancang = Order::hasWhere("hasOneUser", $hasWhere)->where($where)->sum("jiancang_fee");
        $totalDefer = Order::hasWhere("hasOneUser", $hasWhere)->where($where)->sum("defer_total");
        /*$lists = Order::hasWhere("hasOneUser", $hasWhere)
            ->with(["hasOneUser" => ["hasOneParent", "hasOneAdmin" => ["hasOneParent"]], "hasOneOperator"])
            ->where($where)
            ->order("order_id DESC")
            ->paginate($pageSize, false, ['query'=>request()->param()]);*/
        $_lists = Order::hasWhere("hasOneUser", $hasWhere)
            ->with(["hasOneUser", "hasOneOperator", "belongsToMode"])
            ->where($where)
            ->order("order_id DESC")
            ->paginate($pageSize, false, ['query'=>request()->param()]);
        $lists = $_lists->toArray();
        $pages = $_lists->render();
        return compact("lists", "pages", "totalProfit", "totalDeposit", "totalJiancang", "totalDefer");
    }

    public function pagePositionOrders($filter = [], $pageSize = null)
    {
        $where = [];
        $hasWhere = [];
        $myUserIds = Admin::userIds();
        $myUserIds ? $where["stock_order.user_id"] = ["IN", $myUserIds] : null;
        $where['stock_order.state'] = 3;
        // 策略ID
        if(isset($filter['id']) && !empty($filter['id']) && is_numeric($filter['id'])){
            $where["stock_order.order_id"] = trim($filter['id']);
        }
        // 昵称
        if(isset($filter['nickname']) && !empty($filter['nickname'])){
            $_nickname = trim($filter['nickname']);
            $hasWhere["nickname"] = ["LIKE", "%{$_nickname}%"];
        }
        // 手机号
        if(isset($filter['mobile']) && !empty($filter['mobile'])){
            $hasWhere["mobile"] = trim($filter['mobile']);
        }
        // 股票代码
        if(isset($filter['code']) && !empty($filter['code'])){
            $where['stock_order.code'] = trim($filter['code']);
        }
        // 股票名称
        if(isset($filter['name']) && !empty($filter['name'])){
            $_name = trim($filter['name']);
            $where["stock_order.name"] = ["LIKE", "%{$_name}%"];
        }
        // 股票名称
        if(isset($filter['name']) && !empty($filter['name'])){
            $_name = trim($filter['name']);
            $where["stock_order.name"] = ["LIKE", "%{$_name}%"];
        }
        // 结算中心
        if(isset($filter['settle']) && !empty($filter['settle'])){
            $_where = [
                "username" => trim($filter['settle']),
                "role" => Admin::SETTLE_ROLE_ID
            ];
            $settleId = Admin::where($_where)->value("admin_id");
            $ringIds = Admin::childrenAdminIds($settleId);
            $hasWhere["admin_id"] = ["IN", $ringIds];
        }
        // 运营中心
        if(isset($filter['operate']) && !empty($filter['operate'])){
            $_where = [
                "username" => trim($filter['operate']),
                "role" => Admin::OPERATE_ROLE_ID
            ];
            $operateId = Admin::where($_where)->value("admin_id");
            $tempRingIds = Admin::childrenAdminIds($operateId);
            $ringIds = isset($ringIds) ? array_intersect($ringIds, $tempRingIds) : $tempRingIds;
            $hasWhere["admin_id"] = ["IN", $ringIds];
        }
        // 微会员
        if(isset($filter['member']) && !empty($filter['member'])){
            $_where = [
                "username" => trim($filter['member']),
                "role" => Admin::MEMBER_ROLE_ID
            ];
            $memberId = Admin::where($_where)->value("admin_id");
            $tempRingIds = Admin::childrenAdminIds($memberId);
            $ringIds = isset($ringIds) ? array_intersect($ringIds, $tempRingIds) : $tempRingIds;
            $hasWhere["admin_id"] = ["IN", $ringIds];
        }
        // 微圈
        if(isset($filter['ring']) && !empty($filter['ring'])){
            $_where = [
                "username" => trim($filter['ring']),
                "role" => Admin::RING_ROLE_ID
            ];
            $tempRingIds = Admin::where($_where)->column("admin_id");
            $ringIds = isset($ringIds) ? array_intersect($ringIds, $tempRingIds) : $tempRingIds;
            $hasWhere["admin_id"] = ["IN", $ringIds];
        }
        // 经纪人
        /*if(isset($filter['manager']) && !empty($filter['manager'])){
            $_manager = trim($filter['manager']);
            $_where = ["username" => ["LIKE", "%{$_manager}%"]];
            $managerUserIds = User::where($_where)->column("user_id") ? : [-1];
            $hasWhere["parent_id"] = ["IN", $managerUserIds];
        }*/
        // 提交时间
        if(isset($filter['create_begin']) || isset($filter['create_end'])){
            if(!empty($filter['create_begin']) && !empty($filter['create_end'])){
                $_start = strtotime($filter['create_begin']);
                $_end = strtotime($filter['create_end']);
                $where['stock_order.create_at'] = ["BETWEEN", [$_start, $_end]];
            }elseif(!empty($filter['create_begin'])){
                $_start = strtotime($filter['create_begin']);
                $where['stock_order.create_at'] = ["EGT", $_start];
            }elseif(!empty($filter['create_end'])){
                $_end = strtotime($filter['create_end']);
                $where['stock_order.create_at'] = ["ELT", $_end];
            }
        }
        // 是否对冲
        /*if(isset($filter['is_hedging']) && is_numeric($filter['is_hedging'])){
            $hasWhere["stock_order.is_hedging"] = $filter['is_hedging'];
        }*/
        $pageSize = $pageSize ? : config("page_size");
        /*$lists = Order::hasWhere("hasOneUser", $hasWhere)
            ->with(["hasOneUser" => ["hasOneParent", "hasOneAdmin" => ["hasOneParent"]], "hasOneOperator", "belongsToMode"])
            ->where($where)
            ->order("order_id DESC")
            ->paginate($pageSize, false, ['query'=>request()->param()]);*/
        $totalProfit = Order::hasWhere("hasOneUser", $hasWhere)->where($where)->sum("profit");
        $totalDeposit = Order::hasWhere("hasOneUser", $hasWhere)->where($where)->sum("deposit");
        $totalJiancang = Order::hasWhere("hasOneUser", $hasWhere)->where($where)->sum("jiancang_fee");
        $totalDefer = Order::hasWhere("hasOneUser", $hasWhere)->where($where)->sum("defer_total");
        $_lists = Order::hasWhere("hasOneUser", $hasWhere)
                    ->with(["hasOneUser", "hasOneOperator", "belongsToMode"])
                    ->where($where)
                    ->order("order_id DESC")
                    ->paginate($pageSize, false, ['query'=>request()->param()]);
        $lists = $_lists->toArray();
        $pages = $_lists->render();
        $hedging = [1 => '是', 0 => '否'];
        $state = [1 => '委托建仓', 2 => '平仓', 3 => '持仓', 4 => '委托平仓', 5 => '作废'];
        array_filter($lists['data'], function(&$item) use ($state, $hedging){
            $item['state_text'] = $state[$item['state']];
            $item['is_hedging_text'] = $hedging[$item['is_hedging']];
        });
        return compact("lists", "pages", "totalProfit", "totalDeposit", "totalJiancang", "totalDefer");
    }

    // 强制平仓列表
    public function pageForceOrders($filter = [], $pageSize = null)
    {
        $where = [];
        $hasWhere = [];
        $myUserIds = Admin::userIds();
        $myUserIds ? $where["stock_order.user_id"] = ["IN", $myUserIds] : null;
        $where['stock_order.state'] = 6;
        // 策略ID
        if(isset($filter['id']) && !empty($filter['id']) && is_numeric($filter['id'])){
            $where["stock_order.order_id"] = trim($filter['id']);
        }
        // 昵称
        if(isset($filter['nickname']) && !empty($filter['nickname'])){
            $_nickname = trim($filter['nickname']);
            $hasWhere["nickname"] = ["LIKE", "%{$_nickname}%"];
        }
        // 手机号
        if(isset($filter['mobile']) && !empty($filter['mobile'])){
            $hasWhere["mobile"] = trim($filter['mobile']);
        }
        // 股票代码
        if(isset($filter['code']) && !empty($filter['code'])){
            $where['stock_order.code'] = trim($filter['code']);
        }
        // 股票名称
        if(isset($filter['name']) && !empty($filter['name'])){
            $_name = trim($filter['name']);
            $where["stock_order.name"] = ["LIKE", "%{$_name}%"];
        }
        // 微圈
        /*if(isset($filter['ring']) && !empty($filter['ring'])){
            $_ring = trim($filter['ring']);
            $_where = ["username" => ["LIKE", "%{$_ring}%"]];
            $adminIds = Admin::where($_where)->column("admin_id");
            $hasWhere["admin_id"] = ["IN", $adminIds];
        }*/
        // 微会员
        /*if(isset($filter['member']) && !empty($filter['member'])){
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
            $where["stock_order.user_id"] = ["IN", $userIds];
        }*/
        // 经纪人
        /*if(isset($filter['manager']) && !empty($filter['manager'])){
            $_manager = trim($filter['manager']);
            $_where = ["username" => ["LIKE", "%{$_manager}%"]];
            $managerUserIds = User::where($_where)->column("user_id") ? : [-1];
            $hasWhere["parent_id"] = ["IN", $managerUserIds];
        }*/
        // 提交时间
        if(isset($filter['create_begin']) || isset($filter['create_end'])){
            if(!empty($filter['create_begin']) && !empty($filter['create_end'])){
                $_start = strtotime($filter['create_begin']);
                $_end = strtotime($filter['create_end']);
                $where['stock_order.create_at'] = ["BETWEEN", [$_start, $_end]];
            }elseif(!empty($filter['create_begin'])){
                $_start = strtotime($filter['create_begin']);
                $where['stock_order.create_at'] = ["EGT", $_start];
            }elseif(!empty($filter['create_end'])){
                $_end = strtotime($filter['create_end']);
                $where['stock_order.create_at'] = ["ELT", $_end];
            }
        }
        // 平仓类型
        if(isset($filter['force_type']) && !empty($filter['force_type'])){
            $hasWhere["stock_order.force_type"] = $filter['force_type'];
        }
        $pageSize = $pageSize ? : config("page_size");
        /*$lists = Order::hasWhere("hasOneUser", $hasWhere)
                    ->with(["hasOneUser" => ["hasOneParent", "hasOneAdmin" => ["hasOneParent"]]])
                    ->where($where)
                    ->order("order_id DESC")
                    ->paginate($pageSize, false, ['query'=>request()->param()]);*/
        $lists = Order::hasWhere("hasOneUser", $hasWhere)
            ->with(["hasOneUser", "belongsToMode"])
            ->where($where)
            ->order("order_id DESC")
            ->paginate($pageSize, false, ['query'=>request()->param()]);
        $records = $lists->toArray();
        $forceType = [1 => '爆仓', 2 => '止盈止损', 3 => '非自动递延', 4 => '余额不足'];
        array_filter($records['data'], function(&$item) use ($forceType){
            $item['force_type_text'] = $forceType[$item['force_type']];
        });
        return ["lists" => $records, "pages" => $lists->render()];
    }

    // 订单详情（包括关联用户数据）
    public function orderIncUserById($orderId, $state = null)
    {
        $myUserIds = Admin::userIds();
        $where = ["order_id" => $orderId];
        $myUserIds ? $where["user_id"] = ["IN", $myUserIds] : null;
        is_null($state) ? null : $where["state"] = is_array($state) ? ["IN", $state] : $state;
        $order = Order::with(["hasOneUser" => ["hasOneParent", "hasOneAdmin" => ["hasOneParent"]], "hasOneOperator", "belongsToMode"])
                    ->where($where)
                    ->find();
        return $order ? $order->toArray() : [];
    }

    // 订单本身数据（不包括关联数据）
    public function orderById($orderId, $state = null)
    {
        $myUserIds = Admin::userIds();
        $where = ["order_id" => $orderId];
        $myUserIds ? $where["user_id"] = ["IN", $myUserIds] : null;
        is_null($state) ? null : $where["state"] = is_array($state) ? ["IN", $state] : $state;
        $order = Order::where($where)->find();
        return $order ? $order->toArray() : [];
    }

    public function orderIncRecordById($orderId, $state = null)
    {
        $orders = [];
        $myUserIds = Admin::userIds();
        $where = ["order_id" => $orderId];
        $myUserIds ? $where["user_id"] = ["IN", $myUserIds] : null;
        is_null($state) ? null : $where["state"] = is_array($state) ? ["IN", $state] : $state;
        $_order = Order::with(
                        [
                            "hasManyNiurenRecord" => ["belongsToNiuren"],
                            "hasManyManagerRecord" => ["belongsToManager"],
                            "hasManyProxyRecord" => ["belongsToAdmin" => ["hasOneRole"]],
                        ]
                    )
                    ->where($where)
                    ->find();
        if($_order){
            $orders = $_order->toArray();
            $type = [0 => "跟单收入", 1 => "建仓费分成", 2 => "递延费分成"];
            array_filter($orders['has_many_niuren_record'], function(&$item) use ($type){
                $item['type_text'] = $type[$item['type']];
            });
            $type = [0 => "收益分成", 1 => "建仓费分成", 2 => "递延费分成"];
            array_filter($orders['has_many_manager_record'], function(&$item) use ($type){
                $item['type_text'] = $type[$item['type']];
            });
            array_filter($orders['has_many_proxy_record'], function(&$item) use ($type){
                $item['type_text'] = $type[$item['type']];
            });
        }
        return $orders;
    }

    public function updateOrder($data)
    {
        return Order::update($data);
    }

    public function buyFail($orderId)
    {
        Db::startTrans();
        try{
            $order = Order::find($orderId)->toArray();
            $data = [
                "order_id" => $order["order_id"],
                "state" => 5
            ];
            Order::update($data);
            // 用户资金
            $user = User::find($order['user_id']);
            $user->setInc("account", $order['deposit']);
            // 冻结资金
            $user->setDec("blocked_account", $order['deposit']);
            // 资金明细(保证金)
            $rData = [
                "type" => 4,
                "amount" => $order['deposit'],
				"account" => $user->account,
                "remark" => json_encode(['orderId' => $order["order_id"]]),
                "direction" => 1
            ];
            $user->hasManyRecord()->save($rData);
            // 资金明细(建仓费)
			$user->setInc("account", $order['jiancang_fee']);
            $rData = [
                "type" => 0,
                "amount" => $order['jiancang_fee'],
				"account" => $user->account,
                "remark" => json_encode(['orderId' => $order["order_id"]]),
                "direction" => 1
            ];
            $user->hasManyRecord()->save($rData);
            Db::commit();
            return true;
        } catch (\Exception $e){
            Db::rollback();
            return false;
        }
    }

    public function sellOk($orderId)
    {
        Db::startTrans();
        try{
            $order = Order::with("belongsToMode")->find($orderId)->toArray();
            $data = [
                "order_id" => $order["order_id"],
                "state" => 2,
                "update_by" => isLogin()
            ];
            Order::update($data);
            if($order["profit"] > 0){
                // 盈利
                $bonus_rate = isset($order['belongs_to_mode']['point']) ? $order['belongs_to_mode']['point'] : 0;
                $bonus = round($order["profit"] * (1 - $bonus_rate / 100), 2);
                // 用户资金
                $user = User::find($order['user_id']);
                $user->setInc("account", $order['deposit']);
                // 冻结资金
                $user->setDec("blocked_account", $order['deposit']);
                // 资金明细(保证金)
                $rData = [
                    "type" => 4,
                    "amount" => $order['deposit'],
					"account" => $user->account,
                    "remark" => json_encode(['orderId' => $order["order_id"]]),
                    "direction" => 1
                ];
                $user->hasManyRecord()->save($rData);
                // 资金明细(分红)
				$user->setInc("account", $bonus);
                $rData = [
                    "type" => 7,
                    "amount" => $bonus,
					"account" => $user->account,
                    "remark" => json_encode(['orderId' => $order["order_id"]]),
                    "direction" => 1
                ];
                $user->hasManyRecord()->save($rData);
            }else{
                // 亏损
                // 用户资金
                $user = User::find($order['user_id']);
                $user->setInc("account", $order['deposit'] + $order["profit"]);
                // 冻结资金
                $user->setDec("blocked_account", $order['deposit']);
                // 资金明细(保证金)
                $rData = [
                    "type" => 4,
                    "amount" => $order['deposit'] + $order["profit"],
					"account" => $user->account,
                    "remark" => json_encode(['orderId' => $order["order_id"]]),
                    "direction" => 1
                ];
                $user->hasManyRecord()->save($rData);
            }
            Db::commit();
            return true;
        } catch (\Exception $e){
            Db::rollback();
            return false;
        }
    }

    // 强制平仓, $sellPrice-平仓价
    public function forceSell($orderId, $sellPrice)
    {
        Db::startTrans();
        try{
            $order = Order::with("belongsToMode")->find($orderId)->toArray();
            // 订单更改
            $profit = ($sellPrice - $order['price']) * $order['sell_hand']; //实际盈亏
            $data = [
                "order_id" => $orderId,
                "sell_price" => $sellPrice,
                "sell_deposit" => $sellPrice * $order['sell_hand'],
                "profit" => $profit,
                "state" => 2,
                "update_by" => isLogin(),
                //'update_at' => time(),
            ];
            Order::update($data);
            // 分成
            if($profit > 0){
                // 盈利
                $bonus_rate = isset($order['belongs_to_mode']['point']) ? $order['belongs_to_mode']['point'] : 0;
                $bonus = round($profit * (1 - $bonus_rate / 100), 2);
                // 用户资金
                $user = User::find($order['user_id']);
                $user->setInc("account", $order['deposit']);
                // 冻结资金
                $user->setDec("blocked_account", $order['deposit']);
                // 资金明细(保证金)
                $rData = [
                    "type" => 4,
                    "amount" => $order['deposit'],
					"account" => $user->account,
                    "remark" => json_encode(['orderId' => $order["order_id"]]),
                    "direction" => 1
                ];
                $user->hasManyRecord()->save($rData);
                // 资金明细(分红)
				$user->setInc("account", $bonus);
                $rData = [
                    "type" => 7,
                    "amount" => $bonus,
					"account" => $user->account,
                    "remark" => json_encode(['orderId' => $order["order_id"]]),
                    "direction" => 1
                ];
                $user->hasManyRecord()->save($rData);
            }else{
                // 亏损
                // 用户资金
                $user = User::find($order['user_id']);
                $account = $profit + $order['deposit'] > 0 ? $profit + $order['deposit'] : 0; // 爆仓=>最多扣除保证金
                $user->setInc("account", $account);
                // 冻结资金
                $user->setDec("blocked_account", $order['deposit']);
                // 资金明细(保证金)
                if($account > 0){
                    $rData = [
                        "type" => 4,
                        "amount" => $account,
						"account" => $user->account,
                        "remark" => json_encode(['orderId' => $order["order_id"]]),
                        "direction" => 1
                    ];
                    $user->hasManyRecord()->save($rData);
                }
            }
            Db::commit();
            return true;
        }catch(\Exception $e){
            Db::rollback();
            return false;
        }
    }

    // 送股
    public function orderGive($data)
    {
        Db::startTrans();
        try{
            Order::update($data);
            // 操作明细
            $rData = [
                "act" => 0, //动作；0-转送股，1-穿仓价调整，2-转为持仓
                "ext" => json_encode($data), // 返点比例
            ];
            Order::find($data['order_id'])->hasManyAction()->save($rData);
            Db::commit();
            return true;
        }catch(\Exception $e){
            Db::rollback();
            return false;
        }
    }

    // 穿仓
    public function orderWare($orderId, $price)
    {
        Db::startTrans();
        try{
            $order = Order::find($orderId);
            $data = [
                "order_id" => $orderId,
                "sell_price" => $price,
                "sell_deposit" => $order->sell_hand * $price,
                "profit" => ($price - $order->price) * $order->sell_hand,
            ];
            Order::update($data);
            // 操作明细
            $rData = [
                "act" => 1, //动作；0-转送股，1-穿仓价调整，2-转为持仓
                "ext" => json_encode(['sell_price' => $price]),
            ];
            Order::find($orderId)->hasManyAction()->save($rData);
            Db::commit();
            return true;
        }catch(\Exception $e){
            Db::rollback();
            return false;
        }
    }

    // 转为持仓
    public function orderToPosition($orderId)
    {
        Db::startTrans();
        try{
            $data = [
                "order_id"  => $orderId,
                "sell_price" => 0,
                "sell_hand" => 0,
                "sell_deposit" => 0,
                "profit"    => 0,
                "state"     => 3, //状态，1委托建仓，2抛出，3持仓，4委托平仓，5作废，6-强制平仓
                "force_type" => 0 //强制平仓类型；1-爆仓，2-到达止盈止损，3-非自动递延，4-递延费无法扣除
            ];
            Order::update($data);
            // 操作明细
            Order::find($orderId)->hasManyAction()->save(["act" => 2]);
            Db::commit();
            return true;
        }catch(\Exception $e){
            Db::rollback();
            return false;
        }
    }

    public function getAllBy($where=[])
    {
        $map = [];
        if(!empty($where) && is_array($where))
        {
            foreach($where as $k => $v)
            {
                $map[$k] = $v;
            }
        }
        $data = Order::where($map)->select();
        return collection($data)->toArray();
    }
    public function getCodeBy($where=[])
    {
        $map = [];
        if(!empty($where) && is_array($where))
        {
            foreach($where as $k => $v)
            {
                $map[$k] = $v;
            }
        }
        return Order::where($map)->column('code');
    }
    public function positionOrders($filter = [])
    {
        $where = [];
        $hasWhere = [];
        $myUserIds = Admin::userIds();
        $myUserIds ? $where["stock_order.user_id"] = ["IN", $myUserIds] : null;
        $where['stock_order.state'] = 3;
        // 策略ID
        if(isset($filter['id']) && !empty($filter['id']) && is_numeric($filter['id'])){
            $where["stock_order.order_id"] = trim($filter['id']);
        }
        // 昵称
        if(isset($filter['nickname']) && !empty($filter['nickname'])){
            $_nickname = trim($filter['nickname']);
            $hasWhere["nickname"] = ["LIKE", "%{$_nickname}%"];
        }
        // 手机号
        if(isset($filter['mobile']) && !empty($filter['mobile'])){
            $hasWhere["mobile"] = trim($filter['mobile']);
        }
        // 股票代码
        if(isset($filter['code']) && !empty($filter['code'])){
            $where['stock_order.code'] = trim($filter['code']);
        }
        // 股票名称
        if(isset($filter['name']) && !empty($filter['name'])){
            $_name = trim($filter['name']);
            $where["stock_order.name"] = ["LIKE", "%{$_name}%"];
        }
        // 股票名称
        if(isset($filter['name']) && !empty($filter['name'])){
            $_name = trim($filter['name']);
            $where["stock_order.name"] = ["LIKE", "%{$_name}%"];
        }
        // 结算中心
        if(isset($filter['settle']) && !empty($filter['settle'])){
            $_where = [
                "username" => trim($filter['settle']),
                "role" => Admin::SETTLE_ROLE_ID
            ];
            $settleId = Admin::where($_where)->value("admin_id");
            $ringIds = Admin::childrenAdminIds($settleId);
            $hasWhere["admin_id"] = ["IN", $ringIds];
        }
        // 运营中心
        if(isset($filter['operate']) && !empty($filter['operate'])){
            $_where = [
                "username" => trim($filter['operate']),
                "role" => Admin::OPERATE_ROLE_ID
            ];
            $operateId = Admin::where($_where)->value("admin_id");
            $tempRingIds = Admin::childrenAdminIds($operateId);
            $ringIds = isset($ringIds) ? array_intersect($ringIds, $tempRingIds) : $tempRingIds;
            $hasWhere["admin_id"] = ["IN", $ringIds];
        }
        // 微会员
        if(isset($filter['member']) && !empty($filter['member'])){
            $_where = [
                "username" => trim($filter['member']),
                "role" => Admin::MEMBER_ROLE_ID
            ];
            $memberId = Admin::where($_where)->value("admin_id");
            $tempRingIds = Admin::childrenAdminIds($memberId);
            $ringIds = isset($ringIds) ? array_intersect($ringIds, $tempRingIds) : $tempRingIds;
            $hasWhere["admin_id"] = ["IN", $ringIds];
        }
        // 微圈
        if(isset($filter['ring']) && !empty($filter['ring'])){
            $_where = [
                "username" => trim($filter['ring']),
                "role" => Admin::RING_ROLE_ID
            ];
            $tempRingIds = Admin::where($_where)->column("admin_id");
            $ringIds = isset($ringIds) ? array_intersect($ringIds, $tempRingIds) : $tempRingIds;
            $hasWhere["admin_id"] = ["IN", $ringIds];
        }
        // 经纪人
        /*if(isset($filter['manager']) && !empty($filter['manager'])){
            $_manager = trim($filter['manager']);
            $_where = ["username" => ["LIKE", "%{$_manager}%"]];
            $managerUserIds = User::where($_where)->column("user_id") ? : [-1];
            $hasWhere["parent_id"] = ["IN", $managerUserIds];
        }*/
        // 提交时间
        if(isset($filter['create_begin']) || isset($filter['create_end'])){
            if(!empty($filter['create_begin']) && !empty($filter['create_end'])){
                $_start = strtotime($filter['create_begin']);
                $_end = strtotime($filter['create_end']);
                $where['stock_order.create_at'] = ["BETWEEN", [$_start, $_end]];
            }elseif(!empty($filter['create_begin'])){
                $_start = strtotime($filter['create_begin']);
                $where['stock_order.create_at'] = ["EGT", $_start];
            }elseif(!empty($filter['create_end'])){
                $_end = strtotime($filter['create_end']);
                $where['stock_order.create_at'] = ["ELT", $_end];
            }
        }
        // 是否对冲
        /*if(isset($filter['is_hedging']) && is_numeric($filter['is_hedging'])){
            $hasWhere["stock_order.is_hedging"] = $filter['is_hedging'];
        }*/
        /*$lists = Order::hasWhere("hasOneUser", $hasWhere)
            ->with(["hasOneUser" => ["hasOneParent", "hasOneAdmin" => ["hasOneParent"]], "hasOneOperator", "belongsToMode"])
            ->where($where)
            ->order("order_id DESC")
            ->paginate($pageSize, false, ['query'=>request()->param()]);*/
        $totalProfit = Order::hasWhere("hasOneUser", $hasWhere)->where($where)->sum("profit");
        $totalDeposit = Order::hasWhere("hasOneUser", $hasWhere)->where($where)->sum("deposit");
        $totalJiancang = Order::hasWhere("hasOneUser", $hasWhere)->where($where)->sum("jiancang_fee");
        $totalDefer = Order::hasWhere("hasOneUser", $hasWhere)->where($where)->sum("defer_total");
        $_lists = Order::hasWhere("hasOneUser", $hasWhere)
            ->with(["hasOneUser", "hasOneOperator", "belongsToMode"])
            ->where($where)
            ->order("order_id DESC")->select();
//            ->paginate(100, false, ['query'=>request()->param()]);
        $lists = collection($_lists)->toArray();

        return compact("lists", "totalProfit", "totalDeposit", "totalJiancang", "totalDefer");
    }
    public function historyOrders($filter = [])
    {
        $where = [];
        $hasWhere = [];
        $myUserIds = Admin::userIds();
        $myUserIds ? $where["stock_order.user_id"] = ["IN", $myUserIds] : null;
        $where['stock_order.state'] = 3;
        // 策略ID
        if(isset($filter['id']) && !empty($filter['id']) && is_numeric($filter['id'])){
            $where["stock_order.order_id"] = trim($filter['id']);
        }
        // 昵称
        if(isset($filter['nickname']) && !empty($filter['nickname'])){
            $_nickname = trim($filter['nickname']);
            $hasWhere["nickname"] = ["LIKE", "%{$_nickname}%"];
        }
        // 手机号
        if(isset($filter['mobile']) && !empty($filter['mobile'])){
            $hasWhere["mobile"] = trim($filter['mobile']);
        }
        // 股票代码
        if(isset($filter['code']) && !empty($filter['code'])){
            $where['stock_order.code'] = trim($filter['code']);
        }
        // 股票名称
        if(isset($filter['name']) && !empty($filter['name'])){
            $_name = trim($filter['name']);
            $where["stock_order.name"] = ["LIKE", "%{$_name}%"];
        }
        // 股票名称
        if(isset($filter['name']) && !empty($filter['name'])){
            $_name = trim($filter['name']);
            $where["stock_order.name"] = ["LIKE", "%{$_name}%"];
        }
        // 结算中心
        if(isset($filter['settle']) && !empty($filter['settle'])){
            $_where = [
                "username" => trim($filter['settle']),
                "role" => Admin::SETTLE_ROLE_ID
            ];
            $settleId = Admin::where($_where)->value("admin_id");
            $ringIds = Admin::childrenAdminIds($settleId);
            $hasWhere["admin_id"] = ["IN", $ringIds];
        }
        // 运营中心
        if(isset($filter['operate']) && !empty($filter['operate'])){
            $_where = [
                "username" => trim($filter['operate']),
                "role" => Admin::OPERATE_ROLE_ID
            ];
            $operateId = Admin::where($_where)->value("admin_id");
            $tempRingIds = Admin::childrenAdminIds($operateId);
            $ringIds = isset($ringIds) ? array_intersect($ringIds, $tempRingIds) : $tempRingIds;
            $hasWhere["admin_id"] = ["IN", $ringIds];
        }
        // 微会员
        if(isset($filter['member']) && !empty($filter['member'])){
            $_where = [
                "username" => trim($filter['member']),
                "role" => Admin::MEMBER_ROLE_ID
            ];
            $memberId = Admin::where($_where)->value("admin_id");
            $tempRingIds = Admin::childrenAdminIds($memberId);
            $ringIds = isset($ringIds) ? array_intersect($ringIds, $tempRingIds) : $tempRingIds;
            $hasWhere["admin_id"] = ["IN", $ringIds];
        }
        // 微圈
        if(isset($filter['ring']) && !empty($filter['ring'])){
            $_where = [
                "username" => trim($filter['ring']),
                "role" => Admin::RING_ROLE_ID
            ];
            $tempRingIds = Admin::where($_where)->column("admin_id");
            $ringIds = isset($ringIds) ? array_intersect($ringIds, $tempRingIds) : $tempRingIds;
            $hasWhere["admin_id"] = ["IN", $ringIds];
        }
        // 经纪人
        /*if(isset($filter['manager']) && !empty($filter['manager'])){
            $_manager = trim($filter['manager']);
            $_where = ["username" => ["LIKE", "%{$_manager}%"]];
            $managerUserIds = User::where($_where)->column("user_id") ? : [-1];
            $hasWhere["parent_id"] = ["IN", $managerUserIds];
        }*/
        // 提交时间
        if(isset($filter['create_begin']) || isset($filter['create_end'])){
            if(!empty($filter['create_begin']) && !empty($filter['create_end'])){
                $_start = strtotime($filter['create_begin']);
                $_end = strtotime($filter['create_end']);
                $where['stock_order.create_at'] = ["BETWEEN", [$_start, $_end]];
            }elseif(!empty($filter['create_begin'])){
                $_start = strtotime($filter['create_begin']);
                $where['stock_order.create_at'] = ["EGT", $_start];
            }elseif(!empty($filter['create_end'])){
                $_end = strtotime($filter['create_end']);
                $where['stock_order.create_at'] = ["ELT", $_end];
            }
        }
        // 是否对冲
        /*if(isset($filter['is_hedging']) && is_numeric($filter['is_hedging'])){
            $hasWhere["stock_order.is_hedging"] = $filter['is_hedging'];
        }*/
        /*$lists = Order::hasWhere("hasOneUser", $hasWhere)
            ->with(["hasOneUser" => ["hasOneParent", "hasOneAdmin" => ["hasOneParent"]], "hasOneOperator", "belongsToMode"])
            ->where($where)
            ->order("order_id DESC")
            ->paginate($pageSize, false, ['query'=>request()->param()]);*/
        $totalProfit = Order::hasWhere("hasOneUser", $hasWhere)->where($where)->sum("profit");
        $totalDeposit = Order::hasWhere("hasOneUser", $hasWhere)->where($where)->sum("deposit");
        $totalJiancang = Order::hasWhere("hasOneUser", $hasWhere)->where($where)->sum("jiancang_fee");
        $totalDefer = Order::hasWhere("hasOneUser", $hasWhere)->where($where)->sum("defer_total");
        $_lists = Order::hasWhere("hasOneUser", $hasWhere)
            ->with(["hasOneUser", "hasOneOperator", "belongsToMode"])
            ->where($where)
            ->order("order_id DESC")
            ->select();
        $lists = collection($_lists)->toArray();
        return compact("lists", "totalProfit", "totalDeposit", "totalJiancang", "totalDefer");
    }
}