<?php
namespace app\admin\logic;

use app\admin\model\Plugins;

class PluginsLogic
{
    public function allPluginsLists()
    {
        $lists = Plugins::all();
        return $lists ? collection($lists)->toArray() : [];
    }
}