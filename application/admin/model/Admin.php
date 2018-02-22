<?php
namespace app\admin\model;

class Admin extends \app\common\model\Admin
{
    protected $insert = ['create_at'];

    protected function setPasswordAttr($value)
    {
        return spPassword($value);
    }

    protected function setCreateAtAttr()
    {
        return request()->time();
    }

    public function getStatusAttr($value)
    {
        $status = [1 => '禁用', 0 => '正常'];
        return ["value" => $value, "text" => $status[$value]];
    }
}