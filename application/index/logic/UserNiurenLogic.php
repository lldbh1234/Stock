<?php
namespace app\index\logic;

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
}