<?php
namespace app\admin\logic;

use app\admin\model\Admin;
use app\admin\model\Role;

class AdminLogic
{
    public function pageRoleLists($pageSize = null)
    {
        $pageSize = $pageSize ? : config("page_size");
        $lists = Role::paginate($pageSize);
        return ["lists" => $lists->toArray(), "pages" => $lists->render()];
    }

    public function allEnableRoles()
    {
        $lists = Role::where(['show' => 1])->select();
        return $lists ? collection($lists)->toArray() : [];
    }

    // 不包括组织架构的角色列表
    public function allAdminRoles()
    {
        $adminRoles = [
            Admin::ADMIN_ROLE_ID,
            Admin::SERVICE_ROLE_ID,
            Admin::FINANCE_ROLE_ID,
        ];
        $where = [
            "id"    => ["IN", $adminRoles],
            "show"  => 1
        ];
        $lists = Role::where($where)->select();
        return $lists ? collection($lists)->toArray() : [];
    }

    public function allRoles()
    {
        $lists = Role::select();
        return $lists ? collection($lists)->toArray() : [];
    }

    public function roleCreate($data)
    {
        $res = Role::create($data);
        return $res ? $res->id : 0;
    }

    public function roleDelete($id)
    {
        $ids = is_array($id) ? implode(",", $id) : $id;
        return Role::destroy($ids);
    }

    public function roleById($id)
    {
        $role = Role::find($id);
        return $role ? $role->toArray() : [];
    }

    public function roleUpdate($data)
    {
        return Role::update($data);
    }

    public function pageAdminLists($filter = [], $pageSize = null)
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
        // 所属角色
        if(isset($filter['role']) && !empty($filter['role'])){
            $where["role"] = $filter['role'];
        }
        // 状态
        if(isset($filter['status']) && is_numeric($filter['status']) && in_array($filter['status'], [0,1])){
            $where["status"] = $filter['status'];
        }
        $pageSize = $pageSize ? : config("page_size");
        $lists = Admin::with("hasOneRole")->where($where)->paginate($pageSize, false, ['query'=>request()->param()]);
        return ["lists" => $lists->toArray(), "pages" => $lists->render()];
    }

    // 不包括组织架构的后台用户列表
    public function pageAdmins($filter = [], $pageSize = null)
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
        // 所属角色
        if(isset($filter['role']) && !empty($filter['role'])){
            $where["role"] = $filter['role'];
        }else{
            $adminRoles = [
                Admin::ADMIN_ROLE_ID,
                Admin::SERVICE_ROLE_ID,
                Admin::FINANCE_ROLE_ID,
            ];
            $where['role'] = ["IN", $adminRoles];
        }
        // 状态
        if(isset($filter['status']) && is_numeric($filter['status']) && in_array($filter['status'], [0,1])){
            $where["status"] = $filter['status'];
        }
        $pageSize = $pageSize ? : config("page_size");
        $lists = Admin::with("hasOneRole")->where($where)->paginate($pageSize, false, ['query'=>request()->param()]);
        return ["lists" => $lists->toArray(), "pages" => $lists->render()];
    }

    public function getAdminPid()
    {
        if(manager()['admin_id'] == Admin::ADMINISTRATOR_ID){
            return 0;
        }else{
            if(in_array(manager()['role'], [Admin::SERVICE_ROLE_ID, Admin::FINANCE_ROLE_ID])){
                return 0;
            }else{
                return manager()['admin_id'];
            }
        }
    }

    public function getAdminCode($roleId = null)
    {
        $allCodes = Admin::column("code");
        while (true){
            if($roleId == Admin::RING_ROLE_ID){
                // 微圈
                $code = randomString("8", true);
            }else{
                $code = randomString("8");
            }
            if(!in_array($code, $allCodes)){
                break;
            }
        }
        return $code;
    }

    public function adminCreate($data)
    {
        $res = Admin::create($data);
        $pk = model("Admin")->getPk();
        return $res ? $res->$pk : 0;
    }

    public function adminById($id)
    {
        $admin = Admin::find($id);
        return $admin ? $admin->toArray() : [];
    }

    public function adminUpdate($data)
    {
        return Admin::update($data);
    }

    public function adminDelete($id)
    {
        $ids = is_array($id) ? implode(",", $id) : $id;
        return Admin::destroy($ids);
    }

    public function pageTeamLists($role = "settle", $filter = [], $pageSize = null)
    {
        $where = Admin::manager();
        switch ($role){
            case "settle": //结算中心
                $where['role'] = Admin::SETTLE_ROLE_ID;
                break;
            case "operate": //运营中心
                $where['role'] = Admin::OPERATE_ROLE_ID;
                break;
            case "member": //微会员
                $where['role'] = Admin::MEMBER_ROLE_ID;
                break;
            case "ring": //微圈
                $where['role'] = Admin::RING_ROLE_ID;
                break;
        }
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
            $where["mobile"] = trim($filter['mobile']);
        }
        // 状态
        if(isset($filter['status']) && is_numeric($filter['status']) && in_array($filter['status'], [0,1])){
            $where["status"] = $filter['status'];
        }
        // 上级结算中心
        if(isset($filter['settle']) && !empty($filter['settle'])){
            $parents = Admin::where(["username" => ["LIKE", "%{$filter['settle']}%"]])->column("admin_id");
            $where["pid"] = ["IN", $parents];
        }
        $pageSize = $pageSize ? : config("page_size");
        $lists = Admin::with(
                    [
                        "hasOneParent" => function($query){
                            $query->field("password", true);
                        }
                    ])
                    ->field("password", true)
                    ->where($where)
                    ->paginate($pageSize, false, ['query'=>request()->param()]);
        return ["lists" => $lists->toArray(), "pages" => $lists->render()];
    }

    public function teamAdminById($id, $role="settle")
    {
        $where['admin_id'] = $id;
        switch ($role){
            case "settle": //结算中心
                $where['role'] = Admin::SETTLE_ROLE_ID;
                break;
            case "operate": //运营中心
                $where['role'] = Admin::OPERATE_ROLE_ID;
                break;
            case "member": //微会员
                $where['role'] = Admin::MEMBER_ROLE_ID;
                break;
            case "ring": //微圈
                $where['role'] = Admin::RING_ROLE_ID;
                break;
        }
        $admin = Admin::where($where)->find();
        return $admin ? $admin->toArray() : [];
    }
}