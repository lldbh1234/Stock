<?php
namespace app\common\model;


class UserNotice extends BaseModel
{
    protected $pk = "id";
    protected $table = 'stock_user_notice';

    protected $insert = ['create_at'];


}