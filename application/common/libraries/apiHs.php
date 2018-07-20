<?php
/**
 * 恒生GTN
 */

namespace app\common\libraries;

class apiHs
{
	const APP_KEY           = "0d5b55e6-a7d8-4902-a5a3-415298a3b7b6";
	const APP_SECRET        = "5a60bb71-e1c2-445e-a40b-f11859a10a41";
	const REAL_REQUEST_URL  = 'https://sandbox.hscloud.cn/quote/v1/real';
    const KLINE_REQUEST_URL = 'https://sandbox.hscloud.cn/quote/v1/kline';
    const TREND_REQUEST_URL = 'https://sandbox.hscloud.cn/quote/v1/trend';
    const TOKEN_URL         = "https://sandbox.hscloud.cn/oauth2/oauth2/token";

    public function auth()
    {
        $access_token_text = file_get_contents("auth_access_token.json");
        $access_token_text = json_decode($access_token_text, true);
        if($access_token_text['exptime'] > time())
        {
            return $access_token_text['access_token'];
        }
        $url = self::TOKEN_URL;
        $param = [
            'grant_type' => "client_credentials",//客户端凭证模式时，必须为client_credentials；刷新访问令牌时，则必须为refresh_token
        ];
        $headers = [];
        //根据阿里云要求，定义Appcode
        array_push($headers, "Authorization: Basic ".base64_encode(self::APP_KEY.':'.self::APP_SECRET));
        array_push($headers, "Content-Type".":"."application/x-www-form-urlencoded; charset=UTF-8");

        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_HTTP_VERSION , CURL_HTTP_VERSION_1_1 );
        curl_setopt( $ch, CURLOPT_USERAGENT , 'open.hscloud.cn' );
        curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT , 300 );
        curl_setopt( $ch, CURLOPT_TIMEOUT , 300);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER , true );
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt( $ch , CURLOPT_POST , true );
        curl_setopt( $ch , CURLOPT_POSTFIELDS , http_build_query($param) );
        curl_setopt( $ch , CURLOPT_URL , $url );

        $response = curl_exec( $ch );
        if ($response === FALSE) {
            // echo "cURL Error: " . curl_error($ch);
            return false;
        }
        curl_close( $ch );
        $response = json_decode($response, true);

        file_put_contents("auth_access_token.json", json_encode(['access_token' => $response['access_token'], 'exptime' => time()+$response['expires_in']-600]), FILE_APPEND);
        return $response['access_token'];;
    }
	
	public function realtime($code, $field = null){
		$field = $field ? : "prod_code,prod_name,data_timestamp,open_px,high_px,low_px,last_px,preclose_px,business_amount,business_balance,offer_grp,bid_grp,px_change,px_change_rate,circulation_value,pe_rate,amplitude,business_amount_in,business_amount_out,total_shares";
		$data = [
			'en_prod_code'	=> $code,//接收手机号
			'fields'		=> $field,//模板变量，多个以英文逗号隔开
            'access_token'  => self::auth(),
		];
		$response = $this->reponse_url(self::REAL_REQUEST_URL, $data, 0);
		return json_decode($response, true);
	}

    public function kline($code, $period = 6, $count = 50, $type = 'offset')
    {
        $data = [
            'get_type'      => $type,//查找类别	offset 按偏移查找；range 按日期区间查找；必须输入其中一个值
            'prod_code'     => $code,
            'candle_period' => $period,//K线周期	取值可以是数字1-9，表示含义如下： 1：1分钟K线 2：5分钟K线 3：15分钟K线 4：30分钟K线 5：60分钟K线 6：日K线 7：周K线 8：月K线 9：年K线
            'fields'        => 'open_px,high_px,low_px,close_px,business_amount,business_balance',
            'data_count'    => $count,
            'access_token'  => self::auth(),
        ];
        $response = $this->reponse_url(self::KLINE_REQUEST_URL, $data, 0);
        return json_decode($response, true);
    }

    public function trend($code, $crc = '', $min = '')
    {
        $data = [
            'prod_code' => $code,
            'fields' => 'last_px,avg_px,business_amount',
            'crc' => $crc,
            'min_time' => $min,
            'access_token'  => self::auth(),
        ];
        $response = $this->reponse_url(self::TREND_REQUEST_URL, $data, 0);
        return json_decode($response, true);
    }
	
	public function reponse_url($url, $data=false, $ispost=0){
		$headers = [];
		//根据阿里云要求，定义Appcode
        $token = self::auth();
		array_push($headers, "Authorization:Bearer " . $token);
		array_push($headers, "Content-Type".":"."application/x-www-form-urlencoded; charset=UTF-8");
  
		$httpInfo = [];
		 
		$ch = curl_init();
  		curl_setopt( $ch, CURLOPT_HTTP_VERSION , CURL_HTTP_VERSION_1_1 );
		curl_setopt( $ch, CURLOPT_USERAGENT , 'open.hscloud.cn' );
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT , 300 );
		curl_setopt( $ch, CURLOPT_TIMEOUT , 300);
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER , true );
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	    curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		if($ispost){
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
			curl_setopt( $ch , CURLOPT_POST , true );
			curl_setopt( $ch , CURLOPT_POSTFIELDS , http_build_query($data) );
			curl_setopt( $ch , CURLOPT_URL , $url );
		}else{
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
			if($data){
				curl_setopt( $ch , CURLOPT_URL , $url.'?'.urldecode(http_build_query($data)) );
			 
			}else{
				curl_setopt( $ch , CURLOPT_URL , $url);
			}
		}
		$response = curl_exec( $ch );
		if ($response === FALSE) {
			// echo "cURL Error: " . curl_error($ch);
			return false;
		}
		$httpCode = curl_getinfo( $ch , CURLINFO_HTTP_CODE );
		$httpInfo = array_merge( $httpInfo , curl_getinfo( $ch ) );
		curl_close( $ch );
		return $response;
	}
}
