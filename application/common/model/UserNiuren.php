<?php
namespace app\common\model;

class UserNiuren extends BaseModel
{
    protected $table = 'stock_user_niuren';

    protected $insert = ['create_at'];

    protected function setCreateAtAttr()
    {
        return request()->time();
    }
}