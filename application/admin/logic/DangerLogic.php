<?php
namespace app\admin\logic;

use app\admin\model\Danger;

class DangerLogic
{
    public function pageDangerList($filter = [], $pageSize = null)
    {
        $where = [];
        // 股票代码
        if(isset($filter['code']) && !empty($filter['code'])){
            $where["code"] = trim($filter['code']);
        }
        // 股票名称
        if(isset($filter['name']) && !empty($filter['name'])){
            $where["name"] = trim($filter['name']);
        }
        $pageSize = $pageSize ? : config("page_size");
        $lists = Danger::where($where)->paginate($pageSize, false, ['query'=>request()->param()]);
        return ["lists" => $lists->toArray(), "pages" => $lists->render()];
    }

    public function createDanger($data)
    {
        $res = Danger::create($data);
        return $res ? true : false;
    }

    public function dangerByCode($code)
    {
        $danger = Danger::where(["code" => $code])->find();
        return $danger ? $danger->toArray() : [];
    }

    public function deleteByCode($code)
    {
        return Danger::where(["code" => $code])->delete();
    }
}