<?php
namespace app\index\logic;

use think\Db;
use app\index\model\Danger;

class DangerLogic
{
    //  更新抓取高危股票
    public function updateDangerList($list)
    {
        Db::startTrans();
        try{
            Danger::where(["state" => 0])->delete();
            Danger::insertAll($list);
            Db::commit();
            return true;
        } catch (\Exception $e) {
            // 回滚事务
            dump($e->getMessage());
            Db::rollback();
            return false;
        }
    }

    // 根据来源获取高危股票列表
    public function dangerCodesByState($state = null)
    {
        $where = [];
        is_null($state) ? null : $where["state"] = $state;
        return Danger::where($where)->column("code");
    }

    // 高危股票代码
    public function dangerCodes()
    {
        return Danger::column("code");
    }
}