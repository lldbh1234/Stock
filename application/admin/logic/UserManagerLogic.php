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
        $pageSize = $pageSize ? : config("page_size");
        //
        $lists = UserManager::with(['hasOneUser', 'hasOneAdmin'])
            ->where($where)
            ->paginate($pageSize, false, ['query'=>request()->param()]);
        return ["lists" => $lists->toArray(), "pages" => $lists->render()];
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
                $rData = [
                    "type" => 8,
                    "amount" => $poundage,
                    "direction" => 1
                ];
                User::find($where['user_id'])->hasManyRecord()->save($rData);
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