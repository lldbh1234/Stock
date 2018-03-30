<?php
namespace app\common\model;


class UserCard extends BaseModel
{
    protected $table = 'stock_user_card';
    public $field = true;

    protected $insert = ['create_at'];
    protected $update = ['create_at'];

    protected function setCreateAtAttr()
    {
        return request()->time();
    }

    public function belongsToUser()
    {
        return $this->belongsTo("\\app\\common\\model\\User", "user_id", "user_id");
    }

    public function hasOneProvince()
    {
        return $this->hasOne("\\app\\common\\model\\Region", "id", "bank_province");
    }

    public function hasOneCity()
    {
        return $this->hasOne("\\app\\common\\model\\Region", "id", "bank_city");
    }
}