<?php
namespace app\common\model;


class User extends BaseModel
{
    protected $pk = "user_id";
    protected $table = 'stock_user';
    protected $field = true;

    protected $insert = ['create_at'];

    protected function setPasswordAttr($value)
    {
        return spPassword($value);
    }

    protected function setCreateAtAttr()
    {
        return time();
    }

    // 微圈
    public function hasOneAdmin()
    {
        return $this->hasOne("\\app\\common\\model\\Admin", "admin_id", "admin_id");
    }

    // 上级
    public function hasOneParent()
    {
        return $this->hasOne("\\app\\common\\model\\User", "user_id", "parent_id");
    }

    // 经纪人
    public function hasOneManager()
    {
        return $this->hasOne("\\app\\common\\model\\UserManager", "user_id", "user_id");
    }

    // 经纪人收入明细
    public function hasManyManagerRecord()
    {
        return $this->hasMany("\\app\\common\\model\\UserManagerRecord", "user_id", "user_id");
    }

    // 提现
    public function hasManyWithdraw()
    {
        return $this->hasMany("\\app\\common\\model\\UserWithdraw", "user_id", "user_id");
    }

    // 自选
    public function hasManyOptional()
    {
        return $this->hasMany("\\app\\common\\model\\UserOptional", "user_id", "user_id");
    }

    // 关注
    public function hasManyAttention()
    {
        return $this->hasMany("\\app\\common\\model\\UserFollow", "fans_id", "user_id");
    }

    // 粉丝
    public function hasManyFans()
    {
        return $this->hasMany("\\app\\common\\model\\UserFollow", "follow_id", "user_id");
    }

    // 资金明细
    public function hasManyRecord()
    {
        return $this->hasMany("\\app\\common\\model\\UserRecord", "user_id", "user_id");
    }

    // 订单
    public function hasManyOrder()
    {
        return $this->hasMany("\\app\\common\\model\\Order", "user_id", "user_id");
    }

    // 个人信息
    public function hasManyNotice()
    {
        return $this->hasMany("\\app\\common\\model\\UserNotice", "user_id", "user_id");
    }

    // 牛人配置
    public function hasOneNiuren()
    {
        return $this->hasOne("\\app\\common\\model\\UserNiuren", "user_id", "user_id");
    }

    // 牛人收入明细
    public function hasManyNiurenRecord()
    {
        return $this->hasMany("\\app\\common\\model\\UserNiurenRecord", "user_id", "user_id");
    }

    // 银行卡
    public function hasOneCard()
    {
        return $this->hasOne("\\app\\common\\model\\UserCard", "user_id", "user_id");
    }
}