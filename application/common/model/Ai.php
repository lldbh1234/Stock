<?php
namespace app\common\model;


class Ai extends BaseModel
{
    protected $table = 'stock_ai';

    protected function setSortAttr($value)
    {
        return is_numeric($value) ? $value : 50;
    }

    public function getStatusAttr($value)
    {
        $status = [1 => '关闭', 0 => '开启'];
        return ["value" => $value, "text" => $status[$value]];
    }

    public function belongsToType()
    {
        return $this->belongsTo("\\app\\common\\model\\AiType", "type_id", "type_id");
    }
}