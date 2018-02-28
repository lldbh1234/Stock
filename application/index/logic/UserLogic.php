<?php
namespace app\index\logic;

use app\index\model\User;

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
}