<?php
namespace app\index\logic;

use app\index\model\Lever;

class LeverLogic
{
    public function allLevers()
    {
        $levers = Lever::where(["status" => 0])->order("sort,id")->select();
        return $levers ? collection($levers)->toArray() : [];
    }

    public function leverById($id)
    {
        $lever = Lever::find($id);
        return $lever ? $lever->toArray() : [];
    }
}