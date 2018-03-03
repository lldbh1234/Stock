<?php
namespace app\common\model;

class UserWithdraw extends BaseModel
{
    protected $table = 'stock_user_withdraw';
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
        return $this->hasOne('\app\\common\\model\\User', 'user_id', 'user_id');
    }
    public function hasOneAdmin()
    {
        return $this->hasOne('\app\\common\\model\\Admin', 'admin_id', 'update_by');
    }
}