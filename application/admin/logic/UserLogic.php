<?php
namespace app\admin\logic;

use app\admin\model\Order;
use app\admin\model\User;
use app\admin\model\Admin;
use app\admin\model\UserGive;
use app\admin\model\UserRecharge;
use app\admin\model\UserRecord;
use app\admin\model\UserWithdraw;
use think\Db;

class UserLogic
{
    public function pageUserLists($filter = [], $pageSize = null)
    {
        $where = Admin::manager();
        // 登录名
        if(isset($filter['username']) && !empty($filter['username'])){
            $where["username"] = ["LIKE", "%{$filter['username']}%"];
        }
        // 昵称
        if(isset($filter['nickname']) && !empty($filter['nickname'])){
            $where["nickname"] = ["LIKE", "%{$filter['nickname']}%"];
        }
        // 手机号
        if(isset($filter['mobile']) && !empty($filter['mobile'])){
            $where["mobile"] = $filter['mobile'];
        }

        // 状态
        if(isset($filter['state']) && is_numeric($filter['state']) && in_array($filter['state'], [0,1])){
            $where["state"] = $filter['state'];
        }
        // 上级微会员
        if(isset($filter['admin_parent_username']) && !empty($filter['admin_parent_username'])){
            $_where = [
                "username" => ["LIKE", "%{$filter['admin_parent_username']}%"],
                "role" => Admin::MEMBER_ROLE_ID
            ];
            $memAdminIds = Admin::where($_where)->column("admin_id");
            //微会员下微圈
            $parents = Admin::where(["pid" => ['in', $memAdminIds]])->column("admin_id");
            $memAdminIds = array_merge($parents, $memAdminIds);
            $where["admin_id"] = ["IN", $memAdminIds];
        }

        //上级微圈
        if(isset($filter['admin_username']) && !empty($filter['admin_username'])){
            $_where = [
                "username" => ["LIKE", "%{$filter['admin_username']}%"],
                "role" => Admin::RING_ROLE_ID
            ];
            $parents = Admin::where($_where)->column("admin_id");

            $where["admin_id"] = ["IN", $parents];
            if(isset($memAdminIds)){
                $parents = array_intersect($parents, $memAdminIds);
                $where["admin_id"] = ["IN", $parents];
            }

        }

        if(isset($filter['parent_username']) && !empty($filter['parent_username'])){//推荐人
            $parent_ids = User::where(['username' => ["LIKE", "%{$filter['parent_username']}%"]])->column('user_id');
            $where['parent_id'] = ['IN', $parent_ids];
        }

        $pageSize = $pageSize ? : config("page_size");
        $totalAccount = User::with(["hasOneParent", "hasOneAdmin", "hasOneAdmin.hasOneParent"])->where($where)->sum("account");
        //推荐人-微圈-微会员
        $_lists = User::with(["hasOneParent", "hasOneAdmin", "hasOneAdmin.hasOneParent"])
                    ->where($where)
                    ->paginate($pageSize, false, ['query'=>request()->param()]);
        $lists = $_lists->toArray();
        $pages = $_lists->render();
        return compact("lists", "pages", "totalAccount");
    }

    public function userById($userId)
    {
        $user = User::find($userId);
        return $user ? $user->toArray() : [];
    }

    public function userIncFamily($userId)
    {
        $where = Admin::manager();
        $user = User::with(["hasOneParent", "hasOneCard", "hasOneAdmin" => ["hasOneParent" => ["hasOneParent" => ["hasOneParent"]]]])->where($where)->find($userId);
        return $user ? $user->toArray() : [];
    }

    public function pageUserRechargeByUserId($userId, $filter = [], $pageSize = null)
    {
        $myUserIds = Admin::userIds();
        if($myUserIds && !in_array($userId, $myUserIds)){
            return [];
        }else{
            $where = ["state" => 1, "user_id" => $userId];
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
            //$totalActual = UserRecharge::where($where)->sum("actual");
            //$totalPoundage = UserRecharge::where($where)->sum("poundage");
            $_lists = UserRecharge::where($where)
                        ->order("id DESC")
                        ->paginate($pageSize, false, ['query'=>request()->param()]);
            $lists = $_lists->toArray();
            $pages = $_lists->render();
            return compact("lists", "pages", "totalAmount", "totalActual", "totalPoundage");
        }
    }

