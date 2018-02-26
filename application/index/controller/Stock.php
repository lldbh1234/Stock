<?php
namespace app\index\controller;

use think\Controller;
use think\Db;

class Stock extends Controller
{
    public function grabStockLists()
    {
        $_arrays = [];
        $_jsTextIndex = 0;
        $_jsTextArrays = [];
        $_jsText = "var stocks=new Array();";
        $_jsPath = "./static/js/stock.js";
        $url = 'http://money.finance.sina.com.cn/d/api/openapi_proxy.php/?__s=[["hq","hs_a","",0,1,80]]&callback=FDC_DC.theTableData';
        $html = file_get_contents($url);
        $json = substr($html, 70, -3);
        $array = json_decode($json, true);
        $total = $array["count"];
        $count = ceil($total / 80);
        foreach ($array['items'] as $item){
            $_arrays[] = [
                "full_code"	=> $item[0],
                "code"  => $item[1],
                "name"  => $item[2],
            ];
            $_jsTextArrays[] = "stocks[". $_jsTextIndex ."]=new Array('','" . $item[1] . "','" . $item[2] . "','" . $item[0] . "'); ";
            $_jsTextIndex++;
        }
        for ($i = 2; $i <= $count; $i++){
            $_url = 'http://money.finance.sina.com.cn/d/api/openapi_proxy.php/?__s=[["hq","hs_a","",0,'.$i.',80]]&callback=FDC_DC.theTableData';
            $_html = file_get_contents($_url);
            $_json = substr($_html, 70, -3);
            $_array = json_decode($_json, true);
            foreach ($_array['items'] as $_item){
                $_arrays[] = [
                    "full_code"	=> $_item[0],
                    "code"  => $_item[1],
                    "name"  => $_item[2],
                ];
                $_jsTextArrays[] = "stocks[". $_jsTextIndex ."]=new Array('','" . $_item[1] . "','" . $_item[2] . "','" . $_item[0] . "'); ";
                $_jsTextIndex++;
            }
        }
        $_jsText .= implode('', $_jsTextArrays);
        Db::startTrans();
        try{
            model("Lists")->query("truncate table stock_list");
            model("Lists")->saveAll($_arrays);
            @file_put_contents($_jsPath, $_jsText);
            // 提交事务
            Db::commit();
            echo "ok";
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            echo "false";
        }
    }
}