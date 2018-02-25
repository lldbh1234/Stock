<?php
namespace app\common\model;


class ModeLever extends BaseModel
{
    protected $table = 'stock_mode_lever';

    protected function setSortAttr($value)
    {
        return is_numeric($value) ? $value : 50;
    }

    public function getStatusAttr($value)
    {
        $status = [1 => '关闭', 0 => '开启'];
        return ["value" => $value, "text" => $status[$value]];
    }
}