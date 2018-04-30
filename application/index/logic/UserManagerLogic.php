<?php
namespace app\index\logic;

use app\index\model\User;
use app\index\model\UserManager;
use app\index\model\UserRecord;
use think\Db;

class UserManagerLogic
{

    public function updateManager($data)
    {
        return UserManager::update($data);
    }
    public function getInfoByUid($uid=0)
    {
        if($uid <=0) return false;
        $manageInfo = UserManager::where(['user_id' => $uid])->find();
        return $manageInfo ? $manageInfo->toArray() : [];
    }

    public function incomeTransfer($userId)
    {
        $manager = UserManager::where(['user_id' => $userId])->find();
        if($manager && $manager->sure_income > 0){
            Db::startTrans();
            try{
                // 可转变为0，已转增加
                $_data = [
                    "id" => $manager->id,
                    "sure_income" => 0,
                    "already_income" => $manager->already_income + $manager->sure_income
                ];
                UserManager::update($_data);
                // 用户余额增加
                $user = User::find($userId);
                $user->setInc("account", $manager->sure_income);
                // 用户资金明细增加
                $_rData = [
                    'user_id'   => $userId,
                    'type'      => 10,
					"account"	=> $user->account,
                    'amount'    => $manager->sure_income,
                    'direction' => 1,
                ];
                $user->hasManyRecord()->save($_rData);
                // 提交事务
                Db::commit();
                return [true, "转出成功！"];
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                return [false, "系统提示：内部错误！"];
            }
        }
        return [false, "当前用户无可转收入！"];
    }
}