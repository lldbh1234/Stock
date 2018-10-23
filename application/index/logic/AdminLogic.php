<?php
namespace app\index\logic;

use app\index\model\Admin;

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
        $admins = Admin::where(["role" => ["IN", $roles]])->column("admin_id,pid,point,jiancang_point,defer_point");
        //dump($admins);
        $this->familyTree = [];
        $this->_familyTree($admins, $adminId);
        $this->familyTree = $this->_realPoint($this->familyTree);
        return $this->familyTree;
    }

    private function _familyTree($admins, $admin_id, $field = "pid")
    {
        foreach ($admins as $key=>$val){
            if($val['admin_id'] == $admin_id){
                $this->familyTree[$admin_id] = $val;
                $this->_familyTree($admins, $val[$field], $field);
            }
        }
    }

    private function _realPoint($admins)
    {
        foreach ($admins as &$val){
            $val = $this->_handleRealPoint($admins, $val['admin_id']);
        }
        $original = $admins;
        foreach ($admins as &$val){
            foreach ($original as $item){
                if($val['admin_id'] == $item['pid']){
                    $val['real_point'] = $val['real_point'] - $item['real_point'];
                    $val['real_jiancang_point'] = $val['real_jiancang_point'] - $item['real_jiancang_point'];
                    $val['real_defer_point'] = $val['real_defer_point'] - $item['real_defer_point'];
                    break;
                }
            }
        }
        return $admins;
    }

    private function _handleRealPoint($admins, $admin_id, $field = "pid")
    {
        $parent = [];
        foreach ($admins as $key=>$val){
            if($val['admin_id'] == $admin_id){
                $temp = $this->_handleRealPoint($admins, $val[$field], $field);
                if(isset($temp['real_point'])){
                    $val['real_point'] = $val['point'] / 100 * $temp['real_point'];
                    $val['real_jiancang_point'] = $val['jiancang_point'] / 100 * $temp['real_jiancang_point'];
                    $val['real_defer_point'] = $val['defer_point'] / 100 * $temp['real_defer_point'];
                }else{
                    $val['real_point'] = $val['point'] / 100;
                    $val['real_jiancang_point'] = $val['jiancang_point'] / 100;
                    $val['real_defer_point'] = $val['defer_point'] / 100;
                }
                $parent = $val;
            }
        }
        return $parent;
    }

    public function proxyRingCodes($adminId)
    {
        $codes = [];
        $idArr = $arr = is_array($adminId) ? $adminId : [$adminId];
        do {
            $admins = Admin::where(["pid" => ["IN", $idArr]])->column("admin_id,role,code");
            if (empty($admins)) {
                break;
            } else {
                $admins = array_values($admins);
                $adminIds = array_column($admins, "admin_id");
                $roleId = $admins[0]['role'];
                if($roleId == Admin::RING_ROLE_ID){
                    $codes = array_column($admins, "code");
                    break;
                }else{
                    $idArr = $adminIds;
                    $arr = array_merge($arr, $idArr);
                }
            }
        } while (true);
        return $codes;
    }
}