<?php
/**
 * 易源
 */
namespace app\common\libraries;

class apiYiyuan
{
    const APPID = "69788";
    const APPSECRET = "cd3f3976b5b4435882be37c81b043c07";

    public function realtime($code, $field = null){

        $paramArr = [
            'showapi_appid'         => self::APPID,
            'showapi_timestamp'     => date("YmdHis"),
            'stocks'                => $code,
            'needIndex'             => "0",
            "showapi_sign_method"   => "md5",
            "showapi_res_gzip"      => "0",
        ];
        $apiUrl = "http://route.showapi.com/131-46/?";
        $result = self::response($paramArr, $apiUrl);
        $result = json_decode($result, true);

        $response['data']['snapshot']['fields'] = [
            "data_timestamp",
            "shares_per_hand",
            "prod_code",
            "prod_name",
            "data_timestamp",
            "open_px",
            "high_px",
            "low_px",
            "last_px",
            "preclose_px",
            "business_amount",
            "business_balance",
            "offer_grp",
            "bid_grp",
            "px_change",
            "px_change_rate",
            "circulation_value",
            "pe_rate",
            "amplitude",
            "business_amount_in",
            "business_amount_out",
            "total_shares",
        ];
        foreach($result['showapi_res_body']['list'] as $item)
        {
            $response['data']['snapshot'][$code] = [
                strtotime($item['time']), 100, $item['code'],
                $item['name'], strtotime($item['time']), $item['openPrice'],
                $item['todayMax'], $item['todayMin'], $item['nowPrice'],
                $item['closePrice'], $item['tradeNum'], $item['tradeAmount'],
                'offer_grp' => $item['sell1_m'].",".$item['sell1_n'].",".$item['sell2_m'].",".$item['sell2_n'].",".$item['sell3_m'].",".$item['sell3_n'].",".$item['sell4_m'].",".$item['sell4_n'].",".$item['sell5_m'].",".$item['sell5_n'],
                'bid_grp' => $item['buy1_m'].",".$item['buy1_n'].",".$item['buy2_m'].",".$item['buy2_n'].",".$item['buy3_m'].",".$item['buy3_n'].",".$item['buy4_m'].",".$item['buy4_n'].",".$item['buy5_m'].",".$item['buy5_n'],
                $item['diff_money'], $item['diff_rate'], $item['circulation_value'], $item['pe'],
                $item['swing'], $item['diff_rate'], $item['circulation_value'], $item['pe'],
                0, 0, $item['totalcapital'],
            ];

        }
        return $response;
    }
    public function kline($code, $period = 6, $count = 50, $type = 'offset')
    {
        switch ($period)
        {
            case 6:
                $type = "day";
                break;
            case 7:
                $type = "week";
                break;
            case 8:
                $type = "month";
                break;
            default:
                $type = "day";
                break;
        }
        $paramArr = [
            'showapi_appid'         => self::APPID,
            "showapi_sign_method"   => "md5",
            "showapi_res_gzip"      => "0",
            "code"                  => $code,
            "time"                  => $type,//5 = 5分k线(默认) 30 = 30分k线 60 = 60分k线 day = 日k线 week = 周k线 month = 月k线
            "beginDay"              => strtotime("-{$count} day", date("Ymd")),//开始时间，格式为yyyyMMdd，如果不写则默认是 当天。结束时间永远是当前时间
            "type"                  => "bfq",//复权方式，支持两种方式不复权和前复权。bfq =不复权(默认方式) qfq =前复权
        ];

        $apiUrl = "http://route.showapi.com/131-50/?";
        $req = self::response($paramArr, $apiUrl);
        $_result = json_decode($req, true);
        $result = $_result['showapi_res_body']['dataList'];
        $result = array_slice($result, count($result)-60,60,false);

        $response['data']['candle']['fields'] = [
            "min_time",
            "open_px",
            "high_px",
            "low_px",
            "close_px",
            "business_amount",
            "business_balance",
            "rsi_6",
            "rsi_12",
            "rsi_24",
            "c_px_change",
            "c_px_change_percent",
        ];
        foreach($result as $item)
        {
            $response['data']['candle'][$code.".".strtoupper($_result['showapi_res_body']['market'])][] = [
                strtotime(date("Hi"), $item['time']), $item['open'], $item['max'], $item['min'], $item['close'],
                $item['volumn'], 0, 0, 0, 0, 0, 0,
            ];

        }
        return $response;

    }

    public function trend($code, $crc = '', $min = '')
    {
        $paramArr = [
            'showapi_appid'         => self::APPID,
            'showapi_timestamp'     => date("YmdHis"),
            "showapi_sign_method"   => "md5",
            "showapi_res_gzip"      => "0",
            'day'                   => 1,
            'code'                  => $code,
        ];
        $apiUrl = "http://route.showapi.com/131-49/?";
        $req = self::response($paramArr, $apiUrl);
        $_result = json_decode($req, true);
        $result = $_result['showapi_res_body']['dataList'];

        $response['data']['trend']['fields'] = [
            "min_time",
            "last_px",
            "avg_px",
            "business_amount",
            "business_balance",
        ];
        $response['data']['trend']['crc'] = [
            $code.".".strtoupper($_result['showapi_res_body']['market']) => $result[0]['date'],
        ];
//        dump($result[0]['minuteList']);die();
        foreach($result[0]['minuteList'] as $item)
        {
            $response['data']['trend'][$code.".".strtoupper($_result['showapi_res_body']['market'])][] = [
                strtotime($result[0]['date'].$item['time']), $item['nowPrice'], $item['avgPrice'], $item['volume'], $item['volume']*$item['avgPrice'],
            ];

        }
        return $response;
    }

    //创建参数(包括签名的处理)
    function createParam($paramArr) {
        $paraStr = "";
        $signStr = "";
        ksort($paramArr);
        foreach ($paramArr as $key => $val) {
            if ($key != '' && $val != '') {
                $signStr .= $key.$val;
                $paraStr .= $key.'='.urlencode($val).'&';
            }
        }
        $signStr .= self::APPSECRET;//排好序的参数加上secret,进行md5
        $sign = strtolower(md5($signStr));
        $paraStr .= 'showapi_sign='.$sign;//将md5后的值作为参数,便于服务器的效验
        return $paraStr;
    }
    public function response($paramArr, $apiUrl)
    {
        $param = self::createParam($paramArr);
        $url = $apiUrl.$param;
        $result = file_get_contents($url);
        return $result;

    }


}