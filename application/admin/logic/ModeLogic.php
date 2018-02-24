<?php
namespace app\admin\logic;

use app\admin\model\Mode;

class ModeLogic
{
    public function pageModeLists($pageSize = null)
    {
        $pageSize = $pageSize ? : config("page_size");
        $lists = Mode::with("hasOnePlugins,hasOneProduct")->order("sort")->paginate($pageSize);
        return ["lists" => $lists->toArray(), "pages" => $lists->render()];
    }
}