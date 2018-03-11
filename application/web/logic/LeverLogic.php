<?php
namespace app\web\logic;

use app\web\model\Lever;

class LeverLogic
{
    public function allLevers()
    {
        $levers = Lever::where(["status" => 0])->order("sort")->limit(3)->select();
        return $levers ? collection($levers)->toArray() : [];
    }

    public function leverById($id)
    {
        $lever = Lever::find($id);
        return $lever ? $lever->toArray() : [];
    }
}