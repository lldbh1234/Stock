<?php
namespace app\index\logic;

use app\index\model\Stock;
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

    public function stockByCode($code)
    {
        $stock = Stock::where(["code" => $code])->find();
        return $stock ? $stock->toArray() : [];
    }

    // 新浪财经行情
    public function quotationBySina($codes){
        $codes = $this->_fullCodeByCodes($codes);
        return $this->_sinaQuotation->real($codes);
    }

    public function quotationByAli($codes)
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

    public function simpleData($codes)
    {
        $codes = $this->_fullCodeByCodes($codes);
        $response = [];
        if($codes){
            $result = $this->_sinaQuotation->real($codes);
            foreach ($result as $key => $value){
                $response[$key] = [
                    "code"      => $value['code'],
                    "full_code"      => $value['full_code'],
                    "data_timestamp" => $value['data_timestamp'],
                    "shares_per_hand" => 100,
                    "prod_name" => $value['prod_name'],
                    "last_px"   => $value['last_px'],
                    "buy_px"   => $value['buy_px'],
                    "sell_px"   => $value['sell_px'],
                    "px_change" => $value['px_change'],
                    "px_change_rate" => $value['px_change_rate']
                ];
            }
        }
        return $response;
        /*$codes = $this->_handleCodes($codes);
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
        return [];*/
    }

    public function klineData($code, $period = 6, $count = 50)
    {
        $code = $this->_fullCodeByCodes($code);
        $code = reset($code);
        $code = $this->_handleCodes($code);
        if($code){
            $period = in_array($period, [6,7,8]) ? $period : 6;
            $response = $this->_library->kline($code, $period, $count);
            if($response && isset($response['data']['candle'])){
                $_resp = [];
                $data = $response['data']['candle'];
                $fields = $data['fields'];
                $kline = $data[$code];
                foreach ($kline as $item){
                    $_temp = [];
                    foreach($fields as $k=>$v){
                        $_temp[$v] = $item[$k];
                    }
                    $_resp[] = $_temp;
                }
                return $_resp;
            }
        }
        return [];
    }

    public function realData($code, $crc='', $min='')
    {
        $code = $this->_fullCodeByCodes($code);
        $code = reset($code);
        $code = $this->_handleCodes($code);
        $min_date = $min ? date("Hi", strtotime($min)) : [];
        if($code){
            try{
                $_response = [];
                $real = $this->_library->realtime($code);
                $realFields = $real['data']['snapshot']['fields'];
                $realData = $real['data']['snapshot'][$code];
                foreach ($realFields as $key=>$val){
                    if($val == 'offer_grp' || $val == 'bid_grp'){
                        $_array = explode(',', $realData[$key]);
                        array_pop($_array);
                        $_response[$val] = $_array;
                    }else{
                        $_response[$val] = $realData[$key];
                    }
                }
                $trend = $this->_library->trend($code, $crc, $min_date);
                $trendFields = $trend['data']['trend']['fields'];
                $trendCrc = $trend['data']['trend']['crc'][$code];
                $trendData = $trend['data']['trend'][$code];
                $_response['trend_crc'] = $trendCrc;
                $_response['trend'] = [];
                foreach ($trendData as $item){
                    if($item[0] > $min){
                        $_temp = [];
                        foreach($trendFields as $k=>$v){
                            $_temp[$v] = $item[$k];
                        }
                        $_response['trend'][] = $_temp;
                    }
                }
                return $_response;
            }catch (\Exception $e){
                return [];
            }
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
        }elseif (!empty($codes)){
            preg_match('/^([sh|sz]{2})(\d{6})/i', $codes, $match);
            if($match){
                if($match[1] == 'sh'){
                    $codes = "{$match[2]}.SS";
                }elseif($match[1] == 'sz'){
                    $codes = "{$match[2]}.SZ";
                }
            }
        }
        return $codes;
    }
}
