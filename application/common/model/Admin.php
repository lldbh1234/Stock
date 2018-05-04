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
        return time();
    }

    public static function manager()
    {
        $where = [];
        if(manager()['admin_id'] != self::ADMINISTRATOR_ID){
            if(manager()['role'] == self::RING_ROLE_ID){
                // 微圈
                $where['admin_id'] = manager()['admin_id'];
            }else{
                $proxyRoleIds = [
                    self::SETTLE_ROLE_ID,
                    self::OPERATE_ROLE_ID,
                    self::MEMBER_ROLE_ID,
                    self::RING_ROLE_ID
                ];
                /*if(manager()['role'] == self::ADMIN_ROLE_ID){
                    // 超级超级管理员
                    $where['admin_id'] = ["NEQ", self::ADMINISTRATOR_ID];
                }else*/ //if(!in_array(manager()['role'], [self::SERVICE_ROLE_ID, self::FINANCE_ROLE_ID])){
                if(in_array(manager()['role'], $proxyRoleIds)){
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
                    //$where['admin_id'] = ["NEQ", self::ADMINISTRATOR_ID];
                    //$where['role'] = ["NEQ", self::ADMIN_ROLE_ID];
                }
            }
        }
        return $where;
    }

    // 返回空，为全部用户
    public static function userIds()
    {
        $userIds = [];
        if(manager()['admin_id'] != self::ADMINISTRATOR_ID){
            if(manager()['role'] == self::RING_ROLE_ID){
                // 微圈
                $where['admin_id'] = manager()['admin_id'];
                $userIds = User::where($where)->column("user_id");
                $userIds = $userIds ? : [-1];
            }else{
                $proxyRoleIds = [
                    self::SETTLE_ROLE_ID,
                    self::OPERATE_ROLE_ID,
                    self::MEMBER_ROLE_ID,
                    self::RING_ROLE_ID
                ];
                if(in_array(manager()['role'], $proxyRoleIds)){
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
                    $userIds = User::where($where)->column("user_id");
                    $userIds = $userIds ? : [-1];
                }
            }
        }
        return $userIds;
    }

    // 下级所有代理商ID
    public static function childrenAdminIds($adminId)
    {
        $adminIds = [];
        $admin = self::find($adminId);
        if($admin){
            $proxyRoleIds = [
                self::SETTLE_ROLE_ID,
                self::OPERATE_ROLE_ID,
                self::MEMBER_ROLE_ID,
                self::RING_ROLE_ID
            ];
            if(in_array($admin->role, $proxyRoleIds)){
                $idArr = $adminIds = [$adminId];
                do {
                    $idArr = self::where(["pid" => ["IN", $idArr]])->column("admin_id");
                    if (empty($idArr)) {
                        break;
                    } else {
                        $adminIds = array_merge($adminIds, $idArr);
                    }
                } while (true);
            }
        }
        return $adminIds;
    }

    public static function proxyFamilyShow($adminId)
    {
        $admin = self::find($adminId);
        $cols = ["settle" => 0, "operate" => 0, "member" => 0, "ring" => 0];
        if($admin){
            if($admin->role == self::SETTLE_ROLE_ID){
                // 结算中心
            }elseif($admin->role == self::OPERATE_ROLE_ID){
                // 运营中心
                $cols = ["settle" => 1, "operate" => 0, "member" => 0, "ring" => 0];
            }elseif($admin->role == self::MEMBER_ROLE_ID){
                // 微会员
                $cols = ["settle" => 1, "operate" => 1, "member" => 0, "ring" => 0];
            }elseif($admin->role == self::RING_ROLE_ID){
                // 微圈
                $cols = ["settle" => 1, "operate" => 1, "member" => 1, "ring" => 0];
            }
        }
        return $cols;
    }

    public static function tableColumnShow()
    {
        $_self = manager();
        $cols = ["settle" => 1, "operate" => 1, "member" => 1, "ring" => 1];
        if($_self['admin_id'] != Admin::ADMINISTRATOR_ID){
            $proxyRoleIds = [
                Admin::SETTLE_ROLE_ID,
                Admin::OPERATE_ROLE_ID,
                Admin::MEMBER_ROLE_ID,
                Admin::RING_ROLE_ID
            ];
            if(in_array($_self['role'], $proxyRoleIds)){
                // 代理商
                if($_self['role'] == Admin::SETTLE_ROLE_ID){
                    // 结算中心
                    $cols = ["settle" => 0, "operate" => 1, "member" => 1, "ring" => 1];
                }elseif($_self['role'] == Admin::OPERATE_ROLE_ID){
                    // 运营中心
                    $cols = ["settle" => 0, "operate" => 0, "member" => 1, "ring" => 1];
                }elseif ($_self['role'] == Admin::MEMBER_ROLE_ID){
                    // 微会员
                    $cols = ["settle" => 0, "operate" => 0, "member" => 0, "ring" => 1];
                }else{
                    // 微圈
                    $cols = ["settle" => 0, "operate" => 0, "member" => 0, "ring" => 0];
                }
            }
        }
        return $cols;
    }

    public function hasOneRole()
    {
        return $this->hasOne("\\app\\common\\model\\Role", "id", "role");
    }

    public function hasOneParent()
    {
        return $this->hasOne("\\app\\common\\model\\Admin", "admin_id", "pid");
    }

    public function hasManyChildren()
    {
        return $this->hasMany("\\app\\common\\model\\Admin", "pid", "admin_id");
    }

    public function hasOneWechat()
    {
        return $this->hasOne("\\app\\common\\model\\AdminWechat", "admin_id", "admin_id");
    }

    public function hasManyRecord()
    {
        return $this->hasMany("\\app\\common\\model\\AdminRecord", "admin_id", "admin_id");
    }

    // 银行卡
    public function hasOneCard()
    {
        return $this->hasOne("\\app\\common\\model\\AdminCard", "admin_id", "admin_id");
    }

    // 提现
    public function hasManyWithdraw()
    {
        return $this->hasMany("\\app\\common\\model\\AdminWithdraw", "admin_id", "admin_id");
    }
}