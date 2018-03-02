<?php
namespace app\index\logic;


use app\index\model\Mode;

class ModeLogic
{
    public function productModes($productId = 1)
    {
        $modes = Mode::with("hasManyDeposit,hasManyLever")->where(["product_id" => $productId])->select();
        return $modes ? collection($modes)->toArray() : [];
    }
}