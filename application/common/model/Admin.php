<?php
namespace app\common\model;

class Admin extends BaseModel
{
    protected $pk = "admin_id";
    protected $table = 'stock_admin';

    public static function manager()
    {
        $where = [];
        if(manager()['admin_id'] != self::ADMINISTRATOR_ID){
            if(manager()['role'] == self::RING_ROLE_ID){
                // 微圈
                $where['admin_id'] = manager()['admin_id'];
            }else{
                if(!in_array(manager()['role'], [self::SERVICE_ROLE_ID, self::FINANCE_ROLE_ID])){
                    $idArr = $arr =[manager()['admin_id']];
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
                    $where['admin_id'] = ["NEQ", self::ADMINISTRATOR_ID];
                }
            }
        }
        return $where;
    }

    public function hasOneRole()
    {
        return $this->hasOne("\\app\\common\\model\\Role", "id", "role");
    }
}