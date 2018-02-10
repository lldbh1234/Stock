<?php
namespace app\admin\model;

class Admin extends \app\common\model\Admin
{
    protected $insert = ['password', 'create_at'];

    protected function setPasswordAttr($value)
    {
        return spPassword($value);
    }

    protected function setCreateAtAttr()
    {
        return request()->time();
    }
}