<?php
namespace app\admin\logic;

use app\admin\model\System;
use think\Db;

class SystemLogic
{

    public function getAll($where=[])
    {
        $filter = [];
        if(isset($where['alias'])) $filter['alias'] = $where['alias'];
        //获取模块
        $lists = System::where($filter)->select();
        return collection($lists)->toArray();
    }
    public function getAliasList($data)
    {
        $res = [];
        foreach($data as $k => $v)
        {
            $res[$v['alias']] = $v;
        }
        return $res;

    }

    public function update($data)
    {
        return System::update($data);
    }

    public function updateAll($data)
    {
        // 启动事务
        Db::startTrans();
        try{
            System::where('1=1')->delete();
            model('System')->saveAll($data);
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