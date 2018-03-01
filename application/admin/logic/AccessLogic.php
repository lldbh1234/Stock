<?php
namespace app\admin\logic;

use app\admin\model\Access;
use think\Db;

class AccessLogic
{

    public function getRoleBy($where=[])
    {
        $filter = [];
        if(!empty($where) && is_array($where))
        {
            foreach ($where as $k => $v)
            {
                $filter[$k] = $v;
            }

        }
        return Access::where($filter)->column('node_id');

    }
    public function delBy($where = [])
    {
        return isset($where['role_id']) ? Access::where(['role_id' => $where['role_id']])->delete() : '';
    }
    public function insert($data)
    {
        return model('Access')->saveAll($data);
    }
    public function rolePush($role_id, $data)
    {
        // 启动事务
        Db::startTrans();
        try{
            Access::where(['role_id' => $role_id])->delete();
            model('Access')->saveAll($data);
            // 提交事务
            Db::commit();
            return true;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return false;
        }
    }

}