<?php
namespace app\index\logic;

use app\index\model\Admin;

class AdminLogic
{
    public function adminByCode($code)
    {
        return Admin::where(["code" => $code])->find();
    }

    public function allMemberLists()
    {
        return Admin::where(["role" => Admin::MEMBER_ROLE_ID])->column("username,nickname", "admin_id");
    }
}