<?php
namespace app\common\model;


class Order extends BaseModel
{
    protected $pk = "order_id";
    protected $table = 'stock_order';
    protected $field = true;

    protected $insert = ['create_at'];
    protected $update = ['update_at'];

    protected function setCreateAtAttr()
    {
        return request()->time();
    }

    protected function setUpdateAtAttr()
    {
        return request()->time();
    }

    public function hasOneUser()
    {
        return $this->hasOne("\\app\\common\\model\\User", "user_id", "user_id");
    }

    public function hasOneOperator()
    {
        return $this->hasOne("\\app\\common\\model\\Admin", "admin_id", "update_by");
    }
}