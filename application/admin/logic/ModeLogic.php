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
            $_lists = $mode->hasManyDeposit()->order("sort")->paginate($pageSize);
            $lists = $_lists->toArray();
            $pages = $_lists->render();
            $modeName = $mode->name;
        }
        return ["lists" => $lists, "pages" => $pages, "name" => $modeName];
    }

    public function createModeDeposit($modeId, $data)
    {
        $mode = Mode::find($modeId);
        if($mode){
            $res = $mode->hasManyDeposit()->save($data);
            $pk = model("ModeDeposit")->getPk();
            return $res ? $res->$pk : 0;
        }
        return 0;
    }

    public function modeDepositById($modeId, $id)
    {
        $res = Mode::find($modeId)->hasManyDeposit()->find($id);
        return $res ? $res->toArray() : [];
    }

    public function updateModeDeposit($modeId, $id, $data)
    {
        $mode = Mode::find($modeId);
        if($mode){
            return $mode->hasManyDeposit()->find($id)->save($data);
        }
        return false;
    }

    public function deleteModeDeposit($modeId, $id)
    {
        $ids = is_array($id) ? implode(",", $id) : $id;
        return Mode::find($modeId)->hasManyDeposit()->where(["id" => ["IN", $ids]])->delete();
    }

    public function pageModeLevers($modeId, $pageSize = null)
    {
        $lists = [];
        $pages = null;
        $modeName = "";
        $mode = Mode::get($modeId);
        if($mode){
            $pageSize = $pageSize ? : config("page_size");
            $_lists = $mode->hasManyLever()->order("sort")->paginate($pageSize);
            $lists = $_lists->toArray();
            $pages = $_lists->render();
            $modeName = $mode->name;
        }
        return ["lists" => $lists, "pages" => $pages, "name" => $modeName];
    }

    public function createModeLever($modeId, $data)
    {
        $mode = Mode::find($modeId);
        if($mode){
            $res = $mode->hasManyLever()->save($data);
            $pk = model("ModeLever")->getPk();
            return $res ? $res->$pk : 0;
        }
        return 0;
    }

    public function modeLeverById($modeId, $id)
    {
        $res = Mode::find($modeId)->hasManyLever()->find($id);
        return $res ? $res->toArray() : [];
    }

    public function updateModeLever($modeId, $id, $data)
    {
        $mode = Mode::find($modeId);
        if($mode){
            return $mode->hasManyLever()->find($id)->save($data);
        }
        return false;
    }

    public function deleteModeLever($modeId, $id)
    {
        $ids = is_array($id) ? implode(",", $id) : $id;
        return Mode::find($modeId)->hasManyLever()->where(["id" => ["IN", $ids]])->delete();
    }
}