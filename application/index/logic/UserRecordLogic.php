<?php
namespace app\index\logic;

use app\index\model\UserRecord;
use think\Db;

class UserRecordLogic
{

    public function insert($data)
    {
        return UserRecord::insert($data);
    }
}