<?php
namespace app\common\model;


class AiType extends BaseModel
{
    protected $pk = "type_id";
    protected $table = 'stock_ai_type';

    protected function setSortAttr($value)
    {
        return is_numeric($value) ? $value : 50;
    }

    public function getStatusAttr($value)
    {
        $status = [1 => '关闭', 0 => '开启'];
        return ["value" => $value, "text" => $status[$value]];
    }

    public function hasManyAi()
    {
        return $this->hasMany("\\app\\common\\model\\Ai", "type_id", "type_id");
    }
}