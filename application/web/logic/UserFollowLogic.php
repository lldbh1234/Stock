<?php
namespace app\web\logic;

use app\web\model\UserFollow;
use think\Db;

class UserFollowLogic
{

    public function getFollowIdByUid($uid)
    {
        return UserFollow::where(['fans_id' => $uid])->column('follow_id');

    }

    public function getFansIdByUid($uid)
    {
        return UserFollow::where(['follow_id' => $uid])->column('fans_id');

    }
    public function add($addArr)
    {
        return UserFollow::insert($addArr);
    }
    public function delBy($where=[])
    {
        $map = [];
        if(empty($where)) return false;
        isset($where['follow_id	']) ? $map['follow_id'] = $where['follow_id'] : '';
        isset($where['fans_id']) ? $map['fans_id'] = $where['fans_id'] : '';
        return UserFollow::where($where)->delete();
    }

}