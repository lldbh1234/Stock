<?php
namespace app\admin\logic;

use app\admin\model\Admin;
use app\admin\model\Role;
use app\admin\model\AdminWithdraw;
use app\common\payment\authLlpay;
use think\Db;

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

    public function allTeamRoles()
    {
        $adminRoles = [
            Admin::SETTLE_ROLE_ID,
            Admin::OPERATE_ROLE_ID,
            Admin::MEMBER_ROLE_ID,
            Admin::RING_ROLE_ID
        ];
        return Role::where(["id" => ["IN", $adminRoles]])->column("name", "id");
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
                // $length = rand(4, 6);
                $length = 6;
                $code = randomString($length, true);
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

    public function adminIncRole($id)
    {
        $admin = Admin::with("hasOneRole")->find($id);
        return $admin ? $admin->toArray() : [];
    }

    public function adminIncCard($id)
    {
        $admin = Admin::with(["hasOneCard" => ["hasOneProvince", "hasOneCity"]])->find($id);
        return $admin ? $admin->toArray() : [];
    }

    // 绑定银行卡
    public function saveAdminCard($adminId, $data)
    {
        Db::startTrans();
        try{
            $user = Admin::find($adminId);
            if($user->hasOneCard){
                $user->hasOneCard->save($data);
            }else{
                $user->hasOneCard()->save($data);
            }
            /*$llpayUserId = "PROXY{$adminId}";
            $llpayBanks = (new authLlpay())->bankBindList($llpayUserId);
            if($llpayBanks){
                $newCardNo = substr($data['bank_card'], -4);
                $cardNos = array_column($llpayBanks, "card_no");
                if(!in_array($newCardNo, $cardNos)){
                    // 新卡
                    foreach ($llpayBanks as $item){
                        $noAgree = $item['no_agree'];
                        $temp = (new authLlpay())->unbindBank($llpayUserId, $noAgree);
                        if(!$temp){
                            Db::rollback();
                            return false;
                        }
                    }
                }
            }*/
            Db::commit();
            return true;
        } catch (\Exception $e){
            Db::rollback();
            return false;
        }
    }

    public function createAdminWithdraw($adminId, $money, $remark)
    {
        $admin = Admin::find($adminId);
        if($admin){
            Db::startTrans();
            try{
                $admin->setDec("total_fee", $money);
                $rate = cf('proxy_withdraw_poundage', config('proxy_withdraw_poundage'));
                $poundage = round($money * $rate / 100, 2);
                $data = [
                    "amount"    => $money,
                    "actual"    => $money - $poundage,
                    "poundage"  => $poundage,
                    "out_sn"    => createStrategySn(),
                    "remark"    => json_encode($remark),
                ];
                $res = $admin->hasManyWithdraw()->save($data);
                $pk = model("AdminWithdraw")->getPk();
                // 提交事务
                Db::commit();
                return $res->$pk;
            } catch (\Exception $e) {
                // 回滚事务
                dump($e->getMessage());
                Db::rollback();
                return 0;
            }
        }
        return 0;
    }

    public function pageAdminWithdraws($adminId, $filter = [], $pageSize = null)
    {
        $where = ["admin_id" => $adminId];
        if(isset($filter['trade_no']) && !empty($filter['trade_no'])){ //订单号
            $where['out_sn'] = $filter['trade_no'];
        }
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
        if(isset($filter['state']) && is_numeric($filter['state']) && in_array($filter['state'], [0, 1, 2])){//状态
            $where['state'] = $filter['state'];
        }
        $pageSize = $pageSize ? : config("page_size");
        $lists = AdminWithdraw::where($where)->order("id DESC")->paginate($pageSize, false, ['query'=>request()->param()]);
        return ["lists" => $lists->toArray(), "pages" => $lists->render()];
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

    //是否代理商
    public function isProxy($roleId){
        $proxyRoleIds = [
            Admin::SETTLE_ROLE_ID,
            Admin::OPERATE_ROLE_ID,
            Admin::MEMBER_ROLE_ID,
            Admin::RING_ROLE_ID
        ];
        return in_array($roleId, $proxyRoleIds);
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
            $_where = [
                "username" => ["LIKE", "%{$filter['settle']}%"],
                "role" => Admin::SETTLE_ROLE_ID
            ];
            $parents = Admin::where($_where)->column("admin_id");
            $where["pid"] = ["IN", $parents];
        }
        // 上级运营中心
        if(isset($filter['operate']) && !empty($filter['operate'])){
            $_where = [
                "username" => ["LIKE", "%{$filter['operate']}%"],
                "role" => Admin::OPERATE_ROLE_ID
            ];
            $parents = Admin::where($_where)->column("admin_id");
            $where["pid"] = ["IN", $parents];
        }
        // 上级微会员
        if(isset($filter['member']) && !empty($filter['member'])){
            $_where = [
                "username" => ["LIKE", "%{$filter['member']}%"],
                "role" => Admin::MEMBER_ROLE_ID
            ];
            $parents = Admin::where($_where)->column("admin_id");
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

    public function teamAdminsByRole($role="settle")
    {
        $where = Admin::manager();
        $where['status'] = 0;
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
        $admins = Admin::where($where)->select();
        return $admins ? collection($admins)->toArray() : [];
    }

    public function memberWechat($id)
    {
        $wechat = Admin::with("hasOneWechat")->field("password", true)->find($id);
        return $wechat ? $wechat->toArray() : [];
    }

    public function saveRingWechat($adminId, $data)
    {
        $admin = Admin::get($adminId);
        if($admin->hasOneWechat){
            return $admin->hasOneWechat->save($data);
        }else{
            return $admin->hasOneWechat()->save($data);
        }
    }

    public function depositRecharge($admin_id, $money)
    {
        return Admin::where(['admin_id' => $admin_id])->setInc('deposit', $money);
    }
}