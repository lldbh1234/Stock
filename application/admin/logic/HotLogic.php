<?php
namespace app\admin\logic;

use app\admin\model\Hot;

class HotLogic
{
    public function pageHotLists($pageSize = null)
    {
        $pageSize = $pageSize ? : config("page_size");
        $lists = Hot::order("sort")->paginate($pageSize);
        return ["lists" => $lists->toArray(), "pages" => $lists->render()];
    }

    public function createHot($data)
    {
        $res = Hot::create($data);
        $pk = model("Hot")->getPk();
        return $res ? $res->$pk : 0;
    }

    public function hotById($id)
    {
        $hot = Hot::find($id);
        return $hot ? $hot->toArray() : [];
    }

    public function updateHot($data)
    {
        return Hot::update($data);
    }

    public function deleteHot($id)
    {
        $ids = is_array($id) ? implode(",", $id) : $id;
        return Hot::destroy($ids);
    }
}