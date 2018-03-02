<?php
namespace app\admin\logic;

use app\admin\model\User;
use app\admin\model\UserGive;
use think\Db;

class UserGiveLogic
{

    public function pageUserGiveLists($filter = [], $pageSize = null)
    {
        $where = [];
        if(isset($filter['username']) && !empty($filter['username'])){//用户
            $parent_ids = User::where(['username' => ["LIKE", "%{$filter['username']}%"]])->column('user_id');
            $where['user_id'] = ['IN', $parent_ids];
        }

        $pageSize = $pageSize ? : config("page_size");
        //推荐人-微圈-微会员
        $lists = UserGive::where($where)
            ->paginate($pageSize, false, ['query'=>request()->param()]);
        return ["lists" => $lists->toArray(), "pages" => $lists->render()];
    }


}