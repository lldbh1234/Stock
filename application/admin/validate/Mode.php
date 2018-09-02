<?php
namespace app\admin\validate;

use app\admin\logic\DepositLogic;
use app\admin\logic\LeverLogic;
use app\admin\logic\ModeLogic;
use app\admin\model\Plugins;
use app\admin\model\Product;
use think\Validate;

class Mode extends Validate
{
    protected $rule = [
        'id'        => 'require|gt:0|checkId',
        'ids'       => 'require|array|checkIds',
        "mode_id"   => "require|gt:0",
        "name"      => "require|unique:mode|max:32",
        "product_id"    => "require|checkProduct",
        "plugins_code"  => "require|checkCode",
        "free"      => "require|number|gt:0|elt:365",
        "jiancang"  => "require|float|gt:0",
        "defer"     => "require|float|gt:0",
        "profit"    => "require|float|gt:0|elt:100",
        "loss"      => "require|float|gt:0|elt:100",
        "point"     => "require|float|elt:100",
        "sort"      => "number|elt:255",
        "status"    => "require|in:0,1",
        "deposit"   => "array|checkDeposit",
        "lever"     => "array|checkLever"
    ];

    protected $message = [
        'id.require'    => '系统提示：非法操作！',
        'id.min'        => '系统提示：非法操作！',
        'id.checkId'    => '系统提示：非法操作！',
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
        "free.elt"      => "免息期最大365天！",
        "jiancang.require"  => "建仓费不能为空！",
        "jiancang.float"    => "建仓费必须为数字！",
        "jiancang.gt"       => "建仓费必须大于0！",
        "defer.require" => "递延费不能为空！",
        "defer.float"   => "递延费必须为数字！",
        "defer.gt"      => "递延费必须大于0！",
        "profit.require" => "最小止盈不能为空！",
        "profit.float"   => "最小止盈必须为数字！",
        "profit.gt"      => "最小止盈必须大于0！",
        "profit.elt"      => "最小止盈最大100%！",
        "loss.require" => "最小止损不能为空！",
        "loss.float"   => "最小止损必须为数字！",
        "loss.gt"      => "最小止损必须大于0！",
        "loss.elt"      => "最小止损最大100%！",
        "point.require" => "盈利抽成不能为空！",
        "point.float"   => "盈利抽成必须为数字！",
        "point.elt"      => "盈利抽成最大100%！",
        "sort.number"   => "排序必须为数字！",
        "sort.elt"      => "排序值最大为255！",
        'status.require'    => '系统提示：非法操作！',
        'status.in'         => '系统提示：非法操作！',
        'deposit.array'     => '系统提示：非法操作！',
        'deposit.checkDeposit' => '系统提示：非法操作！',
        'lever.array'       => '系统提示：非法操作！',
        'lever.checkLever'  => '系统提示：非法操作！',
    ];

    protected $scene = [
        "create" => ["name", "product_id", "plugins_code", "free", "jiancang", "defer", "profit", "loss", 'point', "sort", "status"],
        "modify" => [
            "mode_id",
            "name" => "require|unique:mode,name^mode_id|max:32",
            "product_id",
            "plugins_code",
            "free",
            "jiancang",
            "defer",
            "profit",
            "loss",
            'point',
            "sort",
            "status"
        ],
        'remove' => ['id'],
        'patch'  => ['ids'],
        'setDeposit' => ['id', 'deposit'],
        'setLever' => ['id', 'lever'],
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

    protected function checkId($value)
    {
        $mode = (new ModeLogic())->modeById($value);
        return $mode ? true : false;
    }

    protected function checkDeposit($value)
    {
        $deposits = (new DepositLogic())->depositLists();
        $depositIds = array_column($deposits, 'id');
        foreach ($value as $item){
            if(!in_array($item, $depositIds)){
                return false;
            }
        }
        return true;
    }

    protected function checkLever($value)
    {
        $levers = (new LeverLogic())->leverLists();
        $leverIds = array_column($levers, 'id');
        foreach ($value as $item){
            if(!in_array($item, $leverIds)){
                return false;
            }
        }
        return true;
    }
}