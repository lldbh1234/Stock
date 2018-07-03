<?php
namespace app\admin\logic;

use app\admin\model\Admin;
use app\admin\model\AdminGive;
use app\admin\model\Role;
use app\admin\model\AdminWithdraw;
use app\common\payment\authLlpay;
use think\Db;

class AdminLogic
{
    protected $familyTree;
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
        $teamRoleIds = [
            Admin::SETTLE_ROLE_ID,
            Admin::OPERATE_ROLE_ID,
            Admin::MEMBER_ROLE_ID,
            Admin::RING_ROLE_ID
        ];
        $where = [
            "id"    => ["NOT IN", $teamRoleIds],
            "show"  => 1
        ];
        $lists = Role::where($where)->select();
        return $lists ? collection($lists)->toArray() : [];
    }

    // 页面列显示
    public function tableColumnShow()
    {
        return Admin::tableColumnShow();
    }

    public function proxyFamilyShow($adminId)
    {
        return Admin::proxyFamilyShow($adminId);
    }

    public function proxyFamily($adminId)
    {
        $show = Admin::proxyFamilyShow($adminId);
        if($show['ring'] == 1){
            $with = ["hasOneRole"];
        }elseif ($show['member'] == 1){
            // 微圈
            $with = ["hasOneRole", "hasOneParent" => ["hasOneParent" => ["hasOneParent"]]];
        }elseif ($show['operate'] == 1){
            // 微会员
            $with = ["hasOneRole", "hasOneParent" => ["hasOneParent"]];
        }elseif ($show['settle'] == 1){
            // 运营中心
            $with = ["hasOneRole", "hasOneParent"];
        }else{
            $with = ["hasOneRole"];
        }
        $proxy = Admin::with($with)->find($adminId);
        return $proxy ? $proxy->toArray() : [];
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
            $teamRoleIds = [
                0, //
                Admin::SETTLE_ROLE_ID,
                Admin::OPERATE_ROLE_ID,
                Admin::MEMBER_ROLE_ID,
                Admin::RING_ROLE_ID
            ];
            $where['role'] = ["NOT IN", $teamRoleIds];
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
            $teamRoleIds = [
                Admin::SETTLE_ROLE_ID,
                Admin::OPERATE_ROLE_ID,
                Admin::MEMBER_ROLE_ID,
                Admin::RING_ROLE_ID
            ];
            if(!in_array(manager()['role'], $teamRoleIds)){
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
                Db::rollback();
                return 0;
            }
        }
        return 0;
    }

    // 代理商今日提现数量
    public function proxyTodayWithdrawCount($adminId)
    {
        $todayBegin = strtotime(date("Y-m-d 00:00:00"));
        $todayEnd = strtotime(date("Y-m-d 23:59:59"));
        $where = [
            "admin_id"  => $adminId,
            "state"     => ["NEQ", -1],
            "create_at" => ["BETWEEN", [$todayBegin, $todayEnd]]
        ];
        return AdminWithdraw::where($where)->count();
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

    // 是否管理员
    public function isAdmin($adminId)
    {
        $managerAdminIds = [
            Admin::ADMINISTRATOR_ID,
            Admin::ROOT_ID
        ];
        return in_array($adminId, $managerAdminIds);
    }

    public function myPoint($adminId)
    {
        $proxyRoleIds = [
            Admin::SETTLE_ROLE_ID,
            Admin::OPERATE_ROLE_ID,
            Admin::MEMBER_ROLE_ID,
            Admin::RING_ROLE_ID
        ];
        $admins = Admin::where(["role" => ["IN", $proxyRoleIds]])->column("admin_id,pid,point,jiancang_point,defer_point");
        $this->familyTree = [];
        $this->_familyTree($admins, $adminId);
        if($this->familyTree){
            $jiangcang = 1;
            $defer = 1;
            $profit = 1;
            foreach ($this->familyTree as $val){
                $jiangcang = $jiangcang * $val['jiancang_point'] / 100;
                $defer = $defer * $val['defer_point'] / 100;
                $profit = $profit * $val['point'] / 100;
            }
            return [$jiangcang, $defer, $profit];
        }
        return [0, 0, 0];
    }

    private function _familyTree($admins, $admin_id, $field = "pid")
    {
        foreach ($admins as $key=>$val){
            if($val['admin_id'] == $admin_id){
                $this->familyTree[$admin_id] = $val;
                $this->_familyTree($admins, $val[$field], $field);
            }
        }
    }

    // 分页微会员列表
    public function pageMemberLists($filter = [], $pageSize = null)
    {
        $where = Admin::manager();
        $where['role'] = Admin::MEMBER_ROLE_ID;
        // 登录名
        if(isset($filter['username']) && !empty($filter['username'])){
            $where["username"] = trim($filter['username']);
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
        // 结算中心
        if(isset($filter['settle']) && !empty($filter['settle'])){
            $_where = [
                "username" => trim($filter['settle']),
                "role" => Admin::SETTLE_ROLE_ID
            ];
            $settle = Admin::where($_where)->find();
            $operates = $settle ? $settle->hasManyChildren()->column("admin_id") : [];
            $where["pid"] = ["IN", $operates];
        }
        // 运营中心
        if(isset($filter['operate']) && !empty($filter['operate'])){
            $_where = [
                "username" => trim($filter['operate']),
                "role" => Admin::OPERATE_ROLE_ID
            ];
            $parents = Admin::where($_where)->column("admin_id");
            $parents = isset($operates) ? array_intersect($operates, $parents) : $parents;
            $where["pid"] = ["IN", $parents];
        }
        $totalFee = Admin::where($where)->sum("total_fee");
        $totalIncome = Admin::where($where)->sum("total_income");
        $pageSize = $pageSize ? : config("page_size");
        $tableCols = Admin::tableColumnShow();
        if($tableCols['settle'] == 1){
            $with = ["hasOneParent" => ["hasOneParent"]];
        }else{
            $with = ["hasOneParent"];
        }
        $_lists = Admin::with($with)
            ->withSum(
                [
                    "hasManyWithdraw" => function($_query){
                        $_query->where(["state" => ["NEQ", -1]]);
                    }
                ], "amount"
            )
            ->field("password", true)
            ->where($where)
            ->order(["status" => "ASC", "admin_id" => "ASC"])
            ->paginate($pageSize, false, ['query'=>request()->param()]);
        $lists = $_lists->toArray();
        $pages = $_lists->render();
        return compact("lists", "pages", "totalFee", "totalIncome");
    }

    // 分页微圈列表
    public function pageRingLists($filter = [], $pageSize = null)
    {
        $where = Admin::manager();
        $where['role'] = Admin::RING_ROLE_ID;
        // 登录名
        if(isset($filter['username']) && !empty($filter['username'])){
            $where["username"] = trim($filter['username']);
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
        // 机构码
        if(isset($filter['code']) && !empty($filter['code'])){
            $where["code"] = trim($filter['code']);
        }
        // 结算中心
        if(isset($filter['settle']) && !empty($filter['settle'])){
            $_where = [
                "username" => trim($filter['settle']),
                "role" => Admin::SETTLE_ROLE_ID
            ];
            $settle = Admin::where($_where)->find();
            $operates = $settle ? $settle->hasManyChildren()->column("admin_id") : [];
            $members = Admin::where(["pid" => ["IN", $operates]])->column("admin_id");
            $where["pid"] = ["IN", $members];
        }
        // 运营中心
        if(isset($filter['operate']) && !empty($filter['operate'])){
            $_where = [
                "username" => trim($filter['operate']),
                "role" => Admin::OPERATE_ROLE_ID
            ];
            $operate = Admin::where($_where)->find();
            $tempMembers = $operate ? $operate->hasManyChildren()->column("admin_id") : [];
            $parents = isset($members) ? array_intersect($members, $tempMembers) : $tempMembers;
            $where["pid"] = ["IN", $parents];
        }
        // 微会员
        if(isset($filter['member']) && !empty($filter['member'])){
            $_where = [
                "username" => trim($filter['member']),
                "role" => Admin::MEMBER_ROLE_ID
            ];
            $members = Admin::where($_where)->column("admin_id");
            $parents = isset($parents) ? array_intersect($members, $parents) : $members;
            $where["pid"] = ["IN", $parents];
        }
        $totalFee = Admin::where($where)->sum("total_fee");
        $totalIncome = Admin::where($where)->sum("total_income");
        $pageSize = $pageSize ? : config("page_size");
        $tableCols = Admin::tableColumnShow();
        if($tableCols['settle'] == 1){
            $with = ["hasOneParent" => ["hasOneParent" => ["hasOneParent"]]];
        }elseif($tableCols['operate'] == 1){
            $with = ["hasOneParent" => ["hasOneParent"]];
        }else{
            $with = ["hasOneParent"];
        }
        $_lists = Admin::with($with)
            ->withSum(
                [
                    "hasManyWithdraw" => function($_query){
                        $_query->where(["state" => ["NEQ", -1]]);
                    }
                ], "amount"
            )
            ->field("password", true)
            ->where($where)
            ->order(["status" => "ASC", "admin_id" => "ASC"])
            ->paginate($pageSize, false, ['query'=>request()->param()]);
        $lists = $_lists->toArray();
        $pages = $_lists->render();
        return compact("lists", "pages", "totalFee", "totalIncome");
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
            $where["username"] = trim($filter['username']);
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
        // 机构码
        if(isset($filter['code']) && !empty($filter['code'])){
            $where["code"] = trim($filter['code']);
        }
        // 上级结算中心
        if(isset($filter['settle']) && !empty($filter['settle'])){
            $_where = [
                "username" => trim($filter['settle']),
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
        $totalFee = Admin::where($where)->sum("total_fee");
        $totalIncome = Admin::where($where)->sum("total_income");
        $pageSize = $pageSize ? : config("page_size");
        $_lists = Admin::with(["hasOneParent"])
                    ->withSum(
                        [
                            "hasManyWithdraw" => function($_query){
                                $_query->where(["state" => ["NEQ", -1]]);
                            }
                        ], "amount"
                    )
                    ->field("password", true)
                    ->where($where)
                    ->order(["status" => "ASC", "admin_id" => "ASC"])
                    ->paginate($pageSize, false, ['query'=>request()->param()]);
        $lists = $_lists->toArray();
        $pages = $_lists->render();
        return compact("lists", "pages", "totalFee", "totalIncome");
    }

    public function teamAdminById($id, $role="settle")
    {
        $proxyRoleIds = [
            Admin::SETTLE_ROLE_ID,
            Admin::OPERATE_ROLE_ID,
            Admin::MEMBER_ROLE_ID,
            Admin::RING_ROLE_ID
        ];
        if(in_array(manager()['role'], $proxyRoleIds)){
            // 代理商用户
            $_childrenAdminIds = Admin::childrenAdminIds(isLogin());
            if(!in_array($id, $_childrenAdminIds)){
                return [];
            }
        }
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
        //$where['status'] = 0;
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

    //代理商赠金
    public function giveProxy($AdminId, $money, $remark = null)
    {
        // 启动事务
        Db::startTrans();
        try{
            $admin = Admin::find($AdminId);
            // 余额增加
            $admin->setInc('total_fee', $money);
            // 收入合计增加
            $admin->setInc('total_income', $money);
            // 赠金日志记录
            $_gData = [
                "admin_id"   => $AdminId,
                "amount"    => $money,
                "remark"    => $remark,
                "create_at" => time(),
                "create_by" => isLogin()
            ];
            AdminGive::create($_gData);
            // 代理商收入明细
            // 代理商收入明细
            $rData = [
                "money" => $money, //返点金额
                "point" => 0, // 返点比例
                "type"  => 3, // 收入类型：0-用户收益分成，1-建仓费分成，2-递延费分成，3-系统赠金
                "remark" => $remark
            ];
            $admin->hasManyRecord()->save($rData);
            // 提交事务
            Db::commit();
            return true;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return false;
        }
    }

    // 代理商赠金日志
    public function pageAdminGiveLogs($filter = [], $pageSize = null)
    {
        $hasWhere = [];
        $where = Admin::manager();
        // 代理商登录名
        if(isset($filter['username']) && !empty($filter['username'])){
            $hasWhere["username"] = trim($filter['username']);
        }
        // 代理商类型
        if(isset($filter['role']) && is_numeric($filter['role'])){
            $hasWhere["role"] = $filter['role'];
        }
        $pageSize = $pageSize ? : config("page_size");
        $lists = AdminGive::hasWhere("belongsToAdmin", $hasWhere)
                ->with(['belongsToAdmin' => ['hasOneRole'], 'hasOneOperator'])
                ->where($where)
                ->paginate($pageSize, false, ['query'=>request()->param()]);
        return ["lists" => $lists->toArray(), "pages" => $lists->render()];
    }
}