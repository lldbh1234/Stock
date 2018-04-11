<?php
namespace app\common\model;

class AdminWithdraw extends BaseModel
{
    protected $table = 'stock_admin_withdraw';
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

    public function hasOneAdmin()
    {
        return $this->hasOne('\app\\common\\model\\Admin', 'admin_id', 'admin_id');
    }

    public function hasOneUpdateBy()
    {
        return $this->hasOne('\app\\common\\model\\Admin', 'admin_id', 'update_by');
    }
}