<?php
namespace app\admin\logic;

use app\admin\model\Ai;
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

    public function pageAiTypeStocks($typeId, $pageSize = null)
    {
        $lists = [];
        $pages = null;
        $typeName = "";
        $mode = AiType::get($typeId);
        if($mode){
            $pageSize = $pageSize ? : config("page_size");
            $_lists = $mode->hasManyAi()->order("sort")->paginate($pageSize);
            $lists = $_lists->toArray();
            $pages = $_lists->render();
            $typeName = $mode->name;
        }
        return ["lists" => $lists, "pages" => $pages, "name" => $typeName];
    }

    public function createAi($data)
    {
        $res = Ai::create($data);
        $pk = model("Ai")->getPk();
        return $res ? $res->$pk : 0;
    }

    public function aiById($id)
    {
        $ai = Ai::find($id);
        return $ai ? $ai->toArray() : [];
    }

    public function updateAi($data)
    {
        return Ai::update($data);
    }

    public function deleteAi($id)
    {
        $ids = is_array($id) ? implode(",", $id) : $id;
        return Ai::destroy($ids);
    }
}