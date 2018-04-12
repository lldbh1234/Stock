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
        return time();
    }

    protected function setUpdateAtAttr()
    {
        return time();
    }

    public function hasOneUser()
    {
        return $this->hasOne("\\app\\common\\model\\User", "user_id", "user_id");
    }

    public function hasOneOperator()
    {
        return $this->hasOne("\\app\\common\\model\\Admin", "admin_id", "update_by");
    }

    public function hasManyNiurenRecord()
    {
        return $this->hasMany("\\app\\common\\model\\UserNiurenRecord", "order_id", "order_id");
    }

    public function hasManyManagerRecord()
    {
        return $this->hasMany("\\app\\common\\model\\UserManagerRecord", "order_id", "order_id");
    }

    public function hasManyProxyRecord()
    {
        return $this->hasMany("\\app\\common\\model\\AdminRecord", "order_id", "order_id");
    }

    public function hasManyAction()
    {
        return $this->hasMany("\\app\\common\\model\\OrderAction", "order_id", "order_id");
    }

    public function belongsToMode()
    {
        return $this->belongsTo("\\app\\common\\model\\Mode", "mode_id", "mode_id");
    }
}