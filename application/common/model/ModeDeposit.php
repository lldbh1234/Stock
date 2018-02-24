<?php
namespace app\common\model;


class ModeDeposit extends BaseModel
{
    protected $table = 'stock_mode_deposit';

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