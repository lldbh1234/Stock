<?php
namespace app\admin\logic;

use app\admin\model\AiType;

class AiLogic
{
    public function pageAiTypes($pageSize = null)
    {
        $pageSize = $pageSize ? : config("page_size");
        $lists = AiType::order("sort")->paginate($pageSize);
        return ["lists" => $lists->toArray(), "pages" => $lists->render()];
    }

    public function createAiType($data)
    {
        $res = AiType::create($data);
        $pk = model("AiType")->getPk();
        return $res ? $res->$pk : 0;
    }

    public function aiTypeById($id)
    {
        $type = AiType::find($id);
        return $type ? $type->toArray() : [];
    }

    public function updateAiType($data)
    {
        return AiType::update($data);
    }

    public function deleteAiType($id)
    {
        $ids = is_array($id) ? implode(",", $id) : $id;
        return AiType::destroy($ids);
    }
}