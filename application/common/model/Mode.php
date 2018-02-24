<?php
namespace app\common\model;


class Mode extends BaseModel
{
    protected $pk = "mode_id";
    protected $table = 'stock_mode';

    protected $insert = ['create_at'];

    protected function setCreateAtAttr()
    {
        return request()->time();
    }

    public function hasOneProduct()
    {
        return $this->hasOne("\\app\\common\\model\\Product", "id", "product_id");
    }

    public function hasOnePlugins()
    {
        return $this->hasOne("\\app\\common\\model\\Plugins", "code", "plugins_code");
    }

    public function hasManyDeposit()
    {
        return $this->hasMany("\\app\\common\\model\\ModeDeposit", "mode_id", "mode_id");
    }

    public function hasManyLever()
    {
        return $this->hasMany("\\app\\common\\model\\ModeLever", "mode_id", "mode_id");
    }
}