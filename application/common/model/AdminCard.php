<?php
namespace app\common\model;


class AdminCard extends BaseModel
{
    protected $table = 'stock_admin_card';
    public $field = true;

    protected $insert = ['create_at'];
    protected $update = ['create_at'];

    protected function setCreateAtAttr()
    {
        return time();
    }

    public function belongsToAdmin()
    {
        return $this->belongsTo("\\app\\common\\model\\Admin", "admin_id", "admin_id");
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