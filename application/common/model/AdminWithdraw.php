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

    public function getStateAttr($value)
    {
        $status = [1 => '代付中', 0 => '待审核', 2=> '已到账', -1 => "已拒绝"];
        return ["value" => $value, "text" => $status[$value]];
    }

    public function getRemarkAttr($value)
    {
        return json_decode($value, true);
    }

    public function belongsToAdmin()
    {
        return $this->belongsTo('\app\\common\\model\\Admin', 'admin_id', 'admin_id');
    }

    public function hasOneUpdateBy()
    {
        return $this->hasOne('\app\\common\\model\\Admin', 'admin_id', 'update_by');
    }
}