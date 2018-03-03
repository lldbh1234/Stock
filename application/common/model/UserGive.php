<?php
/**
 * Created by PhpStorm.
 * User: bruce
 * Date: 18/2/28
 * Time: 下午7:41
 */
namespace app\common\model;

class UserGive extends BaseModel
{
    protected $table = 'stock_user_give';

    public function hasOneUser()
    {
        return $this->hasOne('\app\\common\\model\\User', 'user_id', 'user_id');
    }

}