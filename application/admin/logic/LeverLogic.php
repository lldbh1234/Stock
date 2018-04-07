<?php
namespace app\admin\logic;

use app\admin\model\Lever;

class LeverLogic
{
    public function pageLeverLists($pageSize = null)
    {
        $pageSize = $pageSize ? : config("page_size");
        $lists = Lever::order("sort,id")->paginate($pageSize);
        return ["lists" => $lists->toArray(), "pages" => $lists->render()];
    }

    public function leverLists()
    {
        $lists = Lever::where(["status" => 0])->order("sort,id")->select();
        return $lists ? collection($lists)->toArray() : [];
    }

    public function createLever($data)
    {
        $res = Lever::create($data);
        $pk = model("Lever")->getPk();
        return $res ? $res->$pk : 0;
    }

    public function leverById($id)
    {
        $lever = Lever::find($id);
        return $lever ? $lever->toArray() : [];
    }

    public function updateLever($data)
    {
        return Lever::update($data);
    }

    public function deleteLever($id)
    {
        $ids = is_array($id) ? implode(",", $id) : $id;
        return Lever::destroy($ids);
    }
}