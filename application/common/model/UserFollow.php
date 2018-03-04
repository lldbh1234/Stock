<?php
namespace app\common\model;


class UserFollow extends BaseModel
{
    protected $table = 'stock_user_follow';
    public $field = true;

    public function belongsToAttention()
    {
        return $this->belongsTo("\\app\\common\\model\\User", "follow_id", "user_id");
    }

    public function belongsToFans()
    {
        return $this->belongsTo("\\app\\common\\model\\User", "fans_id", "user_id");
    }
}