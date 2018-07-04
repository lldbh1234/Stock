<?php
namespace app\admin\logic;

use app\admin\model\Admin;
use app\admin\model\User;
use app\admin\model\UserManager;
use think\Db;

class UserManagerLogic
{

    public function pageManagerLists($filter = [], $pageSize = null)
    {
        $where = [];
        $myUserIds = Admin::userIds();
        $myUserIds ? $where['user_id'] = ["IN", $myUserIds] : null;
        // 登录名
        if(isset($filter['realname']) && !empty($filter['realname'])){
            $where["realname"] = ["LIKE", "%{$filter['realname']}%"];
        }
        // 手机号
        if(isset($filter['mobile']) && !empty($filter['mobile'])){
            $where["mobile"] = $filter['mobile'];
        }
        // 状态
        if(isset($filter['state']) && is_numeric($filter['state']) && in_array($filter['state'], [0,1,2])){
            $where["state"] = $filter['state'];
        }


        //---------------------归属筛选----------------------------------------------------------------------------------
        // 结算中心
        if(isset($filter['settle']) && !empty($filter['settle'])){
            $_where = [
                "username" => trim($filter['settle']),
                "role" => Admin::SETTLE_ROLE_ID
            ];
            $settleId = Admin::where($_where)->value("admin_id");
            $ringIds = Admin::childrenAdminIds($settleId);
            $where["admin_id"] = ["IN", $ringIds];
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
            $where["admin_id"] = ["IN", $ringIds];
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
            $where["admin_id"] = ["IN", $ringIds];
        }
        // 微圈
        if(isset($filter['ring']) && !empty($filter['ring'])){
            $_where = [
                "username" => trim($filter['ring']),
                "role" => Admin::RING_ROLE_ID
            ];
            $tempRingIds = Admin::where($_where)->column("admin_id");
            $ringIds = isset($ringIds) ? array_intersect($ringIds, $tempRingIds) : $tempRingIds;
            $where["admin_id"] = ["IN", $ringIds];
        }
        //---------------------归属筛选结束-------------------------------------------------------------------------------



        $totalSure = UserManager::where($where)->sum("sure_income");
        $pageSize = $pageSize ? : config("page_size");
        //
        $_lists = UserManager::with(['hasOneUser' => ['hasOneAdmin'], 'hasOneAdmin'])
            ->where($where)
            ->paginate($pageSize, false, ['query'=>request()->param()]);
        $lists = $_lists->toArray();
        $pages = $_lists->render();
        return compact("lists", "pages", "totalSure");
    }

    public function updateState($where=[])
    {
        Db::startTrans();
        try{
            UserManager::update($where);
            if($where['state'] == 1){
                User::update(['user_id' => $where['user_id'], 'parent_id' => 0, 'is_manager' => 1]);
            }else{
                // 拒绝，回退申请手续费
                $poundage = cf('manager_poundage', 88);
				if($poundage > 0){
				    $user = User::find($where['user_id']);
                    $user->setInc("account", $poundage);
					$rData = [
						"type" => 8,
						"amount" => $poundage,
                        "account" => $user->account,
						"direction" => 1
					];
                    $user->hasManyRecord()->save($rData);
				}
            }
            Db::commit();
            return true;
        }catch (\Exception $e){
            Db::rollback();
            return false;
        }
    }

    public function managerById($id)
    {
        $where = ["id" => $id];
        $myUserIds = Admin::userIds();
        $myUserIds ? $where['user_id'] = ["IN", $myUserIds] : null;
        $manager = UserManager::where($where)->find();
        return $manager ? $manager->toArray() : [];
    }

    public function updateManager($data)
    {
        return UserManager::update($data);
    }
}