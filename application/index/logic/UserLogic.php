<?php
namespace app\index\logic;

use app\index\model\User;

class UserLogic
{
    public function createUser($data)
    {
        return model("User")->save($data);
    }

    public function updateUser($data)
    {
        return User::update($data);
    }
}