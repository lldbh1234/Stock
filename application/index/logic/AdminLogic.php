<?php
namespace app\index\logic;

use app\index\model\Admin;

class AdminLogic
{
    public function adminByCode($code)
    {
        return Admin::where(["code" => $code])->find();
    }
}