    public function pageUserWithdrawByUserId($userId, $filter = [], $pageSize = null)
    {
        $myUserIds = Admin::userIds();
        if($myUserIds && !in_array($userId, $myUserIds)){
            return [];
        }else{
            $where = ["user_id" => $userId, "state" => ["IN", [0, 1, 2]]];
            if(isset($filter['state']) && is_numeric($filter['state']) && in_array($filter['state'], [0,1,2])){//状态
                $where['state'] = $filter['state'];
            }
            // 提现时间
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
            $totalAmount = UserWithdraw::where($where)->sum("amount");
            $totalActual = UserWithdraw::where($where)->sum("actual");
            $totalPoundage = UserWithdraw::where($where)->sum("poundage");
            //推荐人-微圈-微会员
            $_lists = UserWithdraw::where($where)
                    ->order("id DESC")
                    ->paginate($pageSize, false, ['query'=>request()->param()]);
            $lists = $_lists->toArray();
            $pages = $_lists->render();
            return compact("lists", "pages", "totalAmount", "totalActual", "totalPoundage");
        }
    }

    public function pageUserOrderByUserId($userId, $state, $filter = [], $pageSize = null)
    {
        $myUserIds = Admin::userIds();
        if($myUserIds && !in_array($userId, $myUserIds)){
            return [];
        }else{
            $where = ["user_id" => $userId, "state" => $state];
            // 卖出时间
            if(isset($filter['sell_begin']) || isset($filter['sell_end'])){
                if(!empty($filter['sell_begin']) && !empty($filter['sell_end'])){
                    $_start = strtotime($filter['sell_begin']);
                    $_end = strtotime($filter['sell_end']);
                    $where['update_at'] = ["BETWEEN", [$_start, $_end]];
                }elseif(!empty($filter['sell_begin'])){
                    $_start = strtotime($filter['sell_begin']);
                    $where['update_at'] = ["EGT", $_start];
                }elseif(!empty($filter['sell_end'])){
                    $_end = strtotime($filter['sell_end']);
                    $where['update_at'] = ["ELT", $_end];
                }
            }
            $pageSize = $pageSize ? : config("page_size");
            $totalProfit = Order::where($where)->sum("profit");
            $totalDeposit = Order::where($where)->sum("deposit");
            $totalJiancang = Order::where($where)->sum("jiancang_fee");
            $totalDefer = Order::where($where)->sum("defer_total");
            //推荐人-微圈-微会员
            $_lists = Order::with(["belongsToMode"])
                        ->where($where)
                        ->order(["update_at" => "DESC", "order_id" => "DESC"])
                        ->paginate($pageSize, false, ['query'=>request()->param()]);
            $lists = $_lists->toArray();
            $pages = $_lists->render();
            return compact("lists", "pages", "totalProfit", "totalDeposit", "totalJiancang", "totalDefer");
        }
    }

    public function userIncOrder($userId, $state = 1)
    {
        $user = User::with(["hasManyOrder" => function($query) use ($state){
                    $query->where(["state" => $state]);
                }])->find($userId);
        return $user ? $user->toArray() : [];
    }

    public function pageUserRecordByUserId($userId, $filter = [], $pageSize = null)
    {
        $myUserIds = Admin::userIds();
        if($myUserIds && !in_array($userId, $myUserIds)){
            return [];
        }else{
            $where = ["user_id" => $userId];
            // 产生时间
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
            $_lists = UserRecord::where($where)
                    ->order(["create_at" => "DESC", "id" => "DESC"])
                    ->paginate($pageSize, false, ['query'=>request()->param()]);
            $lists = $_lists->toArray();
            $pages = $_lists->render();
            return compact("lists", "pages");
        }
    }

    public function getOne($id)
    {
        $data = User::where(['user_id' => $id])->find();
        return $data->toArray();
    }

    public function update($where=[])
    {
        return User::update($where);
    }

    public function giveMoney($userId, $money, $remark = null)
    {
        // 启动事务
        Db::startTrans();
        try{
            $user = User::find($userId);
            // 余额增加
            $user->setInc('account', $money);
            // 赠金日志记录
            $_gData = [
                "user_id"   => $userId,
                "amount"    => $money,
                "remark"    => $remark,
                "create_at" => time(),
                "create_by" => isLogin()
            ];
            UserGive::create($_gData);
            // 用户资金明细
            $rData = [
                "type" => 11,
                "amount" => $money,
                "account" => $user->account,
                "direction" => 1
            ];
            $user->hasManyRecord()->save($rData);
            // 提交事务
            Db::commit();
            return true;
        } catch (\Exception $e) {
            // 回滚事务
            dump($e->getMessage());
            Db::rollback();
            return false;
        }
    }
}