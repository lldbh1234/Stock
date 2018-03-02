<?php
namespace app\admin\logic;

use app\admin\model\Menu;

class MenuLogic
{

    public function getMenueBy($where=[])
    {
        $filter = [];
        if(isset($where['id'])) $filter['id'] = ['in', $where['id']];
        //$filter['module'] = 0;
        //获取模块
        $module = Menu::where($filter)->order('sort')->select();
        //获取模块下列表
        if(!$module) return [];
        return self::create_menu($module);

    }

    public function create_menu($menu, $pid=0){
        $menus = array();
        foreach($menu as $val){
            if($val['pid'] == $pid){
                $children = $this->create_menu($menu, $val['id']);
                $val['children'] = [];
                if($children){
                    $val['children'] = $children;
                }
                $menus[] = $val;
            }
        }
        return collection($menus)->toArray();
    }

    public function getActBy($where=[])
    {
        $filter = [];
        if(isset($where['id'])) $filter['id'] = ['in', $where['id']];
        //获取节点
        return Menu::where($filter)->column('act');

    }
    public function getChildBy($where=[])
    {
        $filter = [];
        if(isset($where['pid'])) $filter['pid'] = $where['pid'];
        if(isset($where['module'])) $filter['module'] = $where['module'];
        if(empty($filter)) return [];
        return Menu::where($filter)->order('sort')->select();
    }
    public function create($data)
    {
        $res = Menu::create($data);
        return $res ? $res->id : 0;
    }
    public function update($data)
    {
        return Menu::update($data);
    }

    public function delete($id)
    {
        return isset($id) ? Menu::destroy($id) : '';
    }
    public function getOneBy($id){
        return Menu::find($id);
    }

}