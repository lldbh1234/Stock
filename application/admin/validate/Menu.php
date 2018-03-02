<?php
namespace app\admin\validate;

use think\Validate;

class Menu extends Validate
{
    protected $rule = [
//        'name'      => 'require|unique:menu',
        'name'      => 'require',
        'pid'       => 'require',
        'act'       => 'max:64',
        'module'    => 'require|in:0,1,2',
        'status'    => 'require|in:0,1',
        'icon'      => 'max:64',
        'sort'      => 'max:255'
    ];

    protected $message = [
        'name.require'      => '系统提示：节点名称不能为空！',
        'name.unique'       => '系统提示：节点名称已存在！',
        'pid.require'       => '系统提示：请选择父级节点！',
        'module.require'    => '请选择类型！',
        'status.require'    => '请设置状态！',
    ];

    protected $scene = [
        'create' => ['name', 'pid', 'act', 'module', 'status', 'icon', 'sort'],
        'del' => ['id'],
//        'modify' => ['id', 'name' => 'require|unique:menu,name^id|length:1,64', 'pid', 'act', 'module', 'status', 'icon', 'sort'],
        'modify' => ['id', 'name' => 'require|length:1,64', 'pid', 'act', 'module', 'status', 'icon', 'sort'],
    ];

}