<?php
/**
 * Created by PhpStorm.
 * User: bruce
 * Date: 18/2/28
 * Time: 下午7:41
 */
namespace app\common\model;

class AdminGive extends BaseModel
{
    protected $table = 'stock_admin_give';

    public function belongsToAdmin()
    {
        return $this->belongsTo('\app\\common\\model\\Admin', 'admin_id', 'admin_id');
    }

    public function hasOneOperator()
    {
        return $this->hasOne('\app\\common\\model\\Admin', 'admin_id', 'create_by');
    }
}