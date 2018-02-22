<?php
namespace app\admin\validate;

use think\Validate;

class Role extends Validate
{
    protected $rule = [
        'name'      =>  'require|unique:role|length:1,64',
        'remark'    =>  'length:0,100',
        'show'      =>	'require|in:0,1',
    ];

    protected $message = [
        'name.require'  =>	'角色名称不能为空！',
        'name.unique'   =>	'角色名称已存在！',
        'name.length'   =>	'角色名称最大64个字符！',
        'remark.length' =>	'角色描述最大100个字符！',
        'show.require'  =>	'非法操作！',
        'show.in'       =>	'非法操作！',
    ];

    protected $scene = [
        'create'  =>  ['name', 'remark', 'show'],
    ];
}