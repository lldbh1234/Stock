<?php
namespace app\common\model;


class OrderAction extends BaseModel
{
    protected $table = 'stock_order_action';
    protected $field = true;

    protected $insert = ['act_admin', 'act_time'];

    protected function setActAdminAttr()
    {
        return isLogin();
    }

    protected function setActTimeAttr()
    {
        return request()->time();
    }

    public function belongsToOrder()
    {
        return $this->belongsTo("\\app\\common\\model\\Order", "order_id", "order_id");
    }
}