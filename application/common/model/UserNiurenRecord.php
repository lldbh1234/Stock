<?php
namespace app\common\model;

class UserNiurenRecord extends BaseModel
{
    protected $table = 'stock_user_niuren_record';

    protected $insert = ['create_at'];

    protected function setCreateAtAttr()
    {
        return request()->time();
    }
}