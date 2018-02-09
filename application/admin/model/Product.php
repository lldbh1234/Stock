<?php
namespace app\admin\model;

class Product extends \app\common\model\Product
{
    public function getIsTradeAttr($value)
    {
        $trade = [1 => '是', 0 => '否'];
        return ["value" => $value, "text" => $trade[$value]];
    }

    public function getCurrencyAttr($value)
    {
        $currency = [1 => '人民币', 0 => '美元'];
        return ["value" => $value, "text" => $currency[$value]];
    }

    public function getOnSaleAttr($value)
    {
        $sale = [1 => '上架', -1 => '下架'];
        return ["value" => $value, "text" => $sale[$value]];
    }

    public function getStateAttr($value)
    {
        $state = [1 => '开启', 0 => '关闭'];
        return ["value" => $value, "text" => $state[$value]];
    }
}