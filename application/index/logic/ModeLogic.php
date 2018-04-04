<?php
namespace app\index\logic;

use app\index\model\Mode;

class ModeLogic
{
    public function productModes($productId = 1)
    {
        $modes = Mode::where(["product_id" => $productId, "status" => 0])->order("sort")->select();
        return $modes ? collection($modes)->toArray() : [];
    }

    public function modeById($id)
    {
        $mode = Mode::find($id);
        return $mode ? $mode->toArray() : [];
    }

    public function modeIncPluginsById($id)
    {
        $mode = Mode::with("hasOnePlugins")->find($id);
        return $mode ? $mode->toArray() : [];
    }
}