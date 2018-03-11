<?php
namespace app\common\model;

class UserManagerRecord extends BaseModel
{
    protected $table = 'stock_user_manager_record';

    protected $insert = ['create_at'];

    protected function setCreateAtAttr()
    {
        return request()->time();
    }
}