<?php
namespace app\common\model;


class User extends BaseModel
{
    protected $pk = "user_id";
    protected $table = 'stock_user';
    protected $field = true;

    protected $insert = ['create_at'];

    protected function setPasswordAttr($value)
    {
        return spPassword($value);
    }

    protected function setCreateAtAttr()
    {
        return request()->time();
    }

    public function hasOneAdmin()
    {
        return $this->hasOne("\\app\\common\\model\\Admin", "admin_id", "admin_id");
    }

    public function hasManyWithdraw()
    {
        return $this->hasMany("\\app\\common\\model\\UserWithdraw", "user_id", "user_id");
    }

    public function hasManyOptional()
    {
        return $this->hasMany("\\app\\common\\model\\UserOptional", "user_id", "user_id");
    }
}