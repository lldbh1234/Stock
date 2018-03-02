<?php
namespace app\admin\logic;

use app\admin\model\Stock;
use app\common\libraries\api51;

class StockLogic
{
    protected $_library;
    public function __construct()
    {
        $this->_library = new api51();
    }

    public function stockByCode($code)
    {
        return Stock::where(['code' => $code])->find();
    }

    public function stockQuotation($code){
        $realCode = "";
        $quotation = [];
        $fullCode = Stock::where(["code" => $code])->value("full_code");
        preg_match('/^([sh|sz]{2})(\d{6})/i', $fullCode, $match);
        if($match){
            if($match[1] == 'sh'){
                $realCode = "{$match[2]}.SS";
            }elseif($match[1] == 'sz'){
                $realCode = "{$match[2]}.SZ";
            }
        }
        if($realCode){
            $fields = 'prod_name,last_px,px_change,px_change_rate';
            $response = $this->_library->realtime($realCode, $fields);
            $data = @$response['data']['snapshot'][$realCode];
            $fields = @$response['data']['snapshot']['fields'];
            foreach ($fields as $key => $value){
                $quotation[$value] = $data[$key];
            }
        }
        return $quotation;
    }
}