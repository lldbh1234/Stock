<?php
namespace app\common\model;


class Order extends BaseModel
{
    protected $pk = "order_id";
    protected $table = 'stock_order';
    protected $field = true;

    protected $insert = ['create_at'];

    protected function setCreateAtAttr()
    {
        return request()->time();
    }
}