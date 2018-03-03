<?php
namespace app\admin\validate;

use think\Validate;
use app\admin\model\Stock;
use app\admin\model\AiType;

class Ai extends Validate
{
    protected $rule = [
        'id'        => 'require|gt:0',
        'ids'       => 'require|array|checkIds',
        "type_id"   => "require|gt:0|checkTypeId",
        "code"      => "require|checkCode|unique:ai,code^type_id",
        "income"    => "require|float|gt:0",
        "remark"    => "require|max:255",
        "sort"      => "number|max:255",
        "status"    => "require|in:0,1"
    ];

    protected $message = [
        'id.require'    => '系统提示：非法操作！',
        'id.min'        => '系统提示：非法操作！',
        'ids.require'   => '请选择要操作的数据！',
        'ids.array'     => '请选择要操作的数据！',
        'ids.checkIds'  => '请选择要操作的数据！',
        'type_id.require'   => '系统提示：非法操作！',
        'type_id.gt'        => '系统提示：非法操作！',
        'type_id.checkTypeId' => '系统提示：非法操作！',
        "code.require"  => "股票代码不能为空！",
        "code.checkCode"  => "股票代码不存在！",
        "code.unique"   => "股票代码已经添加！",
        "remark.require"    => "推荐理由不能为空！",
        "remark.max"        => "推荐理由最大为255个字符！",
        "income.require"    => "买入价不能为空！",
        "income.float"      => "买入价必须为数字！",
        "income.gt"         => "买入价必须大于0！",
        "sort.number"   => "排序必须为数字！",
        "sort.max"      => "排序值最大为255！",
        'status.require' => '系统提示：非法操作！',
        'status.in'     => '系统提示：非法操作！',
    ];

    protected $scene = [
        "create" => ["type_id", "code", "income", "remark", "sort", "status"],
        "modify" => [
            "id",
            "type_id",
            "code" => "require|checkCode|unique:ai,code^type_id^id",
            "income",
            "remark",
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

    protected function checkTypeId($value)
    {
        $type = AiType::find($value);
        return $type ? true : false;
    }

    protected function checkCode($value)
    {
        $stock = Stock::where(["code" => $value])->find();
        return $stock ? true : false;
    }
}