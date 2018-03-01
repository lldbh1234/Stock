<?php
namespace app\index\logic;


use app\index\model\AiType;

class AiLogic
{
    public function aiTypeLists()
    {
        $lists = AiType::with(
                    [
                        "hasManyAi" => function($query){
                            $query->where(["status" => 0])->order("sort");
                        }
                    ]
                )->where(["status" => 0])->order("sort")->select();
        return $lists ? collection($lists)->toArray() : [];
    }
}