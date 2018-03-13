<?php
namespace app\common\model;


class AdminRecord extends BaseModel
{
    protected $table = 'stock_admin_record';

    protected $insert = ['create_at'];

    protected function setCreateAtAttr()
    {
        return request()->time();
    }

    public function belongsToAdmin()
    {
        return $this->belongsTo("\\app\\common\\model\\Admin", "admin_id", "admin_id");
    }

    public function belongsToOrder()
    {
        return $this->belongsTo("\\app\\common\\model\\Order", "order_id", "order_id");
    }
}