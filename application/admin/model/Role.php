<?php
namespace app\admin\model;


class Role extends \app\common\model\Role
{
    public function getShowAttr($value)
    {
        $show = [1 => '开启', 0 => '关闭'];
        return ["value" => $value, "text" => $show[$value]];
    }
}