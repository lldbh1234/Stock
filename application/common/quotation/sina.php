<?php
namespace app\common\quotation;

class sina
{
    const REAL_REQUEST_URL = "http://hq.sinajs.cn/list=%s";

    public function real($code)
    {
        $codes = $this->_handleStockCodes($code);
        $requestUrl = sprintf(self::REAL_REQUEST_URL, $codes);
        //$response = $this->sinaCurl($requestUrl);
        $response = file_get_contents($requestUrl);
        $response = iconv("GB2312", "UTF-8", $response);
        $response = str_replace(["\r\n", "\n", "\r", " "], "", $response);
        $_response = [];
        if($response){
            $stocks = explode(';', $response);
            foreach ($stocks as $stock){
                if($stock){
                    preg_match('/^varhq_str_([sh|sz]{2})(\d{6})="(.*)"/i', $stock, $match);
                    if($match[3]){
                        $_data = explode(",", $match[3]);
                        $_response[$match[2]] = [
                            "code"      => $match[2], // 股票代码
                            "prod_name" => $_data[0], // 股票名字
                            "last_px"   => round($_data[3], 2), // 当前价格
                            "open_px"   => round($_data[1], 2), // 今日开盘价
                            "preclose_px" => round($_data[2], 2), // 昨日收盘价
                            "high_px"   => round($_data[4], 2), // 今日最高价
                            "low_px"    => round($_data[5], 2), // 今日最低价
                            "px_change" => round($_data[3] - $_data[2], 2), //涨跌金额
                            "px_change_rate" => round(($_data[3] - $_data[2]) / $_data[2] * 100, 2), //涨跌幅
                            "buy_px"    => round($_data[6], 2), // 竞买价，即“买一”报价
                            "sell_px"   => round($_data[7], 2), // 竞卖价，即“卖一”报价
                            "business_amount" => $_data[8], // 成交的股票数，由于股票交易以一百股为基本单位，所以在使用时，通常把该值除以一百
                            "business_balance" => $_data[9], // 成交金额，单位为“元”，为了一目了然，通常以“万元”为成交金额的单位，所以通常把该值除以一万
                            "bid_grp" => [
                                $_data[10], round($_data[11], 2), "0", // “买一”手数 // “买一”报价
                                $_data[12], round($_data[13], 2), "0", // “买二”手数 // “买二”报价
                                $_data[14], round($_data[15], 2), "0", // “买三”手数 // “买三”报价
                                $_data[16], round($_data[17], 2), "0", // “买四”手数 // “买四”报价
                                $_data[18], round($_data[19], 2), "0" // “买五”手数 // “买五”报价
                            ],
                            "sell_grp" => [
                                $_data[20], round($_data[21], 2), "0", // “卖一”手数 // “卖一”报价
                                $_data[22], round($_data[23], 2), "0", // “卖二”手数 // “卖二”报价
                                $_data[24], round($_data[25], 2), "0", // “卖三”手数 // “卖三”报价
                                $_data[26], round($_data[27], 2), "0", // “卖四”手数 // “卖四”报价
                                $_data[28], round($_data[29], 2), "0" // “卖五”手数 // “卖五”报价
                            ],
                            "amplitude" => round(abs($_data[4] - $_data[5]) / $_data[2] * 100, 2), // 振幅
                            "data_datestamp" => str_replace("-", "", $_data[30]),
                            "data_timestamp" => str_replace(":", "", $_data[31]) . "000"
                        ];
                    }
                }
            }
        }
        return $_response;
    }

    public function sinaCurl($uri)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 500);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_URL, $uri);
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            //$errno = curl_error($ch);
            curl_close($ch);
            //return ['errno' => $errno];
            return false;
        } else {
            curl_close($ch);
            return $response;
        }
    }

    private function _handleStockCodes($codes){
        if($codes){
            if(is_array($codes)){
                $codes = implode(',', $codes);
            }
        }
        return $codes;
    }
}