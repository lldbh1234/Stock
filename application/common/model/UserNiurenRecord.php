<?php
namespace app\common\model;

class UserNiurenRecord extends BaseModel
{
    protected $table = 'stock_user_niuren_record';

    protected $insert = ['create_at'];

    protected function setCreateAtAttr()
    {
        return request()->time();
    }

    public function belongsToNiuren()
    {
        return $this->belongsTo("\\app\\common\\model\\User", "user_id", "user_id");
    }

    public function belongsToOrder()
    {
        return $this->belongsTo("\\app\\common\\model\\Order", "order_id", "order_id");
    }
}