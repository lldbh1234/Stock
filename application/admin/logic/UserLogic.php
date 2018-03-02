<?php
namespace app\admin\logic;

use app\admin\model\User;
use app\admin\model\Admin;
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
        //推荐人-微圈-微会员
        $lists = User::with(["hasOneParent", "hasOneAdmin", "hasOneAdmin.hasOneParent"])
            ->where($where)
            ->paginate($pageSize, false, ['query'=>request()->param()]);
        return ["lists" => $lists->toArray(), "pages" => $lists->render()];
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

}