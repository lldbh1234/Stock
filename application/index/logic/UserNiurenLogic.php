<?php
namespace app\index\logic;

use app\index\model\User;
use app\index\model\UserNiuren;
use think\Db;

class UserNiurenLogic
{

    public function updateManager($data)
    {
        return UserNiuren::update($data);
    }
    public function getInfoByUid($uid=0)
    {
        if($uid <=0) return false;
        $niurenInfo = UserNiuren::where(['user_id' => $uid])->find();
        return $niurenInfo ? $niurenInfo->toArray() : [];
    }
    public function incomeTransfer($userId)
    {
        $niuren = UserNiuren::where(['user_id' => $userId])->find();
        if($niuren && $niuren->sure_income > 0){
            Db::startTrans();
            try{
                // 可转变为0，已转增加
                $_data = [
                    "id" => $niuren->id,
                    "sure_income" => 0,
                    "already_income" => $niuren->already_income + $niuren->sure_income
                ];
                UserNiuren::update($_data);
                // 用户余额增加
                $user = User::find($userId);
                $user->setInc("account", $niuren->sure_income);
                // 用户资金明细增加
                $_rData = [
                    'user_id'   => $userId,
                    'type'      => 9,
                    'amount'    => $niuren->sure_income,
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