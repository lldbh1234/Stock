<?php
namespace app\common\model;

class Admin extends BaseModel
{
    protected $pk = "admin_id";
    protected $table = 'stock_admin';

    protected $insert = ['create_at'];

    protected function setPasswordAttr($value)
    {
        return spPassword($value);
    }

    protected function setCreateAtAttr()
    {
        return request()->time();
    }

    public static function manager()
    {
        $where = [];
        if(manager()['admin_id'] != self::ADMINISTRATOR_ID){
            if(manager()['role'] == self::RING_ROLE_ID){
                // 微圈
                $where['admin_id'] = manager()['admin_id'];
            }else{
                /*if(manager()['role'] == self::ADMIN_ROLE_ID){
                    // 超级超级管理员
                    $where['admin_id'] = ["NEQ", self::ADMINISTRATOR_ID];
                }else*/if(!in_array(manager()['role'], [self::SERVICE_ROLE_ID, self::FINANCE_ROLE_ID])){
                    // 组织架构
                    $idArr = $arr = [manager()['admin_id']];
                    do {
                        $idArr = self::where(["pid" => ["IN", $idArr]])->column("admin_id");
                        if (empty($idArr)) {
                            break;
                        } else {
                            $arr = array_merge($arr, $idArr);
                        }
                    } while (true);
                    $where['admin_id'] = ["IN", $arr];
                }else{
                    // 财务、客服
                    $where['admin_id'] = ["NEQ", self::ADMINISTRATOR_ID];
                    $where['role'] = ["NEQ", self::ADMIN_ROLE_ID];
                }
            }
        }
        return $where;
    }

    public function hasOneRole()
    {
        return $this->hasOne("\\app\\common\\model\\Role", "id", "role");
    }

    public function hasOneParent()
    {
        return $this->hasOne("\\app\\common\\model\\Admin", "admin_id", "pid");
    }

    public function hasOneWechat()
    {
        return $this->hasOne("\\app\\common\\model\\AdminWechat", "admin_id", "admin_id");
    }
}