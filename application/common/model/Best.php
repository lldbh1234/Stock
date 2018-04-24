<?php
namespace app\common\model;


class Best extends BaseModel
{
    protected $pk = "order_id";
    protected $table = 'stock_best';

    public function hasOneUser()
    {
        return $this->hasOne("\\app\\common\\model\\User", "user_id", "user_id");
    }
}