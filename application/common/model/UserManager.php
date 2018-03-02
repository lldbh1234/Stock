<?php
namespace app\common\model;


class UserManager extends BaseModel
{
    protected $table = 'stock_user_manager';
    public $field = true;

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
}