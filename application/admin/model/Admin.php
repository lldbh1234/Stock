<?php
namespace app\admin\model;

class Admin extends \app\common\model\Admin
{
    public function getStatusAttr($value)
    {
        $status = [1 => '禁用', 0 => '正常'];
        return ["value" => $value, "text" => $status[$value]];
    }
}