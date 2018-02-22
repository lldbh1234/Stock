<?php
namespace app\admin\logic;

use app\admin\model\Role;

class AdminLogic
{
    public function pageRoleLists($pageSize = null)
    {
        $pageSize = $pageSize ? : config("page_size");
        $lists = Role::paginate($pageSize);
        return ["lists" => $lists->toArray(), "pages" => $lists->render()];
    }

    public function roleCreate($data)
    {
        $res = Role::create($data);
        return $res ? $res->id : 0;
    }

    public function roleDelete($id)
    {
        $ids = is_array($id) ? implode(",", $id) : $id;
        return Role::destroy($ids);
    }

    public function roleById($id)
    {
        $role = Role::find($id);
        return $role ? $role->toArray() : [];
    }

    public function roleUpdate($data)
    {
        return Role::update($data);
    }
}