<?php
/**
 * Created by PhpStorm.
 * User: bruce
 * Date: 18/3/1
 * Time: 下午6:36
 */

namespace app\admin\controller;

use app\admin\logic\AccessLogic;
use app\admin\logic\MenuLogic;
use think\Db;
use think\Request;
class Permission extends Base
{
    public $menuLogic;
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        $this->menuLogic = new MenuLogic();
    }

    public function lists()
    {
        $menu = $this->menuLogic->getMenueBy();
        $this->assign('menu', $menu);
        return view();

    }
    public function add()
    {
        if(request()->isPost())
        {
            $validate = \think\Loader::validate('Menu');
            if(!$validate->scene('create')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{

                $menu_id = $this->menuLogic->create(input("post."));
                if(0 < $menu_id){
                    return $this->ok();
                } else {
                    return $this->fail("添加失败！");
                }
            }
        }
        $menu = $this->menuLogic->getMenueBy();
        $this->assign('menu', $menu);
        return view();

    }
    public function modify()
    {
        if(request()->isPost())
        {
            $validate = \think\Loader::validate('Menu');
            if(!$validate->scene('modify')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{

                if($this->menuLogic->update(input("post."))){
                    return $this->ok();
                } else {
                    return $this->fail("修改失败！");
                }
            }
        }
        $id = input('id');
        $data = $this->menuLogic->getOneBy($id);
        $menu = $this->menuLogic->getMenueBy();
        $this->assign('menu', $menu);
        $this->assign('data', $data);
        $this->assign('id', $id);
        return view();

    }
    public function del()
    {
        if(request()->isPost())
        {
            $validate = \think\Loader::validate('Menu');
            if(!$validate->scene('del')->check(input("post."))){
                return $this->fail($validate->getError());
            }else{

                if($this->menuLogic->delete(input("post."))){
                    return $this->ok();
                } else {
                    return $this->fail("删除失败！");
                }
            }
        }
    }
    public function rolePush()
    {
        $accessLogic = new AccessLogic();
        $addArr = [];
        if(request()->isPost())
        {
            $node = input('post.node/a');
            $role_id = input('post.role_id/d');

            foreach ($node as $v)
            {
                $addArr[] = [
                    'role_id' => $role_id,
                    'node_id' => $v,
                ];

            }
            if($accessLogic->rolePush($role_id, $addArr)){
                return $this->ok();
            }
            return $this->fail('授权失败');
        }

        $role_id = input('id');
        $nodeIdArr = $accessLogic->getRoleBy(['role_id' => $role_id]);
        $menu = $this->menuLogic->getMenueBy();
        $nodeStr = '';
        foreach($nodeIdArr as $v)
        {
            $nodeStr .= $v.',';
        }
        $this->assign('menu', $menu);
        $this->assign('role_id', $role_id);
        $this->assign('nodeIdArr', rtrim($nodeStr));
        return view();
    }

}