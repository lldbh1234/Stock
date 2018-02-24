<?php
namespace app\admin\logic;

use app\admin\model\Product;

class ProductLogic
{
    public function allProductLists()
    {
        $lists = Product::all();
        return $lists ? collection($lists)->toArray() : [];
    }

    public function allEnableProducts()
    {
        $lists = Product::where(["state" => 1])->select();
        return $lists ? collection($lists)->toArray() : [];
    }

    public function pageProductLists($pageSize = null)
    {
        $pageSize = $pageSize ? : config("page_size");
        $lists = Product::paginate($pageSize);
        return ["lists" => $lists->toArray(), "pages" => $lists->render()];
    }
}