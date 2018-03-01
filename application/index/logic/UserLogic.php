<?php
namespace app\index\logic;

use app\index\model\User;
use think\Db;

class UserLogic
{
    public function createUser($data)
    {
        $res = model("User")->save($data);
        return $res ? model("User")->getLastInsID() : 0;
    }

    public function updateUser($data)
    {
        return User::update($data);
    }

    public function userById($userId)
    {
        $user = User::find($userId);
        return $user ? $user->toArray() : [];
    }

    public function createUserWithdraw($userId, $money, $remark)
    {
        $user = User::find($userId);
        if($user){
            Db::startTrans();
            try{
                $user->setDec("account", $money);
                $data = [
                    "amount"    => $money,
                    "actual"    => $money - config('withdraw_poundage'),
                    "poundage"  => config('withdraw_poundage'),
                    "out_sn"    => createOrderSn(),
                    "remark"    => json_encode($remark),
                ];
                $res = $user->hasManyWithdraw()->save($data);
                $pk = model("UserWithdraw")->getPk();
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

    public function userOptional($userId)
    {
        $lists = User::find($userId)->hasManyOptional;
        return $lists ? collection($lists)->toArray() : [];
    }

    public function createUserOptional($userId, $stock)
    {
        try{
            unset($stock['id']);
            $res = User::find($userId)->hasManyOptional()->save($stock);
            return $res ? model("UserOptional")->getLastInsID() : 0;
        } catch(\Exception $e) {
            dump($e->getMessage());
            return 0;
        }
    }

    public function userOptionalCodes($userId)
    {
        return User::find($userId)->hasManyOptional()->column("code");
    }

    public function userIncAdmin($userId)
    {
        $user = User::with("hasOneAdmin")->find($userId);
        return $user ? $user->toArray() : [];
    }
}