<?php
namespace app\admin\model;


class Mode extends \app\common\model\Mode
{
    public function getStatusAttr($value)
    {
        $status = [1 => '关闭', 0 => '开启'];
        return ["value" => $value, "text" => $status[$value]];
    }
}