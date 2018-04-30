<?php
namespace app\admin\logic;

use app\admin\model\User;
use app\admin\model\Admin;
use app\admin\model\UserGive;
use app\admin\model\UserRecharge;
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
        $user = User::with(["hasOneParent", "hasOneAdmin" => ["hasOneParent" => ["hasOneParent" => ["hasOneParent"]]]])->where($where)->find($userId);
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

    public function getOne($id)
    {
        $data = User::where(['user_id' => $id])->find();
        return $data->toArray();
    }
    public function update($where=[])
    {
        return User::update($where);
    }
    public function setInc($data)
    {
        if(isset($data['user_id']) && isset($data['number']))
        {
            // 启动事务
            Db::startTrans();
            try{
                User::where(['user_id' => $data['user_id']])->setInc('account', $data['number']);
                UserGive::create([
                    'user_id'   => $data['user_id'],
                    'amount'    => $data['number'],
                    'create_at' => time(),
                    'create_by' => isLogin()
                ]);
                // 提交事务
                Db::commit();
                return true;
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                return false;
            }
        }
        return false;
    }

}