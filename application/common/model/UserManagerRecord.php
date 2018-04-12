<?php
namespace app\common\model;

class UserManagerRecord extends BaseModel
{
    protected $table = 'stock_user_manager_record';

    protected $insert = ['create_at'];

    protected function setCreateAtAttr()
    {
        return time();
    }

    public function belongsToManager()
    {
        return $this->belongsTo("\\app\\common\\model\\User", "user_id", "user_id");
    }

    public function belongsToOrder()
    {
        return $this->belongsTo("\\app\\common\\model\\Order", "order_id", "order_id");
    }
}