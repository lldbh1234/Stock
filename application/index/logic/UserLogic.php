<?php
namespace app\index\logic;

use app\index\model\Order;
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
    public function getAllBy($where=[])
    {
        $map = [];
        if(!empty($where) && is_array($where))
        {
            foreach($where as $k => $v)
            {
                $map[$k] = $v;
            }
        }
        $data = User::where($map)->select();
        return collection($data)->toArray();

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
            return 0;
        }
    }

    public function removeUserOptional($userId, $ids){
        try{
            return User::find($userId)->hasManyOptional()->where(["id" => $ids])->delete();
        } catch(\Exception $e) {
            return false;
        }
    }

    public function userOptionalCodes($userId)
    {
        return User::find($userId)->hasManyOptional()->column("code");
    }

    public function userIncManager($userId)
    {
        $user = User::with("hasOneAdmin,hasOneManager")->find($userId);
        return $user ? $user->toArray() : [];
    }

    public function saveUserManager($userId, $data)
    {
        $user = User::get($userId);
        if($user->hasOneManager){
            return $user->hasOneManager->save($data);
        }else{
            return $user->hasOneManager()->save($data);
        }
    }

    public function userIncAdmin($userId)
    {
        $user = User::with("hasOneAdmin")->find($userId);
        return $user ? $user->toArray() : [];
    }

    public function userIncAttention($userId)
    {
        $user = User::with("hasManyAttention,hasManyAttention.belongsToAttention")->find($userId);
        return $user ? $user->toArray() : [];
    }
    public function userDetail($uid)
    {
        $result = [];
        $user_info = User::find($uid);
        if($user_info) $user_info = $user_info->toArray();
        $result['user'] = $user_info;
        $map = ['user_id' => $uid];
        //查询策略数量
        $order_num = Order::where($map)->count();
        $result['pulish_strategy'] = $order_num;
        //查询胜算率
        $map['profit'] = ['>', 0];
        $order_win = Order::where($map)->count();

        $result['strategy_win'] = empty($order_win) ? 0 : round($order_win/$order_num/100, 2);
        //查询收益率
        $order_sale_amount = Order::where($map)->sum('sell_price');//卖出
        $order_income_amount = Order::where($map)->sum('price');//买入
        $income = $order_sale_amount-$order_income_amount;

        $result['strategy_yield'] = empty($order_income_amount) ? 0 : round($income/$order_income_amount/100, 2);
        return $result;


    }
}