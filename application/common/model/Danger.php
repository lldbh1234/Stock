<?php
namespace app\common\model;

class Danger extends BaseModel
{
    //protected $pk = "code";
    protected $table = 'stock_danger_list';

    protected $insert = ['create_at'];

    protected function setCreateAtAttr()
    {
        return time();
    }

    public function getStateAttr($value)
    {
        $state = [1 => '手工录入', 0 => '动态抓取'];
        return ["value" => $value, "text" => $state[$value]];
    }
}