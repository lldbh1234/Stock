<?php
namespace app\index\logic;

use app\index\model\Lever;

class LeverLogic
{
    public function allLevers()
    {
        $levers = Lever::where(["status" => 0])->order("sort")->limit(3)->select();
        return $levers ? collection($levers)->toArray() : [];
    }
}