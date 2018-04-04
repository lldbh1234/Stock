<?php
namespace app\common\model;


class Mode extends BaseModel
{
    protected $pk = "mode_id";
    protected $table = 'stock_mode';

    protected $insert = ['create_at'];

    protected function setSortAttr($value)
    {
        return is_numeric($value) ? $value : 50;
    }

    protected function setDepositAttr($value)
    {
        return is_array($value) ? implode(",", $value) : $value;
    }

    protected function setLeverAttr($value)
    {
        return is_array($value) ? implode(",", $value) : $value;
    }

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