<?php
namespace app\index\logic;

use app\index\model\User;
use app\index\model\UserManager;
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
}