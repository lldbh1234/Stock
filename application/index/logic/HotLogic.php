<?php
namespace app\index\logic;


use app\index\model\Hot;

class HotLogic
{
    public function allHots()
    {
        $hots = Hot::where(["status" => 0])->order("sort")->limit(3)->select();
        return $hots ? collection($hots)->toArray() : [];
    }
}