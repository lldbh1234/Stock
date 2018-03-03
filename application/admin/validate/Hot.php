<?php
namespace app\admin\validate;

use think\Validate;
use app\admin\logic\StockLogic;

class Hot extends Validate
{
    protected $rule = [
        'id'        => 'require|gt:0',
        'ids'       => 'require|array|checkIds',
        "code"      => "require|checkCode|unique:hot",
        "sort"      => "number|max:255",
        "status"    => "require|in:0,1"
    ];

    protected $message = [
        'id.require'    => '系统提示：非法操作！',
        'id.min'        => '系统提示：非法操作！',
        'ids.require'   => '请选择要操作的数据！',
        'ids.array'     => '请选择要操作的数据！',
        'ids.checkIds'  => '请选择要操作的数据！',
        "code.require"  => "股票代码不能为空！",
        "code.checkCode"  => "股票代码不存在！",
        "code.unique"   => "股票代码已经添加！",
        "sort.number"   => "排序必须为数字！",
        "sort.max"      => "排序值最大为255！",
        'status.require' => '系统提示：非法操作！',
        'status.in'     => '系统提示：非法操作！',
    ];

    protected $scene = [
        "create" => ["code", "sort", "status"],
        "modify" => [
            "id",
            "code" => "require|checkCode|unique:hot,code^id",
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

    protected function checkCode($value)
    {
        $stock = (new StockLogic())->stockByCode($value);
        return $stock ? true : false;
    }
}