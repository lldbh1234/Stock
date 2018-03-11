<?php
namespace app\web\logic;

use app\web\model\Admin;

class AdminLogic
{
    protected $familyTree;
    public function adminByCode($code)
    {
        return Admin::where(["code" => $code])->find();
    }

    public function allMemberLists()
    {
        return Admin::where(["role" => Admin::MEMBER_ROLE_ID])->column("username,nickname", "admin_id");
    }

    // 微圈家谱，返点使用
    public function ringFamilyTree($adminId)
    {
        $roles = [
            Admin::RING_ROLE_ID, //微圈
            Admin::MEMBER_ROLE_ID, //微会员
            Admin::OPERATE_ROLE_ID, //运营中心
            Admin::SETTLE_ROLE_ID, //结算中心
        ];
        $admins = Admin::where(["role" => ["IN", $roles]])->column("admin_id,pid,point");
        //dump($admins);
        $this->familyTree = [];
        $this->_familyTree($admins, $adminId);
        return $this->familyTree;
    }

    private function _familyTree($admins, $admin_id, $field = "pid")
    {
        foreach ($admins as $key=>$val){
            if($val['admin_id'] == $admin_id){
                $this->familyTree[] = $val;
                $this->_familyTree($admins, $val[$field], $field);
            }
        }
    }
}