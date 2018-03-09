<?php
namespace app\common\model;


class UserRecharge extends BaseModel
{
    protected $table = 'stock_user_recharge';
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

    public function belongsToUser()
    {
        return $this->belongsTo("\\app\\common\\model\\User", "user_id", "user_id");
    }
}