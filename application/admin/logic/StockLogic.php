<?php
namespace app\admin\logic;

use app\admin\model\Stock;
use app\common\libraries\api51;
use app\common\quotation\sina;

class StockLogic
{
    protected $_library;
    protected $_sinaQuotation;
    public function __construct()
    {
        $this->_library = new api51();
        $this->_sinaQuotation = new sina();
    }

    // 新浪财经行情
    public function stockQuotationBySina($code)
    {
        $codes = $this->_fullCodeByCodes($code);
        $response = $this->_sinaQuotation->real($codes);
        return $response;
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

    private function _fullCodeByCodes($codes)
    {
        return Stock::where(["code" => ["IN", $codes]])->column("full_code");
    }
}