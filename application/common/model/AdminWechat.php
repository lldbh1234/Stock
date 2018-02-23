<?php
namespace app\common\model;

class AdminWechat extends BaseModel
{
    protected $insert = ['create_at'];

    protected $pk = "admin_id";
    protected $table = 'stock_admin_wechat';

    protected function setCreateAtAttr()
    {
        return request()->time();
    }
}