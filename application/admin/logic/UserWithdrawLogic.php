<?php
/**
 * Created by PhpStorm.
 * User: bruce
 * Date: 18/3/3
 * Time: 上午10:46
 */
namespace app\admin\logic;

use app\admin\model\User;
use app\admin\model\UserWithdraw;
use think\Db;

class UserWithdrawLogic
{
    public function pageUserWithdrawLists($filter = [], $pageSize = null)
    {
//        $where = Admin::manager();
        $where = [];
        if(isset($filter['username']) && !empty($filter['username'])){//用户
            $parent_ids_by_username = User::where(['username' => ["LIKE", "%{$filter['username']}%"]])->column('user_id');
            $where['user_id'] = ['IN', $parent_ids_by_username];
        }

        if(isset($filter['mobile']) && !empty($filter['mobile'])){//用户
            $parent_ids_by_mobile = User::where(['mobile' => ["LIKE", "%{$filter['mobile']}%"]])->column('user_id');
            if(isset($parent_ids_by_username)){
                $where['user_id'] = ['IN', array_intersect($parent_ids_by_username, $parent_ids_by_mobile)];
            }else{
                $where['user_id'] = ['IN', $parent_ids_by_mobile];
            }

        }

        $pageSize = $pageSize ? : config("page_size");
        //推荐人-微圈-微会员
        $lists = UserWithdraw::with(['hasOneUser', 'hasOneAdmin',])
            ->where($where)
            ->paginate($pageSize, false, ['query'=>request()->param()]);
        return ["lists" => $lists->toArray(), "pages" => $lists->render()];
    }
    public function withdrawById($param)
    {
        $data = UserWithdraw::find($param['id']);
        if(!$data) return ['code' => 1, 'msg' => '系统提示：非法操作！'];
        $data = $data->toArray();
        if($data['state'] == 1) return ['code' => 1, 'msg' => '系统提示：出金已审核，请勿重复操作！'];
        if($data['state'] == -1) return ['code' => 1, 'msg' => '系统提示：出金审核失败，请勿重复操作！'];
        $updateArr = [];
        $userUpdateArr = [];
        if($param['state'] == 1)//审核通过
        {
            $updateArr = [
                'id'    => $param['id'],
                'state' => $param['state'],
                'update_by' => isLogin(),
            ];
        }
        if($param['state'] == -1)//审核失败
        {
            //审核失败返还 用户金额
            $updateArr = [
                'id'    => $param['id'],
                'state' => $param['state'],
                'update_by' => isLogin(),
            ];
            $userUpdateArr = [
                'user_id' => $data['user_id'],
                'account' => ['exp', 'account + '.$data['amount']]
            ];
        }
        Db::startTrans();
        try{
            if($updateArr) UserWithdraw::update($updateArr);
            if($userUpdateArr) User::update($userUpdateArr);
            // 提交事务
            Db::commit();
            return ['code' => 0, '操作成功'];
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return ['code' => 1, '系统提示：操作异常！'];
        }


    }

}