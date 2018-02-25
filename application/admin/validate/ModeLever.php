<?php
namespace app\admin\validate;

use think\Validate;
use app\admin\model\Mode;

class ModeLever extends Validate
{
    protected $rule = [
        'id'        => 'require|gt:0',
        'ids'       => 'require|array|checkIds',
        "mode_id"   => "require|gt:0|checkMode",
        "name"      => "require|unique:mode_lever,name^mode_id|max:64",
        "multiple"  => "require|number|gt:0",
        "sort"      => "number|max:255",
        "status"    => "require|in:0,1"
    ];

    protected $message = [
        'id.require'    => '系统提示：非法操作！',
        'id.gt'         => '系统提示：非法操作！',
        'ids.require'   => '请选择要操作的数据！',
        'ids.array'     => '请选择要操作的数据！',
        'ids.checkIds'  => '请选择要操作的数据！',
        'mode_id.require'   => '系统提示：非法操作！',
        'mode_id.gt'        => '系统提示：非法操作！',
        'mode_id.checkMode' => '系统提示：非法操作！',
        "name.require"      => "杠杆名称不能为空！",
        "name.unique"       => "杠杆名称已经存在！",
        "name.max"          => "杠杆名称最大64个字符！",
        "multiple.require"  => "杠杆倍数不能为空！",
        "multiple.number"   => "杠杆倍数必须为数字！",
        "multiple.gt"       => "杠杆倍数必须大于0！",
        "sort.number"       => "排序必须为数字！",
        "sort.max"          => "排序值最大为255！",
        'status.require'    => '系统提示：非法操作！',
        'status.in'         => '系统提示：非法操作！',
    ];

    protected $scene = [
        "create" => ["mode_id", "name", "multiple", "sort", "status"],
        "modify" => [
            "id",
            "mode_id",
            "name" => "require|unique:mode_lever,name^mode_id^id|max:64",
            "multiple",
            "sort",
            "status"
        ],
        'remove' => ['id', 'mode_id'],
        'patch'  => ['ids', 'mode_id'],
    ];

    protected function checkMode($value)
    {
        $mode = Mode::find($value);
        return $mode ? true : false;
    }

    protected function checkIds($value)
    {
        return count($value) > 0;
    }
}