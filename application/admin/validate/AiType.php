<?php
namespace app\admin\validate;

use think\Validate;

class AiType extends Validate
{
    protected $rule = [
        'id'        => 'require|gt:0',
        'ids'       => 'require|array|checkIds',
        "type_id"   => "require|gt:0",
        "name"      => "require|unique:ai_type|max:64",
        "sort"      => "number|max:255",
        "status"    => "require|in:0,1"
    ];

    protected $message = [
        'id.require'    => '系统提示：非法操作！',
        'id.min'        => '系统提示：非法操作！',
        'ids.require'   => '请选择要操作的数据！',
        'ids.array'     => '请选择要操作的数据！',
        'ids.checkIds'  => '请选择要操作的数据！',
        'type_id.require' => '系统提示：非法操作！',
        'type_id.gt'    => '系统提示：非法操作！',
        "name.require"  => "推荐类型名称不能为空！",
        "name.unique"   => "推荐类型名称已经存在！",
        "name.max"      => "推荐类型名称最大64个字符！",
        "sort.number"   => "排序必须为数字！",
        "sort.max"      => "排序值最大为255！",
        'status.require' => '系统提示：非法操作！',
        'status.in'     => '系统提示：非法操作！',
    ];

    protected $scene = [
        "create" => ["name", "sort", "status"],
        "modify" => [
            "type_id",
            "name" => "require|unique:ai_type,name^type_id|max:64",
            "sort",
            "status"
        ],
        'remove' => ['id'],
        'patch'  => ['ids'],
    ];

    protected function checkIds($value)
    {
        return count($value) > 0;
    }
}