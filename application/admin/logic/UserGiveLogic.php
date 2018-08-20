<?php
namespace app\admin\logic;

use app\admin\model\User;
use app\admin\model\UserGive;
use think\Db;

class UserGiveLogic
{

    public function pageUserGiveLists($filter = [], $pageSize = null)
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
        $lists = UserGive::with(['hasOneUser' => ['hasOneAdmin'], 'hasOneCreateBy'])
            ->where($where)
            ->paginate($pageSize, false, ['query'=>request()->param()]);
        return ["lists" => $lists->toArray(), "pages" => $lists->render()];
    }
    public function userGiveLists($filter = [])
    {
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
        $_lists = UserGive::with(['hasOneUser' => ['hasOneAdmin'], 'hasOneCreateBy'])->where($where)->select();
        $lists  = collection($_lists)->toArray();
        return compact("lists");

    }


}