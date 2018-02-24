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

    public function createMode($data)
    {
        $res = Mode::create($data);
        $pk = model("Mode")->getPk();
        return $res ? $res->$pk : 0;
    }

    public function modeById($id)
    {
        $admin = Mode::find($id);
        return $admin ? $admin->toArray() : [];
    }

    public function updateMode($data)
    {
        return Mode::update($data);
    }

    public function deleteMode($id)
    {
        $ids = is_array($id) ? implode(",", $id) : $id;
        return Mode::destroy($ids);
    }

    public function pageModeDeposits($modeId, $pageSize = null)
    {
        $lists = [];
        $pages = null;
        $modeName = "";
        $mode = Mode::get($modeId);
        if($mode){
            $pageSize = $pageSize ? : config("page_size");
            $_lists = $mode->hasManyDeposit()->paginate($pageSize);
            $lists = $_lists->toArray();
            $pages = $_lists->render();
            $modeName = $mode->name;
        }
        return ["lists" => $lists, "pages" => $pages, "name" => $modeName];
    }
}