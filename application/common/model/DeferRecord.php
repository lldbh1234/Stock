<?php
namespace app\common\model;


class DeferRecord extends BaseModel
{
    protected $table = 'stock_defer_record';

    protected $insert = ['create_at'];

    protected function setCreateAtAttr()
    {
        return request()->time();
    }

    public function belongsToUser()
    {
        return $this->belongsTo("\\app\\common\\model\\User", "user_id", "user_id");
    }

    public function belongsToOrder()
    {
        return $this->belongsTo("\\app\\common\\model\\Order", "order_id", "order_id");
    }
}