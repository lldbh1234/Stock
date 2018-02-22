<?php
namespace app\admin\validate;

use think\Validate;

class Role extends Validate
{
    protected $rule = [
        'id'        => 'require|min:1',
        'ids'       => 'require|array|checkIds',
        'name'      => 'require|unique:role|length:1,64',
        'remark'    => 'length:0,100',
        'show'      => 'require|in:0,1',
    ];

    protected $message = [
        'id.require'    => '系统提示：非法操作！',
        'id.min'        => '系统提示：非法操作！',
        'ids.require'   => '请选择要操作的数据！',
        'ids.array'     => '请选择要操作的数据！',
        'ids.checkIds'  => '请选择要操作的数据！',
        'name.require'  => '角色名称不能为空！',
        'name.unique'   => '角色名称已存在！',
        'name.length'   => '角色名称最大64个字符！',
        'remark.length' => '角色描述最大100个字符！',
        'show.require'  => '非法操作！',
        'show.in'       => '非法操作！',
    ];

    protected $scene = [
        'create' => ['name', 'remark', 'show'],
        'remove' => ['id'],
        'patch'  => ['ids'],
        'modify' => ['id', 'name' => 'require|unique:role,name^id|length:1,64', 'remark', 'show'],
    ];

    protected function checkIds($value)
    {
        return count($value) > 0;
    }
}