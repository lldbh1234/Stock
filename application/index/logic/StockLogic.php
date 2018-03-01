<?php
namespace app\index\logic;

use app\common\libraries\api51;
use app\index\model\Stock;

class StockLogic
{
    protected $_library;
    public function __construct()
    {
        $this->_library = new api51();
    }

    public function stockByCode($code)
    {
        $stock = Stock::where(["code" => $code])->find();
        return $stock ? $stock->toArray() : [];
    }

    public function simpleData($codes)
    {
        $codes = $this->_fullCodeByCodes($codes);
        $codes = $this->_handleCodes($codes);
        $code = implode(',', $codes);
        $fields = 'prod_name,last_px,px_change,px_change_rate';
        $response = $this->_library->realtime($code, $fields);
        if($response){
            $_resp = [];
            $data = $response['data']['snapshot'];
            $fields = $data['fields'];
            foreach ($data as $key=>$val){
                if($key != 'fields'){
                    $_temp = [];
                    $_temp['code'] = substr($key, 0, 6);
                    foreach($fields as $k=>$v){
                        $_temp[$v] = $val[$k];
                    }
                    $_resp[$_temp['code']] = $_temp;
                }
            }
            return $_resp;
        }
        return [];
    }

    public function realTimeData($codes)
    {
        $codes = $this->_fullCodeByCodes($codes);
        $codes = $this->_handleCodes($codes);
        $code = implode(',', $codes);
        $response = $this->_library->realtime($code);
        if($response){
            $_resp = [];
            $data = $response['data']['snapshot'];
            $fields = $data['fields'];
            foreach ($data as $key=>$val){
                if($key != 'fields'){
                    $_temp = [];
                    $_temp['code'] = substr($key, 0, 6);
                    foreach($fields as $k=>$v){
                        if($v == 'offer_grp' || $v == 'bid_grp'){
                            $_array = explode(',', $val[$k]);
                            array_pop($_array);
                            $_temp[$v] = $_array;
                        }else{
                            $_temp[$v] = $val[$k];
                        }
                    }
                    $_resp[] = $_temp;
                }
            }
            return $_resp;
        }
        return [];
    }

    private function _fullCodeByCodes($codes)
    {
        return Stock::where(["code" => ["IN", $codes]])->column("full_code");
    }

    private function _handleCodes($codes = [])
    {
        if(is_array($codes)){
            array_filter($codes, function(&$item){
                preg_match('/^([sh|sz]{2})(\d{6})/i', $item, $_match);
                if($_match){
                    if($_match[1] == 'sh'){
                        $item = "{$_match[2]}.SS";
                    }elseif($_match[1] == 'sz'){
                        $item = "{$_match[2]}.SZ";
                    }
                }
            });
        }
        return $codes;
    }
}