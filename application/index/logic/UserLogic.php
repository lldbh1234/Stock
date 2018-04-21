<?php
namespace app\index\logic;

use app\common\payment\authLlpay;
use app\index\model\Admin;
use app\index\model\UserManagerRecord;
use app\index\model\UserNiuren;
use app\index\model\Order;
use app\index\model\User;
use app\index\model\UserNiurenRecord;
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
    public function saveNiuRen($data=[])
    {
        Db::startTrans();
        try{
            self::updateUser($data);
            UserNiuren::save(['user_id' => $data['user_id']]);
            // 提交事务
            Db::commit();
            return true;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return false;
        }

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
                    "actual"    => $money - cf('withdraw_poundage', config('withdraw_poundage')),
                    "poundage"  => cf('withdraw_poundage', config('withdraw_poundage')),
                    "out_sn"    => createStrategySn(),
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
        Db::startTrans();
        try{
            $user = User::get($userId);

            $data['admin_id'] = $user['admin_id'];
            $data['point'] = cf('manager_point', 5);
            $data['jiancang_point'] = cf('manager_jiancang_point', 5);
            $data['defer_point'] = cf('manager_defer_point', 5);
            if($user->hasOneManager){
                $data['state'] = 0;
                $data['update_at'] = 0;
                $data['update_by'] = 0;
                $user->hasOneManager->save($data);
            }else{
                $user->hasOneManager()->save($data);
            }

            $poundage = cf('manager_poundage', 88);
            $rData = [
                "type" => 8,
                "amount" => $poundage,
                "direction" => 2
            ];
            $user->hasManyRecord()->save($rData);
            Db::commit();
            return true;
        }catch (\Exception $e){
            Db::rollback();
            return false;
        }
    }

    // 绑定银行卡
    public function saveUserCard($userId, $data)
    {
        Db::startTrans();
        try{
            $user = User::find($userId);
            if($user->hasOneCard){
                $user->hasOneCard->save($data);
            }else{
                $user->hasOneCard()->save($data);
            }
            $llpayBanks = (new authLlpay())->bankBindList($userId);
            if($llpayBanks){
                $newCardNo = substr($data['bank_card'], -4);
                $cardNos = array_column($llpayBanks, "card_no");
                if(!in_array($newCardNo, $cardNos)){
                    // 新卡
                    foreach ($llpayBanks as $item){
                        $noAgree = $item['no_agree'];
                        $temp = (new authLlpay())->unbindBank($userId, $noAgree);
                        if(!$temp){
                            Db::rollback();
                            return false;
                        }
                    }
                }
            }
            Db::commit();
            return true;
        } catch (\Exception $e){
            Db::rollback();
            return false;
        }
    }

    public function userMembers($username)
    {
        $ringAdminIds = User::where(["username" => $username])->column("admin_id") ? : [-1];
        $ringAdmins = Admin::with("hasOneParent")->where(["admin_id" => ["IN", $ringAdminIds]])->select();
        $members = [];
        if($ringAdmins){
            $ringAdmins = collection($ringAdmins)->toArray();
            array_filter($ringAdmins, function ($item) use (&$members){
                $members[] = [
                    "admin_id" => $item["has_one_parent"]["admin_id"],
                    "nickname" => $item["has_one_parent"]["nickname"],
                    "username" => $item["has_one_parent"]["username"],
                ];
            });
        }
        return $members;
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

    public function userIncFans($userId)
    {
        $user = User::with("hasManyFans,hasManyFans.belongsToFans")->find($userId);
        return $user ? $user->toArray() : [];
    }

    public function userIncCard($userId)
    {
        $user = User::with(["hasOneCard" => ["hasOneProvince", "hasOneCity"]])->find($userId);
        return $user ? $user->toArray() : [];
    }

    public function userFansLists($userId)
    {
        try{
            $fans = User::find($userId)->hasManyFans()->select();
            return $fans ? collection($fans)->toArray() : [];
        }catch (\Exception $e){
            return [];
        }
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

    // $state 1委托建仓，2抛出，3持仓，4-委托平仓
    public function pageUserOrder($userId, $state = 1, $field = "*", $pageSize = 4){
        try{
            $where = is_array($state) ? ["state" => ["IN", $state]] : ["state" => $state];
            //$res = User::find($userId)->hasManyOrder()->where($where)->field($field)->paginate($pageSize);
            $where['user_id'] = $userId;
            $res = Order::with("belongsToMode")->where($where)->field($field)->paginate($pageSize);
            return $res ? $res->toArray() : [];
        } catch(\Exception $e) {
            return [];
        }
    }

    public function getNiuStaticByUid($uid)
    {
        $data = UserNiuren::where(['user_id' => $uid])->find();
        return $data ? $data->toArray() : [];
    }

    /**
     * 牛人资金记录
     * @param array $where
     * @return array
     */
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
        $data = UserNiurenRecord::where($map)->select();
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
        return UserNiurenRecord::where($map)->sum('money');
    }

    /**
     * 经纪人资金记录
     * @param array $where
     * @return array
     */
    public function manageRecordList($where=[])
    {
        $map = [];
        if(!empty($where) && is_array($where))
        {
            foreach($where as $k => $v)
            {
                $map[$k] = $v;
            }
        }
        $data = UserManagerRecord::where($map)->select();
        return collection($data)->toArray();
    }
    public function manageRecordAmount($where=[])
    {
        $map = [];
        if(!empty($where) && is_array($where))
        {
            foreach($where as $k => $v)
            {
                $map[$k] = $v;
            }
        }
        return UserManagerRecord::where($map)->sum('money');
    }

    public function userStatic($uid)
    {
        $result = [];
        $result['children'] = User::where(['parent_id' => $uid])->count();
        $result['commission'] = UserRecord::where(['type' => ['in', [2, 3]], 'user_id' => $uid])->sum('amount');//提成
        //推广
        //牛人
        $followUserOrderIds = Order::where(['user_id' => $uid])->column('order_id');//牛人订单id arr
        $follow = Order::where(['is_follow' => 1, 'follow_id' => ['in', $followUserOrderIds]])->count();
        $result['follow'] = $follow;
        $result['return_income'] = UserRecord::where(['type' => 2, 'user_id' => $uid])->sum('amount');//跟单
        return $result;

    }
    // $state 1委托建仓，2抛出，3持仓，4-委托平仓
    public function userOrderById($userId, $id, $state=null)
    {
        try{
            $where = [];
            $where['order_id'] = is_array($id) ? ["IN", $id] : $id;
            $state ? is_array($state) ? $where['state'] = ["IN", $state]: $where['state'] = $state : null;
            $orders = User::find($userId)->hasManyOrder()->with("belongsToMode")->where($where)->select();
            return $orders ? collection($orders)->toArray() : [];
        } catch(\Exception $e) {
            return [];
        }
    }

    //撤销建仓
    public function cancelUserBuying($order)
    {
        Db::startTrans();
        try{
            // 订单作废
            $data = [
                "order_id" => $order["order_id"],
                "state" => 5
            ];
            Order::update($data);
            // 用户资金
            $user = User::find($order['user_id']);
            $user->setInc("account", $order['jiancang_fee'] + $order['deposit']);
            // 冻结资金
            $user->setDec("blocked_account", $order['deposit']);
            // 资金明细(保证金)
            $rData = [
                "type" => 4,
                "amount" => $order['deposit'],
                "remark" => json_encode(['orderId' => $order["order_id"]]),
                "direction" => 1
            ];
            $user->hasManyRecord()->save($rData);
            // 资金明细(建仓费)
            $rData = [
                "type" => 0,
                "amount" => $order['jiancang_fee'],
                "remark" => json_encode(['orderId' => $order["order_id"]]),
                "direction" => 1
            ];
            $user->hasManyRecord()->save($rData);
            Db::commit();
            return true;
        } catch(\Exception $e) {
            Db::rollback();
            return false;
        }
    }

    // 撤销平仓
    public function cancelUserSelling($order)
    {
        if($order){
            // 更改订单状态 委托平仓=》持仓
            $data = [
                "order_id" => $order["order_id"],
                "sell_price" => 0,
                "sell_hand" => 0,
                "sell_deposit" => 0,
                "profit"    => 0,
                "state"     => 3
            ];
            return Order::update($data);
        }else{
            return false;
        }
    }

    // 平仓申请
    public function userOrderSelling($order)
    {
        if($order){
            /*$data = [
                "order_id" => $order["order_id"],
                "sell_price" => $order["last_px"],
                "sell_hand" => $order["hand"],
                "sell_deposit" => $order["hand"] * $order["last_px"],
                "profit" => ($order["last_px"] - $order["price"]) * $order["hand"],
                "state" => 4
            ];
            return Order::update($data);*/
            Db::startTrans();
            try{
                $data = [
                    "order_id" => $order["order_id"],
                    "sell_price" => $order["sell_px"],
                    "sell_hand" => $order["hand"],
                    "sell_deposit" => $order["hand"] * $order["sell_px"],
                    "profit" => ($order["sell_px"] - $order["price"]) * $order["hand"],
                    "state" => 2
                ];
                Order::update($data);
                if($data["profit"] > 0){
                    // 盈利
                    $bonus_rate = isset($order['belongs_to_mode']['point']) ? $order['belongs_to_mode']['point'] : 0;
                    $bonus = round($data["profit"] * (1 - $bonus_rate / 100), 2);
                    // 用户资金
                    $user = User::find($order['user_id']);
                    $user->setInc("account", $order['deposit'] + $bonus);
                    // 冻结资金
                    $user->setDec("blocked_account", $order['deposit']);
                    // 资金明细(保证金)
                    $rData = [
                        "type" => 4,
                        "amount" => $order['deposit'],
                        "remark" => json_encode(['orderId' => $order["order_id"]]),
                        "direction" => 1
                    ];
                    $user->hasManyRecord()->save($rData);
                    // 资金明细(分红)
                    $rData = [
                        "type" => 7,
                        "amount" => $bonus,
                        "remark" => json_encode(['orderId' => $order["order_id"]]),
                        "direction" => 1
                    ];
                    $user->hasManyRecord()->save($rData);
                }else{
                    // 亏损
                    // 用户资金
                    $user = User::find($order['user_id']);
                    $user->setInc("account", $order['deposit'] + $data["profit"]);
                    // 冻结资金
                    $user->setDec("blocked_account", $order['deposit']);
                    // 资金明细(保证金)
                    $rData = [
                        "type" => 4,
                        "amount" => $order['deposit'] + $data["profit"],
                        "remark" => json_encode(['orderId' => $order["order_id"]]),
                        "direction" => 1
                    ];
                    $user->hasManyRecord()->save($rData);
                }
                Db::commit();
                return true;
            } catch (\Exception $e){
                Db::rollback();
                return false;
            }
        }else{
            return false;
        }
    }

    //补充保证金
    public function userOrderDepositSupply($userId, $orderId, $deposit)
    {
        Db::startTrans();
        try{
            // 订单保证金增加
            Order::where(["order_id" => $orderId, "user_id" => $userId])->setInc("deposit", $deposit);
            // 余额减少
            $user = User::find($userId);
            $user->setDec("account", $deposit);
            // 锁定余额增加
            $user->setInc("blocked_account", $deposit);
            // 资金明细
            $rData = [
                "type" => 4,
                "amount" => $deposit,
                "remark" => json_encode(['orderId' => $orderId]),
                "direction" => 2
            ];
            $user->hasManyRecord()->save($rData);
            Db::commit();
            return true;
        } catch(\Exception $e) {
            Db::rollback();
            return false;
        }
    }

    public function getUidsByParentId($uid)
    {
        return User::where(['parent_id' => $uid])->column('user_id');
    }
    //修改止盈止损
    public function userOrderModifyPl($userId, $order, $profit, $loss)
    {
        Db::startTrans();
        try{
            // 修改止盈止损
            $profitPoint = round((($profit - $order["price"]) / $order["price"] * 100), 2);
            $lossPoint = round((($order["price"] - $loss) / $order["price"] * 100), 2); //新止损点
            $originalLossPoint = $order['stop_loss_point'] >= 8 ? $order['stop_loss_point'] : 8; //旧止损点
            $lossDiffPoint = $lossPoint - $originalLossPoint;
            $data = [
                "order_id" => $order["order_id"],
                "stop_profit_price" => $profit,
                "stop_profit_point" => $profitPoint,
                "stop_loss_price" => $loss,
                "stop_loss_point" => $lossPoint
            ];
            Order::update($data);
            if($lossPoint > 8 && $lossDiffPoint > 0){
                // 所需补充的金额
                $_deposit = $order["original_deposit"] * $lossDiffPoint / 100;
                // 补充完后的保证金
                // $deposit = $order["deposit"] + $_deposit;
                if($_deposit > 0){
                    $user = User::find($userId);
                    if($user && $user['account'] >= $_deposit){
                        Order::where(["order_id" => $order["order_id"]])->setInc("deposit", $_deposit);
                        // 余额减少
                        $user->setDec("account", $_deposit);
                        // 锁定余额增加
                        $user->setInc("blocked_account", $_deposit);
                        // 资金明细
                        $rData = [
                            "type" => 4,
                            "amount" => $_deposit,
                            "remark" => json_encode(['orderId' => $order["order_id"]]),
                            "direction" => 2
                        ];
                        $user->hasManyRecord()->save($rData);
                    }else{
                        Db::rollback();
                        return false;
                    }
                }
            }
            /*$deposit = $order["deposit"]; //保证金
            $_deposit = ($order["price"] - $loss) * $order['hand']; //调整后的保证金
            if($_deposit > $deposit){
                $diff = $_deposit - $deposit;
                $user = User::find($userId);
                if($user['account'] >= $diff){
                    Order::where(["order_id" => $order["order_id"]])->setInc("deposit", $diff);
                    // 余额减少
                    $user->setDec("account", $diff);
                    // 锁定余额增加
                    $user->setInc("blocked_account", $diff);
                    // 资金明细
                    $rData = [
                        "type" => 4,
                        "amount" => $diff,
                        "remark" => json_encode(['orderId' => $order["order_id"]]),
                        "direction" => 2
                    ];
                    $user->hasManyRecord()->save($rData);
                }else{
                    Db::rollback();
                    return false;
                }
            }*/
            Db::commit();
            return true;
        } catch(\Exception $e) {
            Db::rollback();
            return false;
        }
    }

    public function pageUserRecords($userId, $type = null, $pageSize = 4){
        try{
            $where = [];
            isset($type) ? is_array($type) ? $where["type"] = ["IN", $type] : $where["type"] = $type : null;
            $res = User::find($userId)->hasManyRecord()->where($where)->order(["create_at" => "DESC"])->paginate($pageSize);
            return $res ? $res->toArray() : [];
        } catch(\Exception $e) {
            return [];
        }
    }
}