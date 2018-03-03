<?php
namespace app\common\model;


class UserRecord extends BaseModel
{
    protected $table = 'stock_user_record';
    protected $insert = ['create_at'];

    protected function setCreateAtAttr()
    {
        return request()->time();
    }
}