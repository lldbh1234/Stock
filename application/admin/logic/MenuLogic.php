<?php
namespace app\admin\logic;

use app\admin\model\Menu;

class MenuLogic
{

    public function getMenueBy($where=[])
    {
        $filter = [];
        if(isset($where['id'])) $filter['id'] = ['in', $where['id']];
        $filter['module'] = 0;
        //获取模块
        $module = Menu::where($filter)->order('sort')->select();
        //获取模块下列表
        if(!$module) return [];
        foreach($module as $k => $v)
        {
            $module[$k]['lists'] = self::getChildBy(['pid' => $v['id'], 'module' => 1]);//获取列表

//            if($module[$k]['lists'])
//            {
//                foreach ($module[$k]['lists'] as $key => $val)
//                {
//                    $module[$k]['lists'][$key]['act'] = self::getChildBy(['pid' => $val['id'], 'module' => 2]);//获取操作
//                }
//
//            }

        }
        return collection($module)->toArray();

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
        return Menu::where($filter)->order('sort')->select();
    }

}