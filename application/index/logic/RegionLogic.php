<?php
namespace app\index\logic;


use app\index\model\Region;

class RegionLogic
{
    public function allProvince()
    {
        $provinces = Region::where(["level" => 1])->select();
        return $provinces ? collection($provinces)->toArray() : [];
    }

    public function regionByParentId($parentId = 0)
    {
        $regions = Region::where(["parent_id" => $parentId])->field("id,name")->select();
        return $regions ? collection($regions)->toArray() : [];
    }

    public function regionById($id)
    {
        $regions = Region::find($id);
        return $regions ? $regions->toArray() : [];
    }
}