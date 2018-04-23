<?php
namespace app\index\logic;


use app\index\model\Best;

class BestLogic
{
    public function saveBest($data)
    {
        $best = Best::find($data['order_id']);
        if($best){
            return Best::update($data);
        }else{
            return Best::create($data);
        }
    }
}