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

    public function pageProductLists()
    {
        $lists = Product::paginate(1);
        return ["lists" => $lists->toArray(), "pages" => $lists->render()];
    }
}