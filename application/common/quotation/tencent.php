<?php
namespace app\common\quotation;


class tencent
{
    const REAL_REQUEST_URL = "http://web.sqt.gtimg.cn/q=%s?r=0.040982807166606294";

    public function real($code)
    {
        $codes = $this->_handleStockCodes($code);
        $requestUrl = sprintf(self::REAL_REQUEST_URL, $codes);
        $response = file_get_contents($requestUrl);
        $response = mb_convert_encoding($response, "UTF-8", "GBK");
        $response = str_replace(["\r\n", "\n", "\r", " "], "", $response);
        $_response = [];

        if($response){
            $stocks = explode(';', $response);
            foreach ($stocks as $stock){
                if($stock){
                    preg_match('/^v_([sh|sz]{2})(\d{6})="(.*)"/i', $stock, $match);
                    if($match[3]){
                        $_data = explode("~", $match[3]);
                        $_response[$match[2]] = [
                            "code"      => $match[2], // 股票代码
                            'full_code' => $match[1].$match[2],
                            "prod_name" => $_data[1], // 股票名字
                            "last_px"   => round($_data[3], 2), // 当前价格
                            "open_px"   => round($_data[5], 2), // 今日开盘价
                            "preclose_px" => round($_data[4], 2), // 昨日收盘价
                            "high_px"   => round($_data[33], 2), // 今日最高价
                            "low_px"    => round($_data[34], 2), // 今日最低价
                            "px_change" => round($_data[31], 2), //涨跌金额
                            "px_change_rate" => round($_data[32], 2), //涨跌幅
                            "buy_px"    => round($_data[9], 2), // 竞买价，即“买一”报价
                            "sell_px"   => round($_data[19], 2), // 竞卖价，即“卖一”报价
                            "business_amount" => $_data[36], // 成交的股票数，由于股票交易以一百股为基本单位，所以在使用时，通常把该值除以一百
                            "business_balance" => $_data[37] * 10000, // 成交金额，单位为“万元”，为了一目了然，通常以“万元”为成交金额的单位，所以通常把该值除以一万
                            "business_amount_in" => $_data[8], //内盘成交
                            "business_amount_out" => $_data[7], //外盘成交
                            "bid_grp" => [
                                $_data[10], round($_data[9], 2), "0", // “买一”手数 // “买一”报价
                                $_data[12], round($_data[11], 2), "0", // “买二”手数 // “买二”报价
                                $_data[14], round($_data[13], 2), "0", // “买三”手数 // “买三”报价
                                $_data[16], round($_data[15], 2), "0", // “买四”手数 // “买四”报价
                                $_data[18], round($_data[17], 2), "0" // “买五”手数 // “买五”报价
                            ],
                            "sell_grp" => [
                                $_data[20], round($_data[19], 2), "0", // “卖一”手数 // “卖一”报价
                                $_data[22], round($_data[21], 2), "0", // “卖二”手数 // “卖二”报价
                                $_data[24], round($_data[23], 2), "0", // “卖三”手数 // “卖三”报价
                                $_data[26], round($_data[25], 2), "0", // “卖四”手数 // “卖四”报价
                                $_data[28], round($_data[27], 2), "0" // “卖五”手数 // “卖五”报价
                            ],
                            "amplitude" => round($_data[43], 2), // 振幅
                            "total_value" => round($_data[45] * 10000 * 10000, 2), // 总市值
                            "pe_rate" => round($_data[39], 2), //市盈率
                            "circulation_value" => round($_data[44] * 10000 * 10000, 2),//流通市值
                            "data_datestamp" => date("Y-m-d", strtotime($_data[30])),
                            "data_timestamp" => date("H:i:s", strtotime($_data[30])) . "000",
                        ];
                    }
                }
            }
        }
        return $_response;
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