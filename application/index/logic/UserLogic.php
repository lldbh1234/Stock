<?php
namespace app\index\logic;

use app\index\model\Niuren;
use app\index\model\Order;
use app\index\model\User;
use app\index\model\UserRecord;
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
            return User::find($userId)->hasManyOptional()->where(["code" => ["IN", $ids]])->delete();
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
    public function userDetail($uid, $orderMap=[])
    {
        $result = [];
        $map = ['user_id' => $uid];
        $map = array_merge($orderMap, $map);
        //查询策略数量
        $order_num = Order::where($map)->count();
        $result['pulish_strategy'] = $order_num;
        //查询胜算率
        $map['profit'] = ['>', 0];
        $order_win = Order::where($map)->count();

        $result['strategy_win'] = empty($order_win) ? 0 : round($order_win/$order_num*100, 2);
        //查询收益率
        $order_sale_amount = Order::where($map)->sum('sell_price');//卖出
        $order_income_amount = Order::where($map)->sum('price');//买入
        $income = $order_sale_amount-$order_income_amount;

        $result['strategy_yield'] = empty($order_income_amount) ? 0 : round($income/$order_income_amount*100, 2);
        return $result;
    }

    // $state 1委托，2抛出，3持仓
    public function userIncOrder($userId, $state = 1)
    {
        $user = User::with(["hasManyOrder" => function($query) use ($state){
            $query->where(["state" => $state]);
        }])->find($userId);
        return $user ? $user->toArray() : [];
    }

    // $state 1委托，2抛出，3持仓
    public function pageUserOrder($userId, $state = 1, $pageSize = 2){
        try{
            $res = User::find($userId)->hasManyOrder()->where(["state" => $state])->paginate($pageSize);
            return $res ? $res->toArray() : [];
        } catch(\Exception $e) {
            return [];
        }
    }
    public function getNiuStaticByUid($uid)
    {
        $data = Niuren::where(['user_id' => $uid])->find();
        return $data->toArray();
    }
    public function recordList($where=[])
    {
        $map = [];
        if(!empty($where) && is_array($where))
        {
            foreach($where as $k => $v)
            {
                $map[$k] = $v;
            }
        }
        $data = UserRecord::where($map)->select();
        return collection($data)->toArray();
    }
    public function recordAmount($where=[])
    {
        $map = [];
        if(!empty($where) && is_array($where))
        {
            foreach($where as $k => $v)
            {
                $map[$k] = $v;
            }
        }
        return UserRecord::where($map)->sum('amount');
    }
    public function userStatic($uid)
    {
        $result = [];
        $result['children'] = User::where(['parent_id' => $uid])->count();
        $result['commission'] = UserRecord::where(['type' => ['in', [2,3], 'user_id' => $uid]])->sum('amount');//提成
        //推广
        //牛人
        $result['follow'] = Order::where(['is_follow' => 1, 'follow_id' => $uid])->count();//跟单
        $result['return_income'] = UserRecord::where(['type' => 2, 'user_id' => $uid])->sum('amount');//跟单
        return $result;
    }
}