<?php
namespace app\admin\validate;

use app\admin\model\Plugins;
use app\admin\model\Product;
use think\Validate;

class Mode extends Validate
{
    protected $rule = [
        'id'        => 'require|gt:0',
        'ids'       => 'require|array|checkIds',
        "mode_id"   => "require|gt:0",
        "name"      => "require|unique:mode|max:32",
        "product_id"    => "require|checkProduct",
        "plugins_code"  => "require|checkCode",
        "free"      => "require|number|gt:0|max:365",
        "jiancang"  => "require|float|gt:0",
        "defer"     => "require|float|gt:0",
        "sort"      => "number|max:255",
        "status"    => "require|in:0,1"
    ];

    protected $message = [
        'id.require'    => '系统提示：非法操作！',
        'id.min'        => '系统提示：非法操作！',
        'ids.require'   => '请选择要操作的数据！',
        'ids.array'     => '请选择要操作的数据！',
        'ids.checkIds'  => '请选择要操作的数据！',
        'mode_id.require' => '系统提示：非法操作！',
        'mode_id.gt'    => '系统提示：非法操作！',
        "name.require"  => "模式名称不能为空！",
        "name.unique"   => "模式名称已经存在！",
        "name.max"      => "模式名称最大32个字符！",
        "product_id.require"    => "请选择所属产品！",
        "product_id.checkProduct" => "请选择所属产品！",
        "plugins_code.require"  => "请选择模式类型！",
        "plugins_code.checkCode" => "请选择模式类型！",
        "free.require"  => "免息期不能为空！",
        "free.number"   => "免息期必须为数字！",
        "free.gt"       => "免息期必须大于0！",
        "free.max"      => "免息期最大365天！",
        "jiancang.require"  => "建仓费不能为空！",
        "jiancang.float"    => "建仓费必须为数字！",
        "jiancang.gt"       => "建仓费必须大于0！",
        "defer.require" => "递延费不能为空！",
        "defer.float"   => "递延费必须为数字！",
        "defer.gt"      => "递延费必须大于0！",
        "sort.number"   => "排序必须为数字！",
        "sort.max"      => "排序值最大为255！",
        'status.require'    => '系统提示：非法操作！',
        'status.in'         => '系统提示：非法操作！',
    ];

    protected $scene = [
        "create" => ["name", "product_id", "plugins_code", "free", "jiancang", "defer", "sort", "status"],
        "modify" => [
            "mode_id",
            "name" => "require|unique:mode,name^mode_id|max:32",
            "product_id",
            "plugins_code",
            "free",
            "jiancang",
            "defer",
            "sort",
            "status"
        ],
        'remove' => ['id'],
        'patch'  => ['ids'],
    ];

    public function checkProduct($value)
    {
        $product = Product::find($value);
        return $product ? true : false;
    }

    public function checkCode($value)
    {
        $_where = ["type" => "mode", "code" => $value];
        $plugins = Plugins::where($_where)->find();
        return $plugins ? true : false;
    }

    protected function checkIds($value)
    {
        return count($value) > 0;
    }
